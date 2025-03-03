<?php

namespace Database\Factories\Master\Base;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Master\Base\ItemanameTypeModel>
 */
class ItemnameTypeModelFactory extends Factory
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
            'm_itemname_type' => 1,
            'm_itemname_type_code' => '',
            'm_itemname_type_name' => '項目名称',
            'm_itemname_type_sort' => 100,
        ];
    }
}
