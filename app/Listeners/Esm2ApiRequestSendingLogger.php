<?php

namespace App\Listeners;

use App\Events\Esm2ApiRequestSending;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;

class Esm2ApiRequestSendingLogger
{
    /**
     * Create the event listener.
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     */
    public function handle(Esm2ApiRequestSending $event): void
    {
        //
        if(config('logging.apis.esm2.send_request')) {
            Log::info(__('messages.info.esm2_api_send_request', ['uri'=>$event->uri]), [
                'uri' => $event->uri,
                'method' => $event->method,
                'options' => $event->options,
            ]);
        }
    }
}
