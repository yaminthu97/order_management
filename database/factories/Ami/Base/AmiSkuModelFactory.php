<?php

namespace Database\Factories\Ami\Base;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Ami\Base\AmiSkuModel>
 */
class AmiSkuModelFactory extends Factory
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
            'sku_cd' => 'SKUTEST' . $this->faker->randomFloat(2, 0, 10000),
            'sku_name' => 'テスト商品',
            'including_package_flg' => 1,
            'direct_delivery_flg' => 1,
            'three_temperature_zone_type' => 0,
            'gift_flg' => 1,
            'search_result_display_flg' => 1,
            'stock_cooperation_status' => 0,
            'warehouse_cooperation_status' => 0,
            'sales_price' => 1000,
            'item_price' => 1000,
            'item_cost' => 0.00,
            'sell_limit_stock' => 0,
        ];
    }
}
