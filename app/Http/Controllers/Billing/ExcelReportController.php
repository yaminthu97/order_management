<?php

namespace App\Http\Controllers\Billing;

use App\Http\Requests\Billing\Base\UpdateExcelReportRequest;
use App\Modules\Billing\Base\SearchExcelReportInterface;
use App\Modules\Common\Base\RegisterBatchExecuteInstructionInterface;
use App\Modules\Master\Base\SearchReportTemplatesInterface;
use App\Modules\Master\Base\Enums\BatchListEnumInterface;
use App\Modules\Master\Gfh1207\Enums\ReportTemplatesEnum;
use App\Modules\Master\Gfh1207\Enums\TemplateFileNameEnum;
use App\Modules\Order\Base\CreateBillingOutput;
use App\Modules\Order\Base\FindOrderDestinationInterface;
use App\Services\EsmSessionManager;

use Illuminate\Http\Request;
use Config;
use DB;
use Validator;

/**
 * 見積書・納品書・請求書出力画面コントローラ
 */
class ExcelReportController
{
    private $templateInfo;
    private $templateBillingIds;

    public function __construct() {
        // 対象となるテンプレートID と対象バッチ
        $batchList = app(BatchListEnumInterface::class);
        $this->templateInfo = [
            // 見積書1 (EstimateOut)
            TemplateFileNameEnum::EXPXLSX_ESTIMATE->id() => $batchList::EXPXLSX_ESTIMATE, 
            // 見積書2 (EstimateOut)
            TemplateFileNameEnum::EXPXLSX_ESTIMATE_2->id() => $batchList::EXPXLSX_ESTIMATE, 
            // EXCEL請求書1 (BillingOutExcel)
            TemplateFileNameEnum::EXPXLSX_BILLING_EXCEL->id() => $batchList::EXPXLSX_BILLING_EXCEL, 
            // EXCEL請求書2 (BillingOutExcel)
            TemplateFileNameEnum::EXPXLSX_BILLING_EXCEL_2->id() => $batchList::EXPXLSX_BILLING_EXCEL, 
            // EXCEL納品書1 (DeliveryNoteOut)
            TemplateFileNameEnum::EXPXLSX_BILLING_EXCEL_3->id() => $batchList::EXPXLSX_DELIVERY_NOTE, 
            // EXCEL納品書2 (DeliveryNoteOut)
            TemplateFileNameEnum::EXPXLSX_BILLING_EXCEL_4->id() => $batchList::EXPXLSX_DELIVERY_NOTE,  
        ];
        // 請求書のテンプレートIDリスト(請求書出力履歴の判定用)
        $this->templateBillingIds = [
            TemplateFileNameEnum::EXPXLSX_BILLING_EXCEL->id(),
            TemplateFileNameEnum::EXPXLSX_BILLING_EXCEL_2->id(),
        ];
    }

    /**
     * submitボタン内容の取得
     */
    protected function getSubmitName($req)
    {
        $submitName = '';
        if(!empty($req['submit'])) {
            $submitName = $req['submit'];
        }
        return $submitName;
    }

    /**
     * 画面表示
     */
    public function list(
        Request $request,
    ) {
		$viewExtendData['page_list_count'] = Config::get('Common.const.disp_limits');
        $viewMessage = [];
        $paginator = [];
        $searchRow = [];
        $compact = [
            'viewMessage',
            'paginator',
            'searchRow'
        ];

        // 通常のview
        return account_view('billing.excel-report.base.list', compact( $compact ));
    }

    /**
     * 検索・出力
     */
    public function search(
        Request $request,
        EsmSessionManager $esmSessionManager,
        RegisterBatchExecuteInstructionInterface $registerBatchExecute,
        SearchExcelReportInterface $searchExcelReport,
        SearchReportTemplatesInterface $searchReportTemplates,
        FindOrderDestinationInterface $findOrderDestination,
        CreateBillingOutput $createBillingOutput,
    ) {
        session()->forget('messages.info');
        session()->forget('messages.error');
        session()->forget('errors');

        // postパラメータの取得
        $req = $request->all();
		$submitName = $this->getSubmitName($req);
        $searchRow ??= $req;

        // 基本情報
		$viewExtendData['page_list_count'] = Config::get('Common.const.disp_limits');
        $viewMessage = [];
        $paginator = [];
        $errors = [];
        $validated = null;

        if( $submitName == 'search' ){
            // 表示ページを1ページ目に戻す
            $searchRow['hidden_next_page_no'] = 1;
            // ラジオボタンの選択状態を初期化
            unset( $searchRow['t_order_destination_id'] );
        }

        // 一覧取得
        $searchRow['m_account_id'] = $esmSessionManager->getAccountId();
        $options = [
            'should_paginate' => true,
            'limit' => $searchRow['page_list_count'] ?? 10,
            'page' => $searchRow['hidden_next_page_no'] ?? 1,
        ];
        $paginator = $searchExcelReport->execute( $searchRow, $options );

        // 帳票テンプレートリスト取得
        $templateList = $searchReportTemplates->execute([
            'm_account_id' => $esmSessionManager->getAccountId(),
            'm_report_template_id' => array_keys( $this->templateInfo ),
        ]);

        $compact = [
            'viewExtendData',
            'viewMessage',
            'paginator',
            'searchRow',
            'templateList',
        ];

        // CSV出力
        if( $submitName == 'output' ){
            // 入力内容のバリデーション
            $form = app(UpdateExcelReportRequest::class);
            $validated = Validator::make($searchRow, $form->rules(), $form->messages(), $form->attributes());
            if ( $validated->fails() ) {
                return account_view('billing.excel-report.base.list', compact( $compact ))->withErrors( $validated );
            }

            // 受注基本IDを取得するために受注配送先情報を取得
            $orderDest = $findOrderDestination->execute( $searchRow['t_order_destination_id'] );
            $batchEnum = $this->templateInfo[ $searchRow['m_report_template_id'] ];
            $params = [
                'execute_batch_type' => $batchEnum->value,
                'execute_conditions' => [
                    'search_info' => [
                        't_order_hdr_id' => $orderDest->t_order_hdr_id,
                        't_order_destination_id' => $orderDest->t_order_destination_id,
                        'm_report_template_id' => $searchRow['m_report_template_id'],
                        'output_unit' => $searchRow['output_unit'],
                    ],
                ],
            ];

            $result = DB::transaction(function () use (
                $esmSessionManager,
                $registerBatchExecute, 
                $createBillingOutput,
                $orderDest,
                $batchEnum,
                $searchRow,
                $params,
            ) {

                // 請求書の場合、請求書出力履歴の登録
                if( in_array( $searchRow['m_report_template_id'], $this->templateBillingIds ) ){
                    $billingOutputs = $createBillingOutput->execute(
                        $orderDest->orderHdr?->t_billing_hdr_id, 
                        $searchRow['m_report_template_id'], 
                        null,
                        $esmSessionManager->getAccountId(),
                        $esmSessionManager->getOperatorId()
                    );
                }
                session()->flash('messages.info', [
                    'message' => __('messages.info.create_completed', [ 'data' => $batchEnum->label() . 'バッチ' ])
                ]);

                // バッチ実行指示の登録
                $registerBatchExecute->execute($params);
            });
        }

        // 通常のview
        return account_view('billing.excel-report.base.list', compact( $compact ));
    }
}