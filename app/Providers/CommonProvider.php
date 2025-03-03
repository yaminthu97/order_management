<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;

class CommonProvider extends ServiceProvider
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
        //
        // モジュール系
        $this->bindModule(\App\Modules\Common\Base\SearchInvoiceSystemInterface::class, 'SearchInvoiceSystem');
        $this->bindModule(\App\Modules\Common\Base\SearchDeliveryTimeHopeInterface::class, 'SearchDeliveryTimeHope');
        $this->bindModule(\App\Modules\Common\Base\GetPrefecturalInterface::class, 'GetPrefectural');
        $this->bindModule(\App\Modules\Common\Base\SearchPrefecturalInterface::class, 'SearchPrefectural');
        $this->bindModule(\App\Modules\Common\Base\SearchCampaignsInterface::class, 'SearchCampaigns');
        $this->bindModule(\App\Modules\Common\Base\SearchNoshiNamingPatternInterface::class, 'SearchNoshiNamingPattern');
        $this->bindModule(\App\Modules\Common\Base\SearchNoshiFormatInterface::class, 'SearchNoshiFormat');
        $this->bindModule(\App\Modules\Common\Base\SearchNoshiDetailInterface::class, 'SearchNoshiDetail');
        $this->bindModule(\App\Modules\Common\Base\GetStoreGroupInterface::class, 'GetStoreGroup');
        $this->bindModule(\App\Modules\Common\Base\GetOrderTypeInterface::class, 'GetOrderType');
        $this->bindModule(\App\Modules\Common\Base\SearchDeliveryCompanyTimeHopeInterface::class, 'SearchDeliveryCompanyTimeHope');
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
            $moduleClass = "App\\Modules\\Common\\{$accountCode}\\{$module}";
            if(class_exists($moduleClass)) {
                return $app->make($moduleClass);
            } else {
                return $app->make("App\\Modules\\Common\\Base\\{$module}");
            }
        });
    }
}
