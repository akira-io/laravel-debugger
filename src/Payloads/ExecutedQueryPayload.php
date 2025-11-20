<?php

declare(strict_types=1);

namespace Akira\Debugger\Payloads;

use Illuminate\Database\Events\QueryExecuted;
use Spatie\Ray\Payloads\Payload;

final class ExecutedQueryPayload extends Payload
{
    public function __construct(private readonly QueryExecuted $query) {}

    public function getType(): string
    {
        return 'executed_query';
    }

    public function getContent(): array
    {
        $grammar = $this->query->connection->getQueryGrammar();

        $properties = method_exists($grammar, 'substituteBindingsIntoRawSql') ? [
            'sql' => $grammar->substituteBindingsIntoRawSql(
                $this->query->sql,
                $this->query->connection->prepareBindings($this->query->bindings)
            ),
        ] : [
            'sql' => $this->query->sql,
            'bindings' => $this->query->bindings,
        ];

        if ($this->hasAllProperties()) {
            return array_merge($properties, [
                'connection_name' => $this->query->connectionName,
                'time' => $this->query->time,
            ]);
        }

        return $properties;
    }

    private function hasAllProperties(): bool
    {
        return ! is_null($this->query->time);
    }
}
