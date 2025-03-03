<?php

namespace Database\Factories;

use App\Models\Ami\Base\AmiEcPageModel;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Carbon\Carbon;

class AmiEcPageModelFactory extends Factory
{
    protected $model = AmiEcPageModel::class;

    public function definition()
    {
        return [
            'm_ami_page_id' => null,
            'm_ecs_id' => 1,
            'm_ec_type' => 6, // 6:店舗, 4:Amazon, 
            'auto_stock_cooperation_flg' => 1,
            'auto_ec_page_cooperation_flg' => 1,
            'ec_page_cd' => $this->faker->unique()->regexify('[A-Z0-9]{8}'),
            'ec_page_title' => $this->faker->word(),
            'ec_page_type' => 1,
            'sales_price' => $this->faker->randomFloat(2, 0, 1000),
            'tax_rate' => 1,
            'print_page_title' => 1,
            'sales_start_datetime' => $this->faker->date(),
            'delete_flg' => 1,
            'page_desc' => 1,
            'variation_axis1_direction' => 1,
            'goto_cooperation_status' => 1,
            'cooperation_register_flg' => 1,
            'm_account_id' => 1,
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