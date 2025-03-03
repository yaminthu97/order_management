<?php

namespace Database\Factories\Ami\Base;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Ami\Base\AmiPageNoshiModel>
 */
class AmiPageNoshiModelFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            //`m_ami_page_id` int(11) NOT NULL COMMENT 'ページマスタ管理ID',
            //`m_account_id` int(11) NOT NULL DEFAULT '-1' COMMENT '企業アカウントID',
            //`m_noshi_id` bigint(20) NOT NULL COMMENT '熨斗ID',
            //`m_noshi_format_id` bigint(20) NOT NULL COMMENT '熨斗種類ID',
        ];
    }
}
