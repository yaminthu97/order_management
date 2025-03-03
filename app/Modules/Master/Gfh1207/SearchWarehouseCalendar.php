<?php

namespace App\Modules\Master\Gfh1207;

use App\Models\Master\Gfh1207\WarehouseCalendarModel;
use App\Modules\Master\Base\SearchWarehouseCalendarInterface;
use App\Services\EsmSessionManager;

class SearchWarehouseCalendar implements SearchWarehouseCalendarInterface
{
    /**
     * ESMセッション管理クラス
     */
    protected $esmSessionManager;

    public function __construct(
        EsmSessionManager $esmSessionManager
    ) {
        $this->esmSessionManager = $esmSessionManager;
    }

    private $calendarKeys = [
        "",
        "first",        "second",         "third",         "fourth",        "fifth",
        "sixth",        "seventh",        "eighth",        "ninth",         "tenth",
        "eleventh",     "twelfth",        "thirteenth",    "fourteenth",    "fifteenth",
        "sixteenth",    "seventeenth",    "eighteenth",    "nineteenth",    "twentieth",
        "twenty-first", "twenty-second",  "twenty-third",  "twenty-fourth", "twenty-fifth",
        "twenty-sixth", "twenty-seventh", "twenty-eighth", "twenty-ninth",  "thirtieth",
        "thirty-first",
    ];

    public function execute($where)
    {
        list($total_record_count, $data) = $this->retrieveCalendar($where);

        $arr = [];
        foreach ($data as $val) {
            foreach ($this->calendarKeys as $day => $key) {
                if (empty($key)) {
                    continue;
                }
                if (empty($val[$key])) {
                    continue;
                }
                $arr[] = $val['calendar_year'] . "-" . sprintf("%02d", $val['calendar_month']) . "-" . sprintf("%02d", $day);
            }
        }

        return [$total_record_count, $arr];
    }

    private function retrieveCalendar($where)
    {
        $model = WarehouseCalendarModel::where('m_warehouses_id', $where['m_warehouses_id']);

        //レコードの総数を数える
        $count = $model->count();

        // すべてのレコードを配列として取得する
        $data = $model->get()->toArray();

        return [$count, $data];
    }
}
