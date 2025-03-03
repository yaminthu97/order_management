<?php

namespace Database\Factories\Ami\Base;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Ami\Base\AmiEcPageSkuModel>
 */
class AmiEcPageSkuModelFactory extends Factory
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
            'sales_sku_type' => 'P',
            'sku_vol' => 1,
            'rakuten_sale_price' => 1000,
            'rakuten_display_priceId' => 0,
            'rakuten_is_stock_notice_button' => 0,
            'rakuten_is_noshi_enable' => 0,
            'rakuten_inventory_quantity' => 0,
            'rakuten_restore_inventory_flag' => 0,
            'rakuten_backorder' => 0,
            'rakuten_sku_inventory_specify' => 0,
            'rakuten_is_included_postage' => 0,
            'rakuten_overseas_delivery_id' => 0,
            'rakuten_regular_product_sale_price' => 0,
            'rakuten_first_time_price' => 0,

        ];
    }
}
