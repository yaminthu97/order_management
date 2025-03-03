<?php

namespace Database\Factories\Master\Base;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Master\Base\AccountModel>
 */
class AccountModelFactory extends Factory
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
            'account_cd' => 'test',
            'account_name' => 'テストアカウント',
            'account_english_name' => 'test account',
            'account_postal' => '123-4567',
            'account_prefectural' => '東京都',
            'account_address' => '渋谷区',
            'account_house_number' => '1-1-1',
            'account_contract_period_FROM' => '2021-01-01',
            'account_contract_period_TO' => '2021-12-31',
            'account_contact_email_address' => 'test@example.com',
            'account_contact_name' => '担当者',
            'account_telephone' => '03-1234-5678',
            'syscom_use_version' => "v1_0",
            'master_use_version' => "v1_0",
            'warehouse_use_version' => "v1_0",
            'common_use_version' => "v1_0",
            'stock_use_version' => "v1_0",
            'order_use_version' => "v1_0",
            'cc_use_version' => "v1_0",
            'claim_use_version' => "v1_0",
            'ami_use_version' => "v1_0",
            'goto_use_version' => "v1_0",
            // 'rakuten_app_cd' => "P000001683_U263Pnui9YuEcdSH",
            // 'yahoo_app_cd' => "dj0zaiZpPUFYNG1wZExWRjl6byZzPWNvbnN1bWVyc2VjcmV0Jng9MmI",
            // 'yahoo_auth_cd' => "70e1216451d2baf175e6b95fe67a03301d4a35f7",
            // 'amazon_app_cd' => "AKIAIVQ7L6DOTVTCO6EQ",
            // 'amazon_auth_cd' => "Aw3hOcJ9BhkrxqDg8hHuLg6E8y41h0kBQQcuLOuK",
        ];
    }
}
