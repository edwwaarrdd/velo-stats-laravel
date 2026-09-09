<?php

use App\Jobs\LogTestMessage;
use Illuminate\Support\Facades\Log;

it('logs the message it was given', function (): void {
    Log::shouldReceive('info')->once()->with('Test task received: hello world');

    LogTestMessage::dispatchSync('hello world');
});
