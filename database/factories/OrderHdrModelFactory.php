<?php

namespace Database\Factories;

use App\Models\Order\Base\OrderHdrModel;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Carbon\Carbon;

class OrderHdrModelFactory extends Factory
{
    protected $model = OrderHdrModel::class;

    public function definition()
    {
        return [
            'm_account_id' => 1,
            'ec_order_num' => 1,
            'order_operator_id' => 1,
            'order_type' => 1,
            'sales_store' => 1,
            'm_ecs_id' => 1,
            'm_cust_id' => 1,
            'order_tel1' => $this->faker->phoneNumber(),
            'order_tel2' => $this->faker->phoneNumber(),
            'order_fax' => $this->faker->phoneNumber(),
            'order_email1' => $this->faker->unique()->safeEmail(),
            'order_email2' => $this->faker->unique()->safeEmail(),
            'order_postal' => $this->faker->postcode(),
            'order_address1' => '東京都',
            'order_address2' => 1,
            'order_address3' => 1,
            'order_address4' => 1,
            'order_corporate_name' => $this->faker->name(),
            'order_division_name' => $this->faker->name(),
            'order_name' => $this->faker->name(),
            'order_name_kana' => $this->faker->kanaName(),
            'm_cust_id_billing' => 1,
            'billing_tel1' => $this->faker->phoneNumber(),
            'billing_tel2' => $this->faker->phoneNumber(),
            'billing_fax' => $this->faker->phoneNumber(),
            'billing_email1' => $this->faker->unique()->safeEmail(),
            'billing_email2' => $this->faker->unique()->safeEmail(),
            'billing_postal' => $this->faker->postcode(),
            'billing_address1' => '東京都',
            'billing_address2' => 1,
            'billing_address3' => 1,
            'billing_address4' => 1,
            'billing_corporate_name' => $this->faker->name(),
            'billing_division_name' => $this->faker->name(),
            'billing_name' => $this->faker->name(),
            'billing_name_kana' => $this->faker->kanaName(),
            'billing_destination_id' => 1,
            'm_payment_types_id' => 1,
            'card_company' => 1,
            'card_holder' => 1,
            'card_pay_times' => 1,
            'alert_order_flg' => 1,
            'tax_rate' => 1,
            'sell_total_price' => $this->faker->randomFloat(2, 0, 1000),
            'discount' => 1,
            'standard_discount' => 1,
            'reduce_discount' => 1,
            'shipping_fee' => 1,
            'payment_fee' => 1,
            'transfer_fee' => 1,
            'delivery_type_fee' => 1,
            'package_fee' => 1,
            'use_point' => 1,
            'use_coupon_store' => 1,
            'use_coupon_mall' => 1,
            'total_use_coupon' => 1,
            'order_total_price' => $this->faker->randomFloat(2, 0, 1000),
            'estimate_flg' => 0,
            'tax_price' => $this->faker->randomFloat(2, 0, 1000),
            'standard_total_price' => $this->faker->randomFloat(2, 0, 1000),
            'reduce_total_price' => $this->faker->randomFloat(2, 0, 1000),
            'standard_tax_price' => $this->faker->randomFloat(2, 0, 1000),
            'reduce_tax_price' => $this->faker->randomFloat(2, 0, 1000),
            'order_dtl_coupon_id' => 1,
            'payment_date' => $this->faker->date(),
            'payment_price' => $this->faker->randomFloat(2, 0, 1000),
            'order_comment' => 1,
            'gift_flg' => 1,
            'campaign_flg' => 1,
            'pending_flg' => 1,
            'immediately_deli_flg' => 1,
            'rakuten_super_deal_flg' => 1,
            'mall_member_id' => 1,
            'repeat_flg' => 1,
            'forced_deli_flg' => 1,
            'multiple_deli_flg' => 1,
            'reservation_skip_flg' => 1,
            'progress_type' => 1,
            'receipt_type' => 1,
            'progress_type_self_change' => 1,
            'progress_update_operator_id' => 1,
            'progress_update_datetime' => $this->faker->date(),
            'comment_check_type' => 1,
            'comment_check_datetime' => $this->faker->date(),
            'alert_cust_check_type' => 1,
            'alert_cust_check_datetime' => $this->faker->date(),
            'address_check_type' => 1,
            'address_check_datetime' => $this->faker->date(),
            'deli_hope_date_check_type' => null,
            'deli_hope_date_check_datetime' => $this->faker->date(),
            'credit_type' => 1,
            'credit_datetime' => $this->faker->date(),
            'payment_type' => 1,
            'payment_datetime' => $this->faker->date(),
            'reservation_type' => 1,
            'reservation_datetime' => $this->faker->date(),
            'deli_instruct_type' => 1,
            'deli_instruct_datetime' => $this->faker->date(),
            'deli_decision_type' => 1,
            'deli_decision_datetime' => $this->faker->date(),
            'settlement_sales_type' => 1,
            'settlement_sales_datetime' => $this->faker->date(),
            'sales_status_type' => 1,
            'sales_status_datetime' => $this->faker->date(),
            'bundle_order_id' => 1,
            'bundle_source_ids' => 1,
            'payment_transaction_id' => 1,
            'cb_billed_type' => 1,
            'cb_credit_status' => 1,
            'cb_deli_status' => 1,
            'cb_billed_status' => 1,
            'receipt_direction' => 1,
            'receipt_proviso' => 1,
            'last_receipt_datetime' => $this->faker->date(),
            'ec_order_change_flg' => 1,
            'ec_order_change_datetime' => $this->faker->date(),
            'ec_order_sync_flg' => 1,
            'ec_order_sync_datetime' => $this->faker->date(),
            'order_datetime' => $this->faker->date(),
            'order_count' => 1,
            't_billing_hdr_id' => 1,
            'data_mask_date' => $this->faker->date(),
            'cancel_type' => 1,
            'cancel_note' => 1,
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