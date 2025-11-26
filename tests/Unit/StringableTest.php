<?php

declare(strict_types=1);

use Illuminate\Support\Str;
use Illuminate\Support\Stringable;

it('has a chainable stringable macro to send things to ray', function (): void {
    $str = new Stringable('Lorem');

    $str = $str->append(' Ipsum')->ray()->append(' Dolor Sit Amen');

    expect($str)->toBeInstanceOf(Stringable::class);
    expect((string) $str)->toBe('Lorem Ipsum Dolor Sit Amen');

    expect($this->client->sentRequests())->toHaveCount(1);
});

it('has a chainable str macro to send things to ray', function (): void {
    $str = Str::of('Lorem')->append(' Ipsum')->ray()->append(' Dolor Sit Amen');

    expect($str)->toBeInstanceOf(Stringable::class);
    expect((string) $str)->toBe('Lorem Ipsum Dolor Sit Amen');

    expect($this->client->sentRequests())->toHaveCount(1);
});
