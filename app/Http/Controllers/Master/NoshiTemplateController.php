<?php

namespace App\Http\Controllers\Master;

use App\Enums\DeleteFlg;
use App\Enums\ItemNameType;
use App\Http\Controllers\Common\CommonController;
use App\Http\Requests\Master\Base\UpdateNoshiRequest;
use App\Modules\Master\Base\FindNoshiModuleInterface;
use App\Modules\Common\Base\SearchNoshiFormatInterface;
use App\Modules\Common\Base\SearchNoshiNamingPatternInterface;
use App\Modules\Master\Base\UpdateNoshiModuleInterface;

use App\Services\EsmSessionManager;

use Illuminate\Http\Request;
use Config;
use Validator;

class NoshiTemplateController
{

    protected $service; // プロパティを定義
    protected $noshiDetail; // プロパティを定義
    protected $noshiNamingPattern; // プロパティを定義
    protected $formatService; // プロパティを定義

    public function __construct()
    {
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
     * 編集
     */
    public function edit($id,Request $request, EsmSessionManager $esmSessionManager)
    {
        $formatService = app(SearchNoshiFormatInterface::class);
        $noshiService = app(FindNoshiModuleInterface::class);
        $noshiNamingPattern = app(SearchNoshiNamingPatternInterface::class);

        $req = $request->all();

        $viewExtendData = [
            'noshi_formats'=>$formatService->execute([
                'm_noshi_id'=>$id,
                'm_account_id'=>$esmSessionManager->getAccountId()
            ]),
            'naming_patterns'=>$noshiNamingPattern->execute([
                'm_account_id'=>$esmSessionManager->getAccountId()
            ])
        ];
        $editData = $noshiService->execute($id);

        $searchRow ??= $req;
        $compact = [
            'editData',
            'viewExtendData',
        ];
        return account_view( 'master.base.noshitemplates.edit',compact($compact));
    }
}
