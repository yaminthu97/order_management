<?php

namespace App\Http\Controllers\Master;

use App\Exceptions\DataNotFoundException;
use App\Http\Controllers\Common\CommonController;


use App\Modules\Master\Base\SearchCampaignModuleInterface;        //リストインターフェース
use App\Modules\Master\Base\SaveCampaignModuleInterface;    //保存インターフェース
use App\Modules\Master\Base\GetOneCampaignModuleInterface;  //1件インターフェース

use App\Modules\Master\Base\Enums\AttentionTypeInterface;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Log;

use App\Http\Requests\Master\Gfh1207\UpdateCampaignRequest;

use App\Enums\DeleteFlg;
use App\Enums\GivingConditionEvery;

use App\Services\EsmSessionManager; //追加コード

use Config;
use Validator;


class CampaignListController extends CommonController
{

    protected $className = 'Campaign';
    protected $namespace = 'Campaign';
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

    //検索したリスト
    public function list(Request $request)
    {
        $service = app(SearchCampaignModuleInterface::class);
        
        $req = $request->all();

        if (empty($req)) {
            // 検索初期値
            $req['delete_flg'] = DeleteFlg::Use->value;
        }
        $submit = $this->getSubmitName($req);

        if ($request->isMethod('post')) {
            if ($submit == 'search') {
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
        return account_view( 'master.base.campaign.list',compact($compact));
    }


    //キャンペーン新規登録画面
    public function new(Request $request)
    {
            // 初期値
            $editRow = [
                'm_campaign_id'             => null,
                'campaign_name'             => null,
                'delete_flg'                => DeleteFlg::Use->value,
                'from_date'                 => null,
                'to_date'                   => null,
                'giving_condition_amount'   => null,
                'giving_condition_every'    => GivingConditionEvery::Do->value,
                'giving_page_cd'            => null,
            ];

            session()->put('campaign.gfh_1207.edit', $editRow);
            return account_view( 'master.base.campaign.edit',compact('editRow'));
    }

    // キャンペーン新規登録保存
    public function postNew(UpdateCampaignRequest $request)
    {
        $req = $request->all();

        // セッションをクリア
        session()->forget('campaign.gfh_1207.edit');

        try{
            // 新規登録処理
            $saveService = app(SaveCampaignModuleInterface::class);
            $saveService->execute(null,$req); //新規登録保存
            return redirect(route('campaign.list'))->with([
                'messages.info'=>['message'=>__('messages.info.create_completed', ['data' => 'キャンペーン'])]
            ]);
        } catch(DataNotFoundException $e){
            return redirect(route('campaign.gfh_1207.list'))->with([
                'messages.error'=>['message'=>$e->getMessage()]
            ]);
        }

        return redirect()->route('campaign.list');
    }

    //キャンペーン編集画面
    public function edit($id,Request $request)
    {
        // データをセッションに保存
        $getOneService = app(GetOneCampaignModuleInterface::class);
        // サービスのメソッドを呼び出し

        try {
            $editRow = $getOneService->execute($id);
            session()->put('campaign.gfh_1207.edit', $editRow);
    
            if (empty($editRow)) {
                return redirect(route('campaign.list'))->with([
                    'messages.error'=>['message'=>__('messages.error.data_not_found',['data'=>'キャンペーン','id'=>$id])]
                ]);
            }
            return account_view('master.base.campaign.edit', compact('editRow'));
        }
        catch (DataNotFoundException $e) {
            // データが見つからなかった場合のエラーハンドリング
            return redirect(route('campaign.list'))->with([
                'messages.error' => ['message' => __('messages.error.data_not_found', ['data' => 'キャンペーン', 'id' => $id])]
            ]);
        }
    }

    // キャンペーン編集セーブ
    public function postEdit($id, UpdateCampaignRequest $request,) //今までの
    {
        $req = $request->all();

        try{
            $getOneService = app(GetOneCampaignModuleInterface::class);
            $editRow = $getOneService->execute($id);
            if(empty($editRow) || $id != $editRow['m_campaign_id']){
                return redirect(route('campaign.list'))->with([
                    'messages.error'=>['message'=>__('messages.error.data_not_found',['data'=>'キャンペーン','id'=>$id])]
                ]);
            }

            $saveService = app(SaveCampaignModuleInterface::class);
            $saveService->execute($id, $req); //インスタンスのメソッドを呼び出す

            return redirect(route('campaign.list'))->with([
                'messages.info'=>['message'=>__('messages.info.update_completed',['data'=>'キャンペーン'])]
            ]);
        } catch(DataNotFoundException $e){
            return redirect(route('campaign.list'))->with([
                'messages.error'=>['message'=>$e->getMessage()]
            ]);
        }
    }

    //キャンペーン確認画面
    public function notify(Request $request)
    {
        // フォーム入力データをセッションに保存
        $editRow = $request->all();
        session()->put('campaign.gfh_1207.edit', $editRow);
        
        return account_view('master.base.campaign.notify', compact('editRow'));
    }

    // 編集画面からのPOSTリクエストを処理
    public function postNotify(UpdateCampaignRequest $request)
    {

        $editRow = $request->all();

        session()->put('campaign.gfh_1207.edit', $editRow);
        return account_view('master.base.campaign.notify', compact('editRow'));
    }

    public function update(Request $request)
    {

        // セッションからデータを取得
        $editRow = session()->get('campaign.gfh_1207.edit');

        // キャンセルボタンが押された場合の処理
        if ($request->input('submit') === 'cancel') {
            // 編集データがある場合、編集画面にリダイレクト
            if (isset($editRow['m_campaign_id']) && $editRow['m_campaign_id'] != 0) {
                return redirect()->route('campaign.edit', ['id' => $editRow['m_campaign_id']])
                                 ->withInput($editRow);
            } else {
                // 新規登録の場合、新規画面にリダイレクト
                return redirect()->route('campaign.new')
                                 ->withInput($editRow);
            }
        }

        // バリデーションの実行
        $validated = Validator::make($editRow, (new UpdateCampaignRequest)->rules());
        
        if ($validated->fails()) {
            if(!empty($editRow['m_campaign_id'])){
                return redirect()->route('campaign.edit', ['id' => $editRow['m_campaign_id']])->withInput($editRow)->withErrors($validated);// 入力値を保持したまま戻る
            }else{
                return redirect()->route('campaign.new')->withInput($editRow)->withErrors($validated); // 入力値を保持したまま戻る

            }
        } 

        // セッションをクリア
        session()->forget('campaign.gfh_1207.edit');

        try{
            if (isset($editRow['m_campaign_id']) && $editRow['m_campaign_id'] != 0) {
                // 編集
                $saveService = app(SaveCampaignModuleInterface::class);
                $saveService->execute($editRow);//編集更新

            } else {
                // 新規登録処理
                $saveService = app(SaveCampaignModuleInterface::class);
                $saveService->execute($editRow);//新規登録保存
            }

            return redirect(route('campaign.list'))->with([
                'messages.info'=>['message'=>__('messages.info.create_completed', ['data' => 'キャンペーン'])]
            ]);

        } catch(DataNotFoundException $e){
            return redirect(route('campaign.list'))->with([
                'messages.error'=>['message'=>$e->getMessage()]
            ]);
        }

        return redirect()->route('campaign.list');
    }
}