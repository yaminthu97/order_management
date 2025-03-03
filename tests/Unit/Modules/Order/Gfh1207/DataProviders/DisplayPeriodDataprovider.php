<?php
namespace Tests\Unit\Modules\Order\Gfh1207\DataProviders;

class DisplayPeriodDataprovider
{
    public static function provider()
    {
        return [
            "当日" => ["1", "`order_datetime` <= '2024-08-01 00:00:00' and `order_datetime` >= '2024-08-01'"],
            "二日間" => ["2", "`order_datetime` <= '2024-08-01 00:00:00' and `order_datetime` >= '2024-07-31'"],
            "三日間" => ["3", "`order_datetime` <= '2024-08-01 00:00:00' and `order_datetime` >= '2024-07-30'"],
            "当週" => ["4", "`order_datetime` <= '2024-08-01 00:00:00' and `order_datetime` >= '2024-07-29'"], // 週の始まりを月曜日とするため2024-07-29
            "当月" => ["5", "`order_datetime` <= '2024-08-01 00:00:00' and `order_datetime` >= '2024-08-01'"],
            "三か月" => ["6", "`order_datetime` <= '2024-08-01 00:00:00' and `order_datetime` >= '2024-05-01'"],
            "六か月" => ["7", "`order_datetime` <= '2024-08-01 00:00:00' and `order_datetime` >= '2024-02-01'"],
            "当年" => ["8", "`order_datetime` <= '2024-08-01 00:00:00' and `order_datetime` >= '2023-08-01'"],
        ];
    }

    public static function providerWithOrderTimeFrom()
    {
        return [
            "当日" => ["1", "12:00:00", "`order_datetime` <= '2024-08-01 00:00:00' and `order_datetime` >= '2024-08-01 12:00:00'"],
            "二日間" => ["2", "12:00:00", "`order_datetime` <= '2024-08-01 00:00:00' and `order_datetime` >= '2024-07-31 12:00:00'"],
            "三日間" => ["3", "12:00:00", "`order_datetime` <= '2024-08-01 00:00:00' and `order_datetime` >= '2024-07-30 12:00:00'"],
            "当週" => ["4", "12:00:00", "`order_datetime` <= '2024-08-01 00:00:00' and `order_datetime` >= '2024-07-29 12:00:00'"], // 週の始まりを月曜日とするため2024-07-29
            "当月" => ["5", "12:00:00", "`order_datetime` <= '2024-08-01 00:00:00' and `order_datetime` >= '2024-08-01 12:00:00'"],
            "三か月" => ["6", "12:00:00", "`order_datetime` <= '2024-08-01 00:00:00' and `order_datetime` >= '2024-05-01 12:00:00'"],
            "六か月" => ["7", "12:00:00", "`order_datetime` <= '2024-08-01 00:00:00' and `order_datetime` >= '2024-02-01 12:00:00'"],
            "当年" => ["8", "12:00:00", "`order_datetime` <= '2024-08-01 00:00:00' and `order_datetime` >= '2023-08-01 12:00:00'"],
        ];
    }
}
