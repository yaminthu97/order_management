<?php
namespace Tests\Unit\Modules\Order\Gfh1207\DataProviders;

class SetWhereOrTypeColumnDataprovider
{
    public static function progressTypeAutoSelfDataprovider()
    {
        return [
            "0のみの場合" => ["0", "(`t_order_hdr`.`progress_type_self_change` = 0 or `t_order_hdr`.`progress_type_self_change` is null)"],
            "１つの値" => ["5", "where `t_order_hdr`.`progress_type_self_change` = '5'"],
            "0を含む複数の値" => ["0,5", "(`t_order_hdr`.`progress_type_self_change` is null or `t_order_hdr`.`progress_type_self_change` = '0' or `t_order_hdr`.`progress_type_self_change` = '5')"],
            "0を含まない複数の値" => ["3,7", "(`t_order_hdr`.`progress_type_self_change` = '3' or `t_order_hdr`.`progress_type_self_change` = '7')"],
        ];
    }

    public static function immediatelyDeliFlgDataprovider()
    {
        return [
            "0のみの場合" => ["0", "(`t_order_hdr`.`immediately_deli_flg` = 0 or `t_order_hdr`.`immediately_deli_flg` is null)"],
            "１つの値" => ["5", "where `t_order_hdr`.`immediately_deli_flg` = '5'"],
            "0を含む複数の値" => ["0,5", "(`t_order_hdr`.`immediately_deli_flg` is null or `t_order_hdr`.`immediately_deli_flg` = '0' or `t_order_hdr`.`immediately_deli_flg` = '5')"],
            "0を含まない複数の値" => ["3,7", "(`t_order_hdr`.`immediately_deli_flg` = '3' or `t_order_hdr`.`immediately_deli_flg` = '7')"],
        ];
    }

    public static function rakutenSuperDealFlgDataprovider()
    {
        return [
            "0のみの場合" => ["0", "(`t_order_hdr`.`rakuten_super_deal_flg` = 0 or `t_order_hdr`.`rakuten_super_deal_flg` is null)"],
            "１つの値" => ["5", "where `t_order_hdr`.`rakuten_super_deal_flg` = '5'"],
            "0を含む複数の値" => ["0,5", "(`t_order_hdr`.`rakuten_super_deal_flg` is null or `t_order_hdr`.`rakuten_super_deal_flg` = '0' or `t_order_hdr`.`rakuten_super_deal_flg` = '5')"],
            "0を含まない複数の値" => ["3,7", "(`t_order_hdr`.`rakuten_super_deal_flg` = '3' or `t_order_hdr`.`rakuten_super_deal_flg` = '7')"],
        ];
    }

    public static function alertOrderFlgDataprovider()
    {
        return [
            "0のみの場合" => ["0", "(`t_order_hdr`.`alert_order_flg` = 0 or `t_order_hdr`.`alert_order_flg` is null)"],
            "１つの値" => ["5", "where `t_order_hdr`.`alert_order_flg` = '5'"],
            "0を含む複数の値" => ["0,5", "(`t_order_hdr`.`alert_order_flg` is null or `t_order_hdr`.`alert_order_flg` = '0' or `t_order_hdr`.`alert_order_flg` = '5')"],
            "0を含まない複数の値" => ["3,7", "(`t_order_hdr`.`alert_order_flg` = '3' or `t_order_hdr`.`alert_order_flg` = '7')"],
        ];
    }
}
