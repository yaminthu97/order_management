<?php

namespace Database\Factories;

use App\Models\Order\Base\OrderDestinationModel;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Carbon\Carbon;

class OrderDestinationModelFactory extends Factory
{
    protected $model = OrderDestinationModel::class;

    public function definition()
    {
        return [
            'm_account_id' => 1,
            't_order_hdr_id' => 1,
            'order_destination_seq' => 1,
            'destination_alter_flg' => 1,
            'destination_id' => 1,
            'destination_tel' => $this->faker->phoneNumber(),
            'destination_postal' => $this->faker->postcode(),
            'destination_address1' => '東京都',
            'destination_address2' => 1,
            'destination_address3' => 1,
            'destination_address4' => 1,
            'destination_company_name' => $this->faker->name(),
            'destination_division_name' => $this->faker->name(),
            'destination_name_kana' => $this->faker->kanaName(),
            'destination_name' => $this->faker->name(),
            'deli_hope_date' => $this->faker->date(),
            'deli_hope_time_name' => $this->faker->name(),
            'm_delivery_time_hope_id' => 1,
            'deli_hope_time_cd' => 1,
            'm_delivery_type_id' => 1,
            'shipping_fee' => 1,
            'payment_fee' => 1,
            'wrapping_fee' => 1,
            'deli_plan_date' => $this->faker->date(),
            'area_cd' => 1,
            'gift_message' => 1,
            'gift_wrapping' => 1,
            'nosi_type' => 1,
            'nosi_name' => $this->faker->name(),
            'gp1_type' => 1,
            'gp2_type' => 1,
            'gp3_type' => 1,
            'gp4_type' => 1,
            'gp5_type' => 1,
            'sender_name' => $this->faker->name(),
            'invoice_comment' => 1,
            'picking_comment' => 1,
            'pending_flg' => 1,
            'total_deli_flg' => 1,
            'total_temperature_zone_type' => 1,
            'partial_deli_flg' => 1,
            'multi_warehouse_flg' => 1,
            'ec_destination_num' => 1,
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