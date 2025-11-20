<?php

declare(strict_types=1);

namespace Akira\Debugger\Watchers;

use Akira\Debugger\Debugger;
use Illuminate\Support\Facades\Event;
use Spatie\Ray\Settings\Settings;

final class ViewWatcher extends Watcher
{
    public function register(): void
    {
        $settings = app(Settings::class);

        $this->enabled = $settings->send_views_to_ray;

        Event::listen('composing:*', function ($event, $data): void {
            if (! $this->enabled()) {
                return;
            }

            /** @var \Illuminate\View\View $view */
            $view = $data[0];

            $ray = app(Debugger::class)->view($view);

            optional($this->rayProxy)->applyCalledMethods($ray);
        });
    }
}
