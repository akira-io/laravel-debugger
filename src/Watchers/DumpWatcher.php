<?php

declare(strict_types=1);

namespace Akira\Debugger\Watchers;

use Akira\Debugger\DumpRecorder\DumpRecorder;
use Spatie\Ray\Settings\Settings;

final class DumpWatcher extends Watcher
{
    public function register(): void
    {
        $settings = app(Settings::class);

        $this->enabled = $settings->send_dumps_to_ray;

        app(DumpRecorder::class)->register();
    }
}
