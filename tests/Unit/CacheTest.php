<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Cache;

it('can detect when something gets cached', function (): void {
    ad()->showCache();

    Cache::put('cached-key', 'cached-value');

    assertMatchesOsSafeSnapshot($this->client->sentRequests());
});

it('will not report caches by default', function (): void {
    Cache::put('cached-key', 'cached-value');

    expect($this->client->sentRequests())->toHaveCount(0);
});

it('the cache watcher can be disabled', function (): void {
    ad()->showCache();

    Cache::put('cached-key', 'cached-value');

    ad()->stopShowingCache();

    Cache::put('another-key', 'another-value');

    expect($this->client->sentRequests())->toHaveCount(1);
});

it('can detect when the cache is hit', function (): void {
    ad()->showCache();

    Cache::put('cached-key', 'cached-value');

    Cache::get('cached-key');

    assertMatchesOsSafeSnapshot($this->client->sentRequests());
});

it('can detect when the cache is missed', function (): void {
    ad()->showCache();

    Cache::get('cached-key');

    assertMatchesOsSafeSnapshot($this->client->sentRequests());
});

it('can detect when something gets temporarily cached', function (): void {
    ad()->showCache();

    Cache::put('cached-key', 'cached-value', 10);

    assertMatchesOsSafeSnapshot($this->client->sentRequests());
});

it('can detect when something is cleared from the cache', function (): void {
    ad()->showCache();

    Cache::put('cached-key', 'cached-value', 10);

    Cache::pull('cached-key');

    assertMatchesOsSafeSnapshot($this->client->sentRequests());
});
