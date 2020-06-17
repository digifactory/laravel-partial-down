<?php

namespace DigiFactory\PartialDown\Commands;

use DigiFactory\PartialDown\Middleware\CheckForPartialMaintenanceMode;
use Exception;
use Illuminate\Console\Command;
use Illuminate\Support\Str;
use Symfony\Component\Console\Output\BufferedOutput;

class PartialParts extends Command
{
    /**
     * The console command name.
     *
     * @var string
     */
    protected $signature = 'partial-parts';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Show all parts that are used in the application';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $output = new BufferedOutput();

        $this->runCommand('route:list', [], $output);

        $className = CheckForPartialMaintenanceMode::class;

        preg_match_all('/'.preg_quote($className).':(.*[a-z])/', $output->fetch(), $matches);

        $parts = collect($matches[0])->unique()->map(function ($part) use ($className) {
            return str_replace($className.':', '', $part);
        });

        $this->line('Parts in use: '. $parts->join(', ', ' and '));
    }
}
