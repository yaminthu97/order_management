<?php

namespace Database\Factories\Master\Base;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Master\Base\DeliveryTypeModel>
 */
class DeliveryTypeModelFactory extends Factory
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
            'delete_flg' => 0,
            'm_delivery_type_name' => '配送方法テスト',
            'delivery_type' => 100,
            'm_delivery_sort' => 100,
            'delivery_date_output_type' => 1,
            'delivery_date_create_type' => 1,
            'deferred_payment_delivery_id' => 1,
            'standard_fee' => 1000,
            'frozen_fee' => 1000,
            'chilled_fee' => 1000,
            'delivery_tracking_url' => 'https://example.com',

        ];
    }
}
