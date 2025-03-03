<?php

namespace Database\Factories;

use App\Models\Cc\Base\CustModel;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Carbon\Carbon;

class CustModelFactory extends Factory
{
    protected $model = CustModel::class;

    public function definition()
    {
        return [
            'm_account_id' => 1,
            'delete_flg' => 0,
            'cust_cd' => null,
            'm_cust_runk_id' => 1,
            'customer_category' => 1,
            'dm1_flg' => 1,
            'dm2_flg' => 1,
            'name_kanji' => $this->faker->name(),
            'name_kana' => $this->faker->kanaName(),
            'sex_type' => 1,
            'birthday' => $this->faker->date(),
            'tel1' => $this->faker->phoneNumber(),
            'tel2' => $this->faker->phoneNumber(),
            'tel3' => null,
            'tel4' => null,
            'fax' => $this->faker->phoneNumber(),
            'postal' => $this->faker->postcode(),
            'address1' => '東京都',
            'address2' => 1,
            'address3' => 1,
            'address4' => 1,
            'corporate_kanji' => 1,
            'corporate_kana' => 1,
            'division_name' => $this->faker->name(),
            'corporate_tel' => $this->faker->phoneNumber(),
            'email1' => $this->faker->unique()->safeEmail(),
            'email2' => $this->faker->unique()->safeEmail(),
            'email3' => null,
            'email4' => null,
            'email5' => null,
            'discount_rate' => 0,
            'customer_type' => 1,
            'dm_send_letter_flg' => 1,
            'dm_send_mail_flg' => 1,
            'alert_cust_type' => 1,
            'alert_cust_comment' => 1,
            'note' => null,
            'reserve1' => null,
            'reserve2' => null,
            'reserve3' => null,
            'reserve4' => null,
            'reserve5' => null,
            'reserve6' => null,
            'reserve7' => null,
            'reserve8' => null,
            'reserve9' => null,
            'reserve10' => null,
            'reserve11' => null,
            'reserve12' => null,
            'reserve13' => null,
            'reserve14' => null,
            'reserve15' => null,
            'reserve16' => null,
            'reserve17' => null,
            'reserve18' => null,
            'reserve19' => null,
            'reserve20' => null,
            'entry_operator_id' => 1,
            'entry_timestamp' => Carbon::now(),
            'update_operator_id' => 1,
            'update_timestamp' => Carbon::now(),
            'delete_operator_id' => 1,
            'delete_timestamp' => $this->faker->dateTime(),
            'personal_delete_timestamp' => $this->faker->dateTime(),

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