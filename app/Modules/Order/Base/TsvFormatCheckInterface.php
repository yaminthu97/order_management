<?php

namespace App\Modules\Order\Base;

interface TsvFormatCheckInterface
{
    /**
     * tsv data format
     */
    public function execute(string $tsvData);
}
