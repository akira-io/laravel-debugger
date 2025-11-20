<?php

declare(strict_types=1);

namespace Akira\Debugger\Watchers;

use Akira\Debugger\Debugger;
use Akira\Debugger\Payloads\EventPayload;
use Illuminate\Support\Facades\Event;

final class EventWatcher extends Watcher
{
    public function register(): void
    {
        Event::listen('*', function (string $eventName, array $arguments): void {
            if (! $this->enabled()) {
                return;
            }

            $payload = new EventPayload($eventName, $arguments);

            $ray = app(Debugger::class)->sendRequest($payload);

            optional($this->rayProxy)->applyCalledMethods($ray);
        });
    }
}
