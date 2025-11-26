<?php

declare(strict_types=1);

namespace Spatie\LaravelRay\Tests\TestClasses;

class TestEventWithParameter
{
    public function __construct(protected string $parameter) {}
}
