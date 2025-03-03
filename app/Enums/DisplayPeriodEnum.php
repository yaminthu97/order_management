<?php

namespace App\Enums;

/**
 * 表示期間
 */
enum DisplayPeriodEnum: int
{
    /**
     * 当日
     */
    case TODAY = 1;
    /**
     * 2日間
     */
    case TWO_DAYS = 2;
    /**
     * 3日間
     */
    case THREE_DAYS = 3;
    /**
     * 当週
     */
    case CURRENT_WEEK = 4;
    /**
     * 当月
     */
    case CURRENT_MONTH = 5;
    /**
     * 3カ月
     */
    case THREE_MONTHS = 6;
    /**
     * 6カ月
     */
    case SIX_MONTHS = 7;
    /**
     * 当年
     */
    case CURRENT_YEAR = 8;
    /**
     * 全て
     */
    case ALL = 9;


    public function label(): string
    {
        return match($this) {
            self::TODAY => '当日',
            self::TWO_DAYS => '２日間',
            self::THREE_DAYS => '３日間',
            self::CURRENT_WEEK => '当週',
            self::CURRENT_MONTH => '当月',
            self::THREE_MONTHS => '３カ月',
            self::SIX_MONTHS => '６カ月',
            self::CURRENT_YEAR => '当年',
            self::ALL => '全て',
        };
    }
}
