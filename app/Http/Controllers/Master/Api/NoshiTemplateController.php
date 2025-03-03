<?php

namespace App\Http\Controllers\Master\Api;

use App\Modules\Common\Base\SearchNoshiDetailInterface;
use App\Exceptions\DataNotFoundException;
use App\Services\EsmSessionManager;
use Illuminate\Http\Request;
use App\Modules\Common\Base\SearchNoshiNamingPatternInterface;
use App\Http\Requests\Master\Base\UpdateNoshiTemplateRequest;
use App\Http\Requests\Master\Base\SearchNoshiTemplateRequest;
use App\Modules\Master\Base\UpdateNoshiTemplateModuleInterface; //熨斗詳細(熨斗テンプレート)保存のInterface
use App\Models\Master\Base\NoshiNamingPatternModel; // 名入れパターンのモデル追加

use App\Models\Master\Base\NoshiDetailModel; //熨斗詳細(detail)のモデル
use App\Models\Master\Base\AccountModel; //企業アカウントのモデル
use Illuminate\Support\Facades\Storage;

use Config;
use Validator;


class NoshiTemplateController
{

    public function __construct()
    {
        $this->disk = config('filesystems.default', 'local');
    }


    
    //検索リスト
    // public function list(Request $request,EsmSessionManager $esmSessionManager)
    public function list(SearchNoshiTemplateRequest $request,EsmSessionManager $esmSessionManager)
    {
        $service = app(SearchNoshiDetailInterface::class);
        $req = $request->all();


        //追加1101
        $validator = Validator::make($req, $request->rules(), $request->messages(), $request->attributes());
        if ($validator->fails()) {
            // return response()->json(['errors' => $validator->errors()], 422);
            return response()->json(['message' => $validator->getMessage()], 400); // ステータスコードは適宜変更
        }

        $result = $service->execute(
            [
                'm_noshi_id'=>$req['m_noshi_id']??'',
                'm_noshi_format_id'=>$req['m_noshi_format_id']??'',
                'm_account_id'=>$esmSessionManager->getAccountId()
           ],
           [
                'with'=>'noshiNamingPattern',
                'with_deleted'=>'1'
           ]
        );

        //必要なフィールドのみに再構成する
        $customResult = []; // 新しい配列を初期化

        // 必要なデータのみを抽出
        foreach ($result as $item) {
            $customData = [
                'm_noshi_detail_id'         => $item['m_noshi_detail_id'],
                'm_account_id'              => $item['m_account_id'],
                'm_noshi_id'                => $item['m_noshi_id'],
                'm_noshi_format_id'         => $item['m_noshi_format_id'],
                'delete_flg'                => $item['delete_flg'],
                'm_noshi_naming_pattern_id' => $item['m_noshi_naming_pattern_id'],
                'template_file_name'        => $item['template_file_name'],
            ];

            // noshi_naming_patternが存在する場合、その中のデータも取得
            if (!empty($item->noshiNamingPattern)) {
                $noshiPattern = $item->noshiNamingPattern; // リレーションをオブジェクトとして取得
                $customData['company_name_count'] = $noshiPattern->company_name_count ?? null;
                $customData['section_name_count'] = $noshiPattern->section_name_count ?? null;
                $customData['title_count']        = $noshiPattern->title_count ?? null;
                $customData['f_name_count']       = $noshiPattern->f_name_count ?? null;
                $customData['name_count']         = $noshiPattern->name_count ?? null;
                $customData['ruby_count']         = $noshiPattern->ruby_count ?? null;
            }

            $customResult[] = $customData;
        }
        return response()->json($customResult);
    }


    public function update(Request $request, UpdateNoshiTemplateModuleInterface $updatemodule, UpdateNoshiTemplateRequest $requestForm){
        $editData = $request->all();
        $fileName = $request->file('file');
        $m_noshi_detail_id = $request->input('m_noshi_detail_id');

        // 登録処理
        $requestForm = app(UpdateNoshiTemplateRequest::class);
        $validator = Validator::make($editData, $requestForm->rules(), $requestForm->messages(), $requestForm->attributes());

        if ($validator->fails()) {
            return response()->json(['message' => $validator->getMessage()], 400); // ステータスコードは適宜変更
        }

        try{
            $namingPatternName = '';
            if (isset($editData['m_noshi_naming_pattern_id'])) {
                $namingPattern = NoshiNamingPatternModel::find($editData['m_noshi_naming_pattern_id']);
                if ($namingPattern) {
                    $namingPatternName = $namingPattern->pattern_name;
                }
            }

            if (isset($editData['m_noshi_detail_id']) && $editData['m_noshi_detail_id'] != 0) {
                // 編集

                $updatemodule->execute($editData);//編集更新
                return response()->json(['message' => $namingPatternName . 'のデータが正常に更新されました'], 200);
            } else {
                // 新規登録処理
                $updatemodule->execute($editData);//新規登録保存
                return response()->json(['message' => $namingPatternName . 'のデータが新規作成されました'], 200);
            }

        } catch(DataNotFoundException $e){
            return response()->json(['message' => '更新に失敗しました。'], 500);
        }
    }

    //アップロードされたファイルのダウンロード
    public function download(Request $request)
    {

        $id = $request['m_noshi_detail_id'];
        $accountId = $request['m_account_id'];

        // account_cdの取得
        $account = AccountModel::where('m_account_id', $accountId)->first();
        if (!$account) {
            throw new DataNotFoundException("企業ID{{". $account['m_account_id']."}}が見つかりません。");
        }
        $accountCd = $account->account_cd;
        


        // m_noshi_detail_idに基づいてファイル情報を取得
        $template = NoshiDetailModel::find($id);

        if (!$template || $template->account_id !== $request->account_id) {
            return response()->json([
                'error' => __('messages.error.file_not_found_temp', ['file' => $template['template_file_name']])
            ], 404);
        }

        //ファイルパス
        $noshiDetailId      = $template->m_noshi_detail_id ?? $template->id;
        $fileName           = $template->template_file_name;
        $destinationPath    = "{$accountCd}/noshi/template/{$noshiDetailId}/{$fileName}";
    
        // Storageを使ってファイルのダウンロード
        $disk = config('filesystems.default', 'local');  // filesystems.default の値を取得

        if (!Storage::disk($disk)->exists($destinationPath)) {
            return response()->json([
                'error' => __('messages.error.file_not_found_temp', ['file' => $fileName])
            ], 404);
        }
        $mimeType = Storage::disk($disk)->mimeType($destinationPath);


        // ダウンロード用のレスポンスを返す
        return Storage::disk($disk)->download($destinationPath, $fileName, [
            'Content-Type' => $mimeType,
            'Cache-Control' => 'no-store, no-cache, must-revalidate, max-age=0',
            'Pragma' => 'no-cache',
            'Expires' => 'Fri, 01 Jan 1990 00:00:00 GMT'
        ]); 
    }
}
