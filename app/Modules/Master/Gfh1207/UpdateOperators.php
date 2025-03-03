<?php

namespace App\Modules\Master\Gfh1207;

use App\Events\ModuleCompleted;
use App\Events\ModuleFailed;
use App\Events\ModuleStarted;
use App\Exceptions\DataNotFoundException;
use App\Models\Master\Gfh1207\OperatorModel;
use App\Modules\Master\Base\UpdateOperatorsInterface;
use App\Services\EsmSessionManager;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use PragmaRX\Google2FA\Google2FA;

class UpdateOperators implements UpdateOperatorsInterface
{
    /**
     * ESMセッション管理クラス
     */
    protected $esmSessionManager;

    public function __construct(
        EsmSessionManager $esmSessionManager
    ) {
        $this->esmSessionManager = $esmSessionManager;
    }

    /**
     * 更新処理
     * @param int $id 更新対象のID
     * @param array $fillData 更新データ
     * @param array $exFillData fillableに設定されていないデータ
     * @return Model 更新結果(原則としてEloquentのモデルを返す)
     * @throws \App\Exceptions\ModuleValidationException|DataNotFoundException バリデーションエラー時, データが見つからない場合
     */
    public function execute(int $id, array $fillData, array $exFillData): Model
    {
        ModuleStarted::dispatch(__CLASS__, compact('id', 'fillData', 'exFillData'));

        try {
            // トランザクション開始
            $operator = DB::transaction(function () use ($id, $fillData, $exFillData) {
                $operatorId = $this->esmSessionManager->getOperatorId();
                $operator = OperatorModel::findOrFail($id);

                $errors = [];
                // fillできるデータを設定
                $input_g2fa_key = $fillData['g2fa_key'];

                if ($input_g2fa_key == '0') {
                    $operator->g2fa_key = "0";
                } elseif ($input_g2fa_key != "0" && $operator->g2fa_key == "0") {
                    $google2fa = new Google2FA();
                    $operator->g2fa_key = $google2fa->generateSecretKey();
                }

                if (isset($fillData['login_password']) && $fillData['login_password']) {


                    $hashedPassword = Hash::make($fillData['login_password']);

                    $passwordHistory = $operator->password_history ? json_decode($operator->password_history, true) : [];

                    $passwordHistory[] = [
                        'hash' => $hashedPassword,
                        'date' => Carbon::now()->toDateTimeString()
                    ];

                    $operator->login_password = $hashedPassword;
                    $operator->password_update_timestamp = Carbon::now();
                    if (count($passwordHistory) > 6) {
                        $passwordHistory = array_slice($passwordHistory, -6);
                    }
                    $operator->password_history = json_encode($passwordHistory);
                }

                $operator->delete_flg = $fillData['delete_flg'];
                $operator->m_operator_name =  $fillData['m_operator_name'];
                $operator->m_operator_email = $fillData['m_operator_email'];
                $operator->user_type = $fillData['user_type'];
                $operator->m_operation_authority_id = $fillData['m_operation_authority_id'];
                $operator->cc_authority_code = $fillData['cc_authority_code'];
                $operator->login_id = $fillData['login_id'];
                $operator->update_operator_id = $operatorId;

                // 保存
                $operator->save();
                return $operator;
            });
        } catch (ModelNotFoundException $e) {
            ModuleFailed::dispatch(__CLASS__, compact('id', 'fillData', 'exFillData'), $e);
            throw new DataNotFoundException(__('messages.error.data_not_found', ['data' => '社員マスタ', 'id' => $id]), 0, $e);
        } catch (\Exception $e) {
            ModuleFailed::dispatch(__CLASS__, compact('id', 'fillData', 'exFillData'), $e);
            throw $e;
        }

        ModuleCompleted::dispatch(__CLASS__, [$operator->toArray()]);
        return $operator;
    }
}
