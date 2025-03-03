<?php

namespace App\Services;

use Illuminate\Support\Facades\Request;
use Illuminate\Support\Str;

class EsmSessionManager
{
    /**
     * ログイン情報
     */
    protected $loginSessionInfo = [];


    public function __construct()
    {
        $this->loginSessionInfo = session('OperatorInfo');
    }

    /**
     * アカウントIDの取得
     */
    public function getAccountId()
    {
        // セッションからアカウントIDを取得する処理を書く
        return $this->getLoginSessionInfo('m_account_id');
    }

    /**
     * アカウントコードの取得
     */
    public function getAccountCode(): string
    {
        // セッションからアカウントコードを取得する処理を書く
        return $this->getLoginSessionInfo('account_cd');
    }

    /**
     * オペレータIDの取得
     */
    public function getOperatorId()
    {
        // セッションからオペレータIDを取得する処理を書く
        return $this->getLoginSessionInfo('m_operators_id');
    }

    /**
     * ログインセッション情報の取得
     */
    public function getLoginSessionInfo($columnName)
    {
        if(isset($this->loginSessionInfo[$columnName])) {
            return $this->loginSessionInfo[$columnName];
        }else{
            return '';
        }
    }

    /**
     * セッション情報を保存し、キーを返却する.
     * セッション情報種別にランダムな文字列を付与した文字列をキーとし、paramsを保存する。
     * またセッション情報種別と発行したランダムな文字列の組み合わせをbase64エンコードし、返却する。
     * 返却した文字列はURLのクエリパラメータとして利用されることを想定する。
     */
    public function setSessionKeyName(string $processName, string $processKeyId, array $params):string
    {
        $sessionKey = Str::random(32);
        session()->put($processName . $sessionKey, $params);
        return base64_encode(json_encode([
            $processKeyId  => $sessionKey
        ]));
    }

    /**
     * セッション情報を取得する.
     */
    public function getSessionKeyName(string $processName, string $processKeyId, string $param):array
    {
        $decodedParams = json_decode(base64_decode($param), true);
        return session($processName . $decodedParams[$processKeyId], []);
    }

    /**
     * セッション情報を削除する.
     */
    public function forgetSessionKeyName(string $processName, string $processKeyId, string $param):void
    {
        $decodedParams = json_decode(base64_decode($param), true);
        session()->forget($processName . $decodedParams[$processKeyId]);
    }
}
