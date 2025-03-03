<?php

namespace Database\Factories\Order\Base;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Order\Base\DestinationModel>
 */
class DestinationModelFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'delete_flg' => 0,
            'cust_id' => 1,
            'destination_name' => 'テスト',
            'destination_name_kana' => 'テスト',
            'destination_tel' => '09012345678',
            'destination_postal' => '1234567',
            'destination_address1' => '東京都',
            'destination_address2' => '渋谷区',
            'destination_address3' => '1-1-1',
            'destination_address4' => 'テストビル',
            'destination_company_name' => 'テスト株式会社',
            'destination_division_name' => 'テスト部署',
        ];
    }
}
