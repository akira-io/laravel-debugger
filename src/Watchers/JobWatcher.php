<?php

declare(strict_types=1);

namespace Akira\Debugger\Watchers;

use Akira\Debugger\Debugger;
use Akira\Debugger\Payloads\JobEventPayload;
use Illuminate\Queue\Events\JobFailed;
use Illuminate\Queue\Events\JobProcessed;
use Illuminate\Queue\Events\JobProcessing;
use Illuminate\Queue\Events\JobQueued;
use Illuminate\Support\Facades\Event;
use Spatie\Ray\Settings\Settings;

final class JobWatcher extends Watcher
{
    public function register(): void
    {
        $settings = app(Settings::class);

        $this->enabled = $settings->send_jobs_to_ray;

        Event::listen([
            JobQueued::class,
            JobProcessing::class,
            JobProcessed::class,
            JobFailed::class,
        ], function (object $event) {
            if (! $this->enabled()) {
                return;
            }

            $payload = new JobEventPayload($event);

            $ray = app(Debugger::class)->sendRequest($payload);

            optional($this->rayProxy)->applyCalledMethods($ray);
        });
    }
}
