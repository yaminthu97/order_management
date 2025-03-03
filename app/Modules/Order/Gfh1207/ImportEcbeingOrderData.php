<?php

namespace App\Modules\Order\Gfh1207;

use App\Enums\ItemNameType;
use App\Enums\ThreeTemperatureZoneTypeEnum;
use App\Http\Requests\Gfh1207\ImportEcbeingOrderDataRequest;
use App\Models\Ami\Base\AmiEcPageModel;
use App\Models\Ami\Base\AmiPageSkuModel;
use App\Models\Ami\Gfh1207\AmiPageAttachmentItemsModel;
use App\Models\Ami\Gfh1207\AmiPageModel;
use App\Models\Cc\Gfh1207\CustModel;
use App\Models\Master\Base\EcsModel;
use App\Models\Master\Gfh1207\ItemnameTypesModel;
use App\Models\Master\Gfh1207\NoshiDetailModel;
use App\Models\Master\Gfh1207\NoshiModel;
use App\Models\Order\Gfh1207\OrderDestinationModel;
use App\Models\Order\Gfh1207\OrderDetailAttachmentItemsModel;
use App\Models\Order\Gfh1207\OrderDetailModel;
use App\Models\Order\Gfh1207\OrderDetailNoshiModel;
use App\Models\Order\Gfh1207\OrderDetailSkuModel;
use App\Models\Order\Gfh1207\OrderHdrModel;
use App\Models\Warehouse\Base\WarehouseModel;
use App\Modules\Master\Base\GetYmstTime;
use App\Modules\Order\Base\ImportEcbeingOrderDataInterface;
use Carbon\Carbon;
use Exception;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use InvalidArgumentException;

/**
 * Ecbeing受注取込データを更新または作成する機能
 */
class ImportEcbeingOrderData implements ImportEcbeingOrderDataInterface
{
    //get text file path to save on S3 server
    protected $getTextExportFilePath;

    //tsv format check
    protected $tsvFormatCheck;

    //for s3
    protected $s3;

    //店舗情報取得
    protected $getYmstTime;

    //throw error code constants
    private const PRIVATE_THROW_ERR_CODE = -1;

    //ecbing order column count constants
    private const  ECBEING_ORDER_COLUMN_COUNT = 81;

    //常温
    private const TEMPERATURE_ZONE_NOMAL = ThreeTemperatureZoneTypeEnum::NORMAL->value;

    //13:付属品グループ
    private const  ATTACHMENT_GROUP = ItemNameType::AttachmentGroup->value;

    //1:Handbag
    private const  HAND_BAG = '1';

    //ecbeing 冷凍
    private const  ECEBING_TEMP_FORZEN = 1;

    //ecbeing 冷蔵
    private const  ECEBING_TEMP_COOL = 2;

    //deadlock error code
    private const  DEADLOCK_ERROR_CODE = '40001';

    public function __construct(
        GetTextExportFilePath $getTextExportFilePath,
        TsvFormatCheck $tsvFormatCheck,
        GetYmstTime $getYmstTime,
    ) {
        $this->getTextExportFilePath = $getTextExportFilePath;
        $this->tsvFormatCheck = $tsvFormatCheck;
        $this->s3 = config('filesystems.default', 'local');
        $this->getYmstTime = $getYmstTime;
    }

    /**
     * Ecbeing受注取込データを更新または作成する
     * @param string (order file path)
     * @param int (account id)
     * @param string (account code)
     * @param string (batch type)
     * @param int (batch id)
     * @param int (operators Id)
     * @return array (total rows count , t_order_hdr_id , t_order_destination_id)
     */

    public function execute($orderTsvFilePath, $accountId, $accountCode, $batchType, $bathID, $operatorsId)
    {
        //get tsv file contents
        $fileContents = Storage::disk($this->s3)->get($orderTsvFilePath);

        // 文字コードをチェックし、UTF-8に変換する
        $encoding = mb_detect_encoding($fileContents, ['SJIS-win', 'SJIS-WIN', 'SJIS', 'UTF-8', 'EUC-JP'], true);
        if ($encoding) {
            // SJIS系の表記揺れを統一（SJIS-win / SJIS-WIN → SJIS）
            if (stripos($encoding, 'SJIS') !== false) {
                $encoding = 'SJIS'; // 統一する
            }
            
            // UTF-8 に変換
            $fileContents = mb_convert_encoding($fileContents, 'UTF-8', $encoding);
        }

        // file data is tsv format or not
        $tsvFormatCheck = $this->tsvFormatCheck->execute($fileContents);
        if (!$tsvFormatCheck) {
            // 取込ファイル内容フォーマットが間違っています。
            throw new Exception(__('messages.error.input_file_format_error'), self::PRIVATE_THROW_ERR_CODE);
        }

        $customerOrdersDataArray = $this->convertTsvToArray($fileContents);

        foreach ($customerOrdersDataArray as $customerOrdersData) {
            $mergedArray = array_merge(
                $customerOrdersData['customer'],
                $customerOrdersData['order_hdr'],
                $customerOrdersData['order_destination'],
                $customerOrdersData['order_detail'],
                $customerOrdersData['order_detail_noshi'],
                $customerOrdersData['order_detail_attachment_items']
            );
            $customerOrdersDataMergeArray[] = $mergedArray;
        };

        $this->validateOrderData($customerOrdersDataMergeArray, $accountCode, $batchType, $bathID);

        // Get total number of rows
        $totalRowCnt = count($customerOrdersDataArray);

        try {
            foreach ($customerOrdersDataArray as $customerOrder) {

                // for m_cust table
                $customer = $customerOrder['customer'];
                $orderHdr = $customerOrder['order_hdr'];
                $orderDestination = $customerOrder['order_destination'];
                $orderDetail = $customerOrder['order_detail'];
                $orderDetailNoshi = $customerOrder['order_detail_noshi'];
                $orderDetailAttachmentItems = $customerOrder['order_detail_attachment_items'];

                $custRecord = CustModel::where('reserve10', $customer['reserve10'])->first();
                if (!$custRecord) {
                    throw new Exception(__('messages.error.data_not_found2', ['datatype' => '出力']), self::PRIVATE_THROW_ERR_CODE);
                };
                //顧客マスタを更新する
                $custRecord->update(array_merge($customer, ['update_operator_id' => $operatorsId]));

                // for t_order_hdr table
                // get required m_cust table values
                $customerOrderOperatorsId = $custRecord->order_operators_id; //order_operators_id
                $customerEmail1 = $custRecord->email1; //email1
                $customerEmail2 = $custRecord->email2; //email2
                $customerTel1 = $custRecord->tel1; //tel1
                $customerUpdateOperatorId = $custRecord->update_operator_id; //update_operator_id

                // //change date format ymd to y-m-d
                $orderDate = $this->convertDateFormat($orderHdr['order_date']);
                // //change time  format hms to h:m:s
                $orderTime = $orderHdr['order_time'] ? Carbon::parse($orderHdr['order_time'])->format('H:i:s') : null;

                //受注基本が存在する場合受注基本を作成はスキップ
                $orderHdrRecord = OrderHdrModel::firstOrCreate(
                    ['ec_order_num' => $orderHdr['ec_order_num']],
                    array_merge($orderHdr, [
                        'm_account_id' => $accountId,
                        'order_operator_id' => $customerOrderOperatorsId,
                        'order_datetime' => Carbon::parse($orderDate . $orderTime)->format('Y-m-d H:i:s'), // change datetime format Y-m-d H:i:s
                        'order_name' => $orderHdr['order_name1'] .  $orderHdr['order_name2'],
                        'order_name_kana' => $orderHdr['order_name_kana1'] .  $orderHdr['order_name_kana2'],
                        'order_email1' => $customerEmail1,
                        'order_email2' => $customerEmail2,
                        'm_cust_id_billing' => $custRecord->m_cust_id,
                        'billing_email1' => $customerEmail1,
                        'billing_email2' => $customerEmail2,
                        'billing_tel1' => $customerTel1,
                        'shipping_fee' => $orderHdr['order_hdr_shipping_fee'],
                        'payment_fee' => $orderHdr['order_hdr_payment_fee'],
                        'entry_operator_id' => $customerUpdateOperatorId,
                        'update_operator_id' => $customerUpdateOperatorId,
                        'ec_order_change_flg' => 0,
                        'ec_order_change_datetime' => null,
                        'ec_order_sync_flg' => 0,
                        'ec_order_sync_datetime' => null,
                    ])
                );

                // for t_order_destination table
                // get required t_order_hdr table values
                $orderHdrMAccountId = $orderHdrRecord->m_account_id; //m_account_id
                $orderHdrId = $orderHdrRecord->t_order_hdr_id; //t_order_hdr_id
                $orderHdrEcsId = $orderHdrRecord->m_ecs_id; //m_ecs_id
                $orderHdrEntryOperatorId = $orderHdrRecord->entry_operator_id; //entry_operator_id
                $orderHdrUpdateOperatorId = $orderHdrRecord->update_operator_id; //update_operator_id

                //受注基本ごとの連番
                $orderDestinationSeq = OrderDestinationModel::where('t_order_hdr_id', $orderHdrId)->count() + 1;

                // condition to  t_order_dtl of count = 1 & t_order_dtl.order_sell_vol = 1
                $orderDetailData = OrderDestinationModel::where('order_destination_seq', 1)
                    ->select('order_destination_seq')
                    ->withCount('orderDtls')
                    ->with(['orderDtlsWithDestinationSeq:order_destination_seq,order_sell_vol'])
                    ->whereHas('orderDtlsWithDestinationSeq', function ($query) {
                        $query->where('order_sell_vol', 1);
                    })
                    ->having('order_dtls_count', '=', 1)
                    ->get();

                if (
                    $orderDestination['order_hdr_address'] != $orderDestination['order_destination_address'] && //配送先住所が注文主住所と異なる
                    $orderDetailData->isNotEmpty() && //配送先の受注明細が1商品1数量のみ
                    $orderDestination['total_temperature_zone_type'] == ThreeTemperatureZoneTypeEnum::NORMAL->value //・配送先クール区分が常温である

                ) {
                    $orderDestination['gp1_type'] = 1;
                }

                //受注配送先が存在する場合受注配送先を作成はスキップ
                $orderDestinationRecord = OrderDestinationModel::firstOrCreate(
                    ['t_order_hdr_id' => $orderHdrId, 'order_destination_seq' => $orderDestination['order_destination_seq']],
                    array_merge($orderDestination, [
                        'm_account_id' => $orderHdrMAccountId,
                        't_order_hdr_id' => $orderHdrId,
                        'destination_name' => $orderDestination['destination_name1'] .  $orderDestination['destination_name2'],
                        'shipping_fee' => $orderDestination['order_destination_shipping_fee'],
                        'payment_fee' => $orderDestination['order_destination_payment_fee'],
                        'entry_operator_id' => $orderHdrEntryOperatorId,
                        'update_operator_id' => $orderHdrUpdateOperatorId,
                        'order_destination_seq' => $orderDestinationSeq,
                    ])
                );

                // for t_order_destination table
                // get required t_order_destination table values
                $orderDestinationMAccountId = $orderDestinationRecord->m_account_id; //m_account_id
                $orderDestinationOrderHdrId = $orderDestinationRecord->t_order_hdr_id; //t_order_hdr_id
                $orderDestinationId = $orderDestinationRecord->t_order_destination_id; //t_order_destination_id
                $orderDestinationSeq = $orderDestinationRecord->order_destination_seq; //order_destination_seq
                $orderDestinationEntryOperatorId = $orderDestinationRecord->entry_operator_id; //entry_operator_id
                $orderDestinationUpdateOperatorId = $orderDestinationRecord->update_operator_id; //update_operator_id

                //受注配送先ごとに1からの連番
                $orderDetailSeq = OrderDetailModel::where('t_order_destination_id', $orderDestinationId)->count() + 1;

                $orderDetailAmiEcPage = AmiEcPageModel::where([
                    'm_ecs_id' => $orderHdrEcsId,
                    'ec_page_cd' => $orderDetail['sell_cd']
                ])
                    ->select('m_ami_page_id')
                    ->with(['page:m_ami_page_id,page_title'])
                    ->first();

                $orderDetailAmiPageId = $orderDetailAmiEcPage->page->m_ami_page_id; //m_ami_page_id

                //受注明細を全て登録する
                $orderDetailRecord = OrderDetailModel::create(
                    array_merge($orderDetail, [
                        'm_account_id' => $orderDestinationMAccountId,
                        't_order_hdr_id' => $orderDestinationOrderHdrId,
                        't_order_destination_id' => $orderDestinationId,
                        'ecs_id' => $orderHdrEcsId,
                        'order_destination_seq' => $orderDestinationSeq,
                        'order_dtl_seq' => $orderDetailSeq,
                        'sell_id' => $orderDetailAmiPageId,
                        'sell_name' => $orderDetailAmiEcPage->page->page_title,
                        'tax_price' => $orderDetail['order_sell_price'] * $orderDetail['tax_rate'],
                        'order_time_sell_vol' => $orderDetail['order_sell_vol'],
                        'entry_operator_id' => $orderDestinationEntryOperatorId,
                        'update_operator_id' => $orderDestinationUpdateOperatorId,
                    ])
                );

                // for t_order_dtl_noshi table
                // get required t_order_detail table values
                $orderDetailSellVol = $orderDetailRecord->order_sell_vol; //order_sell_vol

                $orderDetailFields = [
                    'm_account_id' => $orderDetailRecord->m_account_id,
                    't_order_hdr_id' => $orderDetailRecord->t_order_hdr_id,
                    't_order_destination_id' => $orderDetailRecord->t_order_destination_id,
                    't_order_dtl_id' => $orderDetailRecord->t_order_dtl_id,
                    'ecs_id' => $orderDetailRecord->ecs_id,
                    'order_destination_seq' => $orderDetailRecord->order_destination_seq,
                    'order_dtl_seq' => $orderDetailRecord->order_dtl_seq,
                    'sell_cd' => $orderDetailRecord->sell_cd,
                    'entry_operator_id' => $orderDetailRecord->entry_operator_id,
                    'update_operator_id' => $orderDetailRecord->update_operator_id,
                ];

                //のし枚数が0でない場合受注明細熨斗を全て登録する
                if ($orderDetailNoshi['count'] !== 0) {
                    OrderDetailNoshiModel::create(
                        array_merge($orderDetailNoshi, $orderDetailFields)
                    );
                }

                $orderDetailAttachmentItemsMergedArray = array_map(
                    fn ($item) => array_merge($item, $orderDetailFields, ['order_sell_vol' => $orderDetailSellVol]),
                    $orderDetailAttachmentItems
                );

                //受注明細付属品を全て登録する
                OrderDetailAttachmentItemsModel::insert($orderDetailAttachmentItemsMergedArray);

                // for t_order_dtl_sku table
                // get required m_ami_page_sku & m_ami_sku tables values
                $amiPageSku = AmiPageSkuModel::where('m_ami_page_id', $orderDetailAmiPageId)
                    ->select('m_ami_page_sku_id', 'm_ami_sku_id', 'sku_vol')
                    ->with(['sku:m_ami_sku_id,sku_cd,sku_name,m_suppliers_id,three_temperature_zone_type,including_package_flg,direct_delivery_flg,gift_flg,item_price'])
                    ->get()->toArray();

                $amiPageSkuMergedArray = array_map(
                    fn($item) => array_merge(
                        [
                            'order_sell_vol' => $orderDetailSellVol,
                            'item_id' => $item['sku']['m_ami_sku_id'], //SKU ID
                            'item_cd' => $item['sku']['sku_cd'], //SKU CD
                            'item_vol' => $item['sku_vol'], //SKU数量
                            'm_supplier_id' =>  $item['sku']['m_suppliers_id'], //仕入先マスタID
                            'temperature_type' => $item['sku']['three_temperature_zone_type'], //3温度帯
                            'order_bundle_type' => $item['sku']['including_package_flg'], //同梱フラグ
                            'direct_delivery_type' => $item['sku']['direct_delivery_flg'], //直送フラグ
                            'gift_type' => $item['sku']['gift_flg'], //ギフトフラグ
                            'item_cost' => $item['sku']['item_price'], //基本仕入単価
                        ],
                        $orderDetailFields
                    ),
                    $amiPageSku
                );

                //受注明細SKUを全て登録する
                OrderDetailSkuModel::insert($amiPageSkuMergedArray);

                $ordersInfo[] = [
                    'order_hdr_id' => $orderHdrId,
                    'order_destination_id' => $orderDestinationId,
                    'personal_flag' => $orderDestination['campaign_flag'],
                    'order_hdr_create_flag' => $orderHdrRecord->wasRecentlyCreated, // Check if the record was created
                ];
            }

            return  [
                'total_row_count' => $totalRowCnt,
                'orders_info' => $ordersInfo,
            ];
        }
        // Catch for specific database errors
        catch (QueryException $e) {
            Log::error('error_message : ' . $e->getMessage());
            //when deadloack error happen
            if ($e->getCode() == self::DEADLOCK_ERROR_CODE) {
                throw new Exception('デッドロックエラーが発生しました。', self::DEADLOCK_ERROR_CODE);
            }

            // 顧客データ登録・更新処理で異常が発生しました。
            throw new Exception(__('messages.error.process_something_wrong', ['process' => '顧客データ登録・更新処理']), self::PRIVATE_THROW_ERR_CODE);
        }
    }

    /**
     * Ecbeing から ESM への TSV データを配列データに準備する
     * @param string (tsv raw data)
     * @return array (convert tsv to array)
     */
    private function convertTsvToArray($tsvData)
    {
        // Split by lines
        $lines = explode("\n", $tsvData);

        // Remove empty elements
        $lines = array_filter($lines);

        // Re-index the array
        $lines = array_values($lines);

        // Define the mapping of indices to ESM keys
        //（index = tsv file column index, keys = field name of m_cust , t_order_hdr , t_order_destination ,
        //  t_order_dtl , t_order_dtl_noshi , t_order_dtl_attachment_items tables）
        $keyMapping = [
            //for m_cust & t_order_hdr
            0 => 'ec_order_num', //オーダーID.
            1 => 'order_date', //受注日（日時）
            2 => 'order_time', //注文日（時刻）
            3 => 'reserve10', //EC顧客ID
            4 => 'order_corporate_name', //注文者会社名
            5 => 'order_division_name', //注文者部署名
            6 => 'order_name1', //注文者氏名(姓)
            7 => 'order_name2', //注文者氏名(名)
            8 => 'order_name_kana1', //注文者カナ(セイ)
            9 => 'order_name_kana2', //注文者カナ(メイ)
            10 => 'order_postal', //注文者郵便番号
            11 => 'order_address1', //注文者都道府県
            12 => 'order_address2', //注文者住所
            13 => 'order_address3', //注文者住所２
            14 => 'ブランク', //no use
            15 => 'order_address4', //注文者住所３
            16 => 'order_tel1', //注文者電話番号
            17 => 'FAX番号', //FAX番号
            18 => 'sell_total_price', //商品金額合計
            19 => '合計割引金額', //no use
            20 => '利用ポイント数', //no use
            21 => '合計ポイント値引金額', //no use
            22 => 'order_hdr_shipping_fee', //送料合計
            23 => '割引額', //no use
            24 => 'order_total_price', //注文金額合計
            25 => 'order_hdr_payment_fee', //支払手数料
            26 => 'delivery_type_fee', //クール手数料
            27 => 'm_payment_types_id', //支払方法
            28 => 'receipt_type', //領収書区分
            29 => 'receipt_direction', //領収書宛名
            30 => 'order_comment', //注文コメント
            31 => 'dm_send_mail_flg', //EメールDM発送フラグ
            32 => 'dm_send_letter_flg', //郵送DM発送フラグ
            33 => 'standard_total_price', //通常税率合計金額
            34 => 'reduce_total_price', //軽減税率合計金額
            35 => 'standard_tax_price', //通常税率消費税額
            36 => 'reduce_tax_price', //軽減税率消費税額

            //for t_order_destination
            37 => 'order_destination_seq', //配送先番号
            38 => 'destination_company_name', //配送先会社名
            39 => 'destination_division_name', //配送先部署名
            40 => 'destination_name1', //配送先氏名(姓)
            41 => 'destination_name2', //配送先氏名(名)
            42 => 'destination_name_kana1', //配送先カナ(セイ)
            43 => 'destination_name_kana2', //配送先カナ(メイ)
            44 => 'destination_postal', //配送先郵便番号
            45 => 'destination_address1', //配送先都道府県
            46 => 'destination_address2', //配送先住所
            47 => 'destination_address3', //配送先住所２
            48 => 'destination_address4', //配送先住所３
            49 => 'destination_tel', //配送先電話番号
            50 => 'sender_name', //送り主名
            51 => '出荷予定日', //no use
            52 => 'deli_hope_date', //配送先配達希望日
            53 => 'm_delivery_time_hope_id', //配達指定時間帯
            54 => 'campaign_flag', //ご用途（ご本人フラグ）
            55 => 'total_temperature_zone_type', //配送先クール区分
            56 => '配送先合計商品金額', //no use
            57 => 'order_destination_shipping_fee', //送料
            58 => 'コレクト手数料', //no use
            59 => 'order_destination_payment_fee', //クール手数料

            //t_order_dtl
            60 => '明細番号', //no use
            61 => 'sell_cd', //商品コード
            62 => 'order_sell_vol', //数量
            63 => 'order_sell_price', //価格
            64 => '商品金額', //no use
            65 => '割引金額', //no use
            66 => '利用ポイント数', //no use
            67 => 'ポイント値引金額', //no use
            68 => 'm_noshi_format_id', //熨斗種類ID
            69 => 'm_noshi_naming_pattern_id', //名入れパターンID

            //t_order_dtl_noshi
            70 => 'omotegaki', //のし表書き
            71 => 'company_name1', //のし会社名
            72 => 'title1', //のし肩書き
            73 => 'name', //のし名前
            74 => 'name1', //のし名１
            75 => 'name2', //のし名２
            76 => 'name3', //のし名3
            77 => 'count', //のし枚数
            78 => 'attach_flg', //のし貼付フラグ

            //t_order_dtl_attachment_items
            79 => 'carrier_bag_vol', //手提げ袋枚数

            //t_order_dtl
            80 => 'tax_rate', //軽減税率フラグ
        ];

        // Create array of objects
        return array_map(function ($line) use ($keyMapping) {

            // Get the row values as an indexed array
            $row = str_getcsv($line, "\t");
            // when the column count doesn't match the Ecbeibg order column count
            if (count($row) != self::ECBEING_ORDER_COLUMN_COUNT) {
                // 取込ファイル内容フォーマットが間違っています。
                throw new Exception(__('messages.error.input_file_format_error'), self::PRIVATE_THROW_ERR_CODE);
            }

            $filteredRow = [];

            // Build the new key-value pairs based on the mapping
            foreach ($keyMapping as $index => $key) {
                if (isset($row[$index])) {
                    $filteredRow[$key] = $row[$index];
                }
            }

            $customer = $this->customerDataPrepare($filteredRow);
            $orderHdr = $this->orderHdrDataPrepare($filteredRow, $customer);
            $orderDestination = $this->orderDestinationDataPrepare($filteredRow);
            $orderDetail = $this->orderDetailDataPrepare($filteredRow);
            $orderDetailNoshi = $this->orderDetailNoshiDataPrepare($filteredRow);
            $orderDetailAttachmentItems = $this->orderDetailAttachmentItemsDataPrepare($filteredRow);

            return [
                'customer' => $customer,
                'order_hdr' => $orderHdr,
                'order_destination' => $orderDestination,
                'order_detail' => $orderDetail,
                'order_detail_noshi' => $orderDetailNoshi,
                'order_detail_attachment_items' => $orderDetailAttachmentItems,
            ];
        }, $lines);
    }

    /**
     * Customer Data Prepare for m_cust table
     * @param  array (data)
     * @return array
     */
    private function customerDataPrepare($data)
    {
        $customer = [];

        // Prepare data for m_cust table
        $customer['reserve10'] = $this->convertInteger($data['reserve10']);
        $customer['dm_send_mail_flg'] = $this->convertInteger($data['dm_send_mail_flg']);
        $customer['dm_send_letter_flg'] = $this->convertInteger($data['dm_send_letter_flg']);

        return $customer;
    }

    /**
     * Order Hdr Data Prepare for t_order_hdr table
     * @param  array (data)
     * @return array
     */
    private function orderHdrDataPrepare($data, $customer)
    {
        $orderHdr = [];

        //m_ecs から m_ecs_sort が一番小さい、同じならばm_ecs_id が小さいもの
        $ecsId = EcsModel::orderBy('m_ecs_sort', 'asc')->pluck('m_ecs_id')->first();

        // Prepare data for t_order_hdr table
        $orderHdr['ec_order_num'] = $data['ec_order_num'];
        $orderHdr['m_ecs_id'] = $ecsId;
        $orderHdr['order_date'] = $data['order_date'];
        $orderHdr['order_time'] = $data['order_time'];
        $orderHdr['m_cust_id'] = $customer['reserve10'];
        $orderHdr['order_corporate_name'] = $data['order_corporate_name'];
        $orderHdr['order_division_name'] = $data['order_division_name'];
        $orderHdr['order_name1'] = $data['order_name1'];
        $orderHdr['order_name2'] =  $data['order_name2'];
        $orderHdr['order_name_kana1'] = $data['order_name_kana1'];
        $orderHdr['order_name_kana2'] =  $data['order_name_kana2'];
        $orderHdr['order_postal'] = $data['order_postal'] ? str_replace('-', '', $data['order_postal']) : null;  //remove all hyphens
        $orderHdr['order_address1'] = $data['order_address1'];
        $orderHdr['order_address2'] = $data['order_address2'];
        $orderHdr['order_address3'] = $data['order_address3'];
        $orderHdr['order_address4'] = $data['order_address4'];
        $orderHdr['order_tel1'] = $data['order_tel1'];

        $orderHdr['billing_corporate_name'] = $orderHdr['order_corporate_name'];
        $orderHdr['billing_division_name'] = $orderHdr['order_division_name'];
        $orderHdr['billing_name'] = $orderHdr['order_name1'] . $orderHdr['order_name2'];
        $orderHdr['billing_name_kana'] = $orderHdr['order_name_kana1'] . $orderHdr['order_name_kana2'];
        $orderHdr['billing_postal'] = $orderHdr['order_postal'];
        $orderHdr['billing_address1'] = $orderHdr['order_address1'];
        $orderHdr['billing_address2'] = $orderHdr['order_address2'];
        $orderHdr['billing_address3'] = $orderHdr['order_address3'];
        $orderHdr['billing_address4'] = $orderHdr['order_address4'];

        $orderHdr['sell_total_price'] = $this->convertInteger($data['sell_total_price']);
        $orderHdr['order_hdr_shipping_fee'] = $this->convertInteger($data['order_hdr_shipping_fee']);
        $orderHdr['order_total_price'] = $this->convertInteger($data['order_total_price']);
        $orderHdr['order_hdr_payment_fee'] =  $this->convertInteger($data['order_hdr_payment_fee']);
        $orderHdr['delivery_type_fee'] = $this->convertInteger($data['delivery_type_fee']);
        $orderHdr['m_payment_types_id'] = $this->convertInteger($data['m_payment_types_id']);
        $orderHdr['receipt_type'] = $this->convertInteger($data['receipt_type']);
        $orderHdr['receipt_direction'] = $data['receipt_direction'];
        $orderHdr['order_comment'] = $data['order_comment'];
        $orderHdr['standard_total_price'] = $this->convertInteger($data['standard_total_price']);
        $orderHdr['reduce_total_price'] = $this->convertInteger($data['reduce_total_price']);
        $orderHdr['standard_tax_price'] = $this->convertInteger($data['standard_tax_price']);
        $orderHdr['reduce_tax_price'] = $this->convertInteger($data['reduce_tax_price']);

        return $orderHdr;
    }

    /**
     * Order Destination Data Prepare for t_order_destination table
     * @param  array (data)
     * @return array
     */
    private function orderDestinationDataPrepare($data)
    {
        try {
            $orderDestination = [];

            // Prepare data for t_order_destination table
            $orderDestination['order_destination_seq'] = $this->convertInteger($data['order_destination_seq']);
            $orderDestination['destination_company_name'] = $data['destination_company_name'];
            $orderDestination['destination_division_name'] = $data['destination_division_name'];
            $orderDestination['destination_name1'] = $data['destination_name1'];
            $orderDestination['destination_name2'] = $data['destination_name2'];
            $orderDestination['destination_name_kana'] = $data['destination_name_kana1'] . $data['destination_name_kana2'];
            $orderDestination['destination_postal'] =  $data['destination_postal'] ? str_replace('-', '', $data['destination_postal']) : null;  //remove all hyphens
            $orderDestination['destination_address1'] = $data['destination_address1'];
            $orderDestination['destination_address2'] = $data['destination_address2'];
            $orderDestination['destination_address3'] = $data['destination_address3'];
            $orderDestination['destination_address4'] = $data['destination_address4'];
            $orderDestination['destination_tel'] = $data['destination_tel'];
            $orderDestination['sender_name'] = $data['sender_name'];

            $orderDestination['deli_hope_date'] = $this->convertDateFormat($data['deli_hope_date']);
            // when deli_hope_date is null , deli_hope_date is set up to next day
            if (!$orderDestination['deli_hope_date']) {
                $orderDestination['deli_plan_date'] = Carbon::tomorrow()->format('Y-m-d');
            } else {
                // get the m_warehouses_id of the smallest m_warehouse_priority from the m_warehouse table
                $wareHousesId = WarehouseModel::orderBy('m_warehouse_priority', 'asc')->pluck('m_warehouses_id')->first();
                // throw error when m_warehose table
                if (!$wareHousesId) {
                    throw new Exception('倉庫が見つかりません', self::PRIVATE_THROW_ERR_CODE);
                };
                // call 店舗情報取得 モジュール
                $deliveryDays = $this->getYmstTime->execute($wareHousesId, $orderDestination['destination_postal']);
                //deli_hope_date - delivery_days を出荷予定日 (deli_plan_date)とする。
                $orderDestination['deli_plan_date'] = Carbon::createFromFormat('Y-m-d', $orderDestination['deli_hope_date'])
                    ->subDays($deliveryDays->delivery_days)
                    ->format('Y-m-d');
            }

            $orderDestination['m_delivery_time_hope_id'] = $this->convertInteger($data['m_delivery_time_hope_id']);
            $orderDestination['campaign_flag'] = $this->convertInteger($data['campaign_flag']);
            $orderDestination['order_destination_shipping_fee'] = $this->convertInteger($data['order_destination_shipping_fee']);

            // total temperature zone type ecbingからesmへの変換と設定
            $totalTemperatureZoneType = $this->convertInteger($data['total_temperature_zone_type']);
            if ($totalTemperatureZoneType == self::ECEBING_TEMP_FORZEN) {
                $totalTemperatureZoneType = ThreeTemperatureZoneTypeEnum::FROZEN->value;
            } elseif ($totalTemperatureZoneType == self::ECEBING_TEMP_COOL) {
                $totalTemperatureZoneType = ThreeTemperatureZoneTypeEnum::COOL->value;
            }
            $orderDestination['total_temperature_zone_type'] = $totalTemperatureZoneType;

            //常温の場合は0がセットされる。それ以外は手数料金がセットさ
            $paymentFeeOrderDestination = $this->convertInteger($data['order_destination_payment_fee']);
            if ($totalTemperatureZoneType == self::TEMPERATURE_ZONE_NOMAL) {
                $paymentFeeOrderDestination = 0;
            };
            $orderDestination['order_destination_payment_fee'] = $paymentFeeOrderDestination;

            // 一品一葉フラグ
            $orderDestination['gp1_type'] = null;
            $orderDestination['order_hdr_address'] = $data['order_address1'] .  $data['order_address2'] .  $data['order_address3'] .  $data['order_address4'];
            $orderDestination['order_destination_address']  =  $data['destination_address1'] .  $data['destination_address2'] .  $data['destination_address3'] .  $data['destination_address4'];

            return $orderDestination;

            //catch error messages from 店舗情報取得(getYmstTime) モジュール
        } catch (InvalidArgumentException $e) {
            throw new Exception($e->getMessage(), self::PRIVATE_THROW_ERR_CODE);
        }
    }

    /**
     * Order Destination Data Prepare for t_order_dtl table
     * @return array
     */
    private function orderDetailDataPrepare($data)
    {
        $orderDetail = [];

        // Prepare data for t_order_dtl table
        $orderDetail['sell_cd'] = $data['sell_cd'];
        $orderDetail['order_sell_vol'] = $this->convertInteger($data['order_sell_vol']);
        $orderDetail['order_sell_price'] = $this->convertInteger($data['order_sell_price']);
        $orderDetail['tax_rate'] = $this->convertInteger($data['tax_rate']);

        return $orderDetail;
    }

    /**
     * Order Detail Noshi Data Prepare for t_order_dtl_noshi table
     * @param  array (data)
     * @return array
     */
    private function orderDetailNoshiDataPrepare($data)
    {
        $orderDetailNoshi = [];

        //get m_noshi_detail_id , template_file_name from 熨斗詳細マスタ
        $noshiDetail = NoshiDetailModel::select('m_noshi_detail_id', 'm_noshi_id', 'template_file_name')
            // m_noshi_id , noshi_type , attachment_item_group_id  form 熨斗マスタ
            ->with(['noshi:m_noshi_id,noshi_type,attachment_item_group_id'])
            ->where('m_noshi_format_id', $data['m_noshi_format_id'])
            ->whereRelation('noshiNamingPattern', 'm_noshi_naming_pattern_id', $data['m_noshi_naming_pattern_id'])
            ->first();

        // Prepare data for t_order_dtl_noshi table
        $orderDetailNoshi['noshi_detail_id'] = $noshiDetail->m_noshi_detail_id;
        $orderDetailNoshi['noshi_id'] = $noshiDetail->noshi->m_noshi_id;
        $orderDetailNoshi['noshi_type'] = $noshiDetail->noshi->noshi_type;
        $orderDetailNoshi['attachment_item_group_id'] = $noshiDetail->noshi->attachment_item_group_id;
        $orderDetailNoshi['omotegaki'] = $data['omotegaki'];
        $orderDetailNoshi['m_noshi_naming_pattern_id'] = $data['m_noshi_naming_pattern_id'];
        $orderDetailNoshi['company_name1'] = $data['company_name1'];
        $orderDetailNoshi['title1'] = $data['title1'];
        $orderDetailNoshi['name1'] = $data['name'] ? $data['name'] : $data['name1'];
        $orderDetailNoshi['name2'] = $data['name2'];
        $orderDetailNoshi['name3'] = $data['name3'];
        $orderDetailNoshi['count'] = $this->convertInteger($data['count']);
        $orderDetailNoshi['attach_flg'] = $this->convertInteger($data['attach_flg']);

        return $orderDetailNoshi;
    }

    /**
     * Order Detail Attachment Items Data Prepare for t_order_dtl_attachment_items
     * @param  array (data)
     * @return array
     */
    private function orderDetailAttachmentItemsDataPrepare($data)
    {
        $orderDetailAttachmentItems = [];
        $groupId = null;
        $carrierBagVol = $this->convertInteger($data['carrier_bag_vol']);

        //「熨斗詳細マスタ (m_noshi_detail)」の「熨斗ID (m_noshi_id)」から熨斗マスタ (m_noshi) が取得できます。
        // 熨斗マスタの「付属品グループID (attachment_item_group_id)」が「通常/仏」などを表す「付属品グループID」が取得可能です。
        $attachmentItemGroupId = NoshiModel::whereRelation('noshiDetail', 'm_noshi_format_id', $data['m_noshi_format_id'])
            ->whereRelation('noshiDetail.noshiNamingPattern', 'm_noshi_naming_pattern_id', $data['m_noshi_naming_pattern_id'])
            ->pluck('attachment_item_group_id')
            ->first();
        $groupId = $attachmentItemGroupId;

        //項目名称マスタから項目名称区分=13:付属品グループ、並び順が最も小さいものを1件取得
        if (!$attachmentItemGroupId) {
            $itemNameTypeId =  ItemnameTypesModel::where('m_itemname_type', self::ATTACHMENT_GROUP)
                ->orderBy('m_itemname_type_sort', 'asc') // Order by 並び順 ascending
                ->pluck('m_itemname_type')
                ->first();
            $groupId = $itemNameTypeId;
        }

        //商品コード(EcbeingNo.62)の商品IDをm_ami_pageテーブルから取得する。
        $sellId = AmiPageModel::where('page_cd', $data['sell_cd'])->pluck('m_ami_page_id')->first();

        // Get category_id from m_ami_attachment_items table
        $amiPageAttachments = AmiPageAttachmentItemsModel::where([
            'm_ami_page_id' =>  $sellId,
            'group_id' => $groupId
        ])
            ->select('m_ami_attachment_item_id', 'group_id', 'category_id')
            ->with(['amiAttachmentItem:m_ami_attachment_item_id,attachment_item_name,display_flg,invoice_flg']) // Get attachment_item_name from m_ami_attachment_items table
            ->get();

        foreach ($amiPageAttachments as $amiPageAttachment) {

            $categoryId =  $amiPageAttachment->category_id; //category_id

            //get m_itemname_type_code from m_itemname_types table
            $mItemnameTypeCode = ItemnameTypesModel::where('m_itemname_types_id', $categoryId)
                ->pluck('m_itemname_type_code')->first();

            // Prepare data for t_order_dtl_attachment_items
            $attachmentVol = $this->convertInteger($data['order_sell_vol']);

            if ($mItemnameTypeCode == self::HAND_BAG) {
                //手提げ袋枚数がゼロの場合は手提げ袋のみ付属品登録をスキップ
                if ($carrierBagVol == 0) {
                    continue;
                }
                $attachmentVol = $carrierBagVol;
            }

            $orderDetailAttachmentItems[] = [
                'attachment_item_id' => $amiPageAttachment->m_ami_attachment_item_id,
                'attachment_item_cd' => $mItemnameTypeCode,
                'attachment_item_name' => $amiPageAttachment->amiAttachmentItem->attachment_item_name,
                'attachment_vol' => $attachmentVol,
                'group_id' => $this->convertInteger($amiPageAttachment->group_id),
                'category_id' => $categoryId,
                'display_flg' => $amiPageAttachment->amiAttachmentItem->display_flg,
                'invoice_flg' => $amiPageAttachment->amiAttachmentItem->invoice_flg,
            ];
        }

        return $orderDetailAttachmentItems;
    }

    /**
     * Customer Data validations and if validation fail export txt file for error message
     * @param array (customer data list)
     * @param string (account code)
     * @param string (batch type)
     * @return int (bath id)
     * @return mixed
     */

    private function validateOrderData($dataList, $accountCode, $batchType, $bathID)
    {

        $request = new ImportEcbeingOrderDataRequest();
        $rules = $request->rules(); // Get the validation rules from request

        foreach ($dataList as $rowNumber => $data) {
            $validator = Validator::make($data, $rules);

            // 顧客データ登録前のバリデーションエラー場合
            if ($validator->fails()) {
                // Collect all error details
                $failed = $validator->failed();
                $errorColumn = array_keys($failed)[0]; // error column
                $excecuteDateTime = Carbon::now()->toDateTimeString(); //batch excecute date time
                $errorMessage = $validator->errors()->first($errorColumn); //error message
                $errorRow = $rowNumber + 1; //error row
                $errorBathID = $bathID . "_error"; //custom error text file name

                // Format the data as a string
                $errorDetails =
                    "処理実行年月日時分秒: {$excecuteDateTime}\n" .
                    "バッチ実行番号: {$bathID}\n" .
                    "エラー原因: {$errorMessage}\n" .
                    "エラー発生時のcsv/エクセル行数: {$errorRow}\n" .
                    "エラー発生時の該当行データ: {$errorColumn}\n\n";

                //get tsv file path
                $savePath = $this->getTextExportFilePath->execute($accountCode, $batchType, $errorBathID);
                //save tsv file on s3
                $fileuploaded = Storage::disk($this->s3)->put($savePath, $errorDetails);
                //ファイルがAWS S3にアップロードされていない場合
                if (!$fileuploaded) {
                    //AWS S3へのファイルのアップロードに失敗しました。
                    throw new Exception(__('messages.error.upload_s3_failed'), self::PRIVATE_THROW_ERR_CODE);
                }

                //取込ファイル内容のバリデーションチェックでエラーが発生しました。
                throw new Exception(__(
                    'messages.error.process_something_wrong2',
                    ['target' => '取込ファイル内容', 'process' => 'バリデーションチェック']
                ), self::PRIVATE_THROW_ERR_CODE);
            }
            return;
        }
    }

    /**
     * convert string to integer
     * @param string (data)
     * @return int
     */
    private function convertInteger($data)
    {
        return is_numeric($data) ? (int)$data : $data;
    }

    /**
     * convert date format to Y-m-d
     * @param string (data)
     * @return date
     */
    private function convertDateFormat($data)
    {
        return $data ? Carbon::parse($data)->format('Y-m-d') : null;
    }
}
