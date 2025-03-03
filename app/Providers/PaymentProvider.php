<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;

class PaymentProvider extends ServiceProvider
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
        $this->bindModule(\App\Modules\Payment\Base\GetCsvZipExportFilePathInterface::class, 'GetCsvZipExportFilePath');
        $this->bindModule(\App\Modules\Payment\Base\CreateReceiptDataInterface::class, 'CreateReceiptData');
        $this->bindModule(\App\Modules\Payment\Base\CreateBillingDataInterface::class, 'CreateBillingData');
    }


    /**
     * モジュールのバインド
     */
    private function bindModule($interface, $module)
    {
        $this->app->bind($interface, function ($app) use ($module) {
            $esmSessionManager = $app->make('App\Services\EsmSessionManager');
            $accountCode = $esmSessionManager->getAccountCode();
            // 先頭を大文字に
            $accountCode = Str::ucfirst($accountCode);
            $moduleClass = "App\\Modules\\Payment\\{$accountCode}\\{$module}";
            if (class_exists($moduleClass)) {
                return $app->make($moduleClass);
            } else {
                return $app->make("App\\Modules\\Payment\\Base\\{$module}");
            }
        });
    }

}
