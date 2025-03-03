<?php

namespace App\Http\Middleware;

use Closure;
use Validator;

/**
 * ログイン認証ミドルウェア.
 */
class Login
{
    private $_subsystem = 'Common';
    private $_namespace = 'Login';
    private $_ver = 'v1_0';
    private $_managerPath = '';
    private $_editFormStructuresPath = '';
    private $_redirectAuth = '';

    /**
     * @var \App\Http\Managers\Common\v1_0\LoginManager
     */
    private $_manager = null;

    private $_editFormStructures = null;
    private $_redirectLoginUrl = '';

    /**
     * コンストラクター
     */
    public function __construct()
    {
        $this->_managerPath = 'App\\Http\\Managers\\'.$this->_subsystem.'\\'.$this->_ver.'\\';
        $this->_editFormStructuresPath = 'App\\Http\\FormStructures\\'.$this->_subsystem.'\\'.$this->_ver.'\\';
        $this->_redirectLoginUrl = '/'.strtolower($this->_subsystem).'/login/';

        $this->initManager();
        $this->initEditFormStructures();
    }

    /**
     * マネージャを読み込んで返す.
     */
    protected function initManager()
    {
        $path = $this->_managerPath.$this->_namespace.'Manager';
        $this->_manager = new $path($this->_namespace);
    }

    /**
     * フォームストラクチャーを読み込んで返す.
     */
    protected function initEditFormStructures()
    {
        $path = $this->_editFormStructuresPath.'Edit'.$this->_namespace.'FormStructure';
        $this->_editFormStructures = new $path();
    }

    /**
     * Handle an incoming request.
     *
     * @param \Illuminate\Http\Request $request
     * @param \Closure                 $next
     *
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        $formData = $request->all();

        // リクエストパラメータにログインフォームパラメータが含まれている場合はログイン認証処理を実施
        if (!array_key_exists('account_cd', $formData) ||
            !array_key_exists('login_id', $formData) ||
            !array_key_exists('password', $formData)) {
            return redirect()->action($this->_redirectAuth);
        }

        // 認証情報を破棄
        session()->forget('OperatorInfo');
        session()->forget('AuthResponse');
        session()->forget('LoginFormValidationErrors');

        $validator = Validator::make(
            $formData,
            $this->_editFormStructures->getValidationRule(),
            $this->_editFormStructures->getValidationErrorMessages()
        );

        if ($validator->fails()) {
            session()->put('LoginFormValidationErrors', $validator->errors());

            return $next($request);
        }

        $loginRes = $this->_manager->login($request);

        if (empty($loginRes['response']['result']['error'])) {
            $noticeInfo = $this->makeNoticeInfo($this->_manager, $loginRes);
            $alertInfo = $this->makeAlertInfo($this->_manager, $loginRes);
            $loginRes['response']['search_result'] += [
                'CommonHeader' => [
                    'NoticeInfo' => $noticeInfo,
                    'AlertInfo' => $alertInfo,
                ],
            ];
        }

        session()->put('AuthResponse', $loginRes['response']);

        return $next($request);
    }

    /**
     * 共通ヘッダーに表示するためのお知らせ一覧を生成.
     *
     * @param $manager
     * @param $loginRes
     *
     * @return array
     */
    protected function makeNoticeInfo($manager, $loginRes)
    {
        $noticeRes = $manager->searchNotice(
            false,
            $loginRes['response']['search_result']['m_account_id'],
            $loginRes['response']['search_result']['m_operators_id']
        );

        return collect($noticeRes)->map(function ($item, $key) {
            return $item['notice_title'];
        })->toArray();
    }

    /**
     * 共通ヘッダーに表示するためのアラート情報一覧を生成.
     *
     * @param $manager
     * @param $loginRes
     *
     * @return array
     */
    protected function makeAlertInfo($manager, $loginRes)
    {
        $alertInfo = $this->_manager->getComHeadAlertInfo($loginRes['response']['search_result']);
        $alertRes = $manager->searchBatchInstruction(
            $loginRes['response']['search_result']['m_account_id'],
            $loginRes['response']['search_result']['m_operators_id']
        );

        return array_merge(
            $alertInfo,
            collect($alertRes)->map(function ($item, $key) {
                return $item['execute_result'];
            })->toArray()
        );
    }
}
