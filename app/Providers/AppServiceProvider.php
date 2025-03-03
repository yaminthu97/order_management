<?php

namespace App\Providers;

use App\Services\EsmSessionManager;
use App\Services\FileUploadManager;
use App\Validator\CustomValidator;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\URL;

use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
        $this->app->singleton(EsmSessionManager::class, function ($app) {
            return new EsmSessionManager();
        });
        $this->app->singleton(FileUploadManager::class, function ($app) {
            return new FileUploadManager();
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Event::listen('Illuminate\Database\Events\QueryExecuted', function ($query) {
            // SQL実行時間が閾値を超えた場合はログ出力
            if(config('logging.sqls.log_threshold') < $query->time) {
                Log::info(__('messages.info.sql_executed'), [
                    'sql' => $query->sql,
                    'bindings' => $query->bindings,
                    'time' => $query->time,
                ]);
            }
        });

		Validator::resolver(function($translator, $data, $rules, $messages, $attributes){
			return new CustomValidator($translator, $data, $rules, $messages, $attributes);
		});
        
        if (config('app.env') !== 'local' && config('app.env') !== 'testing') {
            URL::forceScheme('https'); // 環境に応じて HTTPS を強制
        } else {
            URL::forceRootUrl(config('app.url'));
        }
    }
}