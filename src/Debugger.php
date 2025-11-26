<?php

declare(strict_types=1);

namespace Akira\Debugger;

use Akira\Debugger\Payloads\EnvironmentPayload;
use Akira\Debugger\Payloads\ExecutedQueryPayload;
use Akira\Debugger\Payloads\LoggedMailPayload;
use Akira\Debugger\Payloads\MailablePayload;
use Akira\Debugger\Payloads\MarkdownPayload;
use Akira\Debugger\Payloads\ModelPayload;
use Akira\Debugger\Payloads\ResponsePayload;
use Akira\Debugger\Payloads\ViewPayload;
use Akira\Debugger\Watchers\CacheWatcher;
use Akira\Debugger\Watchers\ConditionalQueryWatcher;
use Akira\Debugger\Watchers\DeleteQueryWatcher;
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
use Akira\Debugger\Watchers\Watcher;
use Closure;
use Composer\InstalledVersions;
use Exception;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Events\QueryExecuted;
use Illuminate\Database\QueryException;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\MailManager;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Context;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Testing\Fakes\MailFake;
use Illuminate\Testing\TestResponse;
use Illuminate\View\View;
use ReflectionException;
use ReflectionFunction;
use Spatie\Ray\Client;
use Spatie\Ray\Payloads\ExceptionPayload;
use Spatie\Ray\Payloads\Payload;
use Spatie\Ray\Ray as BaseRay;
use Spatie\Ray\Settings\Settings;
use Throwable;

final class Debugger extends BaseRay
{
    public function __construct(Settings $settings, ?Client $client = null, ?string $uuid = null)
    {
        // persist the enabled setting across multiple instantiations
        $enabled = self::$enabled;

        parent::__construct($settings, $client, $uuid);

        self::$enabled = $enabled;
    }

    /**
     * @throws Exception
     */
    public function loggedMail(string $loggedMail): self
    {
        $payload = LoggedMailPayload::forLoggedMail($loggedMail);

        $this->sendRequest($payload);

        return $this;
    }

    /**
     * @throws Exception
     */
    public function mailable(Mailable ...$mailables): self
    {
        $shouldRestoreFake = false;

        if (app(MailManager::class)::class === MailFake::class) {
            $shouldRestoreFake = true;

            Mail::swap(new MailManager(app()));
        }

        if ($shouldRestoreFake) {
            Mail::fake();
        }

        $payloads = array_map(MailablePayload::forMailable(...), $mailables);

        $this->sendRequest($payloads);

        return $this;
    }

    public function showMails(?Closure $callable = null)
    {
        $watcher = app(MailWatcher::class);

        $watcher->enable();

        return $this->handleWatcherCallable($watcher, $callable);
    }

    public function stopShowingMails(): self
    {
        app(MailWatcher::class)->disable();

        return $this;
    }

    /**
     * @param  array|string  ...$keys
     * @return $this
     */
    public function context(...$keys): self
    {
        if (! class_exists(Context::class)) {
            return $this;
        }

        if (isset($keys[0]) && is_array($keys[0])) {
            $keys = $keys[0];
        }

        $context = $keys !== []
            ? Context::only($keys)
            : Context::all();

        $this
            ->send($context)
            ->label('Context');

        return $this;
    }

    /**
     * @param  array|string  ...$keys
     * @return $this
     */
    public function hiddenContext(...$keys): self
    {
        if (! class_exists(Context::class)) {
            return $this;
        }

        if (isset($keys[0]) && is_array($keys[0])) {
            $keys = $keys[0];
        }

        $hiddenContext = $keys !== []
            ? Context::onlyHidden($keys)
            : Context::allHidden();

        $this
            ->send($hiddenContext)
            ->label('Hidden Context');

        return $this;
    }

    /**
     * @param  Model|iterable  ...$model
     */
    public function model(...$model): self
    {
        $models = [];
        foreach ($model as $passedModel) {
            if (is_null($passedModel)) {
                $models[] = null;

                continue;
            }
            if ($passedModel instanceof Model) {
                $models[] = $passedModel;

                continue;
            }

            if (is_iterable($model)) {
                foreach ($passedModel as $item) {
                    $models[] = $item;
                }
            }
        }

        $payloads = array_map(fn (?Model $model): ModelPayload => new ModelPayload($model), $models);

        foreach ($payloads as $payload) {
            ray()->sendRequest($payload);
        }

        return $this;
    }

    /**
     * @param  Model|iterable  $models
     */
    public function models($models): self
    {
        return $this->model($models);
    }

    /**
     * @throws Exception
     */
    public function markdown(string $markdown): self
    {
        $payload = new MarkdownPayload($markdown);

        $this->sendRequest($payload);

        return $this;
    }

    /**
     * @param  string[]|array|null  $onlyShowNames
     */
    public function env(?array $onlyShowNames = null, ?string $filename = null): self
    {
        $filename ??= app()->environmentFilePath();

        $payload = new EnvironmentPayload($onlyShowNames, $filename);

        $this->sendRequest($payload);

        return $this;
    }

    public function showEvents(?Closure $callable = null)
    {
        $watcher = app(EventWatcher::class);

        return $this->handleWatcherCallable($watcher, $callable);
    }

    public function events(?Closure $callable = null)
    {
        return $this->showEvents($callable);
    }

    public function stopShowingEvents(): self
    {
        $eventWatcher = app(EventWatcher::class);

        $eventWatcher->disable();

        return $this;
    }

    public function showExceptions(): self
    {

        $exceptionWatcher = app(ExceptionWatcher::class);

        $exceptionWatcher->enable();

        return $this;
    }

    public function stopShowingExceptions(): self
    {
        $exceptionWatcher = app(ExceptionWatcher::class);

        $exceptionWatcher->disable();

        return $this;
    }

    /**
     * @throws ReflectionException
     */
    public function showJobs(?Closure $callable = null)
    {
        $watcher = app(JobWatcher::class);

        return $this->handleWatcherCallable($watcher, $callable);
    }

    /**
     * @throws ReflectionException
     */
    public function showCache(?Closure $callable = null)
    {
        $watcher = app(CacheWatcher::class);

        return $this->handleWatcherCallable($watcher, $callable);
    }

    public function stopShowingCache(): self
    {
        app(CacheWatcher::class)->disable();

        return $this;
    }

    /**
     * @throws ReflectionException
     */
    public function jobs(?Closure $callable = null)
    {
        return $this->showJobs($callable);
    }

    public function stopShowingJobs(): self
    {
        app(JobWatcher::class)->disable();

        return $this;
    }

    /**
     * @throws Exception
     */
    public function view(View $view): \Spatie\Ray\Ray
    {
        $payload = new ViewPayload($view);

        return $this->sendRequest($payload);
    }

    /**
     * @throws ReflectionException
     */
    public function showViews(?Closure $callable = null)
    {
        $watcher = app(ViewWatcher::class);

        return $this->handleWatcherCallable($watcher, $callable);
    }

    /**
     * @throws ReflectionException
     */
    public function views(?Closure $callable = null)
    {
        return $this->showViews($callable);
    }

    public function stopShowingViews(): self
    {
        app(ViewWatcher::class)->disable();

        return $this;
    }

    /**
     * @throws ReflectionException
     */
    public function showQueries(?Closure $callable = null)
    {
        $watcher = app(QueryWatcher::class);

        return $this->handleWatcherCallable($watcher, $callable);
    }

    /**
     * @throws ReflectionException
     */
    public function countQueries(callable $callable)
    {
        /** @var QueryWatcher $watcher */
        $watcher = app(QueryWatcher::class);

        $watcher->keepExecutedQueries();

        if (! $watcher->enabled()) {
            $watcher->doNotSendIndividualQueries();
        }

        $output = $this->handleWatcherCallable($watcher, $callable);

        $executedQueryStatistics = collect($watcher->getExecutedQueries())

            ->pipe(fn (Collection $queries): array => [
                'Count' => $queries->count(),
                'Total time' => $queries->sum(fn (QueryExecuted $query) => $query->time),
            ]);

        $executedQueryStatistics['Total time'] .= ' ms';

        $watcher
            ->stopKeepingAndClearExecutedQueries()
            ->sendIndividualQueries();

        $this->table($executedQueryStatistics, 'Queries');

        return $output;
    }

    /**
     * @throws ReflectionException
     */
    public function queries(?Closure $callable = null)
    {
        return $this->showQueries($callable);
    }

    public function stopShowingQueries(): self
    {
        app(QueryWatcher::class)->disable();

        return $this;
    }

    public function slowQueries($milliseconds = 500, ?Closure $callable = null)
    {
        return $this->showSlowQueries($milliseconds, $callable);
    }

    public function showSlowQueries(float|int $milliseconds = 500, ?Closure $callable = null)
    {
        $watcher = app(SlowQueryWatcher::class)
            ->setMinimumTimeInMilliseconds($milliseconds);

        return $this->handleWatcherCallable($watcher, $callable);
    }

    public function stopShowingSlowQueries(): self
    {
        app(SlowQueryWatcher::class)->disable();

        return $this;
    }

    /**
     * @throws ReflectionException
     */
    public function showDuplicateQueries(?Closure $callable = null)
    {
        $watcher = app(DuplicateQueryWatcher::class);

        return $this->handleWatcherCallable($watcher, $callable);
    }

    public function stopShowingDuplicateQueries(): self
    {
        app(DuplicateQueryWatcher::class)->disable();

        return $this;
    }

    /**
     * @throws ReflectionException
     */
    public function showConditionalQueries(Closure $condition, ?Closure $callable = null, string $name = 'default')
    {
        $watcher = ConditionalQueryWatcher::buildWatcherForName($condition, $name);

        return $this->handleWatcherCallable($watcher, $callable);
    }

    public function stopShowingConditionalQueries(string $name = 'default'): self
    {
        app(ConditionalQueryWatcher::abstractName($name))->disable();

        return $this;
    }

    /**
     * @throws ReflectionException
     */
    public function showUpdateQueries(?Closure $callable = null)
    {
        $watcher = app(UpdateQueryWatcher::class);

        return $this->handleWatcherCallable($watcher, $callable);
    }

    public function stopShowingUpdateQueries(): self
    {
        app(UpdateQueryWatcher::class)->disable();

        return $this;
    }

    /**
     * @throws ReflectionException
     */
    public function showDeleteQueries(?Closure $callable = null)
    {
        $watcher = app(DeleteQueryWatcher::class);

        return $this->handleWatcherCallable($watcher, $callable);
    }

    public function stopShowingDeleteQueries(): self
    {
        app(DeleteQueryWatcher::class)->disable();

        return $this;
    }

    /**
     * @throws ReflectionException
     */
    public function showInsertQueries(?Closure $callable = null)
    {
        $watcher = app(InsertQueryWatcher::class);

        return $this->handleWatcherCallable($watcher, $callable);
    }

    public function stopShowingInsertQueries(): self
    {
        app(InsertQueryWatcher::class)->disable();

        return $this;
    }

    /**
     * @throws ReflectionException
     */
    public function showSelectQueries(?Closure $callable = null)
    {
        $watcher = app(SelectQueryWatcher::class);

        return $this->handleWatcherCallable($watcher, $callable);
    }

    public function stopShowingSelectQueries(): self
    {
        app(SelectQueryWatcher::class)->disable();

        return $this;
    }

    /**
     * @throws ReflectionException
     */
    public function showRequests(?Closure $callable = null)
    {
        $watcher = app(RequestWatcher::class);

        return $this->handleWatcherCallable($watcher, $callable);
    }

    /**
     * @throws ReflectionException
     */
    public function requests(?Closure $callable = null)
    {
        return $this->showRequests($callable);
    }

    public function stopShowingRequests(): self
    {
        $this->requestWatcher()->disable();

        return $this;
    }

    /**
     * @throws ReflectionException
     */
    public function showHttpClientRequests(?Closure $callable = null)
    {
        if (in_array(HttpClientWatcher::supportedByLaravelVersion(), [false, 0], true)) {
            $this->send('Http logging is not available in your Laravel version')->red();

            return $this;
        }

        $watcher = app(HttpClientWatcher::class);

        return $this->handleWatcherCallable($watcher, $callable);
    }

    /**
     * @throws ReflectionException
     */
    public function httpClientRequests(?Closure $callable = null)
    {
        return $this->showHttpClientRequests($callable);
    }

    public function stopShowingHttpClientRequests(): self
    {
        app(HttpClientWatcher::class)->disable();

        return $this;
    }

    /**
     * @throws Exception
     */
    public function testResponse(TestResponse $testResponse): void
    {
        $payload = ResponsePayload::fromTestResponse($testResponse);

        $this->sendRequest($payload);
    }

    /**
     * @throws Exception
     */
    public function exception(Throwable $exception, array $meta = []): self
    {
        $payloads[] = new ExceptionPayload($exception, $meta);

        if ($exception instanceof QueryException) {
            $executedQuery = new QueryExecuted($exception->getSql(), $exception->getBindings(), null, DB::connection(config('database.default')));

            $payloads[] = new ExecutedQueryPayload($executedQuery);
        }

        $this->sendRequest($payloads)->red();

        return $this;
    }

    /**
     * @param  Payload|Payload[]  $payloads
     *
     * @throws Exception
     */
    public function sendRequest($payloads, array $meta = []): BaseRay
    {
        if (! $this->enabled()) {
            return $this;
        }

        $meta['laravel_version'] = app()->version();

        if (class_exists(InstalledVersions::class)) {
            try {
                $meta['laravel_debugger_package_version'] = InstalledVersions::getVersion('akira/laravel-debugger');
            } catch (Exception) {
                $meta['laravel_debugger_package_version'] = '0.0.0';
            }
        }

        return BaseRay::sendRequest($payloads, $meta);
    }

    /**
     * @throws ReflectionException
     */
    private function handleWatcherCallable(Watcher $watcher, ?Closure $callable = null)
    {
        $rayProxy = new DebuggerProxy;

        $wasEnabled = $watcher->enabled();

        $watcher->enable();

        if ($rayProxy) {
            $watcher->setRayProxy($rayProxy);
        }

        if ($callable instanceof Closure) {
            $output = $callable();

            if (! $wasEnabled) {
                $watcher->disable();
            }

            if (new ReflectionFunction($callable)->hasReturnType()) {
                return $output;
            }
        }

        return $rayProxy;
    }

    private function requestWatcher(): RequestWatcher
    {
        return app(RequestWatcher::class);
    }
}
