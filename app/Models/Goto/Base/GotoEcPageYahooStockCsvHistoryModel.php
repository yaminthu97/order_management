<?php


namespace App\Models\Goto\Base;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

/**
 * Class GotoEcPageYahooStockCsvHistoryModel
 *
 * @package App\Models
 */
class GotoEcPageYahooStockCsvHistoryModel extends Model
{
    protected $table = 't_goto_ec_page_yahoo_stock_csv_history';
    protected $primaryKey = 't_goto_ec_page_yahoo_stock_csv_history';
    protected $connection = 'global';

    /**
     * モデルの日付カラムの保存用フォーマット
     *
     * @var string
     */
    protected $dateFormat = 'Y-m-d H:i:s';

    const CREATED_AT = 'entry_timestamp';
    const UPDATED_AT = 'update_timestamp';

}
