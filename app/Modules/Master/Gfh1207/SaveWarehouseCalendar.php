<?php

namespace App\Modules\Master\Gfh1207;

use App\Models\Master\Gfh1207\WarehouseCalendarModel;
use App\Modules\Master\Base\SaveWarehouseCalendarInterface;
use App\Services\EsmSessionManager;
use Carbon\Carbon;

class SaveWarehouseCalendar implements SaveWarehouseCalendarInterface
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

    public function execute($request, $mainPrimaryKeyValue, $update_operator_id)
    {

        $m_account_id = $this->esmSessionManager->getAccountId();

        if (empty($request['holidays'])) {
            return;
        }

        $newRecords = $this->adjustForWarehouseCalendar(
            $request['year'],
            $request['holidays']
        );

        foreach ($newRecords as $year => $months) {
            // 一旦delete
            $where = [
                'm_account_id' => $m_account_id,
                'm_warehouses_id' => $mainPrimaryKeyValue,
                'calendar_year' => $year,
            ];
            $update = [
                'delete_flg' => 1,
            ];

            WarehouseCalendarModel::where($where)->update($update);

            foreach ($months as $newRecord) {
                $newRecord['update_operator_id'] = $update_operator_id;
                $newRecord['delete_flg'] = 0;
                $this->updateWarehouseCalendar($request, $mainPrimaryKeyValue, $newRecord);
            }
        }
    }

    private function updateWarehouseCalendar($request, $mainPrimaryKeyValue, $newRecord)
    {
        $m_account_id = $this->esmSessionManager->getAccountId();

        $newRecord = $this->setUpdater($newRecord);
        $where = [
            'm_account_id' => $m_account_id,
            'm_warehouses_id' => $mainPrimaryKeyValue,
            'calendar_year' => $newRecord['calendar_year'],
            'calendar_month' => $newRecord['calendar_month'],
        ];
        WarehouseCalendarModel::updateOrCreate($where, $newRecord);
    }

    private function adjustForWarehouseCalendar($year, $holidays)
    {
        $arr = [];
        foreach ($holidays as $holiday) {
            list($y, $m, $d) = explode("-", $holiday);
            if ($year != $y) {
                continue;
            }
            $m = (int) $m;
            $d = (int) $d;
            if (empty($arr[$y][$m])) {
                $arr[$y][$m] = [
                    'calendar_year' => $y,
                    'calendar_month' => $m,
                    'month_days' => date("t", mktime(0, 0, 0, $m, 1, $y))
                ];
                foreach ($this->calendarKeys as $key) {
                    if (empty($key)) {
                        continue;
                    }
                    $arr[$y][$m][$key] = 0;
                }
            }
            $arr[$y][$m][$this->calendarKeys[$d]] = 1;
        }
        return $arr;
    }

    private function setUpdater($records)
    {
        $isArray = is_array($records);
        if ($isArray) {
            $records = (object) $records;
        }
        $records->entry_operator_id = $records->entry_operator_id ?? $records->update_operator_id;
        $records->update_timestamp = Carbon::now()->format('Y-m-d H:i:s.u');
        if ($isArray) {
            return (array) $records;
        }

        return $records;
    }
}
