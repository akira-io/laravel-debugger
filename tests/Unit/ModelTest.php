<?php

declare(strict_types=1);

use Akira\Debugger\Tests\TestClasses\User;

it('can send one model to ray', function (): void {
    $user = User::make(['email' => 'john@example.com']);

    ad()->model($user);

    expect($this->client->sentRequests())->toHaveCount(1);
});

it('can send multiple models to ray', function (): void {
    $user1 = User::make(['email' => 'john@example.com']);
    $user2 = User::make(['email' => 'paul@example.com']);

    ad()->model($user1, $user2);
    expect($this->client->sentRequests())->toHaveCount(2);
});

it('can send a single models to ray using models', function (): void {
    $user = User::make(['email' => 'john@example.com']);

    ad()->models($user);

    expect($this->client->sentRequests())->toHaveCount(1);
});

it('can send a collection of models to ray using models', function (): void {
    $user1 = User::make(['email' => 'john@example.com']);
    $user2 = User::make(['email' => 'paul@example.com']);

    ad()->models(collect([$user1, $user2]));

    expect($this->client->sentRequests())->toHaveCount(2);
});
