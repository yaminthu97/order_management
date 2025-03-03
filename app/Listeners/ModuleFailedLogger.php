<?php

namespace App\Listeners;

use App\Events\ModuleFailed;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;

class ModuleFailedLogger
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
    public function handle(ModuleFailed $event): void
    {
        //最後のクラス名だけ
        $class = explode('\\', $event->moduleClass);
        if(config('logging.modules.failed') || in_array($event->moduleClass, $this->forcedTargets)) {
            Log::info(__('messages.info.module_failed', ['module'=>(string)end($class)]), [
                'class_path' => $event->moduleClass,
                'dump' => $event->dump,
                'exception' => $event->exception->getMessage(),
                'at_time' => date('Y-m-d H:i:s'),
            ]);
        }
    }
}
