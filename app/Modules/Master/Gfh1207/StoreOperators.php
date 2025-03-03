<?php

namespace App\Modules\Master\Gfh1207;

use App\Events\ModuleCompleted;
use App\Events\ModuleFailed;
use App\Events\ModuleStarted;
use App\Models\Master\Gfh1207\OperatorModel;
use App\Modules\Master\Base\StoreOperatorsInterface;
use App\Services\EsmSessionManager;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use PragmaRX\Google2FA\Google2FA;

class StoreOperators implements StoreOperatorsInterface
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
    public function execute(array $fillData, array $exFillData): Model
    {
        ModuleStarted::dispatch(__CLASS__, compact('fillData', 'exFillData'));
        try {
            // トランザクションを張る
            $new = DB::transaction(function () use ($fillData, $exFillData) {
                $operatorId = $this->esmSessionManager->getOperatorId();
                $new = new OperatorModel();
                $errors = [];

                $g2fa_key = $fillData['g2fa_key'];

                if ($g2fa_key == "0") {
                    $g2fa_key = "0";
                } else {
                    $google2fa = new Google2FA();
                    $g2fa_key = $google2fa->generateSecretKey();
                }

                $hashedPassword = Hash::make($fillData['login_password']);

                $passwordHistory[] = [
                    'hash' => $hashedPassword,
                    'date' => Carbon::now()->toDateTimeString()
                ];

                $new->delete_flg = $fillData['delete_flg'];
                $new->m_operator_name =  $fillData['m_operator_name'];
                $new->m_operator_email = $fillData['m_operator_email'];
                $new->user_type = $fillData['user_type'];
                $new->m_operation_authority_id = $fillData['m_operation_authority_id'];
                $new->cc_authority_code = $fillData['cc_authority_code'];
                $new->login_id = $fillData['login_id'];
                $new->login_password = $hashedPassword;
                $new->g2fa_key =  $g2fa_key;
                // パスワード変更日時の保存
                if (!empty($fillData['login_password'])) {
                    $new->password_update_timestamp = Carbon::now();
                }
                $new->password_history = json_encode($passwordHistory);
                $new->entry_operator_id = $operatorId;
                $new->update_operator_id = $operatorId;
                if (isset($exFillData['m_account_id'])) {
                    $new->m_account_id = $exFillData['m_account_id'];
                } else {
                    // 例外処理
                    $errors['m_account_id']['required'] = __('validation.required', ['attribute' => '企業アカウントID']);
                }

                if (count($errors) > 0) {
                    // ただし、確認画面経由後の登録処理の場合、バリデーションメッセージとして画面に表示することはないと思われる
                    throw new \App\Exceptions\ModuleValidationException(__CLASS__, 0, null, $errors);
                }

                // 保存
                $new->save();

                return $new;
            });
        } catch (\Exception $e) {
            ModuleFailed::dispatch(__CLASS__, compact('fillData', 'exFillData'), $e);
            throw $e;
        }

        ModuleCompleted::dispatch(__CLASS__, [$new->toArray()]);
        return $new;
    }
}
