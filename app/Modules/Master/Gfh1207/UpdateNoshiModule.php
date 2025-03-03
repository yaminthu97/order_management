<?php

namespace App\Modules\Master\Gfh1207;

use App\Enums\DeleteFlg;
use App\Enums\ItemNameType;
use App\Exceptions\DataNotFoundException;
use App\Models\Master\Base\NoshiModel;
use App\Models\Master\Base\NoshiFormatModel;
use App\Modules\Common\CommonModule;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\Log;

use Config;
use DB;

/**
 * 熨斗マスタ保存
 */
class UpdateNoshiModule extends CommonModule
{
    public function execute($id, array $params){
        $accountId = $this->getAccountId();
        $operatorId = $this->getOperatorId();

        // 熨斗マスタモデルの作成（IDがある場合はDBから取得）
        if( empty($id) ){
            $noshi = new NoshiModel();
            $noshi->m_account_id = $accountId;
            $noshi->entry_operator_id = $operatorId;
        } else {
            $noshi = NoshiModel::query()
            ->where('m_account_id', $accountId)
            ->where('m_noshi_id', $id)
            ->first();
        }
        if( empty($noshi) ){
            throw new DataNotFoundException(__('messages.error.data_not_found',[
                'data' => '熨斗',
                'id' => $id
            ]));
        }

        try{
            // トランザクション開始
            $noshi = DB::transaction(function () use ($noshi, $params, $accountId, $operatorId) {
                // 熨斗マスタ保存
                $params['update_operator_id'] = $operatorId;
                $noshi->fill( $params );
                $noshi->save();

                // 熨斗種類がある場合、熨斗種類保存
                if( isset( $params['noshiFormatList'] ) ){
                    foreach( $params['noshiFormatList'] as $formatParams ){
                        if( empty( $formatParams['m_noshi_format_id'] ) ){
                            // 熨斗種類IDが空（新規行）で種類名が未設定の場合は登録しない
                            if( empty($formatParams['noshi_format_name']) ){
                                continue;
                            }
                            $format = new NoshiFormatModel();
                            $format->m_noshi_id = $noshi->m_noshi_id;
                            $format->m_account_id = $accountId;
                            $format->entry_operator_id = $operatorId;
                        } else {
                            $format = NoshiFormatModel::query()
                            ->where('m_noshi_id', $noshi->m_noshi_id)
                            ->where('m_noshi_format_id', $formatParams['m_noshi_format_id'])
                            ->where('m_account_id', $accountId)
                            ->first();
                        }
                        if( empty($format) ){
                            throw new DataNotFoundException(__('messages.error.data_not_found',[
                                'data' => '熨斗種類マスタ',
                                'id' => $formatParams['m_noshi_format_id']
                            ]));
                        }
                        $formatParams['update_operator_id'] = $operatorId;
                        $format->fill( $formatParams );
                        $format->save();
                    }
                }
                return $noshi;
            });

        }catch(ModelNotFoundException $e){
            throw new DataNotFoundException(__('messages.error.data_not_found', ['data' => '熨斗マスタ', 'id' => $id]), 0, $e);
        }

        return $noshi;
    }
}
