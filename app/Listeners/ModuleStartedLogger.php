<?php

namespace App\Listeners;

use App\Events\ModuleStarted;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;

class ModuleStartedLogger
{

    protected $forcedTargets;
    /**
     * Create the event listener.
     */
    public function __construct()
    {
        $this->forcedTargets = config('logging.modules.forced_targets',[]);
    }

    /**
     * Handle the event.
     */
    public function handle(ModuleStarted $event): void
    {
        //最後のクラス名だけ
        $class = explode('\\', $event->moduleClass);
        if(config('logging.modules.started') || in_array($event->moduleClass, $this->forcedTargets)) {
            Log::info(__('messages.info.module_started', ['module'=>(string)end($class)]), [
                'class_path' => $event->moduleClass,
                'dump' => $event->dump,
                'at_time' => date('Y-m-d H:i:s'),
            ]);
        }
    }
}
