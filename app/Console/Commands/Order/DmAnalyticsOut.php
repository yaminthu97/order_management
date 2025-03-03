<?php

namespace App\Console\Commands\Order;

use Exception;
use Carbon\Carbon;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

use PhpOffice\PhpSpreadsheet\Chart\Chart;
use PhpOffice\PhpSpreadsheet\Chart\Title;
use PhpOffice\PhpSpreadsheet\Chart\Layout;
use PhpOffice\PhpSpreadsheet\Chart\Legend;
use PhpOffice\PhpSpreadsheet\Chart\PlotArea;
use PhpOffice\PhpSpreadsheet\Chart\DataSeries;
use PhpOffice\PhpSpreadsheet\Chart\DataSeriesValues;

use App\Enums\ItemNameType;
use App\Enums\BatchExecuteStatusEnum;

use App\Models\Master\Base\ItemnameTypeModel;

use App\Models\Cc\Gfh1207\CustModel;
use App\Models\Order\Gfh1207\OrderHdrModel;
use App\Models\Order\Gfh1207\OrderDestinationModel;
use App\Models\Order\Gfh1207\OrderDetailModel;

use App\Modules\Master\Gfh1207\Enums\BatchListEnum;
use App\Modules\Master\Gfh1207\Enums\TemplateFileNameEnum;

use App\Modules\Common\Base\EndBatchExecuteInstructionInterface;
use App\Modules\Common\Base\StartBatchExecuteInstructionInterface;

use App\Modules\Order\Gfh1207\GetTemplateFileName;
use App\Modules\Order\Gfh1207\GetTemplateFilePath;
use App\Modules\Order\Gfh1207\GetExcelExportFilePath;

use App\Services\ExcelReportManager;
use App\Services\TenantDatabaseManager;

ini_set('memory_limit', '4096M');


class DmAnalyticsOut extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:DmAnalyticsOut {t_execute_batch_instruction_id : バッチ実行指示ID} {json? : JSON化した引数}';

    private const PRIVATE_THROW_ERR_CODE = -1;

    private const ROW_INDEX = 2;
    private const CUSTOMER_ID_POS = 'B'; //顧客ID

    private const TEMPLATE_SHEET_NAME = 'テンプレート';

    private const CELL_TAG_MEDIA_NUM = '媒体名_件数';
    private const CELL_TAG_MEDIA_TYPE_NUM = '媒体別受注件数';
    private const CELL_TAG_MEDIA_PRICE = '媒体名_金額';
    private const CELL_TAG_MEDIA_TYPE_PRICE = '媒体別受注金額';
    private const CELL_TAG_DATE_NUM = '受注日_件数';
    private const CELL_TAG_DATE_GROUP_NUM = '日別受注件数';
    private const CELL_TAG_DATE_PRICE = '受注日_金額';
    private const CELL_TAG_DATE_GROUP_PRICE = '日別受注金額';
    private const CELL_TAG_ITEM_TOP_CODE = '注文商品TOP5_商品コード';
    private const CELL_TAG_ITEM_TOP_NUM = '注文商品TOP5_数量';

    private const ITEM_TOP_NUM = 5;

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'DM集計をする';
    // テンプレートファイルパス
    protected $templateFilePath;
    // 開始時処理
    protected $startBatchExecute;
    // 終了時処理
    protected $endBatchExecute;

    /**
     * Create a new command instance.
     */
    public function __construct(
        StartBatchExecuteInstructionInterface $startBatchExecute,
        EndBatchExecuteInstructionInterface $endBatchExecute,
        GetTemplateFileName $getTemplateFileName,
        GetTemplateFilePath $getTemplateFilePath,
        GetExcelExportFilePath $getExcelExportFilePath,
    ) {
        $this->startBatchExecute = $startBatchExecute;
        $this->endBatchExecute = $endBatchExecute;
        $this->getReportOutputTemplateFileName = $getTemplateFileName;
        $this->getTemplateFilePath = $getTemplateFilePath;
        $this->getExcelExportFilePath = $getExcelExportFilePath;
        parent::__construct();
    }

    private function setTenantConnection($accountCode)
    {
        $TenantConnectionValue = $accountCode . '_db';

        if (app()->environment('testing')) {
            // テスト環境の場合
            $TenantConnectionValue = $accountCode . '_db_testing';
        }

        TenantDatabaseManager::setTenantConnection($TenantConnectionValue);
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $executeBatchInstructionId = $this->argument('t_execute_batch_instruction_id');
        $json = $this->argument('json');

        try {
            /**
             * [共通処理] 開始処理
             * バッチ実行指示テーブル（t_execute_batch_instruction ）に新規作成と開始処理
             * - バッチ開始時刻
             */
            $batchExecute = $this->startBatchExecute->execute($executeBatchInstructionId);

            if (!is_null($json)) {
                $jsonData = json_decode($json, true);

                if (json_last_error() !== JSON_ERROR_NONE) {
                    throw new Exception(json_last_error_msg());
                }
            }
        } catch (Exception $e) {
            Log::error($e);

            $this->endBatchExecute->execute($batchExecute, [
                'execute_result' => $e->getMessage(),
                'execute_status' => BatchExecuteStatusEnum::FAILURE->value
            ]);

            return;
        }

        if (BatchListEnum::EXPXLSX_DM_ANALYTICS->value !== $batchExecute->execute_batch_type) {
            $errorMessage = __('messages.error.invalid_parameter');
            Log::error($errorMessage);

            $this->endBatchExecute->execute($batchExecute, [
                'execute_result' => $errorMessage,
                'execute_status' => BatchExecuteStatusEnum::FAILURE->value
            ]);

            return;
        }

        $accountCode = $batchExecute->account_cd;
        $this->setTenantConnection($accountCode);

        $executeCondition = json_decode($batchExecute->execute_conditions, true);

        if (!isset($executeCondition['order_date_from']) || !isset($executeCondition['order_date_to'])) {
            $errorMessage = __('messages.error.invalid_parameter');
            Log::error($errorMessage);

            $this->endBatchExecute->execute($batchExecute, [
                'execute_result' => $errorMessage,
                'execute_status' => BatchExecuteStatusEnum::FAILURE->value
            ]);

            return;
        }

        $accountId = $batchExecute->m_account_id;

        try {
            $this->templateFilePath = $this->getTemplateFileName(
                $executeBatchInstructionId,
                $accountCode,
                $executeBatchInstructionId . '.xlsx'
            );

            $erm = new ExcelReportManager($this->templateFilePath);
            $spreadSheet = $erm->getActiveSheet();
            unset($erm);

            $customerIdList = $this->getSpreadSheetValues($spreadSheet);
        } catch (Exception $e) {
            Log::error($e);

            $errorMessage = $e->getMessage();

            $this->endBatchExecute->execute($batchExecute, [
                'execute_result' => $errorMessage,
                'execute_status' => BatchExecuteStatusEnum::FAILURE->value
            ]);

            return;
        }

        $executeResultStr = 'DM集計終了。';

        DB::beginTransaction();

        try {
            $tbl = $this->getOrderTableData($customerIdList, $executeCondition);
            $receiptItemnameTbl = $this->getReceiptItemnameTbl($accountId, $tbl['orderTypeList']);
            $data = $this->toContinuousValues($tbl, $receiptItemnameTbl);

            $reportName = TemplateFileNameEnum::EXPXLSX_DM_ANALYTICS->value;
            $reportOutputTemplateFileName = $this->getReportOutputTemplateFileName->execute($reportName, $accountId);

            if (is_null($reportOutputTemplateFileName)) {
                throw new \Exception(__('messages.error.data_not_found', ['data' => $reportName . 'のテンプレートデータ', 'id' => '']), self::PRIVATE_THROW_ERR_CODE);
            }

            $reportTemplateFilePath = $this->getTemplateFilePath->execute($accountCode, $reportOutputTemplateFileName);

            // to check exist template file path or not condition
            if (empty($reportTemplateFilePath)) {
                throw new \Exception(__('messages.error.file_not_found', ['file' => 'レポート出力テンプレート', 'path' => $reportTemplateFilePath]), self::PRIVATE_THROW_ERR_CODE);
            }

            $erm = new ExcelReportManager($reportTemplateFilePath);
            $erm->setValues($data['orderTypeTotal'], $data['continuousValues']);

            $orderTypeCnt = count($tbl['orderHdrByOrderType']);
            $dateCnt = count($tbl['orderHdrByDate']);

            $bracketsCellList = $erm->getBracketsCell();
            $bracketsCellTbl = [];

            foreach ($bracketsCellList as $row) {
                $bracketsCellTbl[$row['name']] = $row;
            }

            $continuousEmbedCellList = $erm->getContinuousEmbedCells();
            $continuousEmbedCellTbl = [];

            foreach ($continuousEmbedCellList as $row) {
                $continuousEmbedCellTbl[$row['name']] = $row;
            }

            $offset = $continuousEmbedCellTbl[self::CELL_TAG_MEDIA_NUM]['row']; //どの行数を取得しても同じ。

            if (isset($bracketsCellTbl['媒体件数開始']) && isset($bracketsCellTbl['媒体件数終了'])) {
                $mediaNumCell = $continuousEmbedCellTbl[self::CELL_TAG_MEDIA_NUM];
                $mediaTypeNumCell = $continuousEmbedCellTbl[self::CELL_TAG_MEDIA_TYPE_NUM];
                $leftTop = $bracketsCellTbl['媒体件数開始'];
                $rightBottom = $bracketsCellTbl['媒体件数終了'];

                $this->addChart($erm, DataSeries::TYPE_PIECHART, self::CELL_TAG_MEDIA_TYPE_NUM, $mediaNumCell['column'], $mediaTypeNumCell['column'], $offset, $orderTypeCnt,
                    $leftTop['column'] . $leftTop['row'], $rightBottom['column'] . $rightBottom['row']);
            }

            if (isset($bracketsCellTbl['媒体金額開始']) && isset($bracketsCellTbl['媒体金額終了'])) {
                $mediaPriceCell = $continuousEmbedCellTbl[self::CELL_TAG_MEDIA_PRICE];
                $mediaTypePriceCell = $continuousEmbedCellTbl[self::CELL_TAG_MEDIA_TYPE_PRICE];
                $leftTop = $bracketsCellTbl['媒体金額開始'];
                $rightBottom = $bracketsCellTbl['媒体金額終了'];

                $this->addChart($erm, DataSeries::TYPE_PIECHART, self::CELL_TAG_MEDIA_TYPE_PRICE, $mediaPriceCell['column'], $mediaTypePriceCell['column'], $offset, $orderTypeCnt,
                    $leftTop['column'] . $leftTop['row'], $rightBottom['column'] . $rightBottom['row']);
            }

            if (isset($bracketsCellTbl['日付件数開始']) && isset($bracketsCellTbl['日付件数終了'])) {
                $mediaDateNumCell = $continuousEmbedCellTbl[self::CELL_TAG_DATE_NUM];
                $mediaDateGroupNumCell = $continuousEmbedCellTbl[self::CELL_TAG_DATE_GROUP_NUM];
                $leftTop = $bracketsCellTbl['日付件数開始'];
                $rightBottom = $bracketsCellTbl['日付件数終了'];

                $this->addChart($erm, DataSeries::TYPE_BARCHART, '受注件数（日別）', $mediaDateNumCell['column'], $mediaDateGroupNumCell['column'], $offset, $dateCnt,
                    $leftTop['column'] . $leftTop['row'], $rightBottom['column'] . $rightBottom['row']);
            }

            if (isset($bracketsCellTbl['日付金額開始']) && isset($bracketsCellTbl['日付金額終了'])) {
                $mediaDatePriceCell = $continuousEmbedCellTbl[self::CELL_TAG_DATE_PRICE];
                $mediaDateGroupPriceCell = $continuousEmbedCellTbl[self::CELL_TAG_DATE_GROUP_PRICE];
                $leftTop = $bracketsCellTbl['日付金額開始'];
                $rightBottom = $bracketsCellTbl['日付金額終了'];

                $this->addChart($erm, DataSeries::TYPE_BARCHART, '受注金額（日別）', $mediaDatePriceCell['column'], $mediaDateGroupPriceCell['column'], $offset, $dateCnt,
                    $leftTop['column'] . $leftTop['row'], $rightBottom['column'] . $rightBottom['row']);
            }

            $savePath = $this->getExcelExportFilePath->execute($accountCode, $batchExecute->execute_batch_type, $executeBatchInstructionId);  // to get base file path

            if ($erm->save($savePath) !== true) {
                throw new \Exception(__('messages.error.upload_s3_file_failed', ['file' => $savePath]), self::PRIVATE_THROW_ERR_CODE);
            }

            /**
             * [共通処理] 終了処理
             * バッチ実行指示テーブルの該当バッチに以下の情報を書き込む
             * - バッチ終了時刻
             * - (実行状態)
             * - (実行結果)
             * - (格納ファイルパス)
             */
            $batchExecute = $this->endBatchExecute->execute($batchExecute, [
                'execute_result' => $executeResultStr,
                'execute_status' => BatchExecuteStatusEnum::SUCCESS->value,
            ]);

            DB::commit();
        } catch (Exception $e) {
            DB::rollBack();

            Log::error($e);
            // default fail message
            $errorMessage = BatchExecuteStatusEnum::FAILURE->label();

            // If there is an Exception error message, write it to the log
            if ($e->getCode() == self::PRIVATE_THROW_ERR_CODE) {
                $errorMessage = $e->getMessage();
            }

            /**
             * [共通処理] 終了処理
             * バッチ実行指示テーブルの該当バッチに以下の情報を書き込む
             * - バッチ終了時刻
             * - (実行状態)
             * - (実行結果)
             */
            $batchExecute = $this->endBatchExecute->execute($batchExecute, [
                'execute_result' => $errorMessage,
                'execute_status' => BatchExecuteStatusEnum::FAILURE->value
            ]);
        }
    }

    //Excel のグラフを追加。
    private function addChart($erm, $plotType, $title, $leftColumn, $rightColumn, $offset, $num, $topLeft, $bottomRight)
    {
        $sheetName = self::TEMPLATE_SHEET_NAME;

        if (0 < $num) {
            $plotOrder = $num - 1;
        }

        $endIndex = $offset + $plotOrder;

        $xAxisTickValues = [
            new DataSeriesValues(DataSeriesValues::DATASERIES_TYPE_STRING, $sheetName . '!$' . $leftColumn . '$' . $offset . ':$' . $leftColumn . '$' . $endIndex, null, $num),
        ];

        $dataSeriesValues = [
            new DataSeriesValues(DataSeriesValues::DATASERIES_TYPE_NUMBER, $sheetName . '!$' . $rightColumn . '$' . $offset . ':$' . $rightColumn . '$' . $endIndex, null, $num),
        ];

        $plotGrouping = DataSeries::GROUPING_STANDARD;
        $layout = null;
        $legend = null;

        if ($plotType === DataSeries::TYPE_PIECHART) {
            $plotGrouping = null;

            $layout = new Layout();
            $layout->setShowPercent(true);

            $legend = new Legend(Legend::POSITION_RIGHT, null, false);
        }

        $series = new DataSeries(
            $plotType, //plotType
            $plotGrouping, //plotGrouping
            range(0, count($dataSeriesValues) - 1), //plotOrder
            [], //plotLabel
            $xAxisTickValues, //plotCategory
            $dataSeriesValues //plotValues
        );
    
        if ($plotType === DataSeries::TYPE_BARCHART) {
            $series->setPlotDirection(DataSeries::DIRECTION_COL);
        }

        $plotArea = new PlotArea($layout, [$series]);

        $chart = new Chart(
            $title, //name
            new Title($title), //title
            $legend, //legend
            $plotArea, //plotArea
            true, //plotVisibleOnly
            DataSeries::EMPTY_AS_GAP, //displayBlanksAs
            null, //xAxisLabel
            null //yAxisLabel
        );

        $chart->setTopLeftPosition($topLeft);
        $chart->setBottomRightPosition($bottomRight);

        $erm->getActiveSheet()->addChart($chart);
    }

    //itemname_types を条件で絞り込み、取得。
    private function getItemnameTypeList($accountId, $itemnameType, $orderTypeList)
    {
        $ret = ItemnameTypeModel::where([
            'm_account_id' => $accountId,
            'm_itemname_type' => $itemnameType,
        ])
        ->whereIn('m_itemname_types_id', $orderTypeList)
        ->orderBy('m_itemname_types_id', 'ASC')->get();

        return $ret;
    }

    //Excel 出力のラベル名一覧。
    private function getContinuousHead()
    {
        return [
            '受注方法',
            '受注ID',
            '顧客ID',
            '顧客名',
            '受注金額',
            self::CELL_TAG_MEDIA_NUM,
            self::CELL_TAG_MEDIA_TYPE_NUM,
            self::CELL_TAG_MEDIA_PRICE,
            self::CELL_TAG_MEDIA_TYPE_PRICE,
            self::CELL_TAG_DATE_NUM,
            self::CELL_TAG_DATE_GROUP_NUM,
            self::CELL_TAG_DATE_PRICE,
            self::CELL_TAG_DATE_GROUP_PRICE,
            self::CELL_TAG_ITEM_TOP_CODE,
            self::CELL_TAG_ITEM_TOP_NUM,
        ];
    }

    //Excel 出力のラベル名の対になる data を取得。
    private function getCustomerData($orderHdr)
    {
        $name = $orderHdr->order_corporate_name;

        if (is_null($name)) {
            $name = $orderHdr->order_name;
        }

        return [
            $orderHdr->order_type, //受注方法
            $orderHdr->t_order_hdr_id, //受注ID
            $orderHdr->m_cust_id, //顧客ID
            $name, //顧客名
            $orderHdr->sell_total_price, //受注金額
        ];
    }

    //m_itemname_types_id 別で受注方法の絞り込み、名前を取得。
    private function getReceiptItemnameTbl($accountId, $orderTypeList)
    {
        $receiptItemnameTypeList = $this->getItemnameTypeList($accountId, ItemNameType::ReceiptType, $orderTypeList);
        $receiptItemnameTbl = [];

        foreach ($receiptItemnameTypeList as $itemnameType) {
            $id = $itemnameType->m_itemname_types_id;
            $receiptItemnameTbl[$id] = $itemnameType->m_itemname_type_name;
        }

        return $receiptItemnameTbl;
    }

    //Excel の出力形式に変換。
    private function toContinuousValues($tbl, $receiptItemnameTbl)
    {
        $ret['orderTypeTotal'] = [
            'items' => [
                '媒体別受注件数_合計',
                '媒体別受注金額_合計',
            ],
            'data' => [
                $tbl['orderHdrTotal']['count'],
                $tbl['orderHdrTotal']['total_price'],
            ],
        ];

        $head = $this->getContinuousHead();
        $tmp['items'] = $head;

        $cnt = [];
        $cnt['orderHdrId'] = count($tbl['orderHdrId']);
        $cnt['orderType'] = count($tbl['orderHdrByOrderType']);
        $cnt['date'] = count($tbl['orderHdrByDate']);
        $cnt['sellVol'] = count($tbl['sellVol']);
        rsort($cnt); //最大行順に並べる。

        $headCnt = count($tmp['items']);
        $tmp['data'] = [];

        for ($i = 0; $i < $cnt[0]; ++$i) {
            for ($j = 0; $j < $headCnt; ++$j) {
                $tmp['data'][$i][$j] = ''; //表の総行で初期化
            }
        }

        $i = 0;

        foreach ($tbl['orderHdrId'] as $id) {
            $row = $this->getCustomerData($tbl['orderHdr'][$id]);

            foreach ($row as $j => $val) {
                $tmp['data'][$i][$j] = $val;
            }

            ++$i;
        }

        $i = 0;

        foreach ($tbl['orderHdrByOrderType'] as $orderType => $val) {
            $orderTypeName = $receiptItemnameTbl[$orderType];

            $tmp['data'][$i][array_keys($head, self::CELL_TAG_MEDIA_NUM)[0]] = $orderTypeName;
            $tmp['data'][$i][array_keys($head, self::CELL_TAG_MEDIA_TYPE_NUM)[0]] = $val['count'];
            $tmp['data'][$i][array_keys($head, self::CELL_TAG_MEDIA_PRICE)[0]] = $orderTypeName;
            $tmp['data'][$i][array_keys($head, self::CELL_TAG_MEDIA_TYPE_PRICE)[0]] = $val['total_price'];

            ++$i;
        }

        $i = 0;

        foreach ($tbl['orderHdrByDate'] as $date => $val) {
            $tmp['data'][$i][array_keys($head, self::CELL_TAG_DATE_NUM)[0]] = $date;
            $tmp['data'][$i][array_keys($head, self::CELL_TAG_DATE_GROUP_NUM)[0]] = $val['count'];
            $tmp['data'][$i][array_keys($head, self::CELL_TAG_DATE_PRICE)[0]] = $date;
            $tmp['data'][$i][array_keys($head, self::CELL_TAG_DATE_GROUP_PRICE)[0]] = $val['total_price'];

            ++$i;
        }

        $i = 0;

        foreach ($tbl['sellVol'] as $sellCd => $val) {
            $tmp['data'][$i][array_keys($head, self::CELL_TAG_ITEM_TOP_CODE)[0]] = $sellCd;
            $tmp['data'][$i][array_keys($head, self::CELL_TAG_ITEM_TOP_NUM)[0]] = $val;

            ++$i;
        }

        $ret['continuousValues'] = $tmp;

        return $ret;
    }

    //database の table から仕様の条件の data を取得。
    private function getOrderTableData($customerIdList, $executeCondition)
    {
        $orderDateFrom = $executeCondition['order_date_from'];
        $orderDateTo = $executeCondition['order_date_to'];
        $orderType = $executeCondition['order_type'];

        $oHdr = OrderHdrModel::whereIn('m_cust_id', $customerIdList)
            ->where('order_datetime', '>=', $orderDateFrom . ' 00:00:00')->where('order_datetime', '<=', $orderDateTo . ' 23:59:59');

        if (!is_null($orderType)) {
            $oHdr->where('order_type', $orderType);
        }

        $orderHdrList = $oHdr->get();

        $orderHdrIdList = [];
        $orderTbl['orderHdr'] = [];
        $orderTbl['orderTypeList'] = [];

        foreach ($orderHdrList as $orderHdr) {
            $id = $orderHdr->t_order_hdr_id;
            $orderHdrIdList[] = $id;
            $orderTbl['orderHdr'][$id] = $orderHdr;
            $type = $orderHdr->order_type;

            if (is_null($type)) {
                continue;
            }

            $orderTbl['orderTypeList'][$type] = $type;
        }

        $orderDestinationList = OrderDestinationModel::whereIn('t_order_hdr_id', $orderHdrIdList)->get();
        $orderDestinationIdList = [];

        foreach ($orderDestinationList as $orderDestination) {
            $orderDestinationIdList[] = $orderDestination->t_order_destination_id;
        }

        $dtlList = OrderDetailModel::whereIn('t_order_destination_id', $orderDestinationIdList)->get();
        $sellCdList = [];

        foreach (range(1, 10) as $i) {
            $key = 'sell_cd_' . $i;

            if (isset($executeCondition[$key])) {
                $sellCdCsv = str_replace(' ', '', $executeCondition[$key]);
                $sellCdList[] = explode(',', $sellCdCsv);
            }
        }

        $sellCdTbl = [];

        foreach ($dtlList as $dtl) {
            $sellCd = $dtl->sell_cd;

            if (is_null($sellCd)) {
                continue;
            }

            $sellCdTbl[$dtl->t_order_hdr_id][] = $sellCd;
        }

        $orderSellVolTbl = [];
        $dtlOrderHdrIdList = [];

        foreach ($dtlList as $dtl) {
            $sellCd = $dtl->sell_cd;
            $dtlSellCdList = $sellCdTbl[$dtl->t_order_hdr_id];

            if (is_null($sellCd) || !$this->existSellCd($sellCdList, $dtlSellCdList)) {
                continue;
            }

            $orderSellVolTbl[$sellCd] = 0;
            $orderSellVolTbl[$sellCd] += $dtl->order_sell_vol;
            $dtlOrderHdrIdList[$dtl->t_order_hdr_id] = $dtl->t_order_hdr_id;
        }

        arsort($orderSellVolTbl);
        $orderTbl['sellVol'] = array_slice($orderSellVolTbl, 0, self::ITEM_TOP_NUM, true);
        $orderTbl['orderHdrId'] = $dtlOrderHdrIdList;

        $orderTbl['orderHdrTotal']['count'] = 0;
        $orderTbl['orderHdrTotal']['total_price'] = 0;
        $orderTbl['orderHdrByOrderType'] = [];
        $orderTbl['orderHdrByDate'] = [];

        foreach ($dtlOrderHdrIdList as $id) {
            $orderHdr = $orderTbl['orderHdr'][$id];
            $price = $orderHdr->order_total_price;

            ++$orderTbl['orderHdrTotal']['count'];
            $orderTbl['orderHdrTotal']['total_price'] += $price;

            $type = $orderHdr->order_type;

            if (!is_null($type)) {
                if (!isset($orderTbl['orderHdrByOrderType'][$type])) {
                    $orderTbl['orderHdrByOrderType'][$type]['count'] = 0;
                    $orderTbl['orderHdrByOrderType'][$type]['total_price'] = 0;
                }

                ++$orderTbl['orderHdrByOrderType'][$type]['count'];
                $orderTbl['orderHdrByOrderType'][$type]['total_price'] += $price;
            }

            $date = $this->datetimeToDate($orderHdr->order_datetime);

            if (empty($date)) {
                continue;
            }

            if (!isset($orderTbl['orderHdrByDate'][$date])) {
                $orderTbl['orderHdrByDate'][$date]['count'] = 0;
                $orderTbl['orderHdrByDate'][$date]['total_price'] = 0;
            }

            ++$orderTbl['orderHdrByDate'][$date]['count'];
            $orderTbl['orderHdrByDate'][$date]['total_price'] += $price;
        }

        return $orderTbl;
    }

    //受注毎に sell_cd_X の全て条件に当てはまっている。
    private function existSellCd($sellCdList, $dtlSellCdList)
    {
        $inxTbl = [];

        foreach ($dtlSellCdList as $sellCd) {
            foreach ($sellCdList as $inx => $cdList) {
                if (!isset($inxTbl[$inx]) && in_array($sellCd, $cdList, true)) {
                    $inxTbl[$inx] = true;
                }
            }
        }

        if (count($inxTbl) < count($sellCdList)) {
            return false;
        }

        return true;
    }

    //datetime の形式文字列から日付形式の文字列に変換し返却。
    private function datetimeToDate($datetime)
    {
        if (is_null($datetime)) {
            return false;
        }

        $ary = explode(' ', $datetime);

        return $ary[0];
    }

    //数字で 0 以上である事を保証。
    private function isIdFormat($id)
    {
        if (is_numeric($id) && 0 < $id) {
            return true;
        }

        return false;
    }

    /**
     * Data get
     *
     * @return array ( excel table data )
     */
    private function getSpreadSheetValues($spreadSheet)
    {
        $index = self::ROW_INDEX;
        $customerIdList = [];

        for (;;) {
            $customerId = $spreadSheet->getCell(self::CUSTOMER_ID_POS . $index)->getFormattedValue();

            if ($customerId == '') {
                break;
            }

            if (!$this->isIdFormat($customerId)) {
                continue;
            }

            $customerIdList[] = $customerId;

            ++$index;
        }

        return $customerIdList;
    }

    //import template file の名前を取得。
    private function getTemplateFileName(int $executeBatchInstructionId, string $accountCd, string $fileName)
    {
        return $accountCd . '/excel/import/' . $executeBatchInstructionId . '/' . $fileName;
    }
}
