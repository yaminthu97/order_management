<?php

namespace App\Http\Controllers\Master;

use App\Exceptions\DataNotFoundException;
use App\Http\Controllers\Common\CommonController;
use App\Modules\Master\Base\NoshiNamingPatternModule;
use App\Http\Requests\Master\Base\UpdateNoshiNamingPatternRequest;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Config;

class NoshiNamingPatternController
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
        foreach($req as $key => $row) {
            if(strpos($key, 'submit_') !== false) {
                $submitName = str_replace('submit_', '', $key);
            }
        }
        return $submitName;
    }
    public function list(Request $request,NoshiNamingPatternModule $service)
    {
        $req = $request->all();
        if(empty($req)){
            // 検索初期値
            $req['delete_flg'] = [\App\Enums\DeleteFlg::Use->value];
        }
        $submit = $this->getSubmitName($req);
        if($request->isMethod('post')) {
            if($submit == 'search'){
                // 検索ボタン押下時はページ数、表示件数を初期化する
                $req['page_list_count'] = Config::get('Common.const.disp_limit_default');
                $req['hidden_next_page_no'] = 1;
            }
            $paginator = $service->list($req);
        } else {
            $paginator = $service->list($req);
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
        return view( 'master.noshinamingpattern.list',compact($compact));
    }
    public function new(Request $request)
    {
        // 初期値
        $editRow = [
            'company_name_count'=>0,
            'section_name_count'=>0,
            'title_count'=>0,
            'f_name_count'=>0,
            'name_count'=>0,
            'ruby_count'=>0,
        ];
        return view( 'master.noshinamingpattern.edit',compact('editRow'));
    }
    public function postNew(UpdateNoshiNamingPatternRequest $request,NoshiNamingPatternModule $service)
    {
        $req = $request->all();
        try{
            $service->save(null,$req);
            return redirect(route('noshi.namingpattern.list'))->with([
                'messages.info'=>['message'=>__('messages.info.create_completed', ['data' => '熨斗名入れパターン'])]
            ]);
        } catch(DataNotFoundException $e){
            return redirect(route('noshi.namingpattern.list'))->with([
                'messages.error'=>['message'=>$e->getMessage()]
            ]);
        }
    }
    public function edit($id,Request $request,NoshiNamingPatternModule $service){
		$editRow = $service->getOne($id);
        if(empty($editRow)){
            return redirect(route('noshi.namingpattern.list'))->with([
                'messages.error'=>['message'=>__('messages.error.data_not_found',['data'=>'熨斗名入れパターン','id'=>$id])]
            ]);
        }
        return view( 'master.noshinamingpattern.edit',compact('editRow'));
    }
    public function postEdit($id,UpdateNoshiNamingPatternRequest $request,NoshiNamingPatternModule $service){
        $req = $request->all();
        try{
            $editRow = $service->getOne($id);
            if(empty($editRow) || $id != $req['m_noshi_naming_pattern_id']){
                return redirect(route('noshi.namingpattern.list'))->with([
                    'messages.error'=>['message'=>__('messages.error.data_not_found',['data'=>'熨斗名入れパターン','id'=>$id])]
                ]);
            }

            $service->save($id,$req);
            return redirect(route('noshi.namingpattern.list'))->with([
                'messages.info'=>['message'=>__('messages.info.update_completed',['data'=>'熨斗名入れパターン'])]
            ]);
        } catch(DataNotFoundException $e){
            return redirect(route('noshi.namingpattern.list'))->with([
                'messages.error'=>['message'=>$e->getMessage()]
            ]);
        } 
    }
}
