<?php

declare(strict_types=1);

namespace Akira\Debugger;

use Akira\Debugger\DumpRecorder\DumpRecorder;
use Akira\Debugger\Watchers\CacheWatcher;
use Akira\Debugger\Watchers\QueryWatcher;
use Akira\Debugger\Watchers\ViewWatcher;
use Illuminate\Cache\CacheManager;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Database\Query\Builder;
use Illuminate\Events\Dispatcher;
use Illuminate\Log\Logger;
use Illuminate\Log\LogManager;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Facade;
use Illuminate\Support\Str;
use Illuminate\Support\Stringable;
use Spatie\Backtrace\Backtrace;
use Spatie\Backtrace\Frame;
use Spatie\Ray\Origin\Origin;
use Spatie\Ray\Support\Invador;

final class OriginFactory
{
    public function getOrigin(): Origin
    {
        $frame = $this->getFrame();

        return new Origin(
            optional($frame)->file,
            optional($frame)->lineNumber,
        );
    }

    public function findFrameForCache(Collection $frames): ?Frame
    {
        $index = $frames->search(function (Frame $frame) {
            return $frame->class === CacheManager::class;
        });

        while (Str::startsWith($frames[$index]->class, 'Illuminate')) {
            $index++;
        }

        return $frames[$index] ?? null;
    }

    private function getFrame(): ?Frame
    {
        $frames = collect(Backtrace::create()->frames())->reverse();

        $indexOfRay = $frames
            ->search(function (Frame $frame) {
                if ($frame->class === Ray::class) {
                    return true;
                }

                if (Str::startsWith($frame->file, __DIR__)) {
                    return true;
                }

                return false;
            });

        /** @var Frame|null $rayFrame */
        $rayFrame = $frames[$indexOfRay] ?? null;

        $rayFunctionFrame = $frames[$indexOfRay + 2] ?? null;

        /** @var Frame|null $originFrame */
        $originFrame = $frames[$indexOfRay + 1] ?? null;

        if ($originFrame && Str::endsWith($originFrame->file, \Spatie\Ray\Ray::makePathOsSafe('ray/src/helpers.php'))) {
            $framesAbove = 2;

            if ($rayFunctionFrame && $rayFunctionFrame->method === 'rd') {
                $framesAbove = 3;
            }

            $originFrame = $frames[$indexOfRay + $framesAbove] ?? null;
        }

        if (! $rayFrame) {
            return null;
        }

        if ($rayFrame->class === Stringable::class) {
            return $this->findFrameForStringableMacro($frames, $indexOfRay);
        }

        if ($rayFrame->class === Collection::class && Str::startsWith($rayFrame->method, 'Spatie\LaravelRay')) {
            return $this->findFrameForCollectionMacro($frames, $indexOfRay);
        }

        if ($rayFrame->class === QueryWatcher::class) {
            return $this->findFrameForQuery($frames);
        }

        if ($rayFrame->class === ViewWatcher::class) {
            return $this->findFrameForView($frames, $indexOfRay);
        }

        if ($rayFrame->class === DumpRecorder::class) {
            return $this->findFrameForDump($frames);
        }

        if ($rayFrame->class === CacheWatcher::class) {
            return $this->findFrameForCache($frames);
        }

        if ($originFrame->class === Dispatcher::class) {
            return $this->findFrameForEvent($frames);
        }

        if ($originFrame->class === Builder::class) {
            return $this->findFrameForQueryBuilder($frames);
        }

        if (Str::endsWith($originFrame->file, \Spatie\Ray\Ray::makePathOsSafe('/vendor/psy/psysh/src/ExecutionLoopClosure.php'))) {
            $this->returnTinkerFrame();
        }

        try {
            if (Str::startsWith($originFrame->file, config('view.compiled'))) {
                return $this->replaceCompiledViewPathWithOriginalViewPath($originFrame);
            }
        } catch (BindingResolutionException $exception) {
            // ignore errors caused by using `storage_path`
        }

        if ($originFrame->class === Invador::class) {
            return $frames[$indexOfRay + 2];
        }

        return $originFrame;
    }

    private function findFrameForStringableMacro(Collection $frames, int $indexOfFoundFrame): ?Frame
    {
        return $frames[$indexOfFoundFrame + 2];
    }

    private function findFrameForCollectionMacro(Collection $frames, int $indexOfFoundFrame): ?Frame
    {
        return $frames[$indexOfFoundFrame + 2];
    }

    private function findFrameForQuery(Collection $frames): ?Frame
    {
        $indexOfLastDatabaseCall = $frames
            ->filter(function (Frame $frame) {
                return ! is_null($frame->class);
            })
            ->search(function (Frame $frame) {
                return Str::startsWith($frame->class, 'Illuminate\Database');
            });

        return $frames[$indexOfLastDatabaseCall + 1] ?? null;
    }

    private function findFrameForQueryBuilder(Collection $frames): ?Frame
    {
        $indexOfLastDatabaseCall = $frames
            ->filter(function (Frame $frame) {
                return ! is_null($frame->class);
            })
            ->search(function (Frame $frame) {
                return Str::startsWith($frame->class, 'Illuminate\Database');
            });

        return $frames[$indexOfLastDatabaseCall + 1] ?? null;
    }

    private function findFrameForView(Collection $frames, int $indexOfRayFrame): ?Frame
    {
        return $frames[$indexOfRayFrame + 6] ?? null;
    }

    private function findFrameForDump(Collection $frames): ?Frame
    {
        $indexOfDumpCall = $frames
            ->search(function (Frame $frame) {
                if (! is_null($frame->class)) {
                    return false;
                }

                return in_array($frame->method, ['dump', 'dd']);
            });

        return $frames[$indexOfDumpCall + 1] ?? null;
    }

    private function findFrameForEvent(Collection $frames): ?Frame
    {
        $indexOfLoggerCall = $frames
            ->search(function (Frame $frame) {
                return $frame->class === Logger::class;
            });

        if ($indexOfLoggerCall) {
            return $this->findFrameForLog($frames, $indexOfLoggerCall);
        }

        $indexOfEventDispatcherCall = $frames
            ->search(function (Frame $frame) {
                return ($frame->class === Dispatcher::class) && $frame->method === 'dispatch';
            });

        /** @var Frame $foundFrame */
        if ($foundFrame = $frames[$indexOfEventDispatcherCall + 2]) {
            if (Str::endsWith($foundFrame->file, \Spatie\Ray\Ray::makePathOsSafe('/Illuminate/Foundation/Events/Dispatchable.php'))) {
                $foundFrame = $frames[$indexOfEventDispatcherCall + 3];
            }
        }

        return $foundFrame ?? null;
    }

    private function findFrameForLog(Collection $frames, int $indexOfLoggerCall): ?Frame
    {
        /** @var Frame $foundFrame */
        if ($foundFrame = $frames[$indexOfLoggerCall + 1]) {
            if ($foundFrame->class === LogManager::class) {
                $foundFrame = $frames[$indexOfLoggerCall + 2];

                if ($foundFrame->class = Facade::class) {
                    $foundFrame = $frames[$indexOfLoggerCall + 3];
                }

                if (Str::endsWith($foundFrame->file, \Spatie\Ray\Ray::makePathOsSafe('/Illuminate/Foundation/helpers.php'))) {
                    $foundFrame = $frames[$indexOfLoggerCall + 3];
                }
            }
        }

        return $foundFrame ?? null;
    }

    private function replaceCompiledViewPathWithOriginalViewPath(Frame $frame): Frame
    {
        if (! file_exists($frame->file)) {
            return $frame;
        }

        $fileContents = file_get_contents($frame->file);

        $originalViewPath = mb_trim(Str::between($fileContents, '/**PATH', 'ENDPATH**/'));

        if (! file_exists($originalViewPath)) {
            return $frame;
        }

        $frame->file = $originalViewPath;
        $frame->lineNumber = 1;

        return $frame;
    }
}
