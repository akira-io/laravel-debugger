<?php

declare(strict_types=1);

namespace Akira\Debugger\Watchers;

use Akira\Debugger\Debugger;
use Exception;
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

            /** @var Debugger $debugger */
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
