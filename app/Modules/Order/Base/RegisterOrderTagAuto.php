<?php

namespace App\Modules\Order\Base;

use App\Services\EsmSessionManager;
use App\Models\Order\Base\OrderHdrModel;
use App\Models\Master\Base\OrderTagModel;
use App\Modules\Order\Base\UpdateOrderTag;

/**
 * 受注タグ条件判定処理
 */
class RegisterOrderTagAuto implements RegisterOrderTagAutoInterface
{

    /**
     * ESMセッション管理クラス
     */
    protected $esmSessionManager;

    protected $updateOrderTag;

    protected $condJudge = []; // 条件判定
    protected $condCount = 0; // 条件の数

    public function __construct(EsmSessionManager $esmSessionManager, UpdateOrderTag $updateOrderTag)
    {
        $this->esmSessionManager = $esmSessionManager;
        $this->updateOrderTag = $updateOrderTag;
    }

    
    public function execute(int $orderHdrId, int $autoTimming, ?int $account_id = null, ?int $operator_id = null)
    {
        if (is_null($account_id)) {
            $account_id = $this->esmSessionManager->getAccountId();
        }
        if (is_null($operator_id)) {
            $operator_id = $this->esmSessionManager->getOperatorId();
        }

        // 受注情報取得
        $orderHdr = OrderHdrModel::find($orderHdrId);

        // 受注タグ一覧
        $query = OrderTagModel::query();
        $query->where('m_account_id', $account_id);
        if ($autoTimming === 1) {
            // autoTimming が 1 （登録時）ならば 1, 2 （登録時、登録更新時）のデータを取得
            $query->whereIn('auto_timming', [1, 2]);
        } elseif ($autoTimming === 2) {
            // autoTimming が 2 （登録更新時）ならば 2 （登録更新時）のデータを取得
            $query->where('auto_timming', 2);
        }
        $orderTags = $query->get();

        foreach ($orderTags as $orderTag) {
            // 受注タグ条件判定
            $checkResult = $this->checkOrderTag($orderTag, $orderHdr);

            // 受注タグ追加処理
            if ($checkResult) {
                $this->updateOrderTag->execute($orderHdrId, $orderTag->m_order_tag_id, []);
            }
        }
    }

    private function checkOrderTag($orderTag, $orderHdr)
    {
        for ($i = 1; $i <= 10; $i++)
        {
            $condTableId	= $orderTag['cond' . $i . '_table_id'];
            $condColumnId	= $orderTag['cond' . $i . '_column_id'];
            $condLengthFlg	= $orderTag['cond' . $i . '_length_flg'];
            $condOperator	= $orderTag['cond' . $i . '_operator'];
            $condValue		= $orderTag['cond' . $i . '_value'];
            
            // テーブルIDがNULLの条件は判定しない
            if (!isset($condTableId) || strlen($condTableId) == 0) {
                // 条件がない場合は次の条件へ
            } else {
                // $condCount を1増やす
                $this->condCount++;
                // 自動付与判定処理
                if ($condTableId=='t_order_destination') {
                    // 全 受注配送先 をチェックし1つでも一致すればtrue
                    $judge = false;
                    $orderDestination = $orderHdr->orderDestination()->get();
                    foreach ($orderDestination as $orderDestinationRow) {
                        $judge = $this->judgeAutoGrant($orderDestinationRow, $condColumnId, $condLengthFlg, $condOperator, $condValue);
                    }
                    $this->condJudge[$i] = $judge;
                } elseif ($condTableId=='t_order_dtl') {
                    // 全 受注明細 をチェックし1つでも一致すればtrue
                    $judge = false;
                    $orderDtl = $orderHdr->orderDtl()->get();
                    foreach ($orderDtl as $orderDtlRow) {
                        $judge = $this->judgeAutoGrant($orderDtlRow, $condColumnId, $condLengthFlg, $condOperator, $condValue);
                    }
                    $this->condJudge[$i] = $judge;
                } elseif ($condTableId=='t_order_dtl_sku') {
                    // 全 受注明細SKU をチェックし1つでも一致すればtrue
                    $judge = false;
                    $orderDtlSku = $orderHdr->orderDtlSku()->get();
                    foreach ($orderDtlSku as $orderDtlSkuRow) {
                        $judge = $this->judgeAutoGrant($orderDtlSkuRow, $condColumnId, $condLengthFlg, $condOperator, $condValue);
                    }
                    $this->condJudge[$i] = $judge;
                } elseif ($condTableId=='m_cust') {
                    // 顧客（注文主）
                    $this->condJudge[$i] = $this->judgeAutoGrant($orderHdr->cust()->get(), $condColumnId, $condLengthFlg, $condOperator, $condValue);
                } elseif ($condTableId=='t_order_hdr') {
                    // 受注基本
                    $this->condJudge[$i] = $this->judgeAutoGrant($orderHdr, $condColumnId, $condLengthFlg, $condOperator, $condValue);
                } elseif ($condTableId=='m_ami_page') {
                    // 全 ページマスタ をチェックし1つでも一致すればtrue
                    $judge = false;
                    $orderDtl = $orderHdr->orderDtl()->get();
                    foreach ($orderDtl as $orderDtlRow) {
                        $judge = $this->judgeAutoGrant($orderDtlRow->amiEcPage()->page()->get(), $condColumnId, $condLengthFlg, $condOperator, $condValue);
                    }
                    $this->condJudge[$i] = $judge;
                } elseif ($condTableId=='m_ami_sku') {
                    // 全 ページマスタSKU をチェックし1つでも一致すればtrue
                    $judge = false;
                    $orderDtlSku = $orderHdr->orderDtlSku()->get();
                    foreach ($orderDtlSku as $orderDtlSkuRow) {
                        $judge = $this->judgeAutoGrant($orderDtlSkuRow->amiSku()->get(), $condColumnId, $condLengthFlg, $condOperator, $condValue);
                    }
                    $this->condJudge[$i] = $judge;
                }
            }
        }

        $tagAdd = false;
        // すべての条件のテーブルIDが全てNULLの場合＝condCount が0の場合 ⇒ 無条件でタグを付与
        if ($this->condCount == 0) {
            $tagAdd = true;
        } else {
            if ($orderTag['and_or'] == 0) {
                // 条件.論理和・積 = 0：AND の場合 ⇒ すべての条件の自動付与判定が全て「付与対象」ならタグを付与
                // condJudge がすべて true ならばタグを付与
                $tagAdd = true;
                foreach ($this->condJudge as $condJudgeRow) {
                    if (!$condJudgeRow) {
                        $tagAdd = false;
                        break;
                    }
                }
            } elseif ($orderTag['and_or'] == 1) {
                // 条件.論理和・積 = 1：OR の場合 ⇒ すべての条件の自動付与判定が1つでも「付与対象」ならタグを付与
                // condJudge が1つでも true ならばタグを付与
                $tagAdd = false;
                foreach ($this->condJudge as $condJudgeRow) {
                    if ($condJudgeRow) {
                        $tagAdd = true;
                        break;
                    }
                }
            }
        }
        return $tagAdd;
    }

    // 判定処理
    private function judgeAutoGrant($rows, $condColumnId, $condLengthFlg, $condOperator, $condValue)
    {
        if (!isset($rows[$condColumnId])) {
            return false;
        }
        if ($condLengthFlg == 1) {
            // バイト数フラグが1の場合
            // $rows[$condColumnId] のバイト数を取得
            $column = mb_strlen($rows[$condColumnId]);
        } else {
            // バイト数フラグが0の場合
            // $rows[$condColumnId] を取得
            $column = $rows[$condColumnId];
        }

        // 条件演算子 condOperator による判定
        if ($condOperator == "＝") {
            if ($condValue == $column) {
                return true;
            }
        }
        if ($condOperator == "≠") {
            if ($condValue != $column) {
                return true;
            }
        }
        if ($condOperator == "＜") {
            if ($condValue < $column) {
                return true;
            }
        }
        if ($condOperator == "＞") {
            if ($condValue > $column) {
                return true;
            }
        }
        if ($condOperator == "≦") {
            if ($condValue <= $column) {
                return true;
            }
        }
        if ($condOperator == "≧") {
            if ($condValue >= $column) {
                return true;
            }
        }

        // その他条件演算子
        if ($condOperator == "含む") {
            if (stripos($column, $condValue) !== false) {
                return true;
            }
        }
        if ($condOperator == "含まない") {
            if (stripos($column, $condValue) === false) {
                return true;
            }
        }
        if ($condOperator == "NULL") {
            if (is_null($column)) {
                return true;
            }
        }
        if ($condOperator == "NOT NULL") {
            if (!is_null($column)) {
                return true;
            }
        }
        if ($condOperator == "IN") {
            $condArray = explode(",", $condValue);
            if (in_array($column, $condArray)) {
                return true;
            }
        }
        return false;
    }
}
