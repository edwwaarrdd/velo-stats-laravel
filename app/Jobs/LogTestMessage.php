<?php

namespace App\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Psr\Log\LoggerInterface;

/**
 * Logs a message from the worker, so the queue setup can be verified.
 */
class LogTestMessage implements ShouldQueue
{
    use Queueable;

    public function __construct(private readonly string $message) {}

    public function handle(LoggerInterface $logger): void
    {
        $logger->info("Test task received: {$this->message}");
    }
}
