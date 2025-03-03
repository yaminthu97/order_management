<?php

namespace App\Modules\Order\Base;

interface SetCalcSubTotalInterface
{
    /**
     * 受注登録時、各種小計・合計の計算を行う
     */
    public function execute($editRow);
}
