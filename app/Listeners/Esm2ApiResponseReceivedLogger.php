<?php

namespace App\Listeners;

use App\Events\Esm2ApiResponseReceived;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;

class Esm2ApiResponseReceivedLogger
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
    public function handle(Esm2ApiResponseReceived $event): void
    {
        //
        if(config('logging.apis.esm2.receive_response')) {
            $response = $event->response;
            Log::info(__('messages.info.esm2_api_receive_response', ['uri'=>$event->uri]), [
                'uri' => $event->uri,
                'status_code' => $response->getStatusCode(),
                'body' => $response->getBody(),
            ]);
        }
    }
}
