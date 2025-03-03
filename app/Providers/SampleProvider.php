<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;

class SampleProvider extends ServiceProvider
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
        $this->bindModule(\App\Modules\Sample\Base\SearchSampleInterface::class, 'SearchSample');
        $this->bindModule(\App\Modules\Sample\Base\GetSamplePrefecturalInterface::class, 'GetSamplePrefectural');
        $this->bindModule(\App\Modules\Sample\Base\FindSampleInterface::class, 'FindSample');
        $this->bindModule(\App\Modules\Sample\Base\NewSampleInterface::class, 'NewSample');
        $this->bindModule(\App\Modules\Sample\Base\GetCustomerRankSampleInterface::class, 'GetCustomerRankSample');
        $this->bindModule(\App\Modules\Sample\Base\StoreCheckSampleInterface::class, 'StoreCheckSample');
        $this->bindModule(\App\Modules\Sample\Base\UpdateCheckSampleInterface::class, 'UpdateCheckSample');
        $this->bindModule(\App\Modules\Sample\Base\NotifySampleInterface::class, 'NotifySample');
        $this->bindModule(\App\Modules\Sample\Base\StoreSampleInterface::class, 'StoreSample');
        $this->bindModule(\App\Modules\Sample\Base\UpdateSampleInterface::class, 'UpdateSample');
        $this->bindModule(\App\Modules\Sample\Base\DeleteSampleInterface::class, 'DeleteSample');

        $this->bindFormRequest(\App\Http\Requests\Sample\Base\SearchSampleRequest::class, 'SearchSampleRequest');
        $this->bindFormRequest(\App\Http\Requests\Sample\Base\NewSampleRequest::class, 'NewSampleRequest');
        $this->bindFormRequest(\App\Http\Requests\Sample\Base\EditSampleRequest::class, 'EditSampleRequest');
        $this->bindFormRequest(\App\Http\Requests\Sample\Base\NewNotifySampleRequest::class, 'NewNotifySampleRequest');
        $this->bindFormRequest(\App\Http\Requests\Sample\Base\EditNotifySampleRequest::class, 'EditNotifySampleRequest');
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
            $moduleClass = "App\\Modules\\Sample\\{$accountCode}\\{$module}";
            if(class_exists($moduleClass)) {
                return $app->make($moduleClass);
            } else {
                return $app->make("App\\Modules\\Sample\\Base\\{$module}");
            }
        });
    }

    /**
     * モデルのバインド
     */

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
            $moduleClass = "App\\Http\\Requests\\Sample\\{$accountCode}\\{$module}";
            if(class_exists($moduleClass)) {
                return $app->make($moduleClass);
            } else {
                return $app->make("App\\Http\\Requests\\Sample\\Base\\{$module}");
            }
        });
    }
}
