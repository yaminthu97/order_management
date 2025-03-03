<?php

namespace Database\Factories;

use App\Models\Ami\Base\AmiPageModel;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Carbon\Carbon;

class AmiPageModelFactory extends Factory
{
    protected $model = AmiPageModel::class;

    public function definition()
    {
        return [
            'page_cd' => $this->faker->unique()->regexify('[A-Z0-9]{8}'),
            'page_title' => $this->faker->unique()->regexify('[A-Z0-9]{8}'),
            'page_type' => 0, // 0:単品, 1:セット
            'sales_price' => $this->faker->randomFloat(2, 0, 1000),
            'tax_rate' => 0.1,
            'print_page_title' => 1,
            'sales_start_datetime' => $this->faker->date(),
            'search_result_display_flg' => 1,
            'page_desc' => 1,
            'image_path' => 1,
            'remarks1' => 1,
            'remarks2' => 1,
            'remarks3' => 1,
            'remarks4' => 1,
            'remarks5' => 1,
            'etc1' => 1,
            'etc2' => 1,
            'etc3' => 1,
            'etc4' => 1,
            'etc5' => 1,
            'etc6' => 1,
            'etc7' => 1,
            'etc8' => 1,
            'etc9' => 1,
            'etc10' => 1,
            'etc11' => 1,
            'etc12' => 1,
            'etc13' => 1,
            'etc14' => 1,
            'etc15' => 1,
            'etc16' => 1,
            'etc17' => 1,
            'etc18' => 1,
            'etc19' => 1,
            'etc20' => 1,
            'etc21' => 1,
            'etc22' => 1,
            'etc23' => 1,
            'etc24' => 1,
            'etc25' => 1,
            'etc26' => 1,
            'etc27' => 1,
            'etc28' => 1,
            'etc29' => 1,
            'etc30' => 1,
            'is_rakuten_use' => 1,
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