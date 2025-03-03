<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;

class AmiProvider extends ServiceProvider
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
        $this->bindMudule(\App\Modules\Ami\Base\SearchAmiPageInterface::class, 'SearchAmiPage');
        $this->bindMudule(\App\Modules\Ami\Base\SearchAmiAttachmentInterface::class, 'SearchAmiAttachment');
        $this->bindMudule(\App\Modules\Ami\Base\SearchAmiPageNoshiInterface::class, 'SearchAmiPageNoshi');
        $this->bindMudule(\App\Modules\Ami\Base\SearchAmiEcAttachmentInterface::class, 'SearchAmiEcAttachment');
        $this->bindMudule(\App\Modules\Ami\Base\SearchAttachmentitemModuleInterface::class, 'SearchAttachmentitemModule'); //付属品マスタリスト
        $this->bindMudule(\App\Modules\Ami\Base\SaveAttachmentitemModuleInterface::class, 'SaveAttachmentitemModule'); //付属品マスタ保存
        $this->bindMudule(\App\Modules\Ami\Base\GetOneAttachmentitemModuleInterface::class, 'GetOneAttachmentitemModule'); //１件取得
        $this->bindMudule(\App\Modules\Ami\Base\SearchAmiEcAttachmentInterface::class, 'SearchAmiEcAttachment');
        $this->bindMudule(\App\Modules\Ami\Base\SearchAmiPageNoshiInterface::class, 'SearchAmiPageNoshi');
        $this->bindMudule(\App\Modules\Ami\Base\FindAmiSkuInterface::class, 'FindAmiSku');
        $this->bindMudule(\App\Modules\Ami\Base\FindAmiPageInterface::class, 'FindAmiPage');
        $this->bindMudule(\App\Modules\Ami\Base\FindAmiPageNoshiInterface::class, 'FindAmiPageNoshi');
        $this->bindMudule(\App\Modules\Ami\Base\FindAmiPageAttachmentInterface::class, 'FindAmiPageAttachment');
        $this->bindMudule(\App\Modules\Ami\Base\StoreAmiPageNoshiInterface::class, 'StoreAmiPageNoshi');
        $this->bindMudule(\App\Modules\Ami\Base\StoreAmiPageAttachmentInterface::class, 'StoreAmiPageAttachment');
        $this->bindMudule(\App\Modules\Ami\Base\UpdateAmiPageInterface::class, 'UpdateAmiPage');
        $this->bindMudule(\App\Modules\Ami\Base\UpdateAmiPageNoshiInterface::class, 'UpdateAmiPageNoshi');
        $this->bindMudule(\App\Modules\Ami\Base\UpdateAmiPageAttachmentInterface::class, 'UpdateAmiPageAttachment');
        $this->bindMudule(\App\Modules\Ami\Base\DeleteAmiPageNoshiInterface::class, 'DeleteAmiPageNoshi');
        $this->bindMudule(\App\Modules\Ami\Base\DeleteAmiPageAttachmentInterface::class, 'DeleteAmiPageAttachment');
        $this->bindMudule(\App\Modules\Ami\Base\GetNoshiFormatInterface::class, 'GetNoshiFormat');

        //
        // フォームリクエスト系
        $this->bindFormRequest(\App\Http\Requests\Ami\Base\UpdateAttachmentitemRequest::class, 'UpdateAttachmentitemRequest');
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
            $moduleClass = "App\\Modules\\Ami\\{$accountCode}\\{$module}";
            if(class_exists($moduleClass)) {
                return $app->make($moduleClass);
            } else {
                return $app->make("App\\Modules\\Ami\\Base\\{$module}");
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
            $moduleClass = "App\\Http\\Requests\\Ami\\{$accountCode}\\{$module}";
            if(class_exists($moduleClass)) {
                return $app->make($moduleClass);
            } else {
                return $app->make("App\\Http\\Requests\\Ami\\Base\\{$module}");
            }
        });
    }
}
