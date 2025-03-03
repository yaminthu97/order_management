<?php

namespace Database\Factories\Master\Base;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Master\Base\EcsModel>
 */
class EcsModelFactory extends Factory
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
            'm_ec_name' => 'テストEC',
            'm_ec_type' => 6,
            'm_ecs_sort' => 100,
            'emailaddress' => 'example@test.co.jp',
            'receiving_email_server_id' => 'dummy',
            'receiving_email_server_password' => 'dummy',
            'receiving_email_server_port' => 1,
            'receiving_email_auth_type' => 1,
            'send_email_server_id' => 'example@example.com',
            'send_email_server_password' => 'dummy',
            'send_email_server' => 'example@example.com',
            'send_email_server_port' => 1,
            'send_email_auth_type' => 1,
            'send_email_server_pop_before_smtp_flg' => 0,
            'bcc_emailaddress' => '',
            'accept_orders_mailaddress' => '',
            'delivery_csv_output_sell_name' => '0',
            'delivery_csv_output_requester' => '0',
            'delivery_csv_output_gift_requester' => '0',
            'delivery_csv_requester_name' => '株式会社スクロール360',
        ];
    }
}
