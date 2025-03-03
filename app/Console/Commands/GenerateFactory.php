<?php
namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Carbon\Carbon;

class GenerateFactory extends Command
{
    protected $signature = 'generate:factory {model}';
    protected $description = 'usage: php artisan generate:factory \'App\Models\Order\Base\OrderDestinationModel\'';
    // 使用前に .env の DB_DATABASE を参照先データベース(global_db/gfh_1207_db)に変更すること
    // auto_increment の値はあとから消す必要あり

    public function handle()
    {
        $modelClass = $this->argument('model');
        $model = new $modelClass;
        $table = $model->getTable();
        $columns = Schema::getColumnListing($table);

        $factoryContent = $this->generateFactoryContent($modelClass, $columns);

        $factoryPath = database_path('factories/' . class_basename($modelClass) . 'Factory.php');
        file_put_contents($factoryPath, $factoryContent);

        $this->info("Factory created at: {$factoryPath}");
    }

    protected function generateFactoryContent($modelClass, $columns)
    {
        $definitionContent = '';
        $classParts = explode('\\', $modelClass);
        $modelName = end($classParts);

        foreach ($columns as $column) {
            if ($column === 'id' || $column === 'created_at' || $column === 'updated_at') {
                continue;
            }

            $definitionContent .= $this->generateColumnDefinition($column);
        }

        $definitionContent = rtrim($definitionContent, ',');

        return <<<EOD
<?php

namespace Database\Factories;

use $modelClass;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Carbon\Carbon;

class {$modelName}Factory extends Factory
{
    protected \$model = $modelName::class;

    public function definition()
    {
        return [
$definitionContent
        ];
    }

    public function createWithDatabase(array \$attributes = [], \$database = null)
    {
        if (\$database) {
            // 現在の接続を取得
            \$defaultConnection = config('database.connections.mysql');

            // 新しい接続設定を作成
            \$newConnection = array_merge(\$defaultConnection, [
                'database' => \$database,
                ]);

            // 新しい接続を設定
            config(['database.connections.tenant' => \$newConnection]);

            // 接続を再設定
            DB::purge('tenant');
            DB::reconnect('tenant');

            // スキーマを設定
            DB::statement('USE ' . \$database);
        }

        return parent::create(\$attributes);
    }
}
EOD;
    }

    /*
     * カラム名からそれっぽい faker をとりあえず作る、デフォルト値として1を入れておく
     */
    protected function generateColumnDefinition($column)
    {
        switch (true) {
            case Str::contains($column, 'entry_operator_id'):
                return "            '$column' => 1,\n";
            case Str::contains($column, 'entry_timestamp'):
                return "            '$column' => Carbon::now(),\n";
            case Str::contains($column, 'update_operator_id'):
                return "            '$column' => 1,\n";
            case Str::contains($column, 'update_timestamp'):
                return "            '$column' => Carbon::now(),\n";
            case Str::contains($column, 'cancel_operator_id'):
                return "            '$column' => null,\n";
            case Str::contains($column, 'cancel_timestamp'):
                return "            '$column' => null,\n";
            case Str::contains($column, 'name_kana'):
                return "            '$column' => \$this->faker->kanaName(),\n";
            case Str::contains($column, 'name'):
                return "            '$column' => \$this->faker->name(),\n";
            case Str::contains($column, 'email'):
                return "            '$column' => \$this->faker->unique()->safeEmail(),\n";
            case Str::contains($column, 'password'):
                return "            '$column' => bcrypt('password'),\n";
            case Str::contains($column, 'remember_token'):
                return "            '$column' => Str::random(10),\n";
            case Str::contains($column, 'postal'):
                return "            '$column' => \$this->faker->postcode(),\n";
            case Str::contains($column, 'address1'):
                return "            '$column' => '東京都',\n";
            case Str::contains($column, '_tel'):
                return "            '$column' => \$this->faker->phoneNumber(),\n";
            case Str::contains($column, '_fax'):
                return "            '$column' => \$this->faker->phoneNumber(),\n";
            case Str::contains($column, 'timestamp'):
                return "            '$column' => \$this->faker->dateTime(),\n";
            case Str::contains($column, 'date'):
                return "            '$column' => \$this->faker->date(),\n";
            case Str::contains($column, 'price'):
                return "            '$column' => \$this->faker->randomFloat(2, 0, 1000),\n";
            case Str::contains($column, '_id'):
                return "            '$column' => 1,\n";
            default:
                return "            '$column' => 1,\n";
        }
    }
}