<?php

namespace Database\Factories\Ami\Base;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Ami\Base\AmiEcPageModel>
 */
class AmiEcPageModelFactory extends Factory
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
            'm_ec_type' => 1,
            'auto_stock_cooperation_flg' => 1,
            'auto_ec_page_cooperation_flg' => 1,
            'ec_page_cd' => 'test',
            'ec_page_title' => 'テスト商品ページ',
            'ec_page_type' => 0,
            'sales_price' => 1000,
            'tax_rate' => 0.10,
            'delete_flg' => 0,
            'goto_cooperation_status' => 0,
            'cooperation_register_flg' => 0,
        ];
    }
}
