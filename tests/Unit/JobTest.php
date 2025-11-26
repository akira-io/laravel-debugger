<?php

declare(strict_types=1);

use Akira\Debugger\Tests\TestClasses\TestJob;
use Illuminate\Support\Arr;

it('can automatically send jobs to ray', function () {
    ad()->showJobs();

    dispatch(new TestJob);

    ad()->stopShowingJobs();

    dispatch(new TestJob);

    expect(Arr::get($this->client->sentRequests(), '0.payloads.0.type'))->toEqual('job_event')
        ->and($this->client->sentRequests())->toHaveCount(2);
});

it('show jobs can be colorized', function () {
    $this->useRealUuid();

    ad()->showJobs()->green();

    dispatch(new TestJob);

    $sentPayloads = $this->client->sentRequests();

    expect($sentPayloads)->toHaveCount(4)
        ->and($sentPayloads[1]['uuid'])->toEqual($sentPayloads[0]['uuid'])
        ->and($sentPayloads[0]['uuid'])->not->toEqual('fakeUuid');
});
