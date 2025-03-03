<?php

namespace Database\Factories\Master\Base;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Master\Base\NoshiDetailModel>
 */
class NoshiDetailModelFactory extends Factory
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
            'template_file_name' => 'template_file_name.xlsx',
            //`m_account_id` int(11) DEFAULT NULL COMMENT '企業アカウントID',
            //`m_noshi_id` bigint(20) NOT NULL COMMENT '熨斗ID',
            //`m_noshi_format_id` int(11) NOT NULL COMMENT '熨斗種類ID',
            // `m_noshi_naming_pattern_id` int(11) NOT NULL COMMENT '名入れパターンID',
        ];
    }
}
