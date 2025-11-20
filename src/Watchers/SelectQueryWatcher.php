<?php

declare(strict_types=1);

namespace Akira\Debugger\Watchers;

use Illuminate\Database\Events\QueryExecuted;
use Illuminate\Support\Str;
use Spatie\Ray\Settings\Settings;

final class SelectQueryWatcher extends ConditionalQueryWatcher
{
    public function register(): void
    {
        $settings = app(Settings::class);

        $this->enabled = $settings->send_select_queries_to_ray ?? false;

        $this->setConditionalCallback(fn (QueryExecuted $query) => Str::startsWith(mb_strtolower($query->sql), 'select'));
    }
}
