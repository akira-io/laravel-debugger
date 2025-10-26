<?php

declare(strict_types=1);

use Akira\Debugger\Debugger;

if (! function_exists('ad')) {
    function ad(...$arguments): Debugger
    {
        if (! isset($arguments[0])) {
            return app(Debugger::class);
        }

        return app(Debugger::class)->send(...$arguments);
    }
}

if (! function_exists('debugAndDie')) {
    function debugAndDie(...$arguments): never
    {
        ad(...$arguments);
        exit(1);
    }
}
