<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;

class OrderProvider extends ServiceProvider
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
        $this->bindModule(\App\Modules\Order\Base\GetOrderListConditionsInterface::class, 'GetOrderListConditions');
        $this->bindModule(\App\Modules\Order\Base\SetOrderListConditionsInterface::class, 'SetOrderListConditions');
        $this->bindModule(\App\Modules\Order\Base\FindOrderInterface::class, 'FindOrder');
        $this->bindModule(\App\Modules\Order\Base\SearchInterface::class, 'Search');
        $this->bindModule(\App\Modules\Order\Base\RetrieveDeliveryInfoInterface::class, 'RetrieveDeliveryInfo');
        $this->bindModule(\App\Modules\Order\Base\CheckOperatorAuthInterface::class, 'CheckOperatorAuth');
        $this->bindModule(\App\Modules\Order\Base\FindOrderDeliveryInterface::class, 'FindOrderDelivery');
        $this->bindModule(\App\Modules\Order\Base\UpdateOrderDeliveryInterface::class, 'UpdateOrderDelivery');
        $this->bindModule(\App\Modules\Order\Base\GetExtendDataInterface::class, 'GetExtendData');
        $this->bindModule(\App\Modules\Order\Base\SetOutputBatchExecuteInterface::class, 'SetOutputBatchExecute');
        $this->bindModule(\App\Modules\Order\Base\SetInputBatchExecuteInterface::class, 'SetInputBatchExecute');
        $this->bindModule(\App\Modules\Order\Base\GetOrderCountsInterface::class, 'GetOrderCounts');
        $this->bindModule(\App\Modules\Order\Base\SearchOrderTagMasterInterface::class, 'SearchOrderTagMaster');
        $this->bindModule(\App\Modules\Order\Base\SearchDeliveryTypesInterface::class, 'SearchDeliveryTypes');
        $this->bindModule(\App\Modules\Order\Base\SearchDeliveryFeesInterface::class, 'SearchDeliveryFees');
        $this->bindModule(\App\Modules\Order\Base\UpdateOrderTagInterface::class, 'UpdateOrderTag');
        $this->bindModule(\App\Modules\Order\Base\AddCampaignItemInterface::class, 'AddCampaignItem');
        $this->bindModule(\App\Modules\Order\Base\AddBillingTypeInterface::class, 'AddBillingType');
        $this->bindModule(\App\Modules\Order\Base\SetCalcSubTotalInterface::class, 'SetCalcSubTotal');
        $this->bindModule(\App\Modules\Order\Base\SerchMailSendHistoryInterface::class, 'SerchMailSendHistory');
        $this->bindModule(\App\Modules\Order\Base\SearchSettlementHistoryInterface::class, 'SearchSettlementHistory');
        $this->bindModule(\App\Modules\Order\Base\SearchPaymentInterface::class, 'SearchPayment');
        $this->bindModule(\App\Modules\Order\Base\SearchReportOutputHistoryInterface::class, 'SearchReportOutputHistory');
        $this->bindModule(\App\Modules\Order\Base\SearchCooperationHistoryInterface::class, 'SearchCooperationHistory');
        $this->bindModule(\App\Modules\Order\Base\UpdateApiOrderProgressInterface::class, 'UpdateApiOrderProgress');
        $this->bindModule(\App\Modules\Order\Base\UpdateOrderInfoInterface::class, 'UpdateOrderInfo');
        $this->bindModule(\App\Modules\Order\Base\SearchOrderHdrLogInterface::class, 'SearchOrderHdrLog');
        $this->bindModule(\App\Modules\Order\Base\SearchOrderTagInterface::class, 'SearchOrderTag');
        $this->bindModule(\App\Modules\Order\Base\SearchProgressUpdateHistoryInterface::class, 'SearchProgressUpdateHistory');
        $this->bindModule(\App\Modules\Order\Base\ImportEcbeingOrderDataInterface::class, 'ImportEcbeingOrderData');
        $this->bindModule(\App\Modules\Order\Base\ImportEcbeingCustDataInterface::class, 'ImportEcbeingCustData');
        $this->bindModule(\App\Modules\Order\Base\GetSecurityValueInterface::class, 'GetSecurityValue');
        $this->bindModule(\App\Modules\Order\Base\GetTsvExportFilePathInterface::class, 'GetTsvExportFilePath');
        $this->bindModule(\App\Modules\Order\Base\GetTemplateFileNameInterface::class, 'GetTemplateFileName');
        $this->bindModule(\App\Modules\Order\Base\GetTemplateFilePathInterface::class, 'GetTemplateFilePath');
        $this->bindModule(\App\Modules\Order\Base\GetExcelExportFilePathInterface::class, 'GetExcelExportFilePath');
        $this->bindModule(\App\Modules\Common\Base\CheckBatchParameterInterface::class, 'CheckBatchParameter');
        $this->bindModule(\App\Modules\Order\Base\SearchShippingOrderInterface::class, 'SearchShippingOrder');
        $this->bindModule(\App\Modules\Order\Base\GetInspectionDataInterface::class, 'GetInspectionData');
        $this->bindModule(\App\Modules\Order\Base\CallEcbeingApiInterface::class, 'CallEcbeingApi');
        $this->bindModule(\App\Modules\Order\Base\SendEcbeingShipDataInterface::class, 'SendEcbeingShipData');
        $this->bindModule(\App\Modules\Order\Base\SendEcbeingNyukinOrderDataInterface::class, 'SendEcbeingNyukinOrderData');
        $this->bindModule(\App\Modules\Order\Base\GetTextExportFilePathInterface::class, 'GetTextExportFilePath');
        $this->bindModule(\App\Modules\Order\Base\TsvFormatCheckInterface::class, 'TsvFormatCheck');

        $this->bindModule(\App\Modules\Order\Base\GetZipExportFilePathInterface::class, 'GetZipExportFilePath');

        $this->bindModule(\App\Modules\Order\Base\UpdateOrderInterface::class, 'UpdateOrder');
        $this->bindModule(\App\Modules\Order\Base\UpdateOrderCheckInterface::class, 'UpdateOrderCheck');
        $this->bindModule(\App\Modules\Order\Base\RegisterOrderDrawingInterface::class, 'RegisterOrderDrawing');
        $this->bindModule(\App\Modules\Order\Base\RegisterOrderTagAutoInterface::class, 'RegisterOrderTagAuto');
        $this->bindModule(\App\Modules\Order\Base\RegisterOrderProgressInterface::class, 'RegisterOrderProgress');
        $this->bindModule(\App\Modules\Order\Base\RegisterSendmailInterface::class, 'RegisterSendmail');
        $this->bindModule(\App\Modules\Order\Base\RegisterMailSendHistoryInterface::class, 'RegisterMailSendHistory');
        $this->bindModule(\App\Modules\Order\Base\GetCsvExportFilePathInterface::class, 'GetCsvExportFilePath');
        $this->bindModule(\App\Modules\Order\Base\UpdateCampaignItemInterface::class, 'UpdateCampaignItem');

        $this->bindModule(\App\Modules\Order\Base\GetProcessDateTimeInterface::class, 'GetProcessDateTime');
        $this->bindModule(\App\Modules\Order\Base\SearchCreateNoshiInterface::class, 'SearchCreateNoshi');
        $this->bindModule(\App\Modules\Order\Base\UpdateOrderDtlNoshiInterface::class, 'UpdateOrderDtlNoshi');
        $this->bindModule(\App\Modules\Order\Base\SearchPaymentAccountingInterface::class, 'SearchPaymentAccounting');
        $this->bindModule(\App\Modules\Order\Base\FindOrderDestinationInterface::class, 'FindOrderDestination');
        $this->bindModule(\App\Modules\Order\Base\CreateBillingOutputInterface::class, 'CreateBillingOutput');
        $this->bindModule(\App\Modules\Order\Base\GetTemplateDataInterface::class, 'GetTemplateData');
        $this->bindModule(\App\Modules\Order\Base\SearchTemplateDataInterface::class, 'SearchTemplateData');

        $this->bindModule(\App\Modules\Order\Base\GetOrderTagTblDictInterface::class, 'GetOrderTagTblDict');
        $this->bindModule(\App\Modules\Order\Base\GetOrderTagColDictInterface::class, 'GetOrderTagColDict');
        $this->bindModule(\App\Modules\Order\Base\GetOrderTagColDictInterface::class, 'GetOrderTagColDict');
        $this->bindModule(\App\Modules\Order\Base\SearchOrderTagMasterModuleInterface::class, 'SearchOrderTagMasterModule');
        $this->bindModule(\App\Modules\Order\Base\NewOrderTagMasterInterface::class, 'NewOrderTagMaster');
        $this->bindModule(\App\Modules\Order\Base\FindOrderTagMasterInterface::class, 'FindOrderTagMaster');
        $this->bindModule(\App\Modules\Order\Base\UpdateOrderTagMasterInterface::class, 'UpdateOrderTagMaster');
        $this->bindModule(\App\Modules\Order\Base\NotifyOrderTagMasterInterface::class, 'NotifyOrderTagMaster');
        $this->bindModule(\App\Modules\Order\Base\StoreOrderTagMasterInterface::class, 'StoreOrderTagMaster');
        $this->bindModule(\App\Modules\Order\Base\UpdateOrderTagMasterInterface::class, 'UpdateOrderTagMaster');


        // フォームリクエスト系
        $this->bindFormRequest(\App\Http\Requests\Order\Base\UpdateOrderDeliveryRequest::class, 'UpdateOrderDeliveryRequest');
        $this->bindFormRequest(\App\Http\Requests\Order\Base\UpdateOrderRequest::class, 'UpdateOrderRequest');
        $this->bindFormRequest(\App\Http\Requests\Order\Base\EditOrderRequest::class, 'EditOrderRequest');

        $this->bindFormRequest(\App\Http\Requests\Order\Base\SearchOrderTagMasterRequest::class, 'SearchOrderTagMasterRequest');
        $this->bindFormRequest(\App\Http\Requests\Order\Base\NewOrderTagMasterRequest::class, 'NewOrderTagMasterRequest');
        $this->bindFormRequest(\App\Http\Requests\Order\Base\EditOrderTagMasterRequest::class, 'EditOrderTagMasterRequest');
        $this->bindFormRequest(\App\Http\Requests\Order\Base\NewNotifyOrderTagMasterRequest::class, 'NewNotifyOrderTagMasterRequest');

        // Enum 系
        $this->bindEnums(\App\Modules\Order\Base\Enums\InspectionStatusEnumInterface::class, 'InspectionStatusEnum');
        $this->bindEnums(\App\Modules\Order\Base\Enums\ShipNyukinRunTypeEnumInterface::class, 'ShipNyukinRunTypeEnum');
        $this->bindEnums(\App\Modules\Order\Base\Enums\ShipNyukinExportTypeEnumInterface::class, 'ShipNyukinExportTypeEnum');
        $this->bindEnums(\App\Modules\Order\Base\Enums\OrderCustomerRunTypeEnumInterface::class, 'OrderCustomerRunTypeEnum');
        $this->bindEnums(\App\Modules\Order\Base\Enums\OrderCustomerImportTypeEnumInterface::class, 'OrderCustomerImportTypeEnum');
        $this->bindEnums(\App\Modules\Order\Base\Enums\ShippedDataReportTypeEnumInterface::class, 'ShippedDataReportTypeEnum');
        $this->bindEnums(\App\Modules\Order\Base\Enums\EcbeingExecuteTypeInterface::class, 'EcbeingExecuteType');
        
        $this->bindEnums(\App\Modules\Order\Base\Enums\OutputSubmitCommandsInterface::class, 'OutputSubmitCommands');
        $this->bindEnums(\App\Modules\Order\Base\Enums\InputSubmitCommandsInterface::class, 'InputSubmitCommands');

        $this->bindEnums(\App\Modules\Order\Base\Enums\AutoTimmingEnumInterface::class, 'AutoTimmingEnum');
        $this->bindEnums(\App\Modules\Order\Base\Enums\FontColorEnumInterface::class, 'FontColorEnum');
        $this->bindEnums(\App\Modules\Order\Base\Enums\AndOrEnumInterface::class, 'AndOrEnum');
        $this->bindEnums(\App\Modules\Order\Base\Enums\OperatorEnumInterface::class, 'OperatorEnum');
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
            $moduleClass = "App\\Modules\\Order\\{$accountCode}\\{$module}";
            if (class_exists($moduleClass)) {
                return $app->make($moduleClass);
            } else {
                return $app->make("App\\Modules\\Order\\Base\\{$module}");
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
            $moduleClass = "App\\Http\\Requests\\Order\\{$accountCode}\\{$module}";
            if (class_exists($moduleClass)) {
                return $app->make($moduleClass);
            } else {
                return $app->make("App\\Http\\Requests\\Order\\Base\\{$module}");
            }
        });
    }

    /**
     * Enumsのバインド
     */
    private function bindEnums($interface, $module)
    {
        $this->app->bind($interface, function ($app) use ($module) {
            $esmSessionManager = $app->make('App\Services\EsmSessionManager');
            $accountCode = $esmSessionManager->getAccountCode();
            // Pascal Case に変換
            $accountCode = Str::studly($accountCode);
            $moduleClass = "App\\Modules\\Order\\{$accountCode}\\Enums\\{$module}";
            if(class_exists($moduleClass)) {
                return $moduleClass;
            } else {
                return "App\\Modules\\Order\\Base\\Enums\\{$module}";
            }
        });
    }
}
