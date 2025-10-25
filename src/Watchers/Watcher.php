<?php

declare(strict_types=1);

namespace Akira\Debugger\Watchers;

use Akira\Debugger\DebuggerProxy;

abstract class Watcher
{
    protected bool $enabled = false;

    protected $rayProxy;

    abstract public function register(): void;

    public function enabled(): bool
    {
        return $this->enabled;
    }

    public function enable(): self
    {
        $this->enabled = true;

        return $this;
    }

    public function disable(): self
    {
        $this->enabled = false;

        return $this;
    }

    public function setRayProxy(DebuggerProxy $rayProxy): self
    {
        $this->rayProxy = $rayProxy;

        return $this;
    }
}
