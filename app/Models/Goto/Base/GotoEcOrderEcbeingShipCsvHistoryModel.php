<?php


namespace App\Models\Goto\Base;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

/**
 * Class GotoEcOrderEcbeingShipCsvHistoryModel
 *
 * @package App\Models
 */
class GotoEcOrderEcbeingShipCsvHistoryModel extends Model
{
    protected $table = 't_goto_ec_order_ecbeing_ship_csv_history';
    protected $primaryKey = 't_goto_ec_order_ecbeing_ship_csv_history_id';
    protected $connection = 'global';

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
     * ECサイトマスタテーブル
     */
    public function ecs()
    {
        return $this->belongsTo(\App\Models\Master\Base\EcsModel::class, 'm_ecs_id', 'm_ecs_id');
    }

    /**
     * 受注基本とのリレーション
     */
    public function orderHdr()
    {
        return $this->belongsTo(\App\Models\Order\Base\OrderHdrModel::class, 't_order_hdr_id', 't_order_hdr_id');
    }

    /*
     * バッチ実行指示とのリレーション
     */
    public function executeBatchInstruction()
    {
        return $this->belongsTo(\App\Models\Common\Base\ExecuteBatchInstructionModel::class, 't_execute_batch_instruction_id', 't_execute_batch_instruction_id');
    }

    /**
     * 登録ユーザ
     */
    public function entryOperator()
    {
        return $this->belongsTo(\App\Models\Master\Base\OperatorModel::class, 'entry_operator_id', 'm_operators_id');
    }
}
