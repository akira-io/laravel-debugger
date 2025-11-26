<?php

declare(strict_types=1);

use Illuminate\Support\Facades\DB;

it('can start logging slow queries', function () {
    ad()->showSlowQueries(0);

    DB::table('users')->get('id');

    expect($this->client->sentRequests())->toHaveCount(1);
});

it('can start logging slow queries using alias', function () {
    ad()->slowQueries(0);

    DB::table('users')->get('id');

    expect($this->client->sentRequests())->toHaveCount(1);
});

it('can stop logging slow queries', function () {
    ad()->showSlowQueries(0);

    DB::table('users')->get('id');
    DB::table('users')->get('id');
    expect($this->client->sentRequests())->toHaveCount(2);

    ad()->stopShowingSlowQueries();
    DB::table('users')->get('id');
    expect($this->client->sentRequests())->toHaveCount(2);
});

it('calling log slow queries twice will not log all queries twice', function () {
    ad()->showSlowQueries(0);
    ad()->showSlowQueries(0);

    DB::table('users')->get('id');

    expect($this->client->sentRequests())->toHaveCount(1);
});

it('can log all slow queries in a callable', function () {
    ad()->showSlowQueries(0, function () {
        // will be logged
        DB::table('users')->where('id', 1)->get();
    });

    expect($this->client->sentRequests())->toHaveCount(1);

    // will not be logged
    DB::table('users')->get('id');
    expect($this->client->sentRequests())->toHaveCount(1);
});

it('show slow queries can be colorized', function () {
    $this->useRealUuid();

    ad()->showSlowQueries(0)->green();

    DB::table('users')->where('id', 1)->get();

    $sentPayloads = $this->client->sentRequests();

    expect($sentPayloads)->toHaveCount(2)
        ->and($sentPayloads[1]['uuid'])->toEqual($sentPayloads[0]['uuid'])
        ->and($sentPayloads[0]['uuid'])->not->toEqual('fakeUuid');
});
