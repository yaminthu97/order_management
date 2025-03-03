<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class ProcessJson extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'process:json {json}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $json = $this->argument('json');

        // JSONデータのデコード
        $data = json_decode($json, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            $this->error('Invalid JSON data.');
            return 1;
        }

        // JSONデータの処理
        $this->info('Processing JSON data:');
        $this->info(print_r($data, true));

        // ここでデータを処理するロジックを追加

        return 0;
    }
}
