<?php

declare(strict_types=1);

namespace Akira\Debugger\Watchers;

use Akira\Debugger\Debugger;
use Akira\Debugger\Payloads\CachePayload;
use Illuminate\Cache\Events\CacheHit;
use Illuminate\Cache\Events\CacheMissed;
use Illuminate\Cache\Events\KeyForgotten;
use Illuminate\Cache\Events\KeyWritten;
use Spatie\Ray\Settings\Settings;

final class CacheWatcher extends Watcher
{
    public function register(): void
    {
        $settings = app(Settings::class);

        $this->enabled = $settings->send_cache_to_ray;

        app('events')->listen(CacheHit::class, function (CacheHit $event): void {
            if (! $this->enabled()) {
                return;
            }

            $payload = new CachePayload('Hit', $event->key, $event->tags, $event->value);

            $ray = $this->ray()->sendRequest($payload);

            optional($this->rayProxy)->applyCalledMethods($ray);
        });

        app('events')->listen(CacheMissed::class, function (CacheMissed $event): void {
            if (! $this->enabled()) {
                return;
            }

            $payload = new CachePayload('Missed', $event->key, $event->tags);

            $this->ray()->sendRequest($payload);
        });

        app('events')->listen(KeyWritten::class, function (KeyWritten $event): void {
            if (! $this->enabled()) {
                return;
            }

            $payload = new CachePayload(
                'Key written',
                $event->key,
                $event->tags,
                $event->value,
                $this->formatExpiration($event),
            );

            $this->ray()->sendRequest($payload);
        });

        app('events')->listen(KeyForgotten::class, function (KeyForgotten $event): void {
            if (! $this->enabled()) {
                return;
            }

            $payload = new CachePayload(
                'Key forgotten',
                $event->key,
                $event->tags
            );

            $this->ray()->sendRequest($payload);
        });
    }

    public function ray(): Debugger
    {
        return app(Debugger::class);
    }

    private function formatExpiration(KeyWritten $event): ?int
    {
        return $event->seconds;
    }
}
