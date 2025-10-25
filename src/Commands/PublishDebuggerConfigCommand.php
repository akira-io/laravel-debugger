<?php

declare(strict_types=1);

namespace Akira\Debugger\Commands;

use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;

class PublishDebuggerConfigCommand extends Command
{
    protected $signature = 'debugger:publish-config {--homestead : Indicates that Homestead is being used}
                                               {--docker : Indicates that Docker is being used}';

    protected $description = 'Create the Akira Debugger config file in project root.';

    public function handle(): int
    {
        if ((new Filesystem)->exists('debugger.php')) {
            $this->error('debugger.php already exists in the project root');

            return self::FAILURE;
        }

        copy(__DIR__ . '/../../stub/debugger.php', base_path('debugger.php'));

        if ($this->option('docker')) {
            file_put_contents(
                base_path('debugger.php'),
                str_replace(
                    "'host' => env('DEBUGGER_HOST', 'localhost')",
                    "'host' => env('DEBUGGER_HOST', 'host.docker.internal')",
                    file_get_contents(base_path('debugger.php'))
                )
            );
        }

        if ($this->option('homestead')) {
            file_put_contents(
                base_path('debugger.php'),
                str_replace(
                    "'host' => env('DEBUGGER_HOST', 'localhost')",
                    "'host' => env('DEBUGGER_HOST', '10.0.2.2')",
                    file_get_contents(base_path('debugger.php'))
                )
            );
        }

        $this->info('`debugger.php` created in the project base directory');

        return self::SUCCESS;
    }
}
