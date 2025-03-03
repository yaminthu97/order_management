<?php

namespace App\Modules\Order\Gfh1207;

use App\Modules\Order\Base\GetInspectionDataInterface;

class GetInspectionData implements GetInspectionDataInterface
{
    public $csvDate;
    public $depositorNumber;

    /**
     * CSVファイルヘッダ
     */
    protected $csvHeader = [
        '訂正コード',
        '寄託者コード',
        '寄託者管理番号',
        '得意先コード',
        '届け先コード',
        '届け先名1',
        '届け先名２',
        '届け先電話番号',
        '届け先住所１（漢字）',
        '届け先住所２（漢字）',
        '届け先住所３（漢字）',
        '受注日',
        '作業日',
        '出荷日',
        '運送契約荷主コード',
        '出荷人コード',
        '精算種別コード',
        '精算金額',
        '倉庫コード',
        '連絡',
        '集約区分',
        '出荷区分',
        '配達指定日',
        '配達指定時間帯',
        '届け先郵便番号',
        'ルートコード',
        '市区町村コード',
        '仕分コード',
        '運送会社コード',
        '運送種別コード',
        '納入条件１',
        '納入条件２',
        '法人コード',
        'EOS区分',
        '納品区分',
        'センターサブコード',
        'センターコード',
        'センター名１',
        'センター名２',
        'センター住所１（漢字）',
        'センター住所２（漢字）',
        'センター住所３（漢字）',
        'センター電話番号',
        'センター郵便番号',
        '店舗コード',
        '店舗名１',
        '店舗名２',
        '店舗住所１（漢字）',
        '店舗住所２（漢字）',
        '店舗住所３（漢字）',
        '店舗電話番号',
        '店舗郵便番号',
        '店舗配達日',
        'H発注No',
        '伝票番号１',
        '伝票番号２',
        '納品日',
        '発注者',
        '備考（１６２）',
        '数字A1',
        '数字A2',
        '数字A3',
        '数字A4',
        '数字A5',
        '文字B1',
        '文字B2',
        '文字B3',
        '文字B4',
        '文字B5',
        '文字B6',
        '文字B7',
        '文字B8',
        '文字B9',
        '文字B10',
        '文字D1',
        '文字D2',
        '文字D3',
        '文字D4',
        '文字D5',
        '出荷指図明細行No',
        '寄託者管理行番号',
        '寄託者管理行枝番号',
        '商品コード',
        '品番コード',
        'カラーコード',
        'サイズコード',
        '変更後出荷依頼C/S数',
        '変更後出荷依頼B/L数',
        '変更後出荷依頼バラ数',
        '換算係数',
        '単価',
        '金額',
        '指定製造日',
        '指定製造ロット',
        '指定賞味期限日',
        '指定ロットNo１',
        '指定ロットNo２',
        '指定ロットNo３',
        '指定ロットNo４',
        '指定ロットNo５',
        '指定ロットNo６',
        '指定商品ランクコード',
        '指定在庫ステータスコード',
        '指定/優先在庫所有者ID',
        '在庫所有者指定/優先コード',
        '指定/優先取置顧客ID',
        '取置顧客指定/優先コード',
        '指定ゾーンコード',
        '出荷物理倉庫コード',
        '同一ロット指定区分',
        'ロット逆転不可指定区分',
        '通貨記号',
        '集約項目１',
        '集約項目２',
        '集約項目３',
        '集約項目４',
        '集約項目５',
        '集約項目６',
        '集約項目７',
        '集約項目８',
        '集約項目９',
        '集約項目１０',
        '指図書No（旧：D発注No）',
        '原価単価',
        '原価金額',
        '梱包グループNo',
        'D数字A1',
        'D数字A2',
        'D数字A3',
        'D数字A4',
        'D数字A5',
        'D文字B1',
        'D文字B2',
        'D文字B3',
        'D文字B4',
        'D文字B5',
        'D文字B6',
        'D文字B7',
        'D文字B8',
        'D文字B9',
        'D文字B10',
        'D文字D1',
        'D文字D2',
        'D文字D3',
        'D文字D4',
        'D文字D5'
    ];

    public function execute($searchResult, $depositorNumber)
    {
        // コンテンツデータを取得する
        $contentData = $this->setOutputRow($searchResult, $depositorNumber);

        // ヘッダー付きのCSVデータを準備する
        $csvData = implode(',', $this->csvHeader) . "\n"; // ヘッダー行を追加

        foreach ($contentData as $row) {
            $csvData .= implode(',', $row) . "\n"; // 各データ行を追加
        }

        return [
            'recordCount' => count($contentData),
            'csvData' => $csvData,
        ];
    }

    /**
     * setOutputRow
     *
     * @param  array $searchResult
     * @return array
     */
    protected function setOutputRow($searchResult, $depositorNumber)
    {
        $csvParams = [];
        $rowNumber = 1;

        foreach ($searchResult  as $item) {
            foreach ($item as $subItem) {
                foreach ($subItem as $details) {
                    foreach ($details as $res) {
                        $csvParams[] = [
                            "1", // 訂正コード
                            "274403331", // 寄託者コード
                            $depositorNumber, // 寄託者管理番号
                            $res['m_cust_runk_id'], // 得意先コード
                            $res['destination_id'], // 届け先コード
                            null, // 届け先名1
                            null, // 届け先名２
                            null, // 届け先電話番号
                            null, // 届け先住所１（漢字）
                            null, // 届け先住所２（漢字）
                            null, // 届け先住所３（漢字）
                            null, // 受注日
                            $res['deli_inspection_date'], // 作業日
                            $res['deli_inspection_date'], // 出荷日
                            null, // 運送契約荷主コード
                            null, // 出荷人コード
                            null, // 精算種別コード
                            null, // 精算金額
                            "001", // 倉庫コード
                            null, // 連絡
                            null, // 集約区分
                            null, // 出荷区分
                            null, // 配達指定日
                            null, // 配達指定時間帯
                            null, // 届け先郵便番号
                            null, // ルートコード
                            null, // 市区町村コード
                            null, // 仕分コード
                            null, // 運送会社コード
                            null, // 運送種別コード
                            null, // 納入条件１
                            null, // 納入条件２
                            null, // 法人コード
                            null, // EOS区分
                            null, // 納品区分
                            null, // センターサブコード
                            null, // センターコード
                            null, // センター名１
                            null, // センター名２
                            null, // センター住所１（漢字）
                            null, // センター住所２（漢字）
                            null, // センター住所３（漢字）
                            null, // センター電話番号
                            null, // センター郵便番号
                            null, // 店舗コード
                            null, // 店舗名１
                            null, // 店舗名２
                            null, // 店舗住所１（漢字）
                            null, // 店舗住所２（漢字）
                            null, // 店舗住所３（漢字）
                            null, // 店舗電話番号
                            null, // 店舗郵便番号
                            null, // 店舗配達日
                            null, // H発注No
                            null, // 伝票番号１
                            null, // 伝票番号２
                            null, // 納品日
                            null, // 発注者
                            null, // 備考（１６２）
                            null, // 数字A1
                            null, // 数字A2
                            null, // 数字A3
                            null, // 数字A4
                            null, // 数字A5
                            null, // 文字B1
                            null, // 文字B2
                            null, // 文字B3
                            null, // 文字B4
                            null, // 文字B5
                            null, // 文字B6
                            null, // 文字B7
                            null, // 文字B8
                            null, // 文字B9
                            null, // 文字B10
                            null, // 文字D1
                            null, // 文字D2
                            null, // 文字D3
                            null, // 文字D4
                            null, // 文字D5
                            $rowNumber, // 出荷指図明細行No
                            null, // 寄託者管理行番号
                            null, // 寄託者管理行枝番号
                            $res['item_cd'], // 商品コード
                            null, // 品番コード
                            null, // カラーコード
                            null, // サイズコード
                            null, // 変更後出荷依頼C/S数
                            null, // 変更後出荷依頼B/L数
                            $res['total_order_sell_vol'], // 変更後出荷依頼バラ数
                            null, // 換算係数
                            null, // 単価
                            null, // 金額
                            null, // 指定製造日
                            null, // 指定製造ロット
                            null, // 指定賞味期限日
                            null, // 指定ロットNo１
                            null, // 指定ロットNo２
                            null, // 指定ロットNo３
                            null, // 指定ロットNo４
                            null, // 指定ロットNo５
                            null, // 指定ロットNo６
                            null, // 指定商品ランクコード
                            null, // 指定在庫ステータスコード
                            null, // 指定/優先在庫所有者ID
                            null, // 在庫所有者指定/優先コード
                            null, // 指定/優先取置顧客ID
                            null, // 取置顧客指定/優先コード
                            $res['remarks1'], // 指定ゾーンコード
                            "00001", // 出荷物理倉庫コード
                            "2", // 同一ロット指定区分
                            "2", // ロット逆転不可指定区分
                            null, // 通貨記号
                            null, // 集約項目１
                            null, // 集約項目２
                            null, // 集約項目３
                            null, // 集約項目４
                            null, // 集約項目５
                            null, // 集約項目６
                            null, // 集約項目７
                            null, // 集約項目８
                            null, // 集約項目９
                            null, // 集約項目１０
                            null, // 指図書No（旧：D発注No）
                            null, // 原価単価
                            null, // 原価金額
                            null, // 梱包グループNo
                            null, // D数字A1
                            null, // D数字A2
                            null, // D数字A3
                            null, // D数字A4
                            null, // D数字A5
                            $res['page_cd'], // D文字B1
                            null, // D文字B2
                            null, // D文字B3
                            null, // D文字B4
                            null, // D文字B5
                            null, // D文字B6
                            null, // D文字B7
                            null, // D文字B8
                            null, // D文字B9
                            null, // D文字B10
                            null, // D文字D1
                            null, // D文字D2
                            null, // D文字D3
                            null, // D文字D4
                            null // D文字D5
                        ];
                        $rowNumber++;
                    }
                }
            }
        }
        return $csvParams;
    }
}
