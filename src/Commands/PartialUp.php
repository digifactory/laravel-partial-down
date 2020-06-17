<?php

namespace DigiFactory\PartialDown\Commands;

use Exception;
use Illuminate\Console\Command;
use Illuminate\Support\Str;

class PartialUp extends Command
{
    /**
     * The console command name.
     *
     * @var string
     */
    protected $signature = 'partial-up {part}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Bring specific part of the application out of maintenance mode';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $part = Str::slug($this->argument('part'));

        $lockFilename = 'framework/partial-down-'.$part;

        try {
            if (! file_exists(storage_path($lockFilename))) {
                $this->comment("This part [{$part}] of the application is already up.");

                return true;
            }

            unlink(storage_path($lockFilename));

            $this->info("This part [{$part}] of the application is now live.");
        } catch (Exception $e) {
            $this->error("Failed to disable maintenance mode for this part [{$part}] of the application.");

            $this->error($e->getMessage());

            return 1;
        }
    }
}
