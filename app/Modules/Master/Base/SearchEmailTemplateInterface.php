<?php

namespace App\Modules\Master\Base;

interface SearchEmailTemplateInterface
{
    /**
     * メールテンプレート
     *
     * @param array $condtions 検索条件
     * @param array $options 検索オプション
     */
    public function execute(array $condtions = [], array $options = []);
}
