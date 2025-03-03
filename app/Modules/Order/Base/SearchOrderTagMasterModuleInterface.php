<?php

namespace App\Modules\Order\Base;

/**
 * 受注タグインターフェース
 */
interface SearchOrderTagMasterModuleInterface
{
    /**
     * 受注件数取得
     *
     */
    public function execute(array $conditions = [], array $options = []);
}
