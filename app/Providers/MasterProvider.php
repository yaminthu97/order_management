<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;

class MasterProvider extends ServiceProvider
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
        $this->bindMudule(\App\Modules\Master\Base\GetCustomerRankInterface::class, 'GetCustomerRank');
        $this->bindMudule(\App\Modules\Master\Base\GetDeliveryTypesInterface::class, 'GetDeliveryTypes');
        $this->bindMudule(\App\Modules\Master\Base\GetSkusInterface::class, 'GetSkus');
        $this->bindMudule(\App\Modules\Master\Base\GetEcsInterface::class, 'GetEcs');
        $this->bindMudule(\App\Modules\Master\Base\GetEcsDetailInterface::class, 'GetEcsDetail');
        $this->bindMudule(\App\Modules\Master\Base\GetOrderTypesInterface::class, 'GetOrderTypes');
        $this->bindMudule(\App\Modules\Master\Base\GetOrderListDispsInterface::class, 'GetOrderListDisps');
        $this->bindMudule(\App\Modules\Master\Base\GetCancelReasonInterface::class, 'GetCancelReason');
        $this->bindMudule(\App\Modules\Master\Base\GetOperatorsInterface::class, 'GetOperators');
        $this->bindMudule(\App\Modules\Master\Base\GetDeliveryTimeHopeMapInterface::class, 'GetDeliveryTimeHopeMap');
        $this->bindMudule(\App\Modules\Master\Base\GetPaymentTypesInterface::class, 'GetPaymentTypes');
        $this->bindMudule(\App\Modules\Master\Base\SearchShopsInterface::class, 'SearchShops');
        $this->bindMudule(\App\Modules\Master\Base\SearchEcsInterface::class, 'SearchEcs');
        $this->bindMudule(\App\Modules\Master\Base\SearchItemNameTypesInterface::class, 'SearchItemNameTypes');
        $this->bindMudule(\App\Modules\Master\Base\SearchPaymentTypesInterface::class, 'SearchPaymentTypes');
        $this->bindMudule(\App\Modules\Master\Base\SearchDeliveryTypesInterface::class, 'SearchDeliveryTypes');
        $this->bindMudule(\App\Modules\Master\Base\SearchOperatorsInterface::class, 'SearchOperators');
        $this->bindMudule(\App\Modules\Master\Base\SearchWarehousesInterface::class, 'SearchWarehouses');
        $this->bindMudule(\App\Modules\Master\Base\SearchEmailTemplateInterface::class, 'SearchEmailTemplate');
        $this->bindMudule(\App\Modules\Master\Base\GetYmstTimeInterface::class, 'GetYmstTime');
        $this->bindMudule(\App\Modules\Master\Base\SearchPostalCodeInterface::class, 'SearchPostalCode');

        $this->bindMudule(\App\Modules\Master\Base\GetOneCampaignModuleInterface::class, 'GetOneCampaignModule'); //1件のみ
        $this->bindMudule(\App\Modules\Master\Base\SearchCampaignModuleInterface::class, 'SearchCampaignModule'); //リスト
        $this->bindMudule(\App\Modules\Master\Base\SaveCampaignModuleInterface::class, 'SaveCampaignModule'); //保存

        $this->bindMudule(\App\Modules\Master\Base\SearchNoshiModuleInterface::class, 'SearchNoshiModule');
        $this->bindMudule(\App\Modules\Master\Base\FindNoshiModuleInterface::class, 'FindNoshiModule');
        $this->bindMudule(\App\Modules\Master\Base\UpdateNoshiModuleInterface::class, 'UpdateNoshiModule');
        $this->bindMudule(\App\Modules\Master\Base\GetItemnameTypeInterface::class, 'GetItemnameType');
        $this->bindMudule(\App\Modules\Master\Base\GetOperatorsInterface::class, 'GetOperators');
        $this->bindMudule(\App\Modules\Master\Base\GetDeliveryMethodInterface::class, 'GetDeliveryMethod');
        $this->bindMudule(\App\Modules\Master\Base\GetShopGfhInterface::class, 'GetShopGfh');
        $this->bindMudule(\App\Modules\Master\Base\StoreShopGfhInterface::class, 'StoreShopGfh');
        $this->bindMudule(\App\Modules\Master\Base\UpdateShopGfhInterface::class, 'UpdateShopGfh');

        $this->bindMudule(\App\Modules\Master\Base\NewOperatorsInterface::class, 'NewOperators');
        $this->bindMudule(\App\Modules\Master\Base\NotifyOperatorsInterface::class, 'NotifyOperators');
        $this->bindMudule(\App\Modules\Master\Base\StoreOperatorsInterface::class, 'StoreOperators');
        $this->bindMudule(\App\Modules\Master\Base\FindOperatorsInterface::class, 'FindOperators');
        $this->bindMudule(\App\Modules\Master\Base\UpdateOperatorsInterface::class, 'UpdateOperators');
        $this->bindMudule(\App\Modules\Master\Base\GetOperationAuthoritiesInterface::class, 'GetOperationAuthorities');
        $this->bindMudule(\App\Modules\Master\Base\GetOperatorUserTypeInterface::class, 'GetOperatorUserType');

        $this->bindMudule(\App\Modules\Master\Base\NewPaymentTypesInterface::class, 'NewPaymentTypes');
        $this->bindMudule(\App\Modules\Master\Base\NotifyPaymentTypesInterface::class, 'NotifyPaymentTypes');
        $this->bindMudule(\App\Modules\Master\Base\StorePaymentTypesInterface::class, 'StorePaymentTypes');
        $this->bindMudule(\App\Modules\Master\Base\FindPaymentTypesInterface::class, 'FindPaymentTypes');
        $this->bindMudule(\App\Modules\Master\Base\UpdatePaymentTypesInterface::class, 'UpdatePaymentTypes');

        $this->bindMudule(\App\Modules\Master\Base\UpdateTemplateMasterInterface::class, 'UpdateTemplateMaster');

        $this->bindMudule(\App\Modules\Master\Base\UpdateNoshiTemplateModuleInterface::class, 'UpdateNoshiTemplateModule'); //熨斗テンプレートマスタ(熨斗詳細) 保存


        $this->bindMudule(\App\Modules\Master\Base\GetDeliveryTypeInterface::class, 'GetDeliveryType');
        $this->bindMudule(\App\Modules\Master\Base\NewWarehousesInterface::class, 'NewWarehouses');
        $this->bindMudule(\App\Modules\Master\Base\NotifyWarehousesInterface::class, 'NotifyWarehouses');
        $this->bindMudule(\App\Modules\Master\Base\StoreWarehousesInterface::class, 'StoreWarehouses');
        $this->bindMudule(\App\Modules\Master\Base\FindWarehousesInterface::class, 'FindWarehouses');
        $this->bindMudule(\App\Modules\Master\Base\UpdateWarehousesInterface::class, 'UpdateWarehouses');
        $this->bindMudule(\App\Modules\Master\Base\NewDeliveryReadtimeInterface::class, 'NewDeliveryReadtime');
        $this->bindMudule(\App\Modules\Master\Base\NotifyDeliveryReadtimeInterface::class, 'NotifyDeliveryReadtime');
        $this->bindMudule(\App\Modules\Master\Base\StoreDeliveryReadtimeInterface::class, 'StoreDeliveryReadtime');
        $this->bindMudule(\App\Modules\Master\Base\FindDeliveryReadtimeInterface::class, 'FindDeliveryReadtime');
        $this->bindMudule(\App\Modules\Master\Base\UpdateDeliveryReadtimeInterface::class, 'UpdateDeliveryReadtime');
        $this->bindMudule(\App\Modules\Master\Base\NewDeliveryFeesInterface::class, 'NewDeliveryFees');
        $this->bindMudule(\App\Modules\Master\Base\NotifyDeliveryFeesInterface::class, 'NotifyDeliveryFees');
        $this->bindMudule(\App\Modules\Master\Base\StoreDeliveryFeesInterface::class, 'StoreDeliveryFees');
        $this->bindMudule(\App\Modules\Master\Base\FindDeliveryFeesInterface::class, 'FindDeliveryFees');
        $this->bindMudule(\App\Modules\Master\Base\UpdateDeliveryFeesInterface::class, 'UpdateDeliveryFees');
        $this->bindMudule(\App\Modules\Master\Base\SearchWarehouseCalendarInterface::class, 'SearchWarehouseCalendar');
        $this->bindMudule(\App\Modules\Master\Base\SaveWarehouseCalendarInterface::class, 'SaveWarehouseCalendar');

        $this->bindMudule(\App\Modules\Master\Base\SearchDeliveryTypeInterface::class, 'SearchDeliveryType');
        $this->bindMudule(\App\Modules\Master\Base\NewDeliveryTypeInterface::class, 'NewDeliveryType');
        $this->bindMudule(\App\Modules\Master\Base\NotifyDeliveryTypeInterface::class, 'NotifyDeliveryType');
        $this->bindMudule(\App\Modules\Master\Base\SaveDeliveryTypeInterface::class, 'SaveDeliveryType');
        $this->bindMudule(\App\Modules\Master\Base\UpdateDeliveryTypeInterface::class, 'UpdateDeliveryType');

        $this->bindMudule(\App\Modules\Master\Base\FindDeliveryTypeInterface::class, 'FindDeliveryType');
        $this->bindMudule(\App\Modules\Master\Base\UpdateCheckDeliveryTypeInterface::class, 'UpdateCheckDeliveryType');

        $this->bindMudule(\App\Modules\Master\Base\SaveYmsttimeInterface::class, 'SaveYmsttime');
        $this->bindMudule(\App\Modules\Master\Base\DeleteYmstInterface::class, 'DeleteYmst');
        $this->bindMudule(\App\Modules\Master\Base\SaveYmstpostInterface::class, 'SaveYmstpost');

        $this->bindMudule(\App\Modules\Master\Base\GetNoshiInterface::class, 'GetNoshi');
        $this->bindMudule(\App\Modules\Master\Base\GetNoshiFormatInterface::class, 'GetNoshiFormat');
        $this->bindMudule(\App\Modules\Master\Base\GetNoshiNamingPatternInterface::class, 'GetNoshiNamingPattern');

        $this->bindMudule(\App\Modules\Master\Base\SearchItemnameTypeInterface::class, 'SearchItemnameType');
        $this->bindMudule(\App\Modules\Master\Base\NewItemnameTypeInterface::class, 'NewItemnameType');
        $this->bindMudule(\App\Modules\Master\Base\NotifyItemnameTypeInterface::class, 'NotifyItemnameType');
        $this->bindMudule(\App\Modules\Master\Base\StoreItemnameTypeInterface::class, 'StoreItemnameType');
        $this->bindMudule(\App\Modules\Master\Base\FindItemnameTypeInterface::class, 'FindItemnameType');
        $this->bindMudule(\App\Modules\Master\Base\UpdateItemnameTypeInterface::class, 'UpdateItemnameType');

        $this->bindMudule(\App\Modules\Master\Base\GetNoshiInterface::class, 'GetNoshi');
        $this->bindMudule(\App\Modules\Master\Base\GetNoshiFormatInterface::class, 'GetNoshiFormat');
        $this->bindMudule(\App\Modules\Master\Base\GetNoshiNamingPatternInterface::class, 'GetNoshiNamingPattern');
        $this->bindMudule(\App\Modules\Master\Base\FindReportTemplatesInterface::class, 'FindReportTemplates');
        $this->bindMudule(\App\Modules\Master\Base\SearchReportTemplatesInterface::class, 'SearchReportTemplates');

        //
        // フォームリクエスト系
        $this->bindFormRequest(\App\Http\Requests\Master\Base\NewOperatorsRequest::class, 'NewOperatorsRequest');
        $this->bindFormRequest(\App\Http\Requests\Master\Base\NewNotifyOperatorsRequest::class, 'NewNotifyOperatorsRequest');
        $this->bindFormRequest(\App\Http\Requests\Master\Base\EditOperatorsRequest::class, 'EditOperatorsRequest');
        $this->bindFormRequest(\App\Http\Requests\Master\Base\EditNotifyOperatorsRequest::class, 'EditNotifyOperatorsRequest');

        $this->bindFormRequest(\App\Http\Requests\Master\Base\UpdateNoshiRequest::class, 'UpdateNoshiRequest');
        $this->bindFormRequest(\App\Http\Requests\Master\Base\UpdateNoshiTemplateRequest::class, 'UpdateNoshiTemplateRequest');

        $this->bindFormRequest(\App\Http\Requests\Master\Base\EditDeliveryTypeRequest::class, 'EditDeliveryTypeRequest');
        $this->bindFormRequest(\App\Http\Requests\Master\Base\NewDeliveryTypeRequest::class, 'NewDeliveryTypeRequest');
        $this->bindFormRequest(\App\Http\Requests\Master\Base\SearchDeliveryTypeRequest::class, 'SearchDeliveryTypeRequest');

        $this->bindFormRequest(\App\Http\Requests\Master\Base\ShopGfhRequest::class, 'ShopGfhRequest');

        $this->bindFormRequest(\App\Http\Requests\Master\Base\EditItemnameTypeRequest::class, 'EditItemnameTypeRequest');
        $this->bindFormRequest(\App\Http\Requests\Master\Base\EditNotifyItemnameTypeRequest::class, 'EditNotifyItemnameTypeRequest');
        $this->bindFormRequest(\App\Http\Requests\Master\Base\NewItemnameTypeRequest::class, 'NewItemnameTypeRequest');
        $this->bindFormRequest(\App\Http\Requests\Master\Base\NewNotifyItemnameTypeRequest::class, 'NewNotifyItemnameTypeRequest');
        $this->bindFormRequest(\App\Http\Requests\Master\Base\SearchItemnameTypeRequest::class, 'SearchItemnameTypeRequest');

        $this->bindFormRequest(\App\Http\Requests\Master\Base\NewPaymentTypesRequest::class, 'NewPaymentTypesRequest');
        $this->bindFormRequest(\App\Http\Requests\Master\Base\NewNotifyPaymentTypesRequest::class, 'NewNotifyPaymentTypesRequest');
        $this->bindFormRequest(\App\Http\Requests\Master\Base\EditPaymentTypesRequest::class, 'EditPaymentTypesRequest');
        $this->bindFormRequest(\App\Http\Requests\Master\Base\EditNotifyPaymentTypesRequest::class, 'EditNotifyPaymentTypesRequest');


        $this->bindFormRequest(\App\Http\Requests\Master\Base\NewWarehousesRequest::class, 'NewWarehousesRequest');
        $this->bindFormRequest(\App\Http\Requests\Master\Base\NewNotifyWarehousesRequest::class, 'NewNotifyWarehousesRequest');
        $this->bindFormRequest(\App\Http\Requests\Master\Base\EditWarehousesRequest::class, 'EditWarehousesRequest');
        $this->bindFormRequest(\App\Http\Requests\Master\Base\EditNotifyWarehousesRequest::class, 'EditNotifyWarehousesRequest');
        $this->bindFormRequest(\App\Http\Requests\Master\Base\NewDeliveryReadtimeRequest::class, 'NewDeliveryReadtimeRequest');
        $this->bindFormRequest(\App\Http\Requests\Master\Base\NewNotifyDeliveryReadtimeRequest::class, 'NewNotifyDeliveryReadtimeRequest');
        $this->bindFormRequest(\App\Http\Requests\Master\Base\EditDeliveryReadtimeRequest::class, 'EditDeliveryReadtimeRequest');
        $this->bindFormRequest(\App\Http\Requests\Master\Base\EditNotifyDeliveryReadtimeRequest::class, 'EditNotifyDeliveryReadtimeRequest');
        $this->bindFormRequest(\App\Http\Requests\Master\Base\NewDeliveryFeesRequest::class, 'NewDeliveryFeesRequest');
        $this->bindFormRequest(\App\Http\Requests\Master\Base\NewNotifyDeliveryFeesRequest::class, 'NewNotifyDeliveryFeesRequest');
        $this->bindFormRequest(\App\Http\Requests\Master\Base\EditDeliveryFeesRequest::class, 'EditDeliveryFeesRequest');
        $this->bindFormRequest(\App\Http\Requests\Master\Base\EditNotifyDeliveryFeesRequest::class, 'EditNotifyDeliveryFeesRequest');
        $this->bindFormRequest(\App\Http\Requests\Master\Base\WarehouseCalendarRequest::class, 'WarehouseCalendarRequest');

        //
        // Enum 系
        $this->bindEnum(\App\Modules\Master\Base\Enums\ItemnameTypeInterface::class, 'ItemnameType');
        $this->bindEnum(\App\Modules\Master\Base\Enums\AttentionTypeInterface::class, 'AttentionType');
        $this->bindEnum(\App\Modules\Master\Base\Enums\BatchListEnumInterface::class, 'BatchListEnum');
        $this->bindEnum(\App\Modules\Master\Base\Enums\DeliveryCompanyEnumInterface::class, 'DeliveryCompanyEnum');

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
            $moduleClass = "App\\Modules\\Master\\{$accountCode}\\{$module}";
            if (class_exists($moduleClass)) {
                return $app->make($moduleClass);
            } else {
                return $app->make("App\\Modules\\Master\\Base\\{$module}");
            }
        });
    }

    /**
     * Enumのバインド
     */
    private function bindEnum($interface, $module)
    {
        $this->app->bind($interface, function ($app) use ($module) {
            $esmSessionManager = $app->make('App\Services\EsmSessionManager');
            $accountCode = $esmSessionManager->getAccountCode();
            // Pascal Case に変換
            $accountCode = Str::studly($accountCode);
            $moduleClass = "App\\Modules\\Master\\{$accountCode}\\Enums\\{$module}";
            if (class_exists($moduleClass)) {
                return $moduleClass;
            } else {
                return "App\\Modules\\Master\\Base\\Enums\\{$module}";
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
            $moduleClass = "App\\Http\\Requests\\Master\\{$accountCode}\\{$module}";
            if (class_exists($moduleClass)) {
                return $app->make($moduleClass);
            } else {
                return $app->make("App\\Http\\Requests\\Master\\Base\\{$module}");
            }
        });
    }
}
