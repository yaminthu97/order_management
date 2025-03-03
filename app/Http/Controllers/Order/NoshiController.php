<?php

namespace App\Http\Controllers\Order;

use App\Enums\ItemNameType;
use App\Enums\ProgressTypeEnum;
use App\Modules\Master\Base\GetItemnameTypeInterface;
use App\Modules\Master\Base\GetNoshiInterface;
use App\Modules\Master\Base\GetNoshiFormatInterface;
use App\Modules\Master\Base\GetNoshiNamingPatternInterface;
use App\Modules\Master\Base\GetEcsInterface;
use App\Modules\Order\Base\SearchCreateNoshiInterface;
use App\Services\EsmSessionManager;

use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Config;

class NoshiController {
    private $getItemnameType;
    private $getNoshi;
    private $getNoshiFormat;
    private $getNoshiNamingPattern;
    private $getEcs;

    public function __construct(
        GetItemnameTypeInterface $getItemnameType,
        GetNoshiInterface $getNoshi,
        GetNoshiFormatInterface $getNoshiFormat,
        GetNoshiNamingPatternInterface $getNoshiNamingPattern,
        GetEcsInterface $getEcs
    ){
        $this->getItemnameType = $getItemnameType;
        $this->getNoshi = $getNoshi;
        $this->getNoshiFormat = $getNoshiFormat;
        $this->getNoshiNamingPattern = $getNoshiNamingPattern;
        $this->getEcs = $getEcs;
    }
    private function getViewExtendData(){
        $viewExtendData = [
            'm_cust_runk_name'=>$this->getItemnameType->execute(ItemNameType::CustomerRank->value),
            'noshi_type'=>Arr::pluck($this->getNoshi->execute(),'noshi_type','m_noshi_id'),
            'attachment_item_group_name'=>array_flip($this->getItemnameType->execute(ItemNameType::AttachmentGroup->value)),
            'order_type'=>$this->getItemnameType->execute(ItemNameType::ReceiptType->value),
            'noshi_format'=>Arr::pluck($this->getNoshiFormat->execute(),'noshi_format_name','m_noshi_format_id'),
            'noshi_naming_pattern'=>Arr::pluck($this->getNoshiNamingPattern->execute(),'pattern_name','m_noshi_naming_pattern_id'),
            'page_list_count' => Config::get('Common.const.disp_limits'),
            'ecs'=>$this->getEcs->execute()
        ];
        return $viewExtendData;
    }
    public function list(
        Request $request,
    ) {
        $viewExtendData = $this->getViewExtendData();
        $searchRow = [
            'progress_type'=>ProgressTypeEnum::PendingConfirmation->value,
            'output_counter'=>'0',
        ];
        $compact = [
            'viewExtendData',
            'searchRow',
            
        ];

        // 通常のview
        return account_view('order.base.noshi.list', compact($compact));
    }

    // search
    public function search(
        Request $request,
        EsmSessionManager $esmSessionManager,
        SearchCreateNoshiInterface $searchCreateNoshi
    ) {
        $viewExtendData = $this->getViewExtendData();
        $searchRow = $request->all();
        $option = [
            'should_paginate' => true,
            'limit' => $searchRow['page_list_count'] ?? 10,
            'page' => $searchRow['hidden_next_page_no'] ?? 1,
        ];
        $paginator = $searchCreateNoshi->execute($searchRow,$option);
        $compact = [
            'viewExtendData',
            'searchRow',
            'paginator',
            
        ];
        return account_view('order.base.noshi.list', compact($compact));
    }
}