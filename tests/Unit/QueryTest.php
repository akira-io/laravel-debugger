<?php

declare(strict_types=1);

use Akira\Debugger\Tests\TestClasses\User;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;

it('can start logging queries', function () {
    ad()->showQueries();

    DB::table('users')->get('id');

    expect($this->client->sentRequests())->toHaveCount(1);
});

it('can start logging queries using alias', function () {
    ad()->queries();

    DB::table('users')->get('id');

    expect($this->client->sentRequests())->toHaveCount(1);
});

it('can stop logging queries', function () {
    ad()->showQueries();

    DB::table('users')->get('id');
    DB::table('users')->get('id');
    expect($this->client->sentRequests())->toHaveCount(2);

    ad()->stopShowingQueries();
    DB::table('users')->get('id');
    expect($this->client->sentRequests())->toHaveCount(2);
});

it('calling log queries twice will not log all queries twice', function () {
    ad()->showQueries();
    ad()->showQueries();

    DB::table('users')->get('id');

    expect($this->client->sentRequests())->toHaveCount(1);
});

it('can log all queries in a callable', function () {
    ad()->showQueries(function () {
        // will be logged
        DB::table('users')->where('id', 1)->get();
    });
    expect($this->client->sentRequests())->toHaveCount(1);

    // will not be logged
    DB::table('users')->get('id');
    expect($this->client->sentRequests())->toHaveCount(1);
});

it('can log all queries in a callable and gets results', function () {
    $results = ad()->showQueries(function (): Illuminate\Support\Collection {
        // will be logged
        return DB::table('users')->where('id', 1)->get();
    });
    expect($this->client->sentRequests())->toHaveCount(1)
        ->and($results)->toBeInstanceOf(Illuminate\Support\Collection::class)
        ->and($results->count())->toEqual(0);
});

it('show queries can be colorized', function () {
    $this->useRealUuid();

    ad()->showQueries()->green();

    DB::table('users')->where('id', 1)->get();

    $sentPayloads = $this->client->sentRequests();
    expect($sentPayloads)->toHaveCount(2)
        ->and($sentPayloads[1]['uuid'])->toEqual($sentPayloads[0]['uuid'])
        ->and($sentPayloads[0]['uuid'])->not->toEqual('fakeUuid');
});

it('can count the amount of executed queries', function () {
    ad()->countQueries(function () {
        DB::table('users')->get('id');
        DB::table('users')->get('id');
        DB::table('users')->get('id');
    });

    expect($this->client->sentRequests())->toHaveCount(1);

    $payload = $this->client->sentRequests()[0];

    expect(Arr::get($payload, 'payloads.0.content.values.Count'))->toEqual(3);
});

it('an eloquent query can be sent to ray', function () {
    User::create(['email' => 'john@example.com']);

    $user = User::query()->where('email', 'john@example.com')->ray()->first();

    expect($this->client->sentPayloads())->toHaveCount(1);

    $payload = $this->client->sentPayloads()[0];

    expect(Arr::get($payload, 'type'))->toEqual('executed_query');

    expect($user)->toBeInstanceOf(User::class);
});
