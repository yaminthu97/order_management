<?php
namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class CustomDataFactory extends Factory
{
    // ファクトリーに対応するモデルは指定しない
    protected $model = \stdClass::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'order_name' => $this->faker->word,
            'order_total_price' => $this->faker->numberBetween(1000, 10000),
            // 他のデータフィールド
        ];
    }
}