<?php

namespace Database\Factories\Master\Base;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Master\Base\NoshiModel>
 */
class NoshiModelFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'noshi_type' => '熨斗タイプ',
            'omotegaki' => '表書き',
            'delete_flg' => 0,
            'noshi_cd' => 'NOSHICD001',
            //`m_account_id` int(11) DEFAULT NULL COMMENT '企業アカウントID',
            //`attachment_item_group_id` int(11) DEFAULT NULL COMMENT '付属品グループID;項目名称マスタの付属品グループ区分に属するID',
        ];
    }
}
