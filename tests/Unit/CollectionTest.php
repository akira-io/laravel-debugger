<?php

declare(strict_types=1);

it('has a chainable collection macro to send things to ray', function (): void {
    $array = ['a', 'b', 'c'];

    $newArray = collect($array)->ray()->toArray();

    expect($array)->toEqual($newArray);

    expect($this->client->sentRequests())->toHaveCount(1);
});
