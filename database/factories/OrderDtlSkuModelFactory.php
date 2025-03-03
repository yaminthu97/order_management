<?php

namespace Database\Factories;

use App\Models\Order\Base\OrderDtlSkuModel;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Carbon\Carbon;

class OrderDtlSkuModelFactory extends Factory
{
    protected $model = OrderDtlSkuModel::class;

    public function definition()
    {
        return [
            'm_account_id' => 1,
            't_order_hdr_id' => 1,
            't_order_destination_id' => 1,
            'order_destination_seq' => 1,
            't_order_dtl_id' => 1,
            'order_dtl_seq' => 1,
            'ecs_id' => 1,
            'sell_cd' => 1,
            'order_sell_vol' => 1,
            'item_id' => 1,
            'item_cd' => 1,
            'item_vol' => 1,
            'm_supplier_id' => 1,
            'temperature_type' => 1,
            'order_bundle_type' => 1,
            'direct_delivery_type' => 1,
            'gift_type' => 1,
            'item_cost' => 1,
            'temp_reservation_flg' => 1,
            'm_warehouse_id' => 1,
            'reservation_date' => $this->faker->date(),
            'deli_instruct_date' => $this->faker->date(),
            'deli_decision_date' => $this->faker->date(),
            't_deli_hdr_id' => 1,
            'entry_operator_id' => 1,
            'entry_timestamp' => Carbon::now(),
            'update_operator_id' => 1,
            'update_timestamp' => Carbon::now(),
            'cancel_operator_id' => null,
            'cancel_timestamp' => null,

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