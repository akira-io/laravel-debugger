<?php

declare(strict_types=1);

namespace Akira\Debugger\DumpRecorder;

class MultiDumpHandler
{
    protected array $handlers = [];

    public function dump($value): void
    {
        foreach ($this->handlers as $handler) {
            $handler($value);
        }
    }

    public function addHandler(?callable $callable = null): self
    {
        $this->handlers[] = $callable;

        return $this;
    }

    public function resetHandlers(): void
    {
        $this->handlers = [];
    }
}
