<?php

declare(strict_types=1);

namespace Akira\Debugger\Watchers;

use Akira\Debugger\Debugger;
use Exception;
use Illuminate\Http\Client\Events\RequestSending;
use Illuminate\Http\Client\Events\ResponseReceived;
use Illuminate\Http\Client\Request;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Event;
use Spatie\Ray\Payloads\TablePayload;
use Spatie\Ray\Settings\Settings;

final class HttpClientWatcher extends Watcher
{
    public static function supportedByLaravelVersion(): bool|int
    {
        return version_compare(app()->version(), '8.46.0', '>=');
    }

    public function register(): void
    {
        if (in_array(self::supportedByLaravelVersion(), [false, 0], true)) {
            return;
        }

        $settings = app(Settings::class);

        $this->enabled = $settings->send_http_client_requests_to_ray;

        Event::listen(RequestSending::class, function (RequestSending $event): void {
            if (! $this->enabled()) {
                return;
            }

            $ray = $this->handleRequest($event->request);

            optional($this->rayProxy)->applyCalledMethods($ray);
        });

        Event::listen(ResponseReceived::class, function (ResponseReceived $event): void {
            if (! $this->enabled()) {
                return;
            }

            $ray = $this->handleResponse($event->request, $event->response);

            optional($this->rayProxy)->applyCalledMethods($ray);
        });
    }

    private function handleRequest(Request $request): \Spatie\Ray\Ray
    {
        $payload = new TablePayload([
            'Method' => $request->method(),
            'URL' => $request->url(),
            'Headers' => $request->headers(),
            'Data' => $request->data(),
            'Body' => $request->body(),
            'Type' => $this->getRequestType($request),
        ], 'Http');

        return app(Debugger::class)->sendRequest($payload);
    }

    private function getRequestType(Request $request): string
    {
        if ($request->isJson()) {
            return 'Json';
        }

        if ($request->isMultipart()) {
            return 'Multipart';
        }

        return 'Form';
    }

    /**
     * @throws Exception
     */
    private function handleResponse(Request $request, Response $response): \Spatie\Ray\Ray
    {
        $payload = new TablePayload([
            'URL' => $request->url(),
            'Real Request' => ! empty($response->handlerStats()),
            'Success' => $response->successful(),
            'Status' => $response->status(),
            'Headers' => $response->headers(),
            'Body' => rescue(fn () => $response->json(), $response->body(), false),
            'Cookies' => $response->cookies(),
            'Size' => $response->handlerStats()['size_download'] ?? null,
            'Connection time' => $response->handlerStats()['connect_time'] ?? null,
            'Duration' => $response->handlerStats()['total_time'] ?? null,
            'Request Size' => $response->handlerStats()['request_size'] ?? null,
        ], 'Http');

        return app(Debugger::class)->sendRequest($payload);
    }
}
