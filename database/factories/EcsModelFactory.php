<?php

namespace Database\Factories;

use App\Models\Master\Base\EcsModel;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Carbon\Carbon;

class EcsModelFactory extends Factory
{
    protected $model = EcsModel::class;

    public function definition()
    {
        return [
            'm_account_id' => 1,
            'delete_flg' => 1,
            'm_ec_name' => $this->faker->name(),
            'm_ec_type' => 1,
            'm_ec_url' => 1,
            'm_ecs_sort' => 1,
            'emailaddress' => $this->faker->unique()->safeEmail(),
            'receiving_email_server_id' => $this->faker->unique()->safeEmail(),
            'receiving_email_server_password' => $this->faker->unique()->safeEmail(),
            'receiving_email_server' => $this->faker->unique()->safeEmail(),
            'receiving_email_server_port' => $this->faker->unique()->safeEmail(),
            'receiving_email_auth_type' => $this->faker->unique()->safeEmail(),
            'send_email_server_id' => $this->faker->unique()->safeEmail(),
            'send_email_server_password' => $this->faker->unique()->safeEmail(),
            'send_email_server' => $this->faker->unique()->safeEmail(),
            'send_email_server_port' => $this->faker->unique()->safeEmail(),
            'send_email_auth_type' => $this->faker->unique()->safeEmail(),
            'send_email_ssl_type' => $this->faker->unique()->safeEmail(),
            'send_email_server_pop_before_smtp_flg' => $this->faker->unique()->safeEmail(),
            'bcc_emailaddress' => $this->faker->unique()->safeEmail(),
            'accept_orders_mailaddress' => 1,
            'accept_orders_receiving_email_server_id' => $this->faker->unique()->safeEmail(),
            'accept_orders_receiving_email_server_password' => $this->faker->unique()->safeEmail(),
            'accept_orders_receiving_email_server' => $this->faker->unique()->safeEmail(),
            'accept_orders_receiving_email_server_port' => $this->faker->unique()->safeEmail(),
            'accept_orders_receiving_email_auth_type' => $this->faker->unique()->safeEmail(),
            'accept_orders_send_email_server_id' => $this->faker->unique()->safeEmail(),
            'accept_orders_send_email_server_password' => $this->faker->unique()->safeEmail(),
            'accept_orders_send_email_server' => $this->faker->unique()->safeEmail(),
            'accept_orders_send_email_server_port' => $this->faker->unique()->safeEmail(),
            'accept_orders_send_email_auth_type' => $this->faker->unique()->safeEmail(),
            'accept_orders_send_email_ssl_type' => $this->faker->unique()->safeEmail(),
            'accept_orders_send_email_server_pop_before_smtp_flg' => $this->faker->unique()->safeEmail(),
            'send_email_signature' => $this->faker->unique()->safeEmail(),
            'delivery_csv_output_sell_name' => $this->faker->name(),
            'delivery_csv_output_requester' => 1,
            'delivery_csv_output_gift_requester' => 1,
            'delivery_csv_requester_name' => $this->faker->name(),
            'delivery_csv_requester_name_kana' => $this->faker->kanaName(),
            'delivery_csv_requester_name_postal' => $this->faker->name(),
            'delivery_csv_requester_name_prefectural' => $this->faker->name(),
            'delivery_csv_requester_name_address' => $this->faker->name(),
            'delivery_csv_requester_name_house_number' => $this->faker->name(),
            'delivery_csv_requester_name_address_building' => $this->faker->name(),
            'delivery_csv_requester_name_telephone' => $this->faker->name(),
            'no_confirm_order_comment' => 1,
            'shop_logo_output_flg' => 1,
            'shop_logo_file_path' => 1,
            'atobarai_com_acceptance_account_id' => 1,
            'non_invoice_number_reason' => 1,
            'shop_name_output_flg' => $this->faker->name(),
            'shop_name' => $this->faker->name(),
            'shop_postal_output_flg' => $this->faker->postcode(),
            'shop_postal' => $this->faker->postcode(),
            'shop_prefectural_output_flg' => 1,
            'shop_prefectural' => 1,
            'shop_address_output_flg' => 1,
            'shop_address' => 1,
            'shop_house_number_output_flg' => 1,
            'shop_house_number' => 1,
            'shop_address_building_output_flg' => 1,
            'shop_address_building' => 1,
            'shop_telephone_output_flg' => $this->faker->phoneNumber(),
            'shop_telephone' => $this->faker->phoneNumber(),
            'shop_fax_output_flg' => $this->faker->phoneNumber(),
            'shop_fax' => $this->faker->phoneNumber(),
            'shop_email_address_output_flg' => $this->faker->unique()->safeEmail(),
            'shop_email_address' => $this->faker->unique()->safeEmail(),
            'shop_personal_name_output_flg' => $this->faker->name(),
            'shop_personal_name' => $this->faker->name(),
            'shop_message_1_output_flg' => 1,
            'shop_message_1' => 1,
            'shop_message_2_output_flg' => 1,
            'shop_message_2' => 1,
            'shop_long_message_1_output_flg' => 1,
            'shop_long_message_1' => 1,
            'shop_etc_1_output_flg' => 1,
            'shop_etc_1' => 1,
            'shop_etc_2_output_flg' => 1,
            'shop_etc_2' => 1,
            'entry_operator_id' => 1,
            'entry_timestamp' => Carbon::now(),
            'update_operator_id' => 1,
            'update_timestamp' => Carbon::now(),

        ];
    }

    public function createWithDatabase(array $attributes = [], $database = null)
    {
        if ($database) {
            // 現在の接続を取得
            $defaultConnection = config('database.connections.mysql');

            // 新しい接続設定を作成
            $newConnection = array_merge($defaultConnection, [
                'database' => $database,
                ]);

            // 新しい接続を設定
            config(['database.connections.tenant' => $newConnection]);

            // 接続を再設定
            DB::purge('tenant');
            DB::reconnect('tenant');

            // スキーマを設定
            DB::statement('USE ' . $database);
        }

        return parent::create($attributes);
    }
}