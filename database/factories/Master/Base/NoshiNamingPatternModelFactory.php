<?php

namespace Database\Factories\Master\Base;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Master\Base\NoshiNamingPatternModel>
 */
class NoshiNamingPatternModelFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'pattern_name' => '名入れパターン名',
            'pattern_code' => 'NAMING_PATTERN_CODE001',
            'delete_flg' => 0,
            'm_noshi_naming_pattern_sort' => 100,
            'company_name_count' => 1,
            'section_name_count' => 1,
            'title_count' => 1,
            'f_name_count' => 1,
            'name_count' => 1,
            'ruby_count' => 1,
            //`m_account_id` int(11) DEFAULT NULL COMMENT '企業アカウントID',
        ];
    }
}
