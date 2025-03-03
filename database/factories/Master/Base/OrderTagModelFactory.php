<?php

namespace Database\Factories\Master\Base;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Master\Base\OrderTagModel>
 */
class OrderTagModelFactory extends Factory
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
            'm_order_tag_sort' => 100,
            'tag_name' => '受注タグテスト',
            'tag_display_name' => '受注タグ',
        ];
    }
}
