<?php


namespace App\Models\Mainte\Base;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

/**
 * Class ExecuteBatchInstructionDeleteHistoryModel
 * 
 * @package App\Models
 */
class ExecuteBatchInstructionDeleteHistoryModel extends Model
{
    protected $table = 't_execute_batch_instruction_delete_history';
    protected $primaryKey = 't_execute_batch_instruction_delete_id';

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
     * 実行担当者
     */
    public function operators()
    {
        return $this->belongsTo(\App\Models\Master\Base\OperatorModel::class, 'entry_operator_id', 'm_operators_id');
    }

    /*
     * 削除支持者
     */
    public function deleteInstuctionOperators()
    {
        return $this->belongsTo(\App\Models\Master\Base\OperatorModel::class, 'delete_instuction_operators_id', 'm_operators_id');
    }
}
