<?php

namespace App\Modules\Master\Base;

use App\Enums\DeleteFlg;
use App\Enums\ItemNameType;
use App\Exceptions\DataNotFoundException;
use App\Models\Master\Base\NoshiDetailModel; //熨斗詳細(detail)のモデル
use App\Models\Master\Base\AccountModel; //企業アカウントのモデル
use App\Modules\Common\CommonModule;
use Illuminate\Support\Facades\Storage;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\Log;

use App\Modules\Master\Base\UpdateNoshiTemplateModuleInterface;

use Illuminate\Http\UploadedFile;

use Config;
use DB;

/**
 * 熨斗テンプレートマスタ保存
 */
class UpdateNoshiTemplateModule extends CommonModule implements UpdateNoshiTemplateModuleInterface
{
    //DBへの保存コード
    public function execute(array $data){

        //トランザクション追加
        DB::transaction(function () use ($data) {
            $accountId = $this->getAccountId();
            $operatorId = $this->getOperatorId();

            // account_cdの取得
            $account = AccountModel::where('m_account_id', $accountId)->first();
            if (!$account) {
                throw new DataNotFoundException("企業ID{{". $data['m_account_id']."}}が見つかりません。");
            }
            $accountCd = $account->account_cd;

            if(empty($data['m_noshi_detail_id'])){

                //新規保存
                $model = new NoshiDetailModel();
                $model->m_account_id        = $accountId;
                $model->update_operator_id  = $operatorId;
                $model->entry_operator_id  = $operatorId;

                //保存
                $model->m_noshi_id                  = $data['m_noshi_id'];
                $model->delete_flg                  = $data['delete_flg'];
                $model->m_noshi_format_id           = $data['m_noshi_format_id'];
                $model->m_noshi_naming_pattern_id   = $data['m_noshi_naming_pattern_id'];

                // アップロードされたファイル名から名前を取得(template_file_nameとして扱う)
                if (isset($data['file']) && $data['file'] instanceof UploadedFile) {
                    $data['template_file_name'] = $data['file']->getClientOriginalName();

                    $model->template_file_name          = $data['template_file_name'];
                }else {
                    //アップロードされたファイルが無い場合はそのまま(新規登録のため空)
                }
    
                $model->save();

            } else {
                //更新保存
                $model = NoshiDetailModel::find($data['m_noshi_detail_id']);
                $model->update_operator_id = $operatorId;

                if(empty($model) || $model->m_account_id != $accountId){
                    throw new DataNotFoundException("熨斗詳細マスタID{{". $data['m_noshi_detail_id']."}}が見つかりません。");
                }
                
                $model->delete_flg                  = $data['delete_flg'];
                $model->m_noshi_naming_pattern_id   = $data['m_noshi_naming_pattern_id'];

                // アップロードされたファイル名から名前を取得(template_file_nameとして扱う)
                if (isset($data['file']) && $data['file'] instanceof UploadedFile) {
                    $data['template_file_name'] = $data['file']->getClientOriginalName();
                    $model->template_file_name          = $data['template_file_name'];
                }else {
                    //アップロードされたファイルが無い場合はそのまま(更新のためtemplate_file_nameはそのまま)
                }
    
                $model->save();
            }

            if (isset($data['file']) && $data['file'] instanceof UploadedFile) {

                $file = $data['file'];
                
                // 保存先ディレクトリの生成
                $noshiDetailId = !empty($model['m_noshi_detail_id']) ? $model['m_noshi_detail_id'] : $model->id;
                $destinationPath = "/{$accountCd}/noshi/template/{$noshiDetailId}";
                $fileName = $data['file']->getClientOriginalName(); // ファイル名


                // 指定ディレクトリ内のすべてのファイルを削除
                $existingFiles = Storage::files($destinationPath);

                try{
                    foreach($existingFiles as $f){
                        Storage::delete($f);
                    }   
                    Storage::putFileAs($destinationPath, $file, $fileName);

                } catch(DataNotFoundException $e){
                    return response()->json(['message' => 'ファイルのアップロードに失敗しました'], 500);
                }
            }

            return response()->json(['message' => '正常に更新されました'], 200);
        });
    }
}
