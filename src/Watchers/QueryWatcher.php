<?php

declare(strict_types=1);

namespace Akira\Debugger\Watchers;

use Akira\Debugger\Debugger;
use Akira\Debugger\Payloads\ExecutedQueryPayload;
use Illuminate\Database\Events\QueryExecuted;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;
use Spatie\Ray\Settings\Settings;

class QueryWatcher extends Watcher
{
    /** @var QueryExecuted[] */
    protected array $executedQueries = [];

    protected bool $keepExecutedQueries = false;

    protected bool $sendIndividualQueries = true;

    public function register(): void
    {
        $settings = app(Settings::class);

        $this->enabled = $settings->send_queries_to_ray;

        Event::listen(QueryExecuted::class, function (QueryExecuted $query) {
            if (! $this->enabled()) {
                return;
            }

            if ($this->keepExecutedQueries) {
                $this->executedQueries[] = $query;
            }

            if (! $this->sendIndividualQueries) {
                return;
            }

            $payload = new ExecutedQueryPayload($query);

            $ray = app(Debugger::class)->sendRequest($payload);

            optional($this->rayProxy)->applyCalledMethods($ray);
        });
    }

    public function enable(): self
    {
        if (app()->bound('db')) {
            collect(DB::getConnections())->each(function ($connection) {
                $connection->enableQueryLog();
            });
        }

        return parent::enable();
    }

    public function disable(): self
    {
        DB::disableQueryLog();

        return parent::disable();
    }

    public function keepExecutedQueries(): self
    {
        $this->keepExecutedQueries = true;

        return $this;
    }

    public function getExecutedQueries(): array
    {
        return $this->executedQueries;
    }

    public function sendIndividualQueries(): self
    {
        $this->sendIndividualQueries = true;

        return $this;
    }

    public function doNotSendIndividualQueries(): self
    {
        $this->sendIndividualQueries = false;

        return $this;
    }

    public function stopKeepingAndClearExecutedQueries(): self
    {
        $this->keepExecutedQueries = false;

        $this->executedQueries = [];

        return $this;
    }
}
