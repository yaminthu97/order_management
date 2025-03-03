<?php
namespace App\Modules\Order\Base;

use App\Models\Ami\Base\AmiEcPageModel;
use App\Models\Master\Base\CampaignModel;
use Illuminate\Database\Eloquent\Builder;
use App\Modules\Order\Base\SetCalcSubTotalInterface;

class SetCalcSubTotal implements SetCalcSubTotalInterface
{

    public function execute($editRow)
    {
        $order_total_price = 0; // 請求金額
        $sell_total_price = 0; // 商品購入金額
        $shipping_fee_total = 0;
        $payment_fee_total = 0;
        $wrapping_fee_total = 0;

        $tax08 = 0;
        $tax10 = 0; 
        // 小計
        foreach ($editRow['register_destination'] as $key => $destination) {
            $order_destination_price = 0; // 小計（請求金額）
            $sell_destination_price = 0; // 小計（商品購入金額）
            foreach ($destination['register_detail'] as $dkey => $item) {
                $sell_item_amount = $item['order_sell_price'] * $item['order_sell_vol'];
                $editRow['register_destination'][$key]['register_detail'][$dkey]['order_sell_amoun'] = $sell_item_amount;
                $order_destination_price += $sell_item_amount;
                $sell_destination_price += $sell_item_amount;

                if ($item['tax_rate'] == 0.1) { // 消費税
                    $tax10 += $sell_item_amount;
                } else {
                    $tax08 += $sell_item_amount;
                }
            }
            // 配送先ごとの小計
            $order_destination_price += $destination['shipping_fee']; // 送料
            $order_destination_price += $destination['payment_fee']; // 手数料
            $order_destination_price += $destination['wrapping_fee']; // 包装料
            $editRow['register_destination'][$key]['sum_sell_total'] = (string)$order_destination_price;

            $tax10 += $destination['shipping_fee'];
            $tax10 += $destination['payment_fee'];
            $tax10 += $destination['wrapping_fee'];

            // 全体の合計に追加
            $order_total_price += $order_destination_price;
            $sell_total_price += $sell_destination_price;
            $shipping_fee_total += $destination['shipping_fee'];
            $payment_fee_total += $destination['payment_fee'];
            $wrapping_fee_total += $destination['wrapping_fee'];
        }

        // $editRow['transfer_fee'] 消費税
        $tax10 += $editRow['transfer_fee'] ?? 0;

        // 全体の手数料を追加
        $order_total_price += $editRow['transfer_fee'] ?? 0;

        // 消費税を追加
        $editRow['tax_price08'] = (string)floor($tax08 * 0.08);
        $editRow['tax_price10'] = (string)floor($tax10 * 0.1);
        $editRow['tax_price'] =  $editRow['tax_price08'] + $editRow['tax_price10'];
        $order_total_price +=  $editRow['tax_price'];

        $editRow['total_price'] = (string)$order_total_price;
        $editRow['sum_sell_total'] = (string)$sell_total_price;
        $editRow['order_total_price'] = (string)$order_total_price; // 請求金額
        $editRow['sell_total_price'] = (string)$sell_total_price; // 商品購入金額;税込

        $editRow['shipping_fee'] = (string)$shipping_fee_total; // 送料合計;税抜
        $editRow['payment_fee'] = (string)$payment_fee_total; // 手数料合計;税抜, 支払手数料 + 温度帯別手数料(配送先単位)

        return $editRow;
    }
}