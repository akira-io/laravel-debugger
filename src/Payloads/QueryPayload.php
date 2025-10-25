<?php

declare(strict_types=1);

namespace Akira\Debugger\Payloads;

use Illuminate\Database\Query\Builder;
use Spatie\Ray\Payloads\Payload;

class QueryPayload extends Payload
{
    protected Builder $query;

    public function __construct(Builder $query)
    {
        $this->query = $query;
    }

    public function getType(): string
    {
        return 'executed_query';
    }

    public function getContent(): array
    {
        if (method_exists($this->query, 'toRawSql')) {
            return [
                'sql' => $this->query->toRawSql(),
            ];
        }

        return [
            'sql' => $this->query->toSql(),
            'bindings' => $this->query->getBindings(),
        ];
    }
}
