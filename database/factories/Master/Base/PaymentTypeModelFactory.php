<?php

namespace Database\Factories\Master\Base;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Master\Base\PaymentTypeModel>
 */
class PaymentTypeModelFactory extends Factory
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
            'm_payment_types_name' => '支払方法テスト',
            'payment_type' => 40,
            'delivery_condition' => 1,
            'm_payment_types_sort' => 1,
            'atobarai_com_cooperation_type' => 1,
            'payment_fee' => 0.00,
        ];
    }
}
