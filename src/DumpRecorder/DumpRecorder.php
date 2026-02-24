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
    private static bool $registeredHandler = false;

    private static bool|int|null $runningLaravel9 = null;

    public function __construct(private readonly Container $app)
    {
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

        $this->app->singleton(MultiDumpHandler::class, fn (): MultiDumpHandler => $multiDumpHandler);

        if (! self::$registeredHandler || self::$runningLaravel9) {
            self::$registeredHandler = true;

            $multiDumpHandler->resetHandlers();

            $handlerProperty = new ReflectionProperty(VarDumper::class, 'handler');

            $originalHandler = $handlerProperty->getValue();

            if (! $originalHandler) {
                $this->ensureOriginalHandlerExists();
                $originalHandler = $handlerProperty->getValue();
            }

            // Bypass VarDumper::setHandler() which is a no-op when VAR_DUMPER_FORMAT is set.
            $handlerProperty->setValue(null, function ($dumpedVariable) use ($multiDumpHandler): void {
                $multiDumpHandler->dump($dumpedVariable);
            });

            if ($originalHandler) {
                $multiDumpHandler->addHandler($originalHandler);
            }

            $multiDumpHandler->addHandler(function ($dumpedVariable): void {
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
        $handler = $reflectionProperty->getValue();

        if (! $handler) {
            // No handler registered yet, so we'll force VarDumper to create one.
            $reflectionMethod = new ReflectionMethod(VarDumper::class, 'register');
            $reflectionMethod->invoke(null);
        }
    }
}
