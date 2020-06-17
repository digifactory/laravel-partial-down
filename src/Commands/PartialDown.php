<?php

namespace DigiFactory\PartialDown\Commands;

use Exception;
use Illuminate\Console\Command;
use Illuminate\Support\InteractsWithTime;
use Illuminate\Support\Str;

class PartialDown extends Command
{
    use InteractsWithTime;

    /**
     * The console command signature.
     *
     * @var string
     */
    protected $signature = 'partial-down {part}
                                         {--message= : The message for the maintenance mode}
                                         {--retry= : The number of seconds after which the request may be retried}
                                         {--allow=* : IP or networks allowed to access the application while in maintenance mode}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Put specific part of the application into maintenance mode';

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
            if (file_exists(storage_path($lockFilename))) {
                $this->comment("This part [{$part}] of the application is already down.");

                return true;
            }

            file_put_contents(storage_path($lockFilename),
                json_encode($this->getDownFilePayload(),
                    JSON_PRETTY_PRINT));

            $this->comment("This part [{$part}] of the application is now in maintenance mode.");
        } catch (Exception $e) {
            $this->error("Failed to put this part [{$part}] of the application in maintenance mode.");

            $this->error($e->getMessage());

            return 1;
        }
    }

    /**
     * Get the payload to be placed in the "down" file.
     *
     * @return array
     */
    protected function getDownFilePayload()
    {
        return [
            'time' => $this->currentTime(),
            'message' => $this->option('message'),
            'retry' => $this->getRetryTime(),
            'allowed' => $this->option('allow'),
        ];
    }

    /**
     * Get the number of seconds the client should wait before retrying their request.
     *
     * @return int|null
     */
    protected function getRetryTime()
    {
        $retry = $this->option('retry');

        return is_numeric($retry) && $retry > 0 ? (int) $retry : null;
    }
}