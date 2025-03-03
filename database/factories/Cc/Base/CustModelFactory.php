<?php

namespace Database\Factories\Cc\Base;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Cc\Base\CustModel>
 */
class CustModelFactory extends Factory
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
            'cust_cd' => 'test',
            'm_cust_runk_id' => 0,
            'dm1_flg' => 0,
            'dm2_flg' => 0,
            'name_kanji' => 'テスト',
            'name_kana' => 'てすと',
            'sex_type' => 0,
            'birthday' => '2021-01-01',
            'tel1' => '09012345678',
            'postal' => '1234567',
            'address1' => '東京都',
            'address2' => '千代田区一ツ家',
            'address3' => '1-1-1',
            'discount_rate' => 0.00,
            'customer_type' => 0,
            'dm_send_letter_flg' => 0,
            'alert_cust_type' => 0,
            // 'gen_search_name_kanji' => 'テスト',

        ];
    }
}
