<?php

declare(strict_types=1);

namespace Akira\Debugger\Watchers;

use Illuminate\Database\Events\QueryExecuted;
use Spatie\Ray\Settings\Settings;

final class SlowQueryWatcher extends ConditionalQueryWatcher
{
    private int $minimumTimeInMs = 500;

    public function register(): void
    {
        $settings = app(Settings::class);

        $this->enabled = $settings->send_slow_queries_to_ray ?? false;
        $this->minimumTimeInMs = $settings->slow_query_threshold_in_ms ?? $this->minimumTimeInMs;

        $this->setConditionalCallback(fn (QueryExecuted $query): bool => $query->time >= $this->minimumTimeInMs);
    }

    public function setMinimumTimeInMilliseconds(float|int $milliseconds): self
    {
        $this->minimumTimeInMs = $milliseconds;

        return $this;
    }
}
