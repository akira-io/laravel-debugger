<?php

declare(strict_types=1);

namespace Akira\Debugger\Commands;

use Akira\Debugger\Support\Composer;
use Composer\InstalledVersions;
use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Facades\Process;

final class CleanDebuggerCommand extends Command
{
    protected $signature = 'debugger:clean';

    protected $description = 'Remove all debug calls from your codebase.';

    public function handle(Filesystem $files): int
    {
        $directories = [
            'app',
            'config',
            'database',
            'public',
            'resources',
            'routes',
            'tests',
        ];

        if (! InstalledVersions::isInstalled('rector/rector')) {
            (new Composer($files, defined('TESTBENCH_WORKING_PATH') ? TESTBENCH_WORKING_PATH : base_path()))
                ->requirePackages(['rector/rector'], true, $this->output);
        }

        $this->withProgressBar($directories, function (string $directory): void {
            $result = Process::run('./vendor/bin/rector process '.$directory);

            if (! $result->successful()) {
                $this->error($result->errorOutput());

                return;
            }
        });

        $this->newLine(2);
        $this->info('All debug calls have been removed from your codebase.');

        return self::SUCCESS;
    }
}
