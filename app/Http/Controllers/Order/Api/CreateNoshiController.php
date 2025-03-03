<?php

namespace App\Http\Controllers\Order\Api;

use App\Modules\Order\Base\UpdateOrderDtlNoshiInterface;
use App\Modules\Order\Base\SearchCreateNoshiInterface;
use App\Services\EsmSessionManager;
use App\Services\PowerPointReportManager;
use Illuminate\Http\Request;
use Exception;
use Log;

class CreateNoshiController
{
    public function checkLinkage(
        Request $request,
        UpdateOrderDtlNoshiInterface $update
    ) {
        $req = $request->all();

        if (empty($req['shared_flg'])) {
            Log::error(__('validation.required', ['attribute' => 'まとめて確認チェック']));
            return response()->json([
                'error' => __('validation.required', ['attribute' => 'まとめて確認チェック']),
            ], 400);
        }
        $datas = [];
        foreach($req['shared_flg'] as $val){
            $datas[] = ['t_order_dtl_noshi_id'=>$val,'shared_flg'=>1];
        }
        try{
            $results = $update->execute($datas);
            $rv = [];
            foreach($results as $val){
                $rv[] = [
                    't_order_dtl_noshi_id'=>$val['t_order_dtl_noshi_id'],
                    'noshi_file_name'=>$val['noshi_file_name'],
                    'shared_flg'=>$val['shared_flg']
                ];
            }
            return response()->json([
                'list' => $rv
            ]);
        } catch(Exception $e){
            Log::error($e->getMessage());
            return response()->json([
                'error' => $e->getMessage(),
            ], 400);
        }
    }
    private function setReplaceStr($noshiDtl){
        $data = [];
        $data['${o1}'] = $noshiDtl['omotegaki'];
        for($idx=1;$idx<=5;$idx++){
            if($idx <= $noshiDtl->noshiNamingPattern->company_name_count){
                $data['${c'.$idx.'}'] = $noshiDtl['company_name'.$idx];
            }
            if($idx <= $noshiDtl->noshiNamingPattern->section_name_count){
                $data['${d'.$idx.'}'] = $noshiDtl['section_name'.$idx];
            }
            if($idx <= $noshiDtl->noshiNamingPattern->title_count){
                $data['${t'.$idx.'}'] = $noshiDtl['title'.$idx];
            }
            if($idx <= $noshiDtl->noshiNamingPattern->f_name_count){
                $data['${f'.$idx.'}'] = $noshiDtl['firstname'.$idx];
            }
            if($idx <= $noshiDtl->noshiNamingPattern->name_count){
                $data['${n'.$idx.'}'] = $noshiDtl['name'.$idx];
            }
            if($idx <= $noshiDtl->noshiNamingPattern->ruby_count){
                $data['${r'.$idx.'}'] = $noshiDtl['ruby'.$idx];
            }
        }
        return $data;
    }
    public function create(
        Request $request,
        UpdateOrderDtlNoshiInterface $update,
        SearchCreateNoshiInterface $search,
        PowerPointReportManager $pptService,
        EsmSessionManager $esmSessionManager
    ) {
        $req = $request->all();
        if (empty($req['t_order_dtl_noshi_id'])) {
            Log::error(__('validation.required', ['attribute' => '受注熨斗明細ID']));
            return response()->json([
                'error' => __('validation.required', ['attribute' => '受注熨斗明細ID']),
            ], 400);
        }
        $searchRow = [
            't_order_dtl_noshi_id'=>$req['t_order_dtl_noshi_id']
        ];
        $noshiDtls = $search->execute($searchRow);
        if(empty($noshiDtls)){
            Log::error(__('messages.error.data_not_found', ['data' => '受注明細熨斗ID','id'=>$req['t_order_dtl_noshi_id']]));
            return response()->json([
                'error' => __('messages.error.data_not_found', ['data' => '受注明細熨斗ID','id'=>$req['t_order_dtl_noshi_id']]),
            ], 400);
        }
        $noshiDtl = $noshiDtls[0];

        // 名入パターンチェック
        if(empty($noshiDtl->noshiNamingPattern) || (
            empty($noshiDtl->noshiNamingPattern->company_name_count) && 
            empty($noshiDtl->noshiNamingPattern->section_name_count) && 
            empty($noshiDtl->noshiNamingPattern->title_count) && 
            empty($noshiDtl->noshiNamingPattern->f_name_count) && 
            empty($noshiDtl->noshiNamingPattern->name_count) && 
            empty($noshiDtl->noshiNamingPattern->ruby_count)
            )
        ){
            // 名入パターンがない場合　または　無地
            Log::error(__('messages.error.not_naming_pattern'));
            return response()->json([
                'error' => __('messages.error.not_naming_pattern')
            ], 400);
        }
        if($noshiDtl->noshiDetail && !empty($noshiDtl->noshiDetail->template_file_name)){
            // テンプレートデータパス
            $template = $esmSessionManager->getAccountCode() . '/noshi/template/'.$noshiDtl->noshi_detail_id . '/' . $noshiDtl->noshiDetail->template_file_name;
            // 置換文字設定
            $data = $this->setReplaceStr($noshiDtl);
            // powerpointファイル作成
            $rv = $pptService->createReport($template,$data);
            if(!empty($rv['error'])){
                Log::error($rv['error']);
                return response()->json([
                    'error' => $rv['error'],
                ], 400);
            }
            $pptx = file_get_contents($rv['file']);
            // テンポラリファイルを削除する
            unlink($rv['file']);

            // DB更新
            $datas = [];
            $datas[] = ['t_order_dtl_noshi_id'=>$req['t_order_dtl_noshi_id'],'increment_count'=>1];
            try{
                $rv = [];
                $results = $update->execute($datas);
                foreach($results as $val){
                    $rv[] = [
                        't_order_dtl_noshi_id'=>$val['t_order_dtl_noshi_id'],
                        'noshi_file_name'=>$val['noshi_file_name'],
                        'shared_flg'=>$val['shared_flg']
                    ];
                }
                // ファイルをbase64に変換して返却する
                return response()->json([
                    'list' => $rv,
                    'data' => base64_encode($pptx),
                    'name'=>$results[0]->noshi_file_name,
                ]);
            } catch(Exception $e){
                Log::error($e->getMessage());
                return response()->json([
                    'error' => $e->getMessage(),
                ], 400);
            }    
        } else {
            Log::error(__('messages.error.not_create_noshi'));
            return response()->json([
                'error' => __('messages.error.not_create_noshi'),
            ], 400);
        }
    }
    public function checkCreate(
        Request $request,
        UpdateOrderDtlNoshiInterface $update,
        SearchCreateNoshiInterface $search,
        PowerPointReportManager $pptService,
        EsmSessionManager $esmSessionManager
    ) {
        $req = $request->all();
        if (empty($req['copy_from'])) {
            Log::error(__('validation.required', ['attribute' => 'コピー元']));
            return response()->json([
                'error' => __('validation.required', ['attribute' => 'コピー元']),
            ], 400);
        }
        $searchRow = [
            't_order_dtl_noshi_id'=>$req['copy_from']
        ];
        $noshiDtls = $search->execute($searchRow);
        if(empty($noshiDtls)){
            Log::error(__('messages.error.data_not_found', ['data' => '受注明細熨斗ID','id'=>$req['t_order_dtl_noshi_id']]));
            return response()->json([
                'error' => __('messages.error.data_not_found', ['data' => '受注明細熨斗ID','id'=>$req['t_order_dtl_noshi_id']]),
            ], 400);
        }
        $noshiDtl = $noshiDtls[0];

        // 名入パターンチェック
        if(empty($noshiDtl->noshiNamingPattern) || (
            empty($noshiDtl->noshiNamingPattern->company_name_count) && 
            empty($noshiDtl->noshiNamingPattern->section_name_count) && 
            empty($noshiDtl->noshiNamingPattern->title_count) && 
            empty($noshiDtl->noshiNamingPattern->f_name_count) && 
            empty($noshiDtl->noshiNamingPattern->name_count) && 
            empty($noshiDtl->noshiNamingPattern->ruby_count)
            )
        ){
            // 名入パターンがない場合　または　無地
            Log::error(__('messages.error.not_naming_pattern'));
            return response()->json([
                'error' => __('messages.error.not_naming_pattern')
            ], 400);
        }
        if($noshiDtl->noshiDetail && !empty($noshiDtl->noshiDetail->template_file_name)){
            // テンプレートデータパス
            $template = $esmSessionManager->getAccountCode() . '/noshi/template/'.$noshiDtl->noshi_detail_id . '/' . $noshiDtl->noshiDetail->template_file_name;
            // 置換文字設定
            $data = $this->setReplaceStr($noshiDtl);
            // powerpointファイル作成
            $rv = $pptService->createReport($template,$data);
            if(!empty($rv['error'])){
                Log::error($rv['error']);
                return response()->json([
                    'error' => $rv['error'],
                ], 400);
            }
            $pptx = file_get_contents($rv['file']);
            // テンポラリファイルを削除する
            unlink($rv['file']);

            // DB更新
            $datas = [];
            $datas[] = ['t_order_dtl_noshi_id'=>$req['copy_from'],'increment_count'=>1];
            try{
                $rv = [];
                $results = $update->execute($datas);
                $filename = $results[0]['noshi_file_name'];
                foreach($results as $val){
                    $rv[] = [
                        't_order_dtl_noshi_id'=>$val['t_order_dtl_noshi_id'],
                        'noshi_file_name'=>$val['noshi_file_name'],
                        'shared_flg'=>$val['shared_flg']
                    ];
                }
                // コピー先の更新
                $datas = [];
                foreach($req['copy_to']??[] as $val){
                    $datas[] = ['t_order_dtl_noshi_id'=>$val,'noshi_file_name'=>$filename];
                }
                $results = $update->execute($datas);
                foreach($results as $val){
                    $rv[] = [
                        't_order_dtl_noshi_id'=>$val['t_order_dtl_noshi_id'],
                        'noshi_file_name'=>$val['noshi_file_name'],
                        'shared_flg'=>$val['shared_flg']
                    ];
                }
                // ファイルをbase64に変換して返却する
                return response()->json([
                    'list' => $rv,
                    'data' => base64_encode($pptx),
                    'name'=>$filename,
                ]);
            } catch(Exception $e){
                Log::error($e->getMessage());
                return response()->json([
                    'error' => $e->getMessage(),
                ], 400);
            }    
        } else {
            Log::error(__('messages.error.not_create_noshi'));
            return response()->json([
                'error' => __('messages.error.not_create_noshi'),
            ], 400);
        }
    }

    public function clear(
        Request $request,
        UpdateOrderDtlNoshiInterface $update,
        SearchCreateNoshiInterface $search
    ) {
        $req = $request->all();
        if (empty($req['t_order_dtl_noshi_id'])) {
            Log::error(__('validation.required', ['attribute' => '受注熨斗明細ID']));
            return response()->json([
                'error' => __('validation.required', ['attribute' => '受注熨斗明細ID']),
            ], 400);
        }
        $searchRow = [
            't_order_dtl_noshi_id'=>$req['t_order_dtl_noshi_id']
        ];
        $noshiDtl = $search->execute($searchRow);
        if(empty($noshiDtl)){
            Log::error(__('messages.error.data_not_found', ['data' => '受注明細熨斗ID','id'=>$req['t_order_dtl_noshi_id']]));
            return response()->json([
                'error' => __('messages.error.data_not_found', ['data' => '受注明細熨斗ID','id'=>$req['t_order_dtl_noshi_id']]),
            ], 400);
        }
        $datas = [];
        $datas[] = ['t_order_dtl_noshi_id'=>$req['t_order_dtl_noshi_id'],'output_counter'=>0,'noshi_file_name'=>''];

        $results = $update->execute($datas);
        $rv = [];
        foreach($results as $val){
            $rv[] = [
                't_order_dtl_noshi_id'=>$val['t_order_dtl_noshi_id'],
                'noshi_file_name'=>$val['noshi_file_name'],
                'shared_flg'=>$val['shared_flg']
            ];
        }
        return response()->json([
            'list' => $rv
        ]);
    }
    public function checkShared(
        Request $request,
        UpdateOrderDtlNoshiInterface $update,
        SearchCreateNoshiInterface $searchCreateNoshi
    ) {
        $req = $request->all();
        if (empty($req['copy_from'])) {
            Log::error(__('validation.required', ['attribute' => 'コピー元']));
            return response()->json([
                'error' => __('validation.required', ['attribute' => 'コピー元']),
            ], 400);
        }
        if (empty($req['copy_to'])) {
            Log::error(__('validation.required', ['attribute' => 'まとめて共有チェック']));
            return response()->json([
                'error' => __('validation.required', ['attribute' => 'まとめて共有チェック']),
            ], 400);
        }
        $searchRow = [
            't_order_dtl_noshi_id'=>$req['copy_from']
        ];
        $src = $searchCreateNoshi->execute($searchRow);
        if(empty($src)){
            Log::error(__('messages.error.data_not_found', ['data' => '受注明細熨斗ID','id'=>$req['copy_from']]));
            return response()->json([
                'error' => __('messages.error.data_not_found', ['data' => '受注明細熨斗ID','id'=>$req['copy_from']]),
            ], 400);
        }
        if(empty($src[0]['noshi_file_name'])){
            Log::error(__('messages.error.not_create_noshi'));
            return response()->json([
                'error' => __('messages.error.not_create_noshi'),
            ], 400);
        }
        $datas = [];
        foreach($req['copy_to'] as $val){
            $datas[] = ['t_order_dtl_noshi_id'=>$val,'noshi_file_name'=>$src[0]['noshi_file_name']];
        }
        try{
            $results = $update->execute($datas);
            $rv = [];
            foreach($results as $val){
                $rv[] = [
                    't_order_dtl_noshi_id'=>$val['t_order_dtl_noshi_id'],
                    'noshi_file_name'=>$val['noshi_file_name'],
                    'shared_flg'=>$val['shared_flg']
                ];
            }
            return response()->json([
                'list' => $rv
            ]);
        } catch(Exception $e){
            Log::error($e->getMessage());
            return response()->json([
                'error' => $e->getMessage(),
            ], 400);
        }
    }
}
