<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;

class ClaimProvider extends ServiceProvider
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
        // モデル系


        // モジュール系
        $this->bindModule(\App\Modules\Claim\Base\FindBillingOutputsInterface::class, 'FindBillingOutputs');
        $this->bindModule(\App\Modules\Claim\Base\UpdateBillingHdrInterface::class, 'UpdateBillingHdr');

    }
    /**
     * モジュールのバインド
     */
    private function bindModule($interface, $module)
    {
        $this->app->bind($interface, function ($app) use ($module) {
            $esmSessionManager = $app->make('App\Services\EsmSessionManager');
            $accountCode = $esmSessionManager->getAccountCode();
            // Pascal Case に変換
            $accountCode = Str::studly($accountCode);
            $moduleClass = "App\\Modules\\Claim\\{$accountCode}\\{$module}";
            if(class_exists($moduleClass)) {
                return $app->make($moduleClass);
            } else {
                return $app->make("App\\Modules\\Claim\\Base\\{$module}");
            }
        });
    }
}
