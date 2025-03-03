<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;

class BillingProvider extends ServiceProvider
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
        $this->bindModule(\App\Modules\Billing\Base\FindReportTemplatesInterface::class, 'FindReportTemplates');
        $this->bindModule(\App\Modules\Billing\Base\SearchExcelReportInterface::class, 'SearchExcelReport');

        //
        // フォームリクエスト系
        $this->bindFormRequest(\App\Http\Requests\Billing\Base\UpdateExcelReportRequest::class, 'UpdateExcelReportRequest');
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
            $moduleClass = "App\\Modules\\Billing\\{$accountCode}\\{$module}";
            if (class_exists($moduleClass)) {
                return $app->make($moduleClass);
            } else {
                return $app->make("App\\Modules\\Billing\\Base\\{$module}");
            }
        });
    }

    /**
     * フォームリクエストのバインド
     */
    private function bindFormRequest($interface, $module)
    {
        $this->app->bind($interface, function ($app) use ($module) {
            $esmSessionManager = $app->make('App\Services\EsmSessionManager');
            $accountCode = $esmSessionManager->getAccountCode();
            // Pascal Case に変換
            $accountCode = Str::studly($accountCode);
            $moduleClass = "App\\Http\\Requests\\Billing\\{$accountCode}\\{$module}";
            if(class_exists($moduleClass)) {
                return $app->make($moduleClass);
            } else {
                return $app->make("App\\Http\\Requests\\Billing\\Base\\{$module}");
            }
        });
    }
}
