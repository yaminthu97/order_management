<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;

class CustomerProvider extends ServiceProvider
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
        $this->bindMudule(\App\Modules\Customer\Base\SearchCustomerInterface::class, 'SearchCustomer');
        $this->bindMudule(\App\Modules\Customer\Base\SearchDestinationsInterface::class, 'SearchDestinations');
        $this->bindMudule(\App\Modules\Customer\Base\CustomerCsvImpBatchExecuteInterface::class, 'CustomerCsvImpBatchExecute');
        $this->bindMudule(\App\Modules\Customer\Base\CustomerCsvExpBatchExecuteInterface::class, 'CustomerCsvExpBatchExecute');
        $this->bindMudule(\App\Modules\Customer\Base\SearchCustCommunicationInterface::class, 'SearchCustCommunication');
        $this->bindMudule(\App\Modules\Customer\Base\SearchCcCustomerListInterface::class, 'SearchCcCustomerList');
        $this->bindMudule(\App\Modules\Customer\Base\FindCustomerInfoInterface::class, 'FindCustomerInfo');
        $this->bindMudule(\App\Modules\Customer\Base\CreateSessionParamsInterface::class, 'CreateSessionParams');
        $this->bindMudule(\App\Modules\Customer\Base\SetCustHistOutputBatchExecuteInterface::class, 'SetCustHistOutputBatchExecute');
        $this->bindMudule(\App\Modules\Customer\Base\SearchCustCommunicationInterface::class, 'SearchCustCommunication');
        $this->bindMudule(\App\Modules\Customer\Base\CheckCustomerInterface::class, 'CheckCustomer');
        $this->bindMudule(\App\Modules\Customer\Base\StoreCustomerInterface::class, 'StoreCustomer');
        $this->bindMudule(\App\Modules\Customer\Base\GetPostalCodeInterface::class, 'GetPostalCode');
        $this->bindMudule(\App\Modules\Customer\Base\FindCustomerInterface::class, 'FindCustomer');

        $this->bindMudule(\App\Modules\Customer\Base\FindCustCommunicationInterface::class, 'FindCustCommunication');
        $this->bindMudule(\App\Modules\Customer\Base\FindCustCommunicationDtlInterface::class, 'FindCustCommunicationDtl');
        $this->bindMudule(\App\Modules\Customer\Base\NotifyCustCommunicationInterface::class, 'NotifyCustCommunication');
        $this->bindMudule(\App\Modules\Customer\Base\StoreCustCommunicationInterface::class, 'StoreCustCommunication');
        $this->bindMudule(\App\Modules\Customer\Base\NewCustCommunicationInterface::class, 'NewCustCommunication');
        $this->bindMudule(\App\Modules\Customer\Base\UpdateCustCommunicationInterface::class, 'UpdateCustCommunication');
        $this->bindMudule(\App\Modules\Customer\Base\SetReportOutputBatchExecuteInterface::class, 'SetReportOutputBatchExecute');
        $this->bindMudule(\App\Modules\Customer\Base\GetDeleteAuthorityInterface::class, 'GetDeleteAuthority');
        $this->bindMudule(\App\Modules\Customer\Base\DeleteCustCommunicationDtlInterface::class, 'DeleteCustCommunicationDtl');
        $this->bindMudule(\App\Modules\Customer\Base\StoreCustCommunicationDtlInterface::class, 'StoreCustCommunicationDtl');

        $this->bindFormRequest(\App\Http\Requests\Customer\Base\CustCommunicationRequest::class, 'CustCommunicationRequest');
        $this->bindFormRequest(\App\Http\Requests\Customer\Base\NewNotifyCustCommRequest::class, 'NewNotifyCustCommRequest');
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
            $moduleClass = "App\\Modules\\Customer\\{$accountCode}\\{$module}";
            if (class_exists($moduleClass)) {
                return $app->make($moduleClass);
            } else {
                return $app->make("App\\Modules\\Customer\\Base\\{$module}");
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
            $moduleClass = "App\\Http\\Requests\\Customer\\{$accountCode}\\{$module}";
            if(class_exists($moduleClass)) {
                return $app->make($moduleClass);
            } else {
                return $app->make("App\\Http\\Requests\\Customer\\Base\\{$module}");
            }
        });
    }
}
