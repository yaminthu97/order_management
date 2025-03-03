<?php

namespace App\Http\Controllers\Customer;

use App\Http\Requests\Gfh1207\CcCustomerListRequest;
use App\Modules\Common\Base\GetPrefecturalInterface;
use App\Modules\Customer\Base\SearchCcCustomerListInterface;
use App\Modules\Master\Base\GetItemnameTypeInterface;
use App\Services\EsmSessionManager;
use Config;
use Illuminate\Http\Request;
use App\Enums\ItemNameType;

class CcCustomerListController
{
    protected $esmSessionManager;
    /**
     * 外部連携で使用するアプリの一覧
     */
    protected $postAppList = [
        'mail-dealer',
    ];

    /**
     * 外部連携かどうかを判断するパラメータ
     */
    protected $postAppKeyName = 'post_app';

    /**
     * 電話番号の復号化に使用するAESキー
     */
    protected $aesKey;

    //完全一致でヒットした場合に自動で照会画面に移行するかどうか判定する項目
    protected $checkInfoColumn = [
        'tel1'			=> 'tel',
        'tel2'			=> 'tel',
        'tel3'			=> 'tel',
        'tel4'			=> 'tel',
        'm_cust_id'		=> 'm_cust_id',
        'cust_cd'		=> 'cust_cd',
        'email1'		=> 'email',
        'email2'		=> 'email',
        'email3'		=> 'email',
        'email4'		=> 'email',
        'email5'		=> 'email',
    ];

    public function __construct()
    {
        $this->aesKey = config('env.aes.aes_key');
        $this->esmSessionManager = new EsmSessionManager();
    }

    public function list(
        Request $request,
        GetPrefecturalInterface $getPrefecture,
        GetItemnameTypeInterface $getItemnameType,
        $phone = null
    ) {
        $req = $request->all();
        $submit = $this->getSubmitName($req);
        $req['m_account_id'] = $this->esmSessionManager->getAccountId();

        $prefecture = $getPrefecture->execute();
        $customerRunk = $getItemnameType->execute(ItemNameType::CustomerRank->value);

        if (isset($prefecture['error']) || isset($customerRunk['error'])) {
            // view 向けデータ
            $viewExtendData = [
                'pref'      => [],
                'custrunk'  => []
            ];
            session()->flash('messages.error', ['message' => __('messages.error.connection_error')]);

        } else {
            $viewExtendData = [
                'pref'      => $prefecture,
                'custrunk'  => $customerRunk
            ];

            // リダイレクト時のセッションから呼び出される場合
            if(session()->exists('outside_post_redirect')) {
                $outsidePostRedirect = session()->get('outside_post_redirect');
                if(!empty($outsidePostRedirect[$this->postAppKeyName])) {
                    $req[$this->postAppKeyName] = $outsidePostRedirect[$this->postAppKeyName];

                    switch($outsidePostRedirect[$this->postAppKeyName]) {
                        case 'mail-dealer':
                            //MailDealerからの呼び出し
                            $searchRow['email'] = $outsidePostRedirect['email'];
                            break;
                        default:
                            break;
                    }
                }
                session()->forget('outside_post_redirect');
            }

            // 暗号化方式は AES、Base64で文字列コード化する
            if (isset($phone)) {
                $decryptedPhone = $this->decryptPhoneNumber($phone);
                $searchRow['tel'] = $decryptedPhone;
            }
        }

        $searchRow['tel_forward'] = '1';
        $searchRow['fax_forward'] = '1';

        // view 向け項目初期値
        $paginator ??= null;
        $viewExtendData ??= null;
        $searchRow ??= $req;

        $compact = [
            'paginator',
            'viewExtendData',
            'searchRow',
        ];

        return account_view('CcCustomer.gfh_1207.list', compact($compact));
    }

    public function postList(
        CcCustomerListRequest $request,
        SearchCcCustomerListInterface $search,
        GetPrefecturalInterface $getPrefecture,
        GetItemnameTypeInterface $getItemnameType,
    ) {
        $req = $request->all();
        $submit = $this->getSubmitName($req);
        $req['m_account_id'] = $this->esmSessionManager->getAccountId();


        $prefecture = $getPrefecture->execute();
        $customerRunk = $getItemnameType->execute(ItemNameType::CustomerRank->value);

        if (isset($prefecture['error']) || isset($customerRunk['error'])) {
            // view 向けデータ
            $viewExtendData = [
                'pref'      => [],
                'custrunk'  => []
            ];
            session()->flash('messages.error', ['message' => __('messages.error.connection_error')]);

        } else {
            // view 向けデータ
            $viewExtendData = [
                'pref'      => $getPrefecture->execute(),
                'custrunk'  => $getItemnameType->execute(ItemNameType::CustomerRank->value),
                'list_sort' => [
                    'column_name'   => 'newest_order_date',
                    'sorting_shift' => 'desc'
                ],
                'page_list_count' => Config::get('Common.const.disp_limits')
            ];

            $options = [
                'should_paginate' => true,
                'limit' => $req['page_list_count'] ?? null,
                'page' => $req['hidden_next_page_no'] ?? null,
                'with' => [
                    'page',
                ],
            ];

            if (isset($req['sorting_column'])) {
                if (isset($req['sorting_shift'])) {
                    $options['sorts'][$req['sorting_column']] = $req['sorting_shift'];
                } else {
                    $options['sorts'] = $req['sorting_column'];
                }
            }

            // 検索処理
            $paginator = $search->execute($req, $options);
            if (isset($paginator['error'])) {
                $paginator = null;
                session()->flash('messages.error', ['message' => __('messages.error.connection_error')]);
            } else {

                if($request['sorting_column'] && $request['sorting_shift']) {
                    $viewExtendData['list_sort'] = [
                        'column_name'   => $request['sorting_column'],
                        'sorting_shift' => $request['sorting_shift']
                    ];
                }

                $searchResult['search_record_count'] = $paginator->count();
                $searchResult['total_record_count'] = $paginator->total();

                if (isset($submit) && $submit === 'search') {
                    // 1行に特定できた場合は照会画面に遷移する
                    if($this->checkRedirectInfo($paginator, $req)) {
                        $getId = $paginator[0]['m_cust_id'];
                        return redirect()->route('cc.cc-customer.info', ['id' => $getId]);
                    }
                }

                // 顧客新規登録ボタン押下時
                if (isset($submit) && $submit === 'custnew') {
                    $tel = $request->input('tel');
                    $name_kanji = $request->input('name_kanji');
                    $name_kana = $request->input('name_kana');
                    $postal = $request->input('postal');
                    $address1 = $request->input('address1');
                    $address2 = $request->input('address2');
                    $email = $request->input('email');

                    $custnewData = [
                        'previous_url' => 'cc-customer/list',
                        'previous_subsys' => 'cc',
                    ];

                    if (!empty($tel) || !empty($name_kanji) || !empty($name_kana) ||
                    !empty($postal) || !empty($address1) || !empty($address2) || !empty($email)) {
                        $custnewData += [
                            'tel' => $tel,
                            'name_kanji' => $name_kanji,
                            'name_kana' => $name_kana,
                            'postal' => $postal,
                            'address1' => $address1,
                            'address2' => $address2,
                            'email' => $email,
                        ];
                    }

                    session()->flash('_old_input', $custnewData);

                    return redirect()->route('cc.customer.new')->with('custnewData', $custnewData);
                }
            }
        }

        // view 向け項目初期値
        $searchResult ??= [];
        $paginator ??= null;
        $viewExtendData ??= null;
        $searchRow ??= $req;

        $compact = [
            'searchResult',
            'paginator',
            'viewExtendData',
            'searchRow',
        ];

        return account_view('CcCustomer.gfh_1207.list', compact($compact));
    }

    public function new(Request $request)
    {
        $custnewData = $request->session()->get('custnewData');
        return account_view('customer.new', compact('custnewData'));
    }

    /**
     * submitボタン内容の取得
     */
    protected function getSubmitName($req)
    {
        $submitName = '';
        if(!empty($req['submit'])) {
            $submitName = $req['submit'];
        }
        return $submitName;
    }

    /**
     * 電話番号の復号化
     */
    public function decryptPhoneNumber($encryptedPhoneNumber)
    {
        // URL、Base64のデコードを行う
        $encrypted_text = urldecode($encryptedPhoneNumber);

        $data = base64_decode($encrypted_text);

        $iv = substr($data, 0, 16);                         // 文字列の先頭16文字がIVとしてセットされている
        $encrypted_data = substr($data, 16);                // 複合化対象の文字列
        
        $decrypted_data = openssl_decrypt(
            $encrypted_data,
            'AES-256-CBC',
            $this->aesKey,
            OPENSSL_RAW_DATA,
            $iv);

        return rtrim($decrypted_data, "\0");                // 余分な\0をトリミングして戻す
    }

    /**
     * 照会画面に直接移動するかどうか
     */
    protected function checkRedirectInfo($dataResult, $req)
    {
        // 検索結果が1行でなければNG
        if(count($dataResult) != 1) {
            return false;
        }

        $dataRow = $dataResult[0];

        // 検索結果が1行の場合
        foreach($this->checkInfoColumn as $key => $row) {
            // 検索フォームに入力されている場合のみ比較
            if(isset($req[$row]) && strlen($req[$row]) > 0) {
                // 設定されているデータと検索入力の値が一致していればOK
                if($row == 'tel') {
                    // 電話番号の場合はハイフン抜きで比較
                    $dataPhoneNum = str_replace('-', '', $dataRow[$key]);
                    $reqPhoneNum = str_replace('-', '', mb_convert_kana($req[$row], 'a'));

                    if($dataPhoneNum == $reqPhoneNum) {
                        return true;
                    }
                } else {
                    // それ以外は単純に完全一致
                    if($dataRow[$key] == $req[$row]) {
                        return true;
                    }
                }
            }
        }
        return false;
    }

}
