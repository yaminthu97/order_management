<?php

namespace App\Providers\Views;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\View;

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
        /**
         * 企業ごとに異なる画面を作成したとき、独自のデータが必要になる場合は、ここにビューコンポーザを追加する。
         */
        View::composer('order.gfh_1207.order-delivery-info', 'App\View\Composers\Order\Gfh1207\OrderDeliveryComposer');
        View::composer('order.gfh_1207.info', 'App\View\Composers\Order\Gfh1207\OrderInfoComposer');
    }
}
