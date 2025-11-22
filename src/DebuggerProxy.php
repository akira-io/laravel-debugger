<?php

declare(strict_types=1);

namespace Akira\Debugger;

final class DebuggerProxy
{
    private array $methodsCalled = [];

    public function __call(string $method, array $arguments)
    {
        $this->methodsCalled[] = ['method' => $method, 'arguments' => $arguments];
    }

    public function applyCalledMethods(Debugger $debugger): void
    {
        foreach ($this->methodsCalled as $methodCalled) {
            call_user_func_array([$debugger, $methodCalled['method']], $methodCalled['arguments']);
        }
    }
}
