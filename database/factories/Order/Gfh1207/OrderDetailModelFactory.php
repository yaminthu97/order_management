<?php

namespace Database\Factories\Order\Gfh1207;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Order\Gfh1207\OrderDetailModel>
 */
class OrderDetailModelFactory extends Factory
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
            'order_destination_seq' => 1,
            'order_dtl_seq' => 1,
            'order_sell_price' => 1000,
            'order_cost' => 0.00,
            'order_time_sell_vol' => 1,
            'order_sell_vol' => 1,
            'tax_rate' => 0.10,
        ];
    }
}
