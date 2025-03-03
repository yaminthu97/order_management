<?php

namespace App\Listeners;

use App\Events\Esm2ApiConnectionFailed;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;

class Esm2ApiConnectionFailedLogger
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
    public function handle(Esm2ApiConnectionFailed $event): void
    {
        if(config('logging.apis.esm2.fail_request')) {
            Log::info(__('messages.info.esm2_api_fail_request', ['uri'=>$event->uri]), [
                'uri' => $event->uri,
                'response' => $event->response?->getBody(),
                'status_code' => $event->response?->getStatusCode(),
                'exception' => $event->exception?->getMessage(),
            ]);
        }
    }
}
