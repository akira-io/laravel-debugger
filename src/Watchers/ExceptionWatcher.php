<?php

declare(strict_types=1);

namespace Akira\Debugger\Watchers;

use Akira\Debugger\Debugger;
use Closure;
use Exception;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Log\Events\MessageLogged;
use Illuminate\Support\Facades\Event;
use Spatie\Ray\Settings\Settings;

final class ExceptionWatcher extends Watcher
{
    public function register(): void
    {
        $settings = app(Settings::class);

        $this->enabled = $settings->send_exceptions_to_ray;

        Event::listen(MessageLogged::class, function (MessageLogged $message): void {
            if (! $this->enabled()) {
                return;
            }

            if (! $this->concernsException($message)) {
                return;
            }

            $exception = $message->context['exception'];

            $meta = $this->collectMetaData();

            $debugger = app(Debugger::class);

            $debugger->exception($exception, $meta);
        });
    }

    public function concernsException(MessageLogged $messageLogged): bool
    {
        if (! isset($messageLogged->context['exception'])) {
            return false;
        }

        return $messageLogged->context['exception'] instanceof Exception;
    }

    protected function getRequestAndRouteContext(): array
    {
        return [
            'request_headers' => $this->getRequestHeaders(),
            'application_route' => $this->getApplicationRouteContext(),
            'application_route_parameters' => $this->getApplicationRouteParameters(),
        ];
    }

    protected function getRequestHeaders(): array
    {
        return array_map(function (array $header) {
            return implode(', ', $header);
        }, request()->headers->all());
    }

    protected function getApplicationRouteContext(): array
    {
        $route = request()->route();

        return $route ? array_filter([
            'controller' => $route->getActionName(),
            'route name' => $route->getName() ?: null,
            'middleware' => implode(', ', array_map(function ($middleware) {
                return $middleware instanceof Closure ? 'Closure' : $middleware;
            }, $route->gatherMiddleware())),
        ]) : [];
    }

    protected function getApplicationRouteParameters(): ?string
    {
        $route = request()->route();

        $parameters = $route ? $route->parameters() : null;

        return $parameters ? json_encode(array_map(
            fn ($value) => $value instanceof Model ? $value->withoutRelations() : $value,
            $parameters
        ), JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT) : null;
    }

    private function collectMetaData(): array
    {
        $meta = [];

        if (! app()->has(Request::class)) {
            return $meta;
        }

        /** @var Request $request */
        $request = app(Request::class);

        $headers = collect($request->headers->all())
            ->map(fn (array $header): ?string => $header[0])
            ->toArray();
        $meta['request_headers'] = $headers;

        if ($request->route()) {
            $meta['application_route'] = [
                'route name' => $request->route()->getName(),
                'controller' => $request->route()->getActionName(),
                'middleware' => array_values($request->route()->gatherMiddleware()),
            ];

            $meta['application_route_parameters'] = array_values($request->route()->parameters());
        } else {
            $meta['application_route'] = null;
        }

        return $meta;
    }
}
