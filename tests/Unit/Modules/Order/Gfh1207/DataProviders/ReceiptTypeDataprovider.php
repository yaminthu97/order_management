<?php
namespace Tests\Unit\Modules\Order\Gfh1207\DataProviders;

class ReceiptTypeDataprovider
{
    public static function provider()
    {
        return [
            'receipt_typeが不要の場合' => [
                'values' => [
                    'receipt_type' => '0',
                ],
                'expected' => "`receipt_type` in ('0')"
            ],
            'receipt_typeが一括の場合' => [
                'values' => [
                    'receipt_type' => '1',
                ],
                'expected' => "`receipt_type` in ('1')"
            ],
            'receipt_typeが分割の場合' => [
                'values' => [
                    'receipt_type' => '2',
                ],
                'expected' => "`receipt_type` in ('2')"
            ],
            'receipt_typeが複数指定の場合' => [
                'values' => [
                    'receipt_type' => '1,2',
                ],
                'expected' => "`receipt_type` in ('1', '2')"
            ],
        ];
    }

}
