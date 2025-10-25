<?php

declare(strict_types=1);

namespace Akira\Debugger\Watchers;

use Illuminate\Database\Events\QueryExecuted;
use Illuminate\Support\Str;
use Spatie\Ray\Settings\Settings;

final class UpdateQueryWatcher extends ConditionalQueryWatcher
{
    public function register(): void
    {
        $settings = app(Settings::class);

        $this->enabled = $settings->send_update_queries_to_ray ?? false;

        $this->setConditionalCallback(function (QueryExecuted $query) {
            return Str::startsWith(mb_strtolower($query->sql), 'update');
        });
    }
}
