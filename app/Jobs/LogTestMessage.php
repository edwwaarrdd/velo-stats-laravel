<?php

namespace App\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;

/**
 * Logs a message from the worker, so the queue setup can be verified.
 */
class LogTestMessage implements ShouldQueue
{
    use Queueable;

    public function __construct(private readonly string $message)
    {
        //
    }

    public function handle(): void
    {
        Log::info("Test task received: {$this->message}");
    }
}
