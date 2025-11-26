<?php

declare(strict_types=1);

use Akira\Debugger\AkiraServiceProvider;
use Akira\Debugger\Tests\TestClasses\TestMailable;
use Akira\Debugger\Tests\TestClasses\User;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Facade;
use Illuminate\Support\Facades\Log;
use Spatie\Ray\Ray;
use Spatie\Ray\Settings\Settings;

it('when disabled nothing will be sent to ray', function (): void {
    app(Settings::class)->enable = false;

    ad('test');

    // re-enable for next tests
    ad()->enable();

    expect($this->client->sentRequests())->toHaveCount(0);
});

it('will send logs to ray by default', function (): void {
    Log::info('hey');

    expect($this->client->sentRequests())->toHaveCount(1);
});

it('can disable deprecated notices', function (): void {
    Log::warning('Deprecated');
    Log::warning('deprecated');

    expect($this->client->sentRequests())->toHaveCount(0);
});

it('can enable deprecated notices', function (): void {
    app(Settings::class)->send_deprecated_notices_to_ray = true;

    Log::warning('Deprecated');
    Log::warning('deprecated');

    expect($this->client->sentRequests())->toHaveCount(4);
});

it('will not send dumps to ray when disabled', function (): void {
    app(Settings::class)->send_dumps_to_ray = false;

    dump('');

    expect($this->client->sentRequests())->toHaveCount(0);
});

it('will send dumps to ray by default', function (): void {
    dump('akira');

    expect($this->client->sentRequests())->toHaveCount(1);
});

it('will not send logs to ray when disabled', function (): void {
    app(Settings::class)->send_log_calls_to_ray = false;

    Log::info('hey');

    expect($this->client->sentRequests())->toHaveCount(0);
});

it('will not blow up when not passing anything', function (): void {
    ad();

    expect($this->client->sentRequests())->toHaveCount(0);
});

it('can be disabled', function (): void {
    ad()->disable();
    ad('test');
    expect($this->client->sentRequests())->toHaveCount(0);

    ad()->enable();
    ad('not test');
    expect($this->client->sentRequests())->toHaveCount(1);
});

it('can check enabled status', function (): void {
    ad()->disable();
    expect(ad()->enabled())->toEqual(false);

    ad()->enable();
    expect(ad()->enabled())->toEqual(true);
});

it('can check disabled status', function (): void {
    ad()->disable();
    expect(ad()->disabled())->toEqual(true);

    ad()->enable();
    expect(ad()->disabled())->toEqual(false);
});

it('can replace the remote path with the local one', function (): void {
    $settings = app(Settings::class);

    $settings->remote_path = __DIR__;
    $settings->local_path = 'local_tests';

    ad('test');

    expect(Arr::get($this->client->sentRequests(), '0.payloads.0.origin.file'))->toContain('local_tests');
});

it('will automatically use specialized payloads', function (): void {
    ad(new TestMailable, new User);

    $payloads = $this->client->sentRequests();

    expect($payloads[0]['payloads'][0]['type'])->toEqual('mailable');
    expect($payloads[0]['payloads'][1]['type'])->toEqual('eloquent_model');
});

it('sends an environment payload', function (): void {
    ad()->env([], __DIR__.'/stubs/dotenv.env');

    $payloads = $this->client->sentRequests();

    expect($payloads[0]['payloads'][0]['type'])->toEqual('table')
        ->and($payloads[0]['payloads'][0]['content']['label'])->toEqual('.env')
        ->and($payloads[0]['payloads'][0]['content']['values']['APP_ENV'])->toEqual('local')
        ->and($payloads[0]['payloads'][0]['content']['values']['DB_DATABASE'])->toEqual('ad_test')
        ->and($payloads[0]['payloads'][0]['content']['values']['SESSION_LIFETIME'])->toEqual('120')
        ->and(count($payloads[0]['payloads'][0]['content']['values']))->toBeGreaterThanOrEqual(16);
});

it('sends a filtered environment payload', function (): void {
    ad()->env(['APP_ENV', 'DB_DATABASE'], __DIR__.'/stubs/dotenv.env');

    $payloads = $this->client->sentRequests();

    expect($payloads[0]['payloads'][0]['type'])->toEqual('table')
        ->and($payloads[0]['payloads'][0]['content']['label'])->toEqual('.env')
        ->and($payloads[0]['payloads'][0]['content']['values']['APP_ENV'])->toEqual('local')
        ->and($payloads[0]['payloads'][0]['content']['values']['DB_DATABASE'])->toEqual('ad_test')
        ->and($payloads[0]['payloads'][0]['content']['values'])->toHaveCount(2);
});

it('the project name will automatically be set if it something other than laravel', function (): void {
    new AkiraServiceProvider($this->app)->setProjectName();

    expect(Ray::$projectName)->toEqual('Debugger');

    config()->set('app.name', 'my-project');

    new AkiraServiceProvider($this->app)->setProjectName();

    expect(Ray::$projectName)->toEqual('Debugger');
});

it('still boots and works although the DB facade has not been bound', function (): void {
    unset($this->app['db']);
    Facade::clearResolvedInstance('db');

    new AkiraServiceProvider($this->app)->boot();

    ad('foo');

    expect($this->client->sentRequests())->toHaveCount(1);
});
