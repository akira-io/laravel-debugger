<?php

declare(strict_types=1);

use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Route;

it('can listen to requests', function (): void {
    Route::get('test', fn (): string => 'ok');

    ad()->requests();

    $this->get('test');

    expect(Arr::get($this->client->sentRequests(), '0.payloads.0.content.values')['Response code'])->toEqual(200);
});

it('can listen to requests that return json', function (): void {
    Route::get('test-json', fn () => response()->json(['message' => 'ok']));

    ad()->requests();

    $this->get('test-json');

    expect(Arr::get($this->client->sentRequests(), '0.payloads.0.content.values')['Response code'])->toEqual(200);
});

it('can listen to requests that return text', function (): void {
    Route::get('test-text', fn (): Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response => response('ok', 200, ['content-type' => 'text/plain']));

    ad()->requests();

    $this->get('test-text');

    expect(Arr::get($this->client->sentRequests(), '0.payloads.0.content.values')['Response code'])->toEqual(200);
});

it('can listen to requests that return redirects', function (): void {
    Route::get('test-redirect', fn () => response()->redirectTo('/'));

    ad()->requests();

    $this->get('test-redirect');

    expect(Arr::get($this->client->sentRequests(), '0.payloads.0.content.values')['Response code'])->toEqual(302);
});

it('show request can be colorized', function (): void {
    $this->useRealUuid();

    ad()->showRequests()->green();

    $this->get('test-redirect');

    $sentPayloads = $this->client->sentRequests();

    expect($sentPayloads)->toHaveCount(2)
        ->and($sentPayloads[1]['uuid'])->toEqual($sentPayloads[0]['uuid'])
        ->and($sentPayloads[0]['uuid'])->not->toEqual('fakeUuid');
});
