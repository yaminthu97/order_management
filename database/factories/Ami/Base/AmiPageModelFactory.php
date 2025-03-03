<?php

namespace Database\Factories\Ami\Base;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Ami\Base\AmiPageModel>
 */
class AmiPageModelFactory extends Factory
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
            'page_cd' => 'test' . $this->faker->randomFloat(2, 0, 10000),
            'page_title' => 'テスト商品ページ',
            'page_type' => 0,
            'sales_price' => 1000,
            'tax_rate' => 0.10,
            'search_result_display_flg' => 1,
            'is_rakuten_use' => 1,
        ];
    }
}
