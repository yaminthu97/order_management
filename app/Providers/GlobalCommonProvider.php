<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;

class GlobalCommonProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        // モジュール系
        $this->bindMudule(\App\Modules\Common\Base\GetPrefecturalInterface::class, 'GetPrefectural');
        $this->bindMudule(\App\Modules\Common\Base\RegisterBatchExecuteInstructionInterface::class, 'RegisterBatchExecuteInstruction');
        $this->bindMudule(\App\Modules\Common\Base\StartBatchExecuteInstructionInterface::class, 'StartBatchExecuteInstruction');
        $this->bindMudule(\App\Modules\Common\Base\EndBatchExecuteInstructionInterface::class, 'EndBatchExecuteInstruction');
    }

    /**
     * モジュールのバインド
     */
    private function bindMudule($interface, $module)
    {
        $this->app->bind($interface, function ($app) use ($module) {
            $esmSessionManager = $app->make('App\Services\EsmSessionManager');
            $accountCode = $esmSessionManager->getAccountCode();
            // Pascal Case に変換
            $accountCode = Str::studly($accountCode);
            $moduleClass = "App\\Modules\\Common\\{$accountCode}\\{$module}";
            if(class_exists($moduleClass)) {
                return $app->make($moduleClass);
            } else {
                return $app->make("App\\Modules\\Common\\Base\\{$module}");
            }
        });
    }
}
