<?php


namespace App\Models\Mainte\Base;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

/**
 * Class FileDownloadHistoryModel
 * 
 * @package App\Models
 */
class FileDownloadHistoryModel extends Model
{
    protected $table = 't_file_download_history';
    protected $primaryKey = 't_file_download_history_id';

    /**
     * モデルの日付カラムの保存用フォーマット
     *
     * @var string
     */
    protected $dateFormat = 'Y-m-d H:i:s';

    const CREATED_AT = 'entry_timestamp';
    const UPDATED_AT = 'update_timestamp';

    /*
     * 企業アカウントとのリレーション
     */
    public function account()
    {
        return $this->belongsTo(\App\Models\Master\Base\AccountModel::class, 'm_account_id', 'm_account_id');
    }

    /*
     * バッチ実行指示とのリレーション
     */
    public function executeBatchInstruction()
    {
        return $this->belongsTo(\App\Models\Common\Base\ExecuteBatchInstructionModel::class, 't_execute_batch_instruction_id', 't_execute_batch_instruction_id');
    }

    /*
     * ダウンロード実行ユーザ
     */
    public function operator()
    {
        return $this->belongsTo(\App\Models\Master\Base\OperatorModel::class, 'm_operator_id', 'm_operator_id');
    }
}
