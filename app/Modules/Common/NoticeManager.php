<?php

namespace App\Modules\Common;

use Config;
use Illuminate\Http\Request;

/**
 * お知らせ検索.
 *
 * Class NoticeManager
 */
class NoticeManager extends CommonManager
{
    private $_config;
    private $_namespace = 'Notice';

    /**
     * コンストラクタ
     */
    public function __construct()
    {
        parent::__construct();
        $this->_config = Config::get('Common.const.NoticeManager');
    }

    /**
     * お知らせを検索する.
     */
    public function searchNoticeInfo(Request $request, $method)
    {
        $opeInfo = session()->get('OperatorInfo');
        if (empty($opeInfo)) {
            return [];
        }

        $req = $request->all();
        unset($req['_token']);

        $search_info = ['delete_flg' => 0];

        $condText = collect($req)->filter(function ($value, $key) {
            return strpos($key, 'notice_pri_typ') === false && !empty($value);
        });

        $condChkBox = collect($req)->filter(function ($value, $key) {
            return -1 < strpos($key, 'notice_pri_typ');
        });

        // TODO: お知らせ情報取得APIで以上・以下の検索条件に対応された時に公開日に関する検索条件を追加する。
        foreach ($condText as $key => $item) {
            if ($key === 'notice_msg_inc') {
                $search_info += ['notice_message' => $item];
                $search_info += [$key.'_search_mode' => 1];
            } elseif ($key === 'notice_msg_not_inc') {
                // TODO: お知らせ情報取得APIで not like 対応がされた時に検索条件を追加する。
                $search_info += ['notice_message' => $item];
            } else {
                $search_info += [$key => $item];
            }
        }

        if (0 < count($condChkBox)) {
            $chkVal = [];
            foreach ($condChkBox as $key => $item) {
                $chkVal[] = $item;
            }
            $search_info += ['notice_priority' => $chkVal];
        }

        $json = [
            'request' => [
                'm_account_id' => $opeInfo['m_account_id'],
                'operator_id' => $opeInfo['m_operators_id'],
                'feature_id' => str_replace(Config::get('Master.const.APP_URL'), '', url()->current()),
                'display_csv_flag' => 0,
                'search_info' => $search_info,
            ],
        ];

        $response = $this->callApi('POST', 'gcommon/'.$this->getVer().'/searchNotices', $json, 'gcommon');
        $body = json_decode($response->getBody(), true);
        if (!empty($body['response']['error']['code'])) {
            dd($body);
        }

        return $body['response']['search_result'];
    }
}
