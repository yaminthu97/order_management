<?php

namespace App\Modules\Common;

use Carbon\Carbon;
use Config;
use Illuminate\Http\Request;
use mysql_xdevapi\Exception;

/**
 * 認証マネージャー
 *
 * Class LoginManager
 */
class LoginManager extends CommonManager
{
    private $_defValue = [];

    /**
     * コンストラクタ
     */
    public function __construct()
    {
        $this->_defValue = Config::get('Common.const.LoginManager');

        parent::__construct();
    }

    // ログイン認証
    public function login(Request $request)
    {
        $encKey = env('login_key') ?? 'amQtrlOorR4tAak4GPCAsYSBOlNghEqm2wGsHW';
        $iv = env('login_iv') ?? base64_decode('PUvz65KwA2s5IIlx/Zl8yw==');

        $param = [
            'connect_ip' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'referrer' => url()->previous(),
            'account_cd' => $request['account_cd'],
            'login_id' => $request['login_id'],
            'password' => base64_encode(openssl_encrypt($request['password'], 'AES-256-CBC', $encKey, OPENSSL_RAW_DATA, $iv)),
        ];

        unset($encKey);
        unset($iv);

        return $this->callSearchApi($param, 'OperatorLogin', 'common', $request['m_account_id']);
    }

    /**
     * 共通ヘッダー用アラート情報を取得.
     *
     * @param $opeInfo
     *
     * @return array
     *
     * @throws \App\Exceptions\ApiException
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function getComHeadAlertInfo($opeInfo)
    {
        $ecsSerInfo = [
            'delete_flg' => 0,
        ];

        $ecList = $this->callSearchApi(
            $ecsSerInfo,
            'Ecs',
            'master',
            $opeInfo['m_account_id'],
            $opeInfo['m_operators_id']
        );

        if (empty($ecList) || count($ecList['response']['search_result']) === 0) {
            return [];
        }

        $collEcList = collect($ecList['response']['search_result']);
        $grpEcList = $collEcList->groupBy('m_ec_type')->toArray();
        $siteDefList = $this->_defValue['EcSite'];
        $alertMsgList = [];

        foreach ($siteDefList as $site) {
            if (!array_key_exists($site['id'], $grpEcList)) {
                continue;
            }

            $siteList = $grpEcList[$site['id']];
            $ecSpecSerInfo = [
                'delete_flg' => 0,
                'm_account_id' => $opeInfo['m_account_id'],
                'm_ecs_id' => collect($siteList)->map(function ($item, $key) { return $item['m_ecs_id']; })->toArray(),
            ];
            $ecSpecList = $this->callSearchApi($ecSpecSerInfo, $site['namespace']);

            switch ($site['id']) {
                // Yahoo
                case 1:
                    $msgs = $this->makeAlertMsgYahoo($ecSpecList['response']['search_result'], collect($siteList)->first()['m_ec_name']);
                    if (0 < count($msgs)) {
                        $alertMsgList[] = $msgs;
                    }
                    break;

                    // 楽天市場
                case 3:
                    $msgs = $this->makeAlertMsgRakuten($ecSpecList['response']['search_result'], collect($siteList)->first()['m_ec_name']);
                    if (0 < count($msgs)) {
                        $alertMsgList[] = $msgs;
                    }
                    break;

                    // Wowma
                case 5:
                    $msgs = $this->makeAlertMsgWowma($ecSpecList['response']['search_result'], collect($siteList)->first()['m_ec_name']);
                    if (0 < count($msgs)) {
                        $alertMsgList[] = $msgs;
                    }
                    break;

                default:
                    throw new Exception('no site type');
            }
        }

        return collect($alertMsgList)->flatten()->toArray();
    }

    /**
     * 楽天サイト詳細情報を基に認証キーの有効期限に関するアラートメッセージを生成して返却.
     *
     * @param $siteSpecList
     * @param $ecSiteName
     *
     * @return array
     */
    private function makeAlertMsgRakuten($siteSpecList, $ecSiteName)
    {
        $chkDate = Carbon::now($this->timeZone);
        $msgList = [];

        foreach ($siteSpecList as $siteSpec) {
            if (!empty($siteSpec['api_auth_key_expiration_date'])) {
                $expDate = Carbon::parse($siteSpec['api_auth_key_expiration_date']);
                if ($expDate->subDay($this->_defValue['expirationAlertStartDays']) <= $chkDate) {
                    $msgList[] = [
                        sprintf(
                            $this->_defValue['AlertMsgTemplate'],
                            $ecSiteName,
                            'API認証キー有効期限',
                            $siteSpec['api_auth_key_expiration_date']
                        ),
                    ];
                }
            }

            if (!empty($siteSpec['api_new_auth_key_expiration_date'])) {
                $expDate = Carbon::parse($siteSpec['api_new_auth_key_expiration_date']);
                if ($expDate->subDay($this->_defValue['expirationAlertStartDays']) <= $chkDate) {
                    $msgList[] = [
                        sprintf(
                            $this->_defValue['AlertMsgTemplate'],
                            $ecSiteName,
                            'API新方式ライセンスキー有効期限',
                            $siteSpec['api_new_auth_key_expiration_date']
                        ),
                    ];
                }
            }

            if (!empty($siteSpec['ftp_server_password_expiration_date'])) {
                $expDate = Carbon::parse($siteSpec['ftp_server_password_expiration_date']);
                if ($expDate->subDay($this->_defValue['expirationAlertStartDays']) <= $chkDate) {
                    $msgList[] = [
                        sprintf(
                            $this->_defValue['AlertMsgTemplate'],
                            $ecSiteName,
                            'FTPサーバーログインパスワード有効期限',
                            $siteSpec['ftp_server_password_expiration_date']
                        ),
                    ];
                }
            }
        }

        return $msgList;
    }

    /**
     * Yahooサイト詳細情報を基に認証キーの有効期限に関するアラートメッセージを生成して返却.
     *
     * @param $siteSpecList
     * @param $ecSiteName
     *
     * @return array
     */
    private function makeAlertMsgYahoo($siteSpecList, $ecSiteName)
    {
        $chkDate = Carbon::now($this->timeZone);
        $msgList = [];

        foreach ($siteSpecList as $siteSpec) {
            if (!empty($siteSpec['secret_key_expiration_date'])) {
                $expDate = Carbon::parse($siteSpec['secret_key_expiration_date']);
                if ($expDate->subDay($this->_defValue['expirationAlertStartDays']) <= $chkDate) {
                    $msgList[] = [
                        sprintf(
                            $this->_defValue['AlertMsgTemplate'],
                            $ecSiteName,
                            'シークレットキー有効期限',
                            $siteSpec['secret_key_expiration_date']
                        ),
                    ];
                }
            }

            if (!empty($siteSpec['ftp_server_password_expiration_date'])) {
                $expDate = Carbon::parse($siteSpec['ftp_server_password_expiration_date']);
                if ($expDate->subDay($this->_defValue['expirationAlertStartDays']) <= $chkDate) {
                    $msgList[] = [
                        sprintf(
                            $this->_defValue['AlertMsgTemplate'],
                            $ecSiteName,
                            'FTPサーバーログインパスワード有効期限',
                            $siteSpec['ftp_server_password_expiration_date']
                        ),
                    ];
                }
            }
        }

        return $msgList;
    }

    /**
     * Wowmaサイト詳細情報を基に認証キーの有効期限に関するアラートメッセージを生成して返却.
     *
     * @param $siteSpecList
     * @param $ecSiteName
     *
     * @return array
     */
    private function makeAlertMsgWowma($siteSpecList, $ecSiteName)
    {
        $chkDate = Carbon::now($this->timeZone);
        $msgList = [];

        foreach ($siteSpecList as $siteSpec) {
            if (!empty($siteSpec['auth_key_limit_date'])) {
                $expDate = Carbon::parse($siteSpec['auth_key_limit_date']);
                if ($expDate->subDay($this->_defValue['expirationAlertStartDays']) <= $chkDate) {
                    $msgList[] = [
                        sprintf(
                            $this->_defValue['AlertMsgTemplate'],
                            $ecSiteName,
                            '認証キー有効期限',
                            $siteSpec['auth_key_limit_date']
                        ),
                    ];
                }
            }
        }

        return $msgList;
    }
}
