<?php

declare(strict_types=1);

it('can send the view payload', function () {
    ad()->showViews();

    view('test')->render();

    $payloads = $this->client->sentRequests();
    expect($payloads)->toHaveCount(1)
        ->and($payloads[0]['payloads'][0]['type'])->toEqual('view');
});

it('show views can be colorized', function () {
    $this->useRealUuid();

    ad()->showViews()->green();

    view('test')->render();

    $sentPayloads = $this->client->sentRequests();

    expect($sentPayloads)->toHaveCount(2)
        ->and($sentPayloads[1]['uuid'])->toEqual($sentPayloads[0]['uuid'])
        ->and($sentPayloads[0]['uuid'])->not->toEqual('fakeUuid');
});
