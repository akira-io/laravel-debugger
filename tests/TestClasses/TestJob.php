<?php

declare(strict_types=1);

namespace Akira\Debugger\Tests\TestClasses;

use Illuminate\Contracts\Queue\ShouldQueue;

class TestJob implements ShouldQueue
{
    public function handle() {}
}
