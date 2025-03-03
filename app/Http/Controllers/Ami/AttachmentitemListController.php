<?php

namespace App\Http\Controllers\Ami;

use App\Exceptions\DataNotFoundException;
use App\Http\Controllers\Common\CommonController;


use App\Modules\Ami\Base\SearchAttachmentitemModuleInterface;  //リストインターフェース
use App\Modules\Ami\Base\SaveAttachmentitemModuleInterface;    //保存インターフェース
use App\Modules\Ami\Base\GetOneAttachmentitemModuleInterface;  //1件インターフェース

use App\Modules\Master\Base\SearchItemNameTypesInterface;

use App\Modules\Master\Base\Enums\AttentionTypeInterface;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Log;

use App\Http\Requests\Ami\Base\UpdateAttachmentitemRequest;

use App\Enums\DeleteFlg;
use App\Enums\DisplayFlg;
use App\Enums\InvoiceFlg;
use App\Enums\ItemNameType;

use App\Services\EsmSessionManager; //追加コード

use Config;
use Validator;


class AttachmentitemListController
{
    protected $className = 'Attachment_item';
    protected $namespace = 'Attachment_item';
    protected $sessionManager;

    protected $service; // プロパティを定義

    protected $saveService;
    protected $getOneService;

    public function __construct()
    {
    }


    /**
     * submitボタン内容の取得
     */
    protected function getSubmitName($req)
    {
        $submitName = '';
        if(!empty($req['submit_name'])) {
            $submitName = $req['submit_name'];
        }
        return $submitName;
    }

    //リスト
    public function list(Request $request, SearchAttachmentitemModuleInterface $service, SearchItemNameTypesInterface $serviceCategory)
    {
        
        // viewExtendData の取得
        $viewExtendData = array(
            'attachment_item_category_list' => $serviceCategory->execute([
                'delete_flg' => DeleteFlg::Use->value, 
                'm_itemname_type' => ItemNameType::AttachmentCategory->value
            ])
        );
        
        $req = $request->all();

        if (empty($req)) {
            // 検索初期値
            $req['delete_flg'] = DeleteFlg::Use->value;
        }
        $submit = $this->getSubmitName($req);

        $paginator = $service->execute($req);
        $req['page_list_count'] = Config::get('Common.const.disp_limit_default');
        $req['hidden_next_page_no'] = 1;

        $viewExtendData['page_list_count'] = Config::get('Common.const.disp_limits');
        $searchRow ??= $req;
        $paginator ??= null;
        $compact = [
            'searchRow',
            'paginator',
            'viewExtendData',
        ];
        return account_view( 'ami.base.attachmentitem.list',compact($compact));
    }

    //検索処理
    public function postList(Request $request, SearchAttachmentitemModuleInterface $service, SearchItemNameTypesInterface $serviceCategory)
    {
        // viewExtendData の取得
        $viewExtendData = array(
            'attachment_item_category_list' => $serviceCategory->execute([
                'delete_flg' => DeleteFlg::Use->value, 
                'm_itemname_type' => ItemNameType::AttachmentCategory->value
            ])
        );
        
        $req = $request->all();

        if (empty($req)) {
            // 検索初期値
            $req['delete_flg'] = DeleteFlg::Use->value;
        }
        $submit = $this->getSubmitName($req);


        if ($submit == 'search') {
            // 検索ボタン押下時はページ数、表示件数を初期化する
            $req['page_list_count'] = Config::get('Common.const.disp_limit_default');
            $req['hidden_next_page_no'] = 1;
        }

        $paginator = $service->execute($req);
        $viewExtendData['page_list_count'] = Config::get('Common.const.disp_limits');
        $searchRow ??= $req;
        $paginator ??= null;
        $compact = [
            'searchRow',
            'paginator',
            'viewExtendData',
        ];
        return account_view( 'ami.base.attachmentitem.list',compact($compact));
    }


    //付属品マスタ新規登録
    public function new(Request $request, SearchItemNameTypesInterface $service)
    {
        // 初期値
        $editRow = [
            'm_ami_attachment_item_id'   => null,
            'category_id'            => '',
            'attachment_item_cd'     => null,
            'attachment_item_name'   => null,
            'delete_flg'             => DeleteFlg::Use->value,
            'display_flg'            => DisplayFlg::VISIBLE->value,
            'invoice_flg'            => InvoiceFlg::Describe->value,
            'reserve1'               => null,
            'reserve2'               => null,
            'reserve3'               => null,
        ];

        // viewExtendData の取得
        $viewExtendData = array(
            'attachment_item_category_list' => $service->execute([
                'delete_flg' => DeleteFlg::Use->value, 
                'm_itemname_type' => ItemNameType::AttachmentCategory->value
            ])
        );

        session()->put('gfh_1207.attachmentitem.edit', $editRow);
        return account_view( 'ami.base.attachmentitem.edit',compact('editRow', 'viewExtendData'));
    }

    // 付属品マスタ新規登録保存
    public function postNew(UpdateAttachmentitemRequest $request, SaveAttachmentitemModuleInterface $saveService)
    {

        $req = $request->all();

        // セッションをクリア
        session()->forget('attachmentitem.gfh_1207.edit');

        try{
            // 新規登録処理
            $saveService->execute(null,$req); //新規登録保存
            return redirect(route('attachmentitem.list'))->with([
                'messages.info'=>['message'=>__('messages.info.create_completed', ['data' => '付属品マスタ'])]
            ]);
        } catch(DataNotFoundException $e){
            return redirect(route('attachmentitem.gfh_1207.list'))->with([
                'messages.error'=>['message'=>$e->getMessage()]
            ]);
        }

        return redirect()->route('attachmentitem.list');
    }

    //付属品マスタ編集画面
    public function edit($id,Request $request, GetOneAttachmentitemModuleInterface $getOneService, SearchItemNameTypesInterface $service)
    {

        try {
            // viewExtendData の取得
            $viewExtendData = array(
                'attachment_item_category_list' => $service->execute([
                    'delete_flg' => DeleteFlg::Use->value, 
                    'm_itemname_type' => ItemNameType::AttachmentCategory->value
                ])
            );

            $editRow = $getOneService->execute($id);
            session()->put('attachmentitem.scroll.edit', $editRow);
    
            if (empty($editRow)) {
                return redirect(route('attachment_item.list'))->with([
                    'messages.error'=>['message'=>__('messages.error.data_not_found',['data'=>'付属品マスタ','id'=>$id])]
                ]);
            }
            return account_view('ami.base.attachmentitem.edit', compact('editRow', 'viewExtendData'));
        }
        catch (DataNotFoundException $e) {
            // データが見つからなかった場合のエラーハンドリング
            return redirect(route('attachment_item.list'))->with([
                'messages.error' => ['message' => __('messages.error.data_not_found', ['data' => '付属品マスタ', 'id' => $id])]
            ]);
        }
    }

    // 付属品マスタ編集セーブ
    public function postEdit($id, 
    UpdateAttachmentitemRequest $request,
    GetOneAttachmentitemModuleInterface $getOneService, 
    SaveAttachmentitemModuleInterface $saveService) //今までの
    {

        $req = $request->all();

        try{
            $editRow = $getOneService->execute($id);
            if(empty($editRow) || $id != $editRow['m_ami_attachment_item_id']){
                return redirect(route('attachmentitem.list'))->with([
                    'messages.error'=>['message'=>__('messages.error.data_not_found',['data'=>'付属品マスタ','id'=>$id])]
                ]);
            }
            $saveService->execute($id, $req); //インスタンスのメソッドを呼び出す

            return redirect(route('attachment_item.list'))->with([
                'messages.info'=>['message'=>__('messages.info.update_completed',['data'=>'付属品マスタ'])]
            ]);
        } catch(DataNotFoundException $e){
            return redirect(route('attachment_item.list'))->with([
                'messages.error'=>['message'=>$e->getMessage()]
            ]);
        }
    }

    //付属品マスタ確認画面
    public function notify(Request $request, SearchItemNameTypesInterface $service)
    {

        // viewExtendData の取得
        $viewExtendData = array(
            'attachment_item_category_list' => $service->execute([
                'delete_flg' => DeleteFlg::Use->value, 
                'm_itemname_type' => ItemNameType::AttachmentCategory->value
            ])
        );

        // フォーム入力データをセッションに保存
        $editRow = $request->all();

        session()->put('attachmentitem.scroll.edit', $editRow);
        
        return account_view('ami.base.attachmentitem.notify', compact('editRow', 'viewExtendData'));
    }

    // 編集画面からのPOSTリクエストを処理
    public function postNotify(UpdateAttachmentitemRequest $request, SearchItemNameTypesInterface $service)
    {

        // viewExtendData の取得
        $viewExtendData = array(
            'attachment_item_category_list' => $service->execute([
                'delete_flg' => DeleteFlg::Use->value, 
                'm_itemname_type' => ItemNameType::AttachmentCategory->value
            ])
        );

        $editRow = $request->all();

        session()->put('attachmentitem.scroll.edit', $editRow);
        return account_view('ami.base.attachmentitem.notify', compact('editRow', 'viewExtendData'));
    }

    public function update(Request $request, SaveAttachmentitemModuleInterface $saveService)
    {

        // セッションからデータを取得
        $editRow = session()->get('attachmentitem.scroll.edit');

        // キャンセルボタンが押された場合の処理
        if ($request->input('submit') === 'cancel') {
            // 編集データがある場合、編集画面にリダイレクト
            if (isset($editRow['m_ami_attachment_item_id']) && $editRow['m_ami_attachment_item_id'] != 0) {
                return redirect()->route('attachment_item.edit', ['id' => $editRow['m_ami_attachment_item_id']])
                                 ->withInput($editRow);
            } else {
                // 新規登録の場合、新規画面にリダイレクト
                return redirect()->route('attachment_item.new')
                                 ->withInput($editRow);
            }
        }

        // バリデーションの実行
        $validated = Validator::make($editRow, (new UpdateAttachmentitemRequest)->rules());
        if ($validated->fails()) {
            if(!empty($editRow['m_ami_attachment_item_id'])){
                return redirect()->route('attachment_item.edit', ['id' => $editRow['m_ami_attachment_item_id']])->withInput($editRow)->withErrors($validated);// 入力値を保持したまま戻る
            }else{
                return redirect()->route('attachment_item.new')->withInput($editRow)->withErrors($validated); // 入力値を保持したまま戻る

            }
        }

        // セッションをクリア
        session()->forget('attachmentitem.scroll.edit');

        try{
            if (isset($editRow['m_ami_attachment_item_id']) && $editRow['m_ami_attachment_item_id'] != 0) {
                // 編集
                $saveService->execute($editRow);//編集更新

            } else {
                // 新規登録処理
                $saveService->execute($editRow);//新規登録保存
            }

            return redirect(route('attachment_item.list'))->with([
                'messages.info'=>['message'=>__('messages.info.create_completed', ['data' => '付属品マスタ'])]
            ]);

        } catch(DataNotFoundException $e){
            return redirect(route('attachment_item.list'))->with([
                'messages.error'=>['message'=>$e->getMessage()]
            ]);
        }

        return redirect()->route('attachment_item.list');
    }
}