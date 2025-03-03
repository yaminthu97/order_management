<?php

namespace App\Listeners;

use App\Events\ModuleCompleted;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;

class ModuleCompletedLogger
{
    protected $forcedTargets;
    /**
     * Create the event listener.
     */
    public function __construct()
    {
        //
        $this->forcedTargets = config('logging.modules.forced_targets',[]);
    }

    /**
     * Handle the event.
     */
    public function handle(ModuleCompleted $event): void
    {
        //最後のクラス名だけ
        $class = explode('\\', $event->moduleClass);
        if(config('logging.modules.completed') || in_array($event->moduleClass, $this->forcedTargets)) {
            Log::info(__('messages.info.module_completed', ['module'=>(string)end($class)]), [
                'class_path' => $event->moduleClass,
                'dump' => $event->dump,
                'at_time' => date('Y-m-d H:i:s'),
            ]);
        }
    }
}
