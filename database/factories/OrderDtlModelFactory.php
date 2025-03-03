<?php

namespace Database\Factories;

use App\Models\Order\Base\OrderDtlModel;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Carbon\Carbon;

class OrderDtlModelFactory extends Factory
{
    protected $model = OrderDtlModel::class;

    public function definition()
    {
        return [
            'm_account_id' => 1,
            't_order_hdr_id' => 1,
            't_order_destination_id' => 1,
            'order_destination_seq' => 1,
            'order_dtl_seq' => 1,
            'ecs_id' => 1,
            'sell_id' => 1,
            'sell_cd' => 1,
            'sell_option' => 1,
            'sell_name' => $this->faker->name(),
            'order_dtl_coupon_id' => 1,
            'order_dtl_coupon_price' => $this->faker->randomFloat(2, 0, 1000),
            'order_sell_price' => $this->faker->randomFloat(2, 0, 1000),
            'order_cost' => 1,
            'order_time_sell_vol' => 1,
            'order_sell_vol' => 1,
            'tax_rate' => 1,
            'tax_price' => $this->faker->randomFloat(2, 0, 1000),
            'order_return_vol' => 1,
            'temp_reservation_flg' => 1,
            'reservation_date' => $this->faker->date(),
            'deli_instruct_date' => $this->faker->date(),
            'deli_decision_date' => $this->faker->date(),
            't_deli_hdr_id' => 1,
            'bundle_from_order_id' => 1,
            'bundle_from_order_dtl_id' => 1,
            'attachment_item_group_id' => 1,
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