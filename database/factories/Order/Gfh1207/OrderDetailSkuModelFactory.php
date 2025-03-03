<?php

namespace Database\Factories\Order\Gfh1207;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Order\Gfh1207\OrderDetailSkuModel>
 */
class OrderDetailSkuModelFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            //
            'order_dtl_seq' => 1,
            'item_vol' => 1,
            'temperature_type' => 0,
            'order_bundle_type' => 1,
            'direct_delivery_type' => 0,
            'gift_type' => 1,
            'item_cost' => 0.00,
        ];
    }
}
