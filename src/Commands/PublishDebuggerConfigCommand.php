<?php

declare(strict_types=1);

namespace Akira\Debugger\Commands;

use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;

final class PublishDebuggerConfigCommand extends Command
{
    protected $signature = 'debugger:publish-config {--homestead : Indicates that Homestead is being used}
                                               {--docker : Indicates that Docker is being used}';

    protected $description = 'Create the Akira Debugger config file in project root.';

    public function handle(): int
    {
        if ((new Filesystem)->exists(config_path('debugger.php'))) {
            $this->error('config/debugger.php already exists');

            return self::FAILURE;
        }

        copy(__DIR__.'/../../config/debugger.php', config_path('debugger.php'));

        if ($this->option('docker')) {
            file_put_contents(
                config_path('debugger.php'),
                str_replace(
                    "'host' => env('DEBUGGER_HOST', 'localhost')",
                    "'host' => env('DEBUGGER_HOST', 'host.docker.internal')",
                    file_get_contents(config_path('debugger.php'))
                )
            );
        }

        if ($this->option('homestead')) {
            file_put_contents(
                config_path('debugger.php'),
                str_replace(
                    "'host' => env('DEBUGGER_HOST', 'localhost')",
                    "'host' => env('DEBUGGER_HOST', '10.0.2.2')",
                    file_get_contents(config_path('debugger.php'))
                )
            );
        }

        $this->info('Configuration published to config/debugger.php');

        return self::SUCCESS;
    }
}
