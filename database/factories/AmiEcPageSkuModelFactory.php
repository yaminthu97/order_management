<?php

namespace Database\Factories;

use App\Models\Ami\Base\AmiEcPageSkuModel;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Carbon\Carbon;

class AmiEcPageSkuModelFactory extends Factory
{
    protected $model = AmiEcPageSkuModel::class;

    public function definition()
    {
        return [
            'm_ami_ec_page_id' => null,
            'm_ami_sku_id' => null,
            'sales_sku_type' => 1,
            'sku_vol' => 1,
            'm_ami_ec_page_variation_choice_id1' => 1,
            'm_ami_ec_page_variation_choice_id2' => 1,
            'm_ami_ec_page_variation_choice_id3' => 1,
            'm_ami_ec_page_variation_choice_id4' => 1,
            'm_ami_ec_page_variation_choice_id5' => 1,
            'm_ami_ec_page_variation_choice_id6' => 1,
            'rakuten_sku_control_number' => 1,
            'rakuten_system_linkage_sku_no' => 1,
            'rakuten_sale_price' => $this->faker->randomFloat(2, 0, 1000),
            'rakuten_display_price' => $this->faker->randomFloat(2, 0, 1000),
            'rakuten_display_priceId' => $this->faker->randomFloat(2, 0, 1000),
            'rakuten_order_limit' => 1,
            'rakuten_is_stock_notice_button' => 1,
            'rakuten_is_noshi_enable' => 1,
            'rakuten_inventory_quantity' => 1,
            'rakuten_restore_inventory_flag' => 1,
            'rakuten_backorder' => 1,
            'rakuten_normal_delivery_date_id' => $this->faker->date(),
            'rakuten_backorder_delivery_date_id' => $this->faker->date(),
            'rakuten_sku_inventory_specify' => 1,
            'rakuten_asuraku_delivery_id' => 1,
            'rakuten_delivery_set_id' => 1,
            'rakuten_is_included_postage' => 1,
            'rakuten_postage_segment1' => 1,
            'rakuten_postage_segment2' => 1,
            'rakuten_postage' => 1,
            'rakuten_shop_area_soryo_pattern_id' => 1,
            'rakuten_single_item_shipping' => 1,
            'rakuten_overseas_delivery_id' => 1,
            'rakuten_catalog_id' => 1,
            'rakuten_catalogId_exemption_reason' => 1,
            'rakuten_set_product_catalog_id' => 1,
            'rakuten_sku_image_type' => 1,
            'rakuten_sku_image_path' => 1,
            'rakuten_sku_image_alt' => 1,
            'rakuten_regular_product_sale_price' => $this->faker->randomFloat(2, 0, 1000),
            'rakuten_first_time_price' => $this->faker->randomFloat(2, 0, 1000),
            'rakuten_normal_shipping_lead_time' => 1,
            'rakuten_backorder_shipping_lead_time' => 1,
            'rakuten_shipping_source_address' => 1,
            'm_account_id' => 1,
            'entry_operator_id' => 1,
            'entry_timestamp' => Carbon::now(),

        ];
    }

    public function createWithDatabase(array $attributes = [], $database = null)
    {
        if ($database) {
            // 現在の接続を取得
            $defaultConnection = config('database.connections.mysql');

            // 新しい接続設定を作成
            $newConnection = array_merge($defaultConnection, [
                'database' => $database,
                ]);

            // 新しい接続を設定
            config(['database.connections.tenant' => $newConnection]);

            // 接続を再設定
            DB::purge('tenant');
            DB::reconnect('tenant');

            // スキーマを設定
            DB::statement('USE ' . $database);
        }

        return parent::create($attributes);
    }
}