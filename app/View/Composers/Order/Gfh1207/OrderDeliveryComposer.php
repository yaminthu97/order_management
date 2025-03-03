<?php
namespace App\View\Composers\Order\Gfh1207;

use App\Enums\ThreeTemperatureZoneTypeEnum;
use Illuminate\View\View;

class OrderDeliveryComposer
{
    public function compose(View $view)
    {
        $view->with('threeTemperatureZoneTypes', ThreeTemperatureZoneTypeEnum::cases());
    }
}
