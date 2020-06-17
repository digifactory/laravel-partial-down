<?php

namespace DigiFactory\PartialDown\Commands;

use DigiFactory\PartialDown\Middleware\CheckForPartialMaintenanceMode;
use Illuminate\Console\Command;
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
        $outputBuffer = new BufferedOutput();

        $this->runCommand('route:list', [], $outputBuffer);

        $output = $outputBuffer->fetch();

        $className = CheckForPartialMaintenanceMode::class;

        preg_match_all('/'.preg_quote($className).':(.*[a-z])/', $output, $matches);

        $parts = collect($matches[0])->unique()->map(function ($part) use ($className) {
            return [str_replace($className.':', '', $part)];
        });

        if ($parts->count() === 0) {
            $this->error('No parts found!');
        } else {
            $headers = ['Parts in use'];
            $this->table($headers, $parts->toArray());
        }
    }
}
