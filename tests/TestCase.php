<?php

declare(strict_types=1);

namespace Akira\Debugger\Tests;

use Akira\Debugger\AkiraServiceProvider;
use Akira\Debugger\Debugger;
use Akira\Debugger\Tests\TestClasses\FakeClient;
use Illuminate\Database\Query\Builder;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Str;
use Orchestra\Testbench\TestCase as Orchestra;
use ReflectionException;
use Spatie\Ray\Origin\Hostname;
use Spatie\Ray\Settings\Settings;

class TestCase extends Orchestra
{
    protected FakeClient $client;

    protected function setUp(): void
    {
        parent::setUp();

        $this->client = new FakeClient;

        $this->app->bind(Debugger::class, function () {
            $settings = app(Settings::class);

            $ad = new Debugger($settings, $this->client, 'fakeUuid');

            if (! $settings->enable) {
                $ad->disable();
            }

            return $ad;
        });

        Hostname::set('fake-hostname');

        View::addLocation(__DIR__.'/resources/views');
    }

    public function getEnvironmentSetUp($app): void
    {
        config()->set('database.default', 'sqlite');
        config()->set('database.connections.sqlite', [
            'driver' => 'sqlite',
            'database' => ':memory:',
            'prefix' => '',
        ]);

        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('email')->nullable();
        });
    }

    protected function getPackageProviders($app): array
    {
        return [
            AkiraServiceProvider::class,
        ];
    }

    /**
     * @throws ReflectionException
     */
    protected function useRealUuid(): void
    {
        $this->app->bind(Debugger::class, function () {
            Debugger::$fakeUuid = null;

            return Debugger::create($this->client);
        });
    }

    protected function assertSqlContains($queryContent, $needle): void
    {
        $sql = method_exists(Builder::class, 'toRawSql')
            ? $queryContent['sql']
            : Str::replaceArray('?', $queryContent['bindings'], $queryContent['sql']);

        $this->assertStringContainsString($needle, $sql);
    }
}
