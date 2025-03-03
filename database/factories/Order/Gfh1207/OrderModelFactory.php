<?php

namespace Database\Factories\Order\Gfh1207;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Order\Gfh1207\OrderModel>
 */
class OrderModelFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'order_type' => 4,
            'sell_total_price' => 1000,
            'discount' => 0.00,
            'standard_discount' => 0.00,
            'reduce_discount' => 0.00,
            'shipping_fee' => 0.00,
            'payment_fee' => 0.00,
            'transfer_fee' => 0.00,
            'delivery_type_fee' => 0.00,
            'package_fee' => 0.00,
            'use_point' => 0.00,
            'use_coupon_store' => 0.00,
            'use_coupon_mall' => 0.00,
            'total_use_coupon' => 0.00,
            'order_total_price' => 1000,
            'estimate_flg' => 0,
            'standard_total_price' => 1000,
            'reduce_total_price' => 0.00,
            'standard_tax_price' => 0.00,
            'reduce_tax_price' => 0.00,
            'payment_price' => 0.00,
            'gift_flg' => 0,
            'campaign_flg' => 0,
            'pending_flg' => 0,
            'repeat_flg' => 0,
            'progress_type' => 0,
            'receipt_type' => 0,
            'progress_type_self_change' => null,
            'comment_check_type' => 0,
            'alert_cust_check_type' => 0,
            'address_check_type' => 0,
            'deli_hope_date_check_type' => 0,
            'credit_type' => 0,
            'payment_type' => 0,
            'reservation_type' => 0,
            'deli_instruct_type' => 0,
            'deli_decision_type' => 0,
            'sales_status_type' => 0,
        ];
    }
}
