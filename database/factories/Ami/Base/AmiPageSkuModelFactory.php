<?php

namespace Database\Factories\Ami\Base;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Ami\Base\AmiPageSkuModel>
 */
class AmiPageSkuModelFactory extends Factory
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
            'sales_sku_type' => 'P',
            'sku_vol' => 1,
        ];
    }
}
