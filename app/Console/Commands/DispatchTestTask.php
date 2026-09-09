<?php

namespace App\Console\Commands;

use App\Jobs\LogTestMessage;
use Illuminate\Console\Command;

class DispatchTestTask extends Command
{
    protected $signature = 'tasks:dispatch-test {--message=Hello from tasks:dispatch-test : Message the worker should write to the log}';

    protected $description = 'Dispatch a test job that logs a message from the worker.';

    public function handle(): int
    {
        LogTestMessage::dispatch((string) $this->option('message'));

        $this->info('Dispatched a test task to the queue.');

        return self::SUCCESS;
    }
}
