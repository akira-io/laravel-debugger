<?php

declare(strict_types=1);

namespace Akira\Debugger\DumpRecorder;

use Akira\Debugger\Debugger;
use Illuminate\Contracts\Container\Container;
use ReflectionException;
use ReflectionMethod;
use ReflectionProperty;
use Symfony\Component\VarDumper\VarDumper;

final class DumpRecorder
{
    private array $dumps = [];

    private Container $app;

    private static bool $registeredHandler = false;

    private static $runningLaravel9 = null;

    public function __construct(Container $app)
    {
        $this->app = $app;

        if (self::$runningLaravel9 === null) {
            self::$runningLaravel9 = version_compare(app()->version(), '9.0.0', '>=');
        }
    }

    /**
     * @throws ReflectionException
     */
    public function register(): self
    {
        $multiDumpHandler = new MultiDumpHandler;

        $this->app->singleton(MultiDumpHandler::class, function () use ($multiDumpHandler) {
            return $multiDumpHandler;
        });

        if (! self::$registeredHandler || self::$runningLaravel9) {
            self::$registeredHandler = true;

            $multiDumpHandler->resetHandlers();

            $this->ensureOriginalHandlerExists();

            $originalHandler = VarDumper::setHandler(function ($dumpedVariable) use ($multiDumpHandler) {
                $multiDumpHandler->dump($dumpedVariable);
            });

            if ($originalHandler) {
                $multiDumpHandler->addHandler($originalHandler);
            }

            $multiDumpHandler->addHandler(function ($dumpedVariable) {
                if ($this->shouldDump()) {
                    app(Debugger::class)->send($dumpedVariable);
                }
            });
        }

        return $this;
    }

    private function shouldDump(): bool
    {
        /** @var Ray $ray */
        $ray = app(Debugger::class);

        return $ray->settings->send_dumps_to_ray;
    }

    /**
     * Only the `VarDumper` knows how to create the orignal HTML or CLI VarDumper.
     * Using reflection and the private VarDumper::register() method we can force it
     * to create and register a new VarDumper::$handler before we'll overwrite it.
     * Of course, we only need to do this if there isn't a registered VarDumper::$handler.
     *
     * @throws ReflectionException
     */
    private function ensureOriginalHandlerExists(): void
    {
        $reflectionProperty = new ReflectionProperty(VarDumper::class, 'handler');
        if (PHP_VERSION_ID < 80100) {
            $reflectionProperty->setAccessible(true);
        }
        $handler = $reflectionProperty->getValue();

        if (! $handler) {
            // No handler registered yet, so we'll force VarDumper to create one.
            $reflectionMethod = new ReflectionMethod(VarDumper::class, 'register');
            if (PHP_VERSION_ID < 80100) {
                $reflectionMethod->setAccessible(true);
            }
            $reflectionMethod->invoke(null);
        }
    }
}
