<?php

declare(strict_types=1);

use Akira\Debugger\Tests\TestClasses\TestMailable;
use Akira\Debugger\Tests\TestClasses\User;
use Illuminate\Support\Arr;
use Spatie\Ray\Settings\Settings;

it('when disabled nothing will be sent to ray', function () {
    app(Settings::class)->enable = false;

    ad('test');

    ad()->enable();

    expect($this->client->sentRequests())->toHaveCount(0);
});

it('can be disabled', function () {
    ad()->disable();
    ad('test');
    expect($this->client->sentRequests())->toHaveCount(0);

    ad()->enable();
    ad('not test');
    expect($this->client->sentRequests())->toHaveCount(1);
});

it('will not blow up when not passing anything', function () {
    ad();

    expect($this->client->sentRequests())->toHaveCount(0);
});

it('can check enabled status', function () {
    ad()->disable();
    expect(ad()->enabled())->toEqual(false);

    ad()->enable();
    expect(ad()->enabled())->toEqual(true);
});

it('can check disabled status', function () {
    ad()->disable();
    expect(ad()->disabled())->toEqual(true);

    ad()->enable();
    expect(ad()->disabled())->toEqual(false);
});

it('can replace the remote path with the local one', function () {
    app(Settings::class)->remote_path = __DIR__;
    app(Settings::class)->local_path = 'local_tests';

    ad('test');

    expect(Arr::get($this->client->sentRequests(), '0.payloads.0.origin.file'))->toContain('local_tests');
});

it('will automatically use specialized payloads', function () {
    ad(new TestMailable(), new User);

    $payloads = $this->client->sentRequests();

    expect($payloads[0]['payloads'][0]['type'])->toEqual('mailable')
        ->and($payloads[0]['payloads'][1]['type'])->toEqual('eloquent_model');
});
