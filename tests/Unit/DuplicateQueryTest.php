<?php

declare(strict_types=1);

namespace Akira\Debugger\Tests\Unit;

use Akira\Debugger\Tests\TestClasses\User;
use DateTimeImmutable;
use Illuminate\Support\Facades\DB;

it('can start logging duplicate queries', function () {
    ad()->showDuplicateQueries();

    DB::table('users')->get();

    expect($this->client->sentRequests())->toHaveCount(0);

    DB::table('users')->get('id');

    expect($this->client->sentRequests())->toHaveCount(0);

    DB::table('users')->get();

    expect($this->client->sentRequests())->toHaveCount(1);
});

it('ignores queries with different bindings', function () {
    ad()->showDuplicateQueries();

    DB::table('users')->where('id', 1)->get();
    DB::table('users')->where('id', 2)->get();

    expect($this->client->sentRequests())->toHaveCount(0);

    DB::table('users')->where('id', 1)->get();

    expect($this->client->sentRequests())->toHaveCount(1);

    DB::table('users')->where('id', 1)->get();

    expect($this->client->sentRequests())->toHaveCount(2);
});

it('can stop logging duplicate queries', function () {
    ad()->showDuplicateQueries();

    DB::table('users')->get('id');
    DB::table('users')->get('id');
    expect($this->client->sentRequests())->toHaveCount(1);

    ad()->stopShowingDuplicateQueries();
    DB::table('users')->get('id');
    expect($this->client->sentRequests())->toHaveCount(1);
});

it('can log all duplicate queries in a callable', function () {
    ad()->showDuplicateQueries(function () {
        // will be logged
        DB::table('users')->where('id', 1)->get();
        DB::table('users')->where('id', 1)->get();
    });
    expect($this->client->sentRequests())->toHaveCount(1);

    // will not be logged
    DB::table('users')->where('id', 1)->get();
    expect($this->client->sentRequests())->toHaveCount(1);
});

it('eloquent duplicate queries are sent to ray', function () {
    ad()->showDuplicateQueries();

    User::create(['email' => 'john@example.com']);
    User::create(['email' => 'john@example.com']);

    expect($this->client->sentPayloads())->toHaveCount(1);
});

it('can log duplicated queries with datetime parameters', function () {
    ad()->showDuplicateQueries();

    DB::table('users')->where('created_at', '<', new DateTimeImmutable(now()->toString()))->get();
    DB::table('users')->where('created_at', '<', new DateTimeImmutable(now()->toString()))->get();

    expect($this->client->sentRequests())->toHaveCount(1);
});
