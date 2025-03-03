<?php

namespace App\Services;

use Illuminate\Support\Facades\Storage;

class PowerPointReportManager
{
    protected $disk;

    public function __construct()
    {
        // filesystems の設定を取得する
        $this->disk = config('filesystems.default', 'local');
    }

    public function createReport($templateFile,$values)
    {
        // テンプレートファイル
        if(Storage::disk($this->disk)->exists($templateFile) == false){
            $rv['error'] = __('messages.error.file_not_found',['file'=>'熨斗テンプレート','path'=>$templateFile]);
            return $rv;
        }
        try{
            $tempFilePath = tempnam(sys_get_temp_dir(), 'pptx');
            $fileContents = Storage::disk($this->disk)->get($templateFile);
            file_put_contents($tempFilePath, $fileContents);
    
            // JSONファイル
            $jsonFilePath = tempnam(sys_get_temp_dir(), 'json');
            file_put_contents($jsonFilePath,json_encode($values));
    
            // 出力ファイル
            $outFilePath = tempnam(sys_get_temp_dir(), 'pptx');
    
            $rv = [];
            // python 実行
            
            $python = config('define.create_noshi_cmd');
            if(empty($python)){
                $rv['error'] = __('messages.error.not_environment',['name'=>'CREATE_NOSHI_CMD']);
                return $rv;
            }
            $python = $python.' '.$tempFilePath.' '.$jsonFilePath.' '.$outFilePath.' 2>&1';
            $result_code = null;
            // 出力のバッファリングを有効にする
            ob_start();
            $last_line = system($python, $result_code);
            // 出力用バッファをクリアする
            ob_end_clean();
            if($last_line === false){
                $rv['error'] = __("messages.error.python_failure");
                return $rv;
            } else {
                if($result_code != 0){
                    if($result_code == 105){
                        // powerpoint形式エラー
                        $rv['error'] = __("messages.error.template_format_error",['format'=>'パワーポイント']);
                        return $rv;
                    } else {
                        $rv['error'] = empty($last_line)?__("messages.error.python_failure"):$last_line;
                        return $rv;
                    }
                }
                $rv['file'] = $outFilePath;
                return $rv;
            }
        } finally {
            unlink($tempFilePath);
            unlink($jsonFilePath);
        }
    }
}
