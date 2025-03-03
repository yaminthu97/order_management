<?php

namespace Database\Factories;

use App\Models\Ami\Base\AmiSkuModel;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Carbon\Carbon;

class AmiSkuModelFactory extends Factory
{
    protected $model = AmiSkuModel::class;

    public function definition()
    {
        return [
            'sku_cd' => $this->faker->unique()->regexify('[A-Z0-9]{8}'),
            'sku_name' => $this->faker->word(),
            'jan_cd' => $this->faker->unique()->regexify('[0-9]{13}'),
            'including_package_flg' => 1,
            'direct_delivery_flg' => 1,
            'three_temperature_zone_type' => 1,
            'gift_flg' => 1,
            'search_result_display_flg' => 1,
            'stock_cooperation_status' => 1,
            'warehouse_cooperation_status' => 1,
            'm_suppliers_id' => 1,
            'sales_price' => $this->faker->randomFloat(2, 0, 1000),
            'item_price' => $this->faker->randomFloat(2, 0, 1000),
            'item_cost' => 1,
            'remarks1' => 1,
            'remarks2' => 1,
            'remarks3' => 1,
            'remarks4' => 1,
            'remarks5' => 1,
            'm_account_id' => 1,
            'sell_limit_stock' => 1,
            'supplier_item_cd' => 1,
            'entry_operator_id' => 1,
            'entry_timestamp' => Carbon::now(),
            'update_operator_id' => 1,
            'update_timestamp' => Carbon::now(),

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