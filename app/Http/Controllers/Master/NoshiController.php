<?php

namespace App\Http\Controllers\Master;

use App\Enums\DeleteFlg;
use App\Enums\ItemNameType;
use App\Exceptions\DataNotFoundException;
use App\Http\Controllers\Common\CommonController;
use App\Http\Requests\Master\Base\UpdateNoshiRequest;
use App\Modules\Master\Base\FindNoshiModuleInterface;
use App\Modules\Master\Base\SearchItemNameTypesInterface;
use App\Modules\Master\Base\SearchNoshiModuleInterface;
use App\Modules\Master\Base\UpdateNoshiModuleInterface;

use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Config;
use Validator;

class NoshiController
{

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
     * 一覧
     */
    public function list(Request $request)
    {
        $service = app(SearchNoshiModuleInterface::class);

        $req = $request->all();
        $submit = $this->getSubmitName($req);
        if($request->isMethod('post')) {
            if($submit == 'search'){
                // 検索ボタン押下時はページ数、表示件数を初期化する
                $req['page_list_count'] = Config::get('Common.const.disp_limit_default');
                $req['hidden_next_page_no'] = 1;
            }
            $paginator = $service->execute($req);
        } else {
            $paginator = $service->execute($req);
            $req['page_list_count'] = Config::get('Common.const.disp_limit_default');
            $req['hidden_next_page_no'] = 1;
        }
        $viewExtendData['page_list_count'] = Config::get('Common.const.disp_limits');        
        $searchRow ??= $req;
        $paginator ??= null;

        $compact = [
            'searchRow',
            'paginator',
            'viewExtendData',
        ];
        return account_view( 'master.base.noshi.list',compact($compact));
    }

    /**
     * 新規登録
     */
    public function new(Request $request)
    {
        $service = app(SearchItemNameTypesInterface::class);

        // 初期値
        $editData = [
            'm_noshi_id' => null,
            'noshi_type' => null,
            'delete_flg' => DeleteFlg::Use->value,
            'attachment_item_group_id' => '',
            'omotegaki' => null,
            'noshi_cd' => null,
            'noshiFormatList' => $this->addFormatList(null)
        ];

        $viewExtendData = array(
            'attachment_item_group_list' => $service->execute([
                'delete_flg' => DeleteFlg::Use->value, 
                'm_itemname_type' => ItemNameType::AttachmentGroup->value
            ])
        );
        return account_view( 'master.base.noshi.edit',compact('editData', 'viewExtendData'));
    }

    /**
     * 新規登録実行
     */
    public function postNew(Request $request)
    {
        $service = app(UpdateNoshiModuleInterface::class);

        $editData = $request->all();
        $submitName = $this->getSubmitName($editData);

        // 熨斗種類を追加
        if( $submitName == 'add_format' ){
            $editData['noshiFormatList'] = $this->addFormatList( ( $editData['noshiFormatList'] ?? null ) );
            return redirect()->route('master.noshi.new')->withInput( $editData );
        }

        // 登録処理
        $requestForm = app(UpdateNoshiRequest::class);
        $validator = Validator::make($editData, $requestForm->rules(), $requestForm->messages(), $requestForm->attributes());
        if ( $validator->fails() ) {
            return redirect()->route('master.noshi.new')->withErrors($validator)->withInput();
        }

        try{
            $service->execute(null, $editData);
            return redirect(route('master.noshi.list'))->with([
                'messages.info'=>['message'=>__('messages.info.create_completed', ['data' => '熨斗マスタ'])]
            ]);
        } catch(DataNotFoundException $e){
            return redirect(route('master.noshi.list'))->with([
                'messages.error'=>['message' => $e->getMessage()]
            ]);
        }
    }

    /**
     * 編集
     */
    public function edit($id, Request $request ){
        $service = app(FindNoshiModuleInterface::class);
        $itemService = app(SearchItemNameTypesInterface::class);

		$editData = $service->execute( $id );
        if( empty( $editData ) ){
            return redirect( route('master.noshi.list'))->with([
                'messages.error'=>['message'=>__('messages.error.data_not_found',['data'=>'熨斗マスタ', 'id' => $id])]
            ]);
        }

        $viewExtendData = array(
            'attachment_item_group_list' => $itemService->execute([
                'delete_flg' => DeleteFlg::Use->value, 
                'm_itemname_type' => ItemNameType::AttachmentGroup->value
            ])
        );
        // 熨斗種類が未設定の場合、デフォルトで空行をセットする
        if( !isset( $editData['noshiFormatList'] ) || count( $editData['noshiFormatList'] ) == 0 ){
            $editData['noshiFormatList'] = $this->addFormatList(null);
        }

        return account_view( 'master.base.noshi.edit',compact('editData', 'viewExtendData'));
    }

    /**
     * 編集実行
     */
    public function postEdit($id, Request $request){
        $saveService = app(UpdateNoshiModuleInterface::class);
        $getService = app(FindNoshiModuleInterface::class);

        $editData = $request->all();
        $submitName = $this->getSubmitName($editData);

        // 熨斗種類を追加
        if( $submitName == 'add_format' ){
            $editData['noshiFormatList'] = $this->addFormatList( ( $editData['noshiFormatList'] ?? null ) );
            return redirect()->route('master.noshi.edit', ['id' => $id])->withInput( $editData );
        }

        // 登録処理
        $requestForm = app(UpdateNoshiRequest::class);
        $validator = Validator::make($editData, $requestForm->rules(), $requestForm->messages(), $requestForm->attributes());
        if ( $validator->fails() ) {
            return redirect()->route('master.noshi.edit', ['id' => $id])->withErrors($validator)->withInput();
        }

        try{
            $noshi = $getService->execute($id);
            if( empty( $noshi ) || $id != $editData['m_noshi_id']){
                return redirect(route('master.noshi.list'))->with([
                    'messages.error'=>['message'=>__('messages.error.data_not_found',['data'=>'熨斗マスタ', 'id' => $id ])]
                ]);
            }

            $saveService->execute($id, $editData);
            return redirect(route('master.noshi.list'))->with([
                'messages.info'=>['message'=>__('messages.info.update_completed',['data'=>'熨斗マスタ'])]
            ]);
        } catch(DataNotFoundException $e){
            return redirect(route('master.noshi.list'))->with([
                'messages.error'=>['message' => $e->getMessage()]
            ]);
        } 
    }

    /**
     * 熨斗種類リストに行を追加する
     */
    private function addFormatList($list)
    {
        if( empty( $list ) ){
            $list = [];
        }
        $list[] = ['m_noshi_format_id' => null, 'noshi_format_name' => null, 'delete_flg' => DeleteFlg::Use->value ];
        return $list;
    }
}
