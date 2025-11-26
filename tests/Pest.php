<?php

declare(strict_types=1);

use Akira\Debugger\Tests\TestCase;
use Illuminate\Support\Facades\Context;

uses(TestCase::class)->in('.');

function assertMatchesOsSafeSnapshot($data): void
{
    $json = json_encode($data);
    $json = str_replace('D:\\\\a\\\\laravel-debugger\\\\laravel-debugger', '', $json);
    $json = str_replace('\\\\', '/', $json);

    expect($json)->toMatchSnapshot();
}

function contextSupported(): bool
{
    return class_exists(Context::class);
}
