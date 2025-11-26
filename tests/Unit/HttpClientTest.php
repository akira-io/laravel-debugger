<?php

declare(strict_types=1);

use Akira\Debugger\Watchers\HttpClientWatcher;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Http;

beforeEach(function (): void {
    if (! HttpClientWatcher::supportedByLaravelVersion()) {
        $this->markTestSkipped('Tests require Laravel 8.45.0 or greater.');
    }

    Http::fake(
        [
            '*/ok*' => Http::response(['hello' => 'world'], 200, ['Content-Type' => 'application/json']),
            '*/not-found*' => Http::response(null, 404),
            '*/json*' => Http::response(['foo' => 'bar']),
        ]
    );
});

it('can listen to http client requests', function (): void {
    ad()->showHttpClientRequests();

    Http::get('test.com/ok', ['hello' => 'world']);

    expect(Arr::get($this->client->sentRequests(), '0.payloads.0.content.values')['URL'])
        ->toEqual('test.com/ok?hello=world')
        ->and(Arr::get($this->client->sentRequests(), '0.payloads.0.content.label'))->toEqual('Http');
});

it('can listen to http client responses', function (): void {
    ad()->showHttpClientRequests();

    Http::get('test.com/json');

    expect(Arr::get($this->client->sentRequests(), '1.payloads.0.content.values')['URL'])->toEqual('test.com/json')
        ->and(Arr::get($this->client->sentRequests(), '1.payloads.0.content.label'))->toEqual('Http');
});

it('can listen for non successful requests', function (): void {
    ad()->showHttpClientRequests();

    Http::get('test.com/not-found');

    expect(Arr::get($this->client->sentRequests(), '1.payloads.0.content.values')['Status'])->toEqual('404');
});

it('doesnt send a payload when disabled', function (): void {
    Http::get('test.com/not-found');

    expect($this->client->sentRequests())->toBeEmpty();
});

it('show http client can be colorized', function (): void {
    $this->useRealUuid();

    ad()->showHttpClientRequests()->green();

    Http::get('test.com/ok');

    $sentPayloads = $this->client->sentRequests();

    expect($sentPayloads)->toHaveCount(4)
        ->and($sentPayloads[1]['uuid'])->toEqual($sentPayloads[0]['uuid'])
        ->and($sentPayloads[0]['uuid'])->not->toEqual('fakeUuid'); // 2 for the request and 2 for the response.
});
