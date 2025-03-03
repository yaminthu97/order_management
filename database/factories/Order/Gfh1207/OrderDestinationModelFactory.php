<?php

namespace Database\Factories\Order\Gfh1207;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Order\Gfh1207\OrderDestinationModel>
 */
class OrderDestinationModelFactory extends Factory
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
            'order_destination_seq' => 1,
            'shipping_fee' => 0.00,
            'payment_fee' => 0.00,
            'wrapping_fee' => 0.00,
            'destination_tel' => '09012345678',
            'destination_postal' => '1234567',
            'destination_address1' => '東京都',
            'destination_address2' => '千代田区一ツ家',
            'destination_address3' => '1-1-1',
            'destination_name' => 'テスト太郎',
        ];
    }
}
