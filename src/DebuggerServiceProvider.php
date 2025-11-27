<?php

declare(strict_types=1);

namespace Akira\Debugger;

use Akira\Debugger\Commands\CleanDebuggerCommand;
use Akira\Debugger\Commands\PublishDebuggerConfigCommand;
use Akira\Debugger\Payloads\MailablePayload;
use Akira\Debugger\Payloads\ModelPayload;
use Akira\Debugger\Payloads\QueryPayload;
use Akira\Debugger\Watchers\ApplicationLogWatcher;
use Akira\Debugger\Watchers\CacheWatcher;
use Akira\Debugger\Watchers\DeleteQueryWatcher;
use Akira\Debugger\Watchers\DeprecatedNoticeWatcher;
use Akira\Debugger\Watchers\DumpWatcher;
use Akira\Debugger\Watchers\DuplicateQueryWatcher;
use Akira\Debugger\Watchers\EventWatcher;
use Akira\Debugger\Watchers\ExceptionWatcher;
use Akira\Debugger\Watchers\HttpClientWatcher;
use Akira\Debugger\Watchers\InsertQueryWatcher;
use Akira\Debugger\Watchers\JobWatcher;
use Akira\Debugger\Watchers\MailWatcher;
use Akira\Debugger\Watchers\QueryWatcher;
use Akira\Debugger\Watchers\RequestWatcher;
use Akira\Debugger\Watchers\SelectQueryWatcher;
use Akira\Debugger\Watchers\SlowQueryWatcher;
use Akira\Debugger\Watchers\UpdateQueryWatcher;
use Akira\Debugger\Watchers\ViewWatcher;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Query\Builder;
use Illuminate\Mail\Mailable;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Stringable;
use Illuminate\Testing\TestResponse;
use Illuminate\View\Compilers\BladeCompiler;
use Spatie\Ray\Client;
use Spatie\Ray\PayloadFactory;
use Spatie\Ray\Payloads\Payload;
use Spatie\Ray\Settings\Settings;
use Spatie\Ray\Settings\SettingsFactory;

final class DebuggerServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this
            ->registerCommands()
            ->registerSettings()
            ->setProjectName()
            ->registerBindings()
            ->registerWatchers()
            ->registerMacros()
            ->registerBladeDirectives()
            ->registerPayloadFinder();
    }

    public function boot(): void
    {
        $this->bootWatchers();
        $this->registerPublishing();
    }

    public function setProjectName(): self
    {
        if (Debugger::$projectName === '') {
            $projectName = config('app.name');

            if ($projectName !== 'Laravel') {
                ad()->project($projectName);
            }
        }

        return $this;
    }

    private function registerPublishing(): void
    {
        if ($this->app->runningInConsole()) {
            // Publish configuration file
            $this->publishes([
                __DIR__.'/../config/debugger.php' => config_path('debugger.php'),
            ], 'debugger-config');

            // Publish all
            $this->publishes([
                __DIR__.'/../config/debugger.php' => config_path('debugger.php'),
            ], 'debugger');
        }
    }

    private function registerCommands(): self
    {
        $this->commands(PublishDebuggerConfigCommand::class);
        $this->commands(CleanDebuggerCommand::class);

        return $this;
    }

    private function registerSettings(): self
    {
        $this->app->singleton(Settings::class, function ($app) {
            $settings = SettingsFactory::createFromConfigFile($app->configPath());

            return $settings->setDefaultSettings([
                'enable' => env('DEBUGGER_ENABLED', ! app()->environment('production')),
                'send_cache_to_ray' => env('SEND_CACHE_TO_DEBUGGER', false),
                'send_dumps_to_ray' => env('SEND_DUMPS_TO_DEBUGGER', true),
                'send_jobs_to_ray' => env('SEND_JOBS_TO_DEBUGGER', false),
                'send_mails_to_ray' => env('SEND_MAILS_TO_DEBUGGER', true),
                'send_log_calls_to_ray' => env('SEND_LOG_CALLS_TO_DEBUGGER', true),
                'send_queries_to_ray' => env('SEND_QUERIES_TO_DEBUGGER', false),
                'send_duplicate_queries_to_ray' => env('SEND_DUPLICATE_QUERIES_TO_DEBUGGER', false),
                'send_slow_queries_to_ray' => env('SEND_SLOW_QUERIES_TO_DEBUGGER', false),
                'send_update_queries_to_ray' => env('SEND_UPDATE_QUERIES_TO_DEBUGGER', false),
                'send_insert_queries_to_ray' => env('SEND_INSERT_QUERIES_TO_DEBUGGER', false),
                'send_delete_queries_to_ray' => env('SEND_DELETE_QUERIES_TO_DEBUGGER', false),
                'send_select_queries_to_ray' => env('SEND_SELECT_QUERIES_TO_DEBUGGER', false),
                'send_requests_to_ray' => env('SEND_REQUESTS_TO_DEBUGGER', false),
                'send_http_client_requests_to_ray' => env('SEND_HTTP_CLIENT_REQUESTS_TO_DEBUGGER', false),
                'send_views_to_ray' => env('SEND_VIEWS_TO_DEBUGGER', false),
                'send_exceptions_to_ray' => env('SEND_EXCEPTIONS_TO_DEBUGGER', true),
                'send_deprecated_notices_to_ray' => env('SEND_DEPRECATED_NOTICES_TO_DEBUGGER', false),
            ]);
        });

        return $this;
    }

    private function registerBindings(): self
    {
        $settings = app(Settings::class);

        $this->app->bind(Client::class, fn (): Client => new Client($settings->port, $settings->host));

        $this->app->bind(Debugger::class, function (): Debugger {
            $client = app(Client::class);

            $settings = app(Settings::class);

            $debugger = new Debugger($settings, $client);

            if (! $settings->enable) {
                $debugger->disable();
            }

            return $debugger;
        });

        Payload::$originFactoryClass = OriginFactory::class;

        return $this;
    }

    private function registerWatchers(): self
    {
        $watchers = [
            ExceptionWatcher::class,
            MailWatcher::class,
            ApplicationLogWatcher::class,
            JobWatcher::class,
            EventWatcher::class,
            DumpWatcher::class,
            QueryWatcher::class,
            DuplicateQueryWatcher::class,
            SlowQueryWatcher::class,
            InsertQueryWatcher::class,
            SelectQueryWatcher::class,
            UpdateQueryWatcher::class,
            DeleteQueryWatcher::class,
            ViewWatcher::class,
            CacheWatcher::class,
            RequestWatcher::class,
            HttpClientWatcher::class,
            DeprecatedNoticeWatcher::class,
        ];

        collect($watchers)
            ->each(function (string $watcherClass): void {
                $this->app->singleton($watcherClass);
            });

        return $this;
    }

    private function bootWatchers(): void
    {
        $watchers = [
            ExceptionWatcher::class,
            MailWatcher::class,
            ApplicationLogWatcher::class,
            JobWatcher::class,
            EventWatcher::class,
            DumpWatcher::class,
            QueryWatcher::class,
            DuplicateQueryWatcher::class,
            SlowQueryWatcher::class,
            InsertQueryWatcher::class,
            SelectQueryWatcher::class,
            UpdateQueryWatcher::class,
            DeleteQueryWatcher::class,
            ViewWatcher::class,
            CacheWatcher::class,
            RequestWatcher::class,
            HttpClientWatcher::class,
            DeprecatedNoticeWatcher::class,
        ];

        collect($watchers)
            ->each(function (string $watcherClass): void {
                $watcher = app($watcherClass);

                $watcher->register();
            });
    }

    private function registerMacros(): self
    {
        Collection::macro('debug', function (string $description = ''): object {
            $description === ''
                ? ad($this->items)
                : ad($description, $this->items);

            return $this;
        });

        Collection::macro('ray', fn (string $description = '') => $this->debug($description));
        Collection::macro('ad', fn (string $description = '') => $this->debug($description));

        TestResponse::macro('debug', function (): object {
            ad()->testResponse($this);

            return $this;
        });

        TestResponse::macro('ray', fn () => $this->debug());

        Stringable::macro('debug', function (string $description = ''): object {
            $description === ''
                ? ad($this->value)
                : ad($description, $this->value);

            return $this;
        });

        Stringable::macro('ray', fn (string $description = '') => $this->debug($description));

        Builder::macro('debug', function (): object {
            $payload = new QueryPayload($this);

            ad()->sendRequest($payload);

            return $this;
        });

        Builder::macro('ray', fn () => $this->debug());

        return $this;
    }

    private function registerBladeDirectives(): self
    {
        if (! $this->app->has('blade.compiler')) {
            return $this;
        }

        $this->callAfterResolving('blade.compiler', function (BladeCompiler $bladeCompiler): void {
            Blade::directive('debug', fn ($expression): string => "<?php ad($expression); ?>");
            Blade::directive('ray', fn ($expression): string => "<?php ad($expression); ?>");
            Blade::directive('ad', fn ($expression): string => "<?php ad($expression); ?>");
            Blade::directive('measure', fn (): string => '<?php ad()->measure() ?>');
            Blade::directive('xdebug', fn (): string => '<?php ad($__data)?>');
            Blade::directive('xray', fn (): string => '<?php ad($__data)?>');
        });

        return $this;
    }

    private function registerPayloadFinder(): self
    {
        PayloadFactory::registerPayloadFinder(function ($argument): ModelPayload|MailablePayload|null {
            if ($argument instanceof Model) {
                return new ModelPayload($argument);
            }

            if ($argument instanceof Mailable) {
                return MailablePayload::forMailable($argument);
            }

            return null;
        });

        return $this;
    }
}
