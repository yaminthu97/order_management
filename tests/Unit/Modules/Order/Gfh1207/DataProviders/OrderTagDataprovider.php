<?php
namespace Tests\Unit\Modules\Order\Gfh1207\DataProviders;

class OrderTagDataprovider
{
    public static function testOrderTagIncludeProvider()
    {
        return [
            "order_tags_includeが存在し、order_tags_excludeが存在しない場合" => [
                'values' => [
                    "order_tags_include" => "1,2,3",
                ],
                'expected' => "exists (select * from `t_order_tag` where `t_order_hdr`.`t_order_hdr_id` = `t_order_tag`.`t_order_hdr_id` and `m_order_tag_id` in ('1', '2', '3') and (`cancel_operator_id` is null or `cancel_operator_id` = 0))"
            ],
            "order_tags_includeが存在し、order_tags_excludeが存在する場合" => [
                'values' => [
                    "order_tags_include" => "1,2,3",
                    "order_tags_exclude" => "2"
                ],
                'expected' => "exists (select * from `t_order_tag` where `t_order_hdr`.`t_order_hdr_id` = `t_order_tag`.`t_order_hdr_id` and `m_order_tag_id` in ('1', '3') and (`cancel_operator_id` is null or `cancel_operator_id` = 0))"
            ],
            "order_tags_includeが存在し、全てのorder_tags_excludeを除外した結果が空の場合" => [
                'values' => [
                    "order_tags_include" => "2",
                    "order_tags_exclude" => "2"
                ],
                'expected' => ""
            ],
            "order_tags_includeが存在せず、order_tags_excludeが存在する場合" => [
                'values' => [
                    "order_tags_exclude" => "1"
                ],
                'expected' => "" // 条件が無効なのでwhere句が追加されないことを期待
            ],
            "order_tags_includeとorder_tags_excludeが存在しない場合" => [
                'values' => [],
                'expected' => "" // 条件が無効なのでwhere句が追加されないことを期待
            ]
        ];
    }

    public static function TestOrderTagExcludeProvider()
    {
        return [
            "order_tags_excludeが存在し、order_tags_includeが存在しない場合" => [
                'values' => [
                    "order_tags_exclude" => "1,2,3",
                ],
                'expected' => "not exists (select * from `t_order_tag` where `t_order_hdr`.`t_order_hdr_id` = `t_order_tag`.`t_order_hdr_id` and `m_order_tag_id` in ('1', '2', '3') and (`cancel_operator_id` is null or `cancel_operator_id` = 0))"
            ],
            "order_tags_excludeが存在し、order_tags_includeが存在する場合" => [
                'values' => [
                    "order_tags_exclude" => "1,2,3",
                    "order_tags_include" => "2"
                ],
                'expected' => "not exists (select * from `t_order_tag` where `t_order_hdr`.`t_order_hdr_id` = `t_order_tag`.`t_order_hdr_id` and `m_order_tag_id` in ('1', '3') and (`cancel_operator_id` is null or `cancel_operator_id` = 0))"
            ],
            "order_tags_excludeが存在し、全てのorder_tags_includeを除外した結果が空の場合" => [
                'values' => [
                    "order_tags_exclude" => "2",
                    "order_tags_include" => "2"
                ],
                'expected' => ""
            ],
            "order_tags_excludeが存在せず、order_tags_includeが存在する場合" => [
                'values' => [
                    "order_tags_include" => "1"
                ],
                'expected' => "" // 条件が無効なのでwhere句が追加されないことを期待
            ],
            "order_tags_excludeとorder_tags_includeが存在しない場合" => [
                'values' => [],
                'expected' => "" // 条件が無効なのでwhere句が追加されないことを期待
            ]
        ];
    }
}
