<?php


namespace App\Models\Goto\Base;

use Illuminate\Database\Eloquent\Model;

/**
 * Class GotoEcPageYahooItemCsvOutModel
 *
 * @package App\Models
 */
class GotoEcPageYahooItemCsvOutModel extends Model
{
    protected $table = 'w_goto_ec_page_yahoo_item_csv_out';
    protected $primaryKey = 'w_goto_ec_page_yahoo_item_csv_out_id';
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
