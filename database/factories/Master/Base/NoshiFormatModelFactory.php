<?php

namespace Database\Factories\Master\Base;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Master\Base\NoshiFormatModel>
 */
class NoshiFormatModelFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'noshi_format_name' => '熨斗種類名',
            'delete_flg' => 0,
            //`m_account_id` int(11) DEFAULT NULL COMMENT '企業アカウントID',
            //`m_noshi_id` bigint(20) NOT NULL COMMENT '熨斗ID',
        ];
    }
}
