<?php
namespace App\View\Composers\Order\Gfh1207;

use Illuminate\View\View;

class OrderInfoComposer
{
    public function compose(View $view)
    {
        // 熨斗と付属品を Lazy Eager Loading
        $order = $view->order;
        $order->load('orderDestination.orderDtls.orderDtlNoshi');
        $order->load('orderDestination.orderDtls.orderDtlAttachmentItem');

        $view->with('order', $order);
    }
}
