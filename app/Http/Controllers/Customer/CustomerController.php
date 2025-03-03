<?php

namespace App\Http\Controllers\Customer;

use App\Enums\ItemNameType;
use App\Exceptions\DataNotFoundException;
use App\Http\Requests\Gfh1207\RegisterCustomerRequests;
use App\Modules\Common\Base\GetPrefecturalInterface;
use App\Modules\Customer\Base\CheckCustomerInterface;
use App\Modules\Customer\Base\FindCustomerInterface;
use App\Modules\Customer\Base\GetPostalCodeInterface;
use App\Modules\Customer\Base\StoreCustomerInterface;
use App\Modules\Master\Base\GetCustomerRankInterface;
use App\Modules\Master\Base\GetItemnameTypeInterface;
use App\Services\EsmSessionManager;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class CustomerController
{
    protected $className = 'Customer';
    protected $namespace = 'Customer';
    protected $searchViewId = 'NECSM0110';
    protected $registerViewId = 'NECSM0112';
    protected $notifyViewId = 'NECSM0113';

    protected $registerSessionName = '';
    protected $masterFlg = true;
    protected $esmSessionManager;
    protected $searchPrimaryKey = 'm_cust_id';
    public const NAME_MATCH_EXEC_FLAG = 1;
    public const POSTAL_LENGTH = 7;
    public const POSTAL_START_INDEX = 0;
    public const POSTAL_END_INDEX = 3;
    public const DELETE_BUTTON_CLICK = 'delete';
    public const REGISTER_BUTTON_CLICK = 'register';
    public const CANCEL_BUTTON_CLICK = 'cancel';
    public const GET_PARAM_KEY = 'params';
    public const DELETE_FLG = '0';
    public const INITIAL_DEFAULT_VALUE = '0';
    public const RANDOM_CHARACTER_LENGTH = 32;
    public const EMPTY_LENGTH_CHECK = 0;
    public const BIRTHDAY_DEFAULT_VALUE = '0000-00-00';

    public function __construct()
    {
        $this->esmSessionManager = new EsmSessionManager();
    }

    public function new(
        Request $request,
        GetPrefecturalInterface $getPrefecture,
        GetCustomerRankInterface $getCustomerRank,
        GetItemnameTypeInterface $getItemnameType,
    ) {
        $prefecture = $getPrefecture->execute();
        $customerRunk = $getCustomerRank->execute();
        $itemnameType = $getItemnameType->execute(ItemNameType::CustomerType->value, self::DELETE_FLG, true);

        $editRow = $request->all();

        if (isset($prefecture['error']) || isset($customerRunk['error']) || isset($itemnameType['error'])) {
            $viewExtendData = [
                'pref'            => [],
                'cust_runk_list'  => [],
                'item_name_type'  => []
            ];
            session()->flash('messages.error', ['message' => __('messages.error.connection_error')]);
            return account_view('customer.gfh_1207.edit', compact('viewExtendData', 'editRow'));

        }
        $viewExtendData = [
            'pref'            => $prefecture,
            'cust_runk_list'  => $customerRunk,
            'item_name_type'  => $itemnameType
        ];

        try {
            if (session()->exists('_old_input')) {
                //前画面の内容引き継ぎ
                $oldInput = session()->get('_old_input');
                if (empty($editRow['cust_cd']) && isset($oldInput['cust_cd'])) {
                    $editRow['cust_cd'] = $oldInput['cust_cd'];
                }
                if (empty($editRow['name_kanji']) && isset($oldInput['name_kanji'])) {
                    $editRow['name_kanji'] = $oldInput['name_kanji'];
                }
                if (empty($editRow['name_kana']) && isset($oldInput['name_kana'])) {
                    $editRow['name_kana'] = $oldInput['name_kana'];
                }
                if (empty($editRow['tel']) && isset($oldInput['tel'])) {
                    $editRow['tel1'] = $oldInput['tel'];
                }
                if (empty($editRow['postal']) && isset($oldInput['postal'])) {
                    $editRow['postal'] = $oldInput['postal'];
                }
                if (empty($editRow['email']) && isset($oldInput['email'])) {
                    $editRow['email1'] = $oldInput['email'];
                }
                if (empty($editRow['address1']) && isset($oldInput['address1'])) {
                    $editRow['address1'] = $oldInput['address1'];
                }
                if (empty($editRow['address2']) && isset($oldInput['address2'])) {
                    $editRow['address2'] = $oldInput['address2'];
                }

                //全画面判定（同一subsys）
                $this->masterFlg = false;
                if (isset($oldInput['previous_url']) && strlen($oldInput['previous_url']) > self::EMPTY_LENGTH_CHECK) {
                    $editRow += [
                        'previous_url' => $oldInput['previous_url'],
                        'previous_subsys' => $oldInput['previous_subsys'],
                    ];
                }
            }
        } catch (Exception $e) {
            Log::error('Session data not found: ' . $e->getMessage());
        }
        return account_view('customer.gfh_1207.edit', compact('viewExtendData', 'editRow'));

    }

    public function postNew(
        RegisterCustomerRequests $request,
        CheckCustomerInterface $checkCustomer
    ) {
        $editRow = [];

        try {
            $sessionKeyId = config('define.cc.session_key_id');
            $sessionKey = $this->getSessionKey($request, $editRow, $sessionKeyId);

            if (empty($sessionKey)) {
                $sessionKey = $this->makeRandomKey();
            }

            $this->registerSessionName .= $sessionKey;

            if (session()->exists($this->registerSessionName)) {
                $editRow = session()->get($this->registerSessionName);
                session()->forget($this->registerSessionName);
            } else {
                if (!empty($request->all())) {
                    $editRow = $request->all();
                }

                //パラメータをデコードしてセット
                $editRow = $this->decodeGetParameter($editRow);
                $editRow = $this->setNewPresetData($editRow, $request);
            }

            if (empty($editRow[$sessionKeyId])) {
                $editRow[$sessionKeyId] = $sessionKey;
            }

            $editRow = $request->all();
            $editRow['data_key_id'] = $this->registerSessionName;

            // 名寄せ実行フラグ
            if (session()->exists('name_match_exec_flag')) {
                $editRow['name_match_exec_flag'] = session()->get('name_match_exec_flag');
            } else {
                $editRow['name_match_exec_flag'] = self::NAME_MATCH_EXEC_FLAG;
            }

            if (isset($editRow['name_match_exec_flag']) && $editRow['name_match_exec_flag'] == self::NAME_MATCH_EXEC_FLAG) {
                $getDuplicateCustomerId = $checkCustomer->execute($editRow);

                if (isset($getDuplicateCustomerId['error'])) {
                    return redirect()->route('cc.customer.new');
                }

                if ($getDuplicateCustomerId != self::INITIAL_DEFAULT_VALUE) {
                    $editRow['m_cust_id'] = $getDuplicateCustomerId;
                }
            }

            try {
                if (session()->exists('_old_input')) {
                    //全画面判定（同一subsys）
                    $this->masterFlg = false;
                    $old = session()->get('_old_input');
                    if (isset($old['previous_url']) && strlen($old['previous_url']) > self::EMPTY_LENGTH_CHECK) {
                        $editRow += [
                            'previous_url' => $old['previous_url'],
                            'previous_subsys' => $old['previous_subsys'],
                        ];
                    }
                }
            } catch (Exception $e) {
                Log::error('Session data not found: ' . $e->getMessage());
            }

            session()->put($this->registerSessionName, $editRow);
            $b64_param_url = $this->makeUrlParams($request, $editRow);

            return redirect(route('cc.customer.notify', $b64_param_url))->withInput($editRow);
        } catch (\Exception $e) {
            Log::error('Database connection error: ' . $e->getMessage());
            return redirect()->route('cc.customer.new');
        }
    }

    public function edit(
        FindCustomerInterface $findCustomer,
        GetPrefecturalInterface $getPrefecture,
        GetCustomerRankInterface $getCustomerRank,
        StoreCustomerInterface $storeCustomer,
        GetItemnameTypeInterface $getItemnameType,
        $id
    ) {
        $prefecture = $getPrefecture->execute();
        $customerRunk = $getCustomerRank->execute();
        $itemnameType = $getItemnameType->execute(ItemNameType::CustomerType->value, self::DELETE_FLG, true);

        if (isset($prefecture['error']) || isset($customerRunk['error']) || isset($itemnameType['error'])) {
            $viewExtendData = [
                'pref'            => [],
                'cust_runk_list'  => [],
                'item_name_type'  => []
            ];
            session()->flash('messages.error', ['message' => __('messages.error.connection_error')]);
            return redirect()->route('cc.customer.list');
        }
        $viewExtendData = [
            'pref'            => $prefecture,
            'cust_runk_list'  => $customerRunk,
            'item_name_type'  => $itemnameType
        ];

        try {
            // Eloquentで1件取得
            $editRow = $findCustomer->execute($id);
            $editRow = $this->setEditRow($editRow);
            return account_view('customer.gfh_1207.edit', compact('viewExtendData', 'editRow'));

        } catch (DataNotFoundException $e) {
            // データが見つからなかった場合のエラーハンドリング
            return redirect()->route('cc.customer.list')->with([
                'messages.error' => ['message' => __('messages.error.data_not_found', ['data' => '顧客', 'id' => $id])]
            ]);
        }

    }

    public function postEdit(
        RegisterCustomerRequests $request,
        $id
    ) {
        $editRow = $request->validated();

        try {
            $sessionKeyId = config('define.cc.session_key_id');
            $sessionKey = $this->getSessionKey($request, $editRow, $sessionKeyId);

            if (empty($sessionKey)) {
                $sessionKey = $this->makeRandomKey();
            }

            $this->registerSessionName .= $sessionKey;

            if (session()->exists($this->registerSessionName)) {
                $editRow = $request->all();
                session()->forget($this->registerSessionName);
            } else {
                if (!empty($request->all())) {
                    $editRow = $request->all();
                }
                //パラメータをデコードしてセット
                $editRow = $this->decodeGetParameter($editRow);
            }

            if (empty($editRow[$sessionKeyId])) {
                $editRow[$sessionKeyId] = $sessionKey;
            }

            $editRow['data_key_id'] = $this->registerSessionName;
            $editRow['m_cust_id'] = $id;
            $submit = $this->getSubmitName($request);

            try {
                if (session()->exists('_old_input')) {
                    //全画面判定（同一subsys）
                    $this->masterFlg = false;
                    $old = session()->get('_old_input');
                    if (isset($old['previous_url']) && strlen($old['previous_url']) > self::EMPTY_LENGTH_CHECK) {
                        $editRow += [
                            'previous_url' => $old['previous_url'],
                            'previous_subsys' => $old['previous_subsys'],
                        ];
                    }
                }
            } catch (Exception $e) {
                Log::error('Session data not found: ' . $e->getMessage());
            }

            // 削除の場合
            if ($submit == self::DELETE_BUTTON_CLICK) {
                $editRow['delete_operator_id'] = $this->esmSessionManager->getAccountId();
            } else {
                if (isset($editRow['delete_operator_id'])) {
                    unset($editRow['delete_operator_id']);
                }
            }

            session()->put($this->registerSessionName, $editRow);
            $b64_param_url = $this->makeUrlParams($request, $editRow);

            return redirect(route('cc.customer.notify', $b64_param_url))->withInput($editRow);

        } catch (DataNotFoundException $e) {
            // データが見つからなかった場合のエラーハンドリング
            return redirect()->route('cc.customer.list')->with([
                'messages.error' => ['message' => __('messages.error.data_not_found', ['data' => '顧客', 'id' => $id])]
            ]);
        }

    }

    public function notify(
        Request $request,
        GetCustomerRankInterface $getCustomerRank,
        GetItemnameTypeInterface $getItemnameType
    ) {
        $customerRunk = $getCustomerRank->execute();
        $itemnameType = $getItemnameType->execute(ItemNameType::CustomerType->value, self::DELETE_FLG, true);

        if (isset($customerRunk['error']) || isset($itemnameType['error'])) {
            $viewExtendData = [
                'pref'            => [],
                'cust_runk_list'  => [],
                'item_name_type'  => []
            ];
            session()->flash('messages.error', ['message' => __('messages.error.connection_error')]);
            return redirect()->route('cc.customer.list');
        }
        $viewExtendData = [
            'cust_runk_list'  => $customerRunk,
            'item_name_type'  => $itemnameType
        ];
        $sessionKeyId = config('define.cc.session_key_id');
        $sessionKey = $this->getSessionKey($request, [], $sessionKeyId);
        $this->registerSessionName .= $sessionKey;

        $editRow = session()->get($this->registerSessionName);
        $editRow = $this->setEditRow($editRow);

        return account_view('customer.gfh_1207.notify', compact('editRow', 'viewExtendData'));

    }

    public function postNotify(
        Request $request,
        StoreCustomerInterface $storeCustomer
    ) {
        $submit = $this->getSubmitName($request);
        $sessionKeyId = config('define.cc.session_key_id');
        $sessionKey = $this->getSessionKey($request, [], $sessionKeyId);
        $this->registerSessionName .= $sessionKey;

        $editRow = session()->get($this->registerSessionName);
        $editRow = $this->setEditRow($editRow);
        $editRow = $this->dataPrepare($editRow);

        // 削除の場合
        if ($editRow['submit'] == self::DELETE_BUTTON_CLICK) {
            $editRow['delete_timestamp'] = Carbon::now();
        } else {
            if (isset($editRow['delete_operator_id'])) {
                unset($editRow['delete_operator_id']);
            }
        }

        // 登録をクリックする
        if ($submit == self::REGISTER_BUTTON_CLICK) {
            try {
                $storingProcess = $storeCustomer->execute($editRow);
                // SQLエラーのエラーハンドリング
                if (isset($storingProcess['error'])) {
                    return redirect()->route('cc.customer.new')
                                ->with(['messages.error' => ['message' => __('messages.error.connection_error')]])
                                ->withInput($editRow);
                }
            } catch (Exception $e) {
                return redirect()->route('cc.customer.new')
                                ->with(['messages.error' => ['message' => __('messages.error.register_failed', ['data' => '顧客'])]])
                                ->withInput($editRow);
            }

            // 登録が無事完了した場合
            // セッション内容の削除
            session()->forget($this->registerSessionName);

            //パラメータをデコードしてセット
            $param = [];
            $isInfoPage = false;
            $m_cust_id = $storeCustomer->getId();

            if (isset($editRow['delete_timestamp'], $editRow['delete_operator_id'])) {
                return redirect()->route('cc.customer.list');
            }

            if (isset($editRow['previous_subsys'])) {
                $param['previous_subsys'] = $editRow['previous_subsys'];
            }

            if (isset($editRow['previous_url'])) {
                $param['previous_url'] = $editRow['previous_url'];
                // 顧客照会からの場合はセットしない
                if (strpos($param['previous_url'], 'cc-customer/info') !== false) {
                    $isInfoPage = true;
                    $param['previous_url'] = '';

                }
                // 顧客受付から遷移された場合は顧客照会へ進む
                if ($editRow['previous_url'] == 'cc-customer/list') {
                    $isInfoPage = true;
                }
            }

            if (isset($editRow['previous_key'])) {
                $param['previous_key'] = $editRow['previous_key'];
            }

            $json_param = json_encode($param);
            $b64_param = base64_encode($json_param);

            //照会画面へ
            if ($isInfoPage) {
                if (isset($param['previous_url']) && strlen($param['previous_url']) > self::EMPTY_LENGTH_CHECK) {
                    return redirect()->route('cc.cc-customer.info', ['id' => $m_cust_id, 'params' => $b64_param])
                            ->with(['messages.info' => ['message' => __('messages.info.create_completed', ['data' => '顧客'])]]);
                } else {
                    return redirect()->route('cc.cc-customer.info', ['id' => $m_cust_id])
                            ->with(['messages.info' => ['message' => __('messages.info.create_completed', ['data' => '顧客'])]]);
                }
            }

            //前画面あり
            if (isset($param['previous_url']) && strlen($param['previous_url']) > self::EMPTY_LENGTH_CHECK) {
                return redirect()->route('cc.customer.list', ['params' => $b64_param])
                                ->with(['messages.info' => ['message' => __('messages.info.create_completed', ['data' => '顧客'])]]);
            }

            if ($this->masterFlg) {
                // マスタ系なら、一覧に戻す
                return redirect()->route('cc.customer.list');
            }

            // そうでない場合は、新規の場合は新規登録画面へ、編集の場合は該当データの編集画面へ戻す
            if ($this->isNewFlg($editRow)) {
                return redirect()->route('cc.customer.post-new', ['params' => $b64_param])
                        ->with(['messages.info' => ['message' => __('messages.info.create_completed', ['data' => '顧客'])]]);
            } else {
                return redirect()->route('cc.customer.post-edit', ['id' => $editRow['m_cust_id'], 'params' => $b64_param])
                        ->with(['messages.info' => ['message' => __('messages.info.update_completed', ['data' => '顧客'])]]);
            }
        }

        // キャンセルをクリックする
        if ($submit == self::CANCEL_BUTTON_CLICK) {
            $b64_param_url =  $this->makeUrlParams($request, $editRow);

            if ($this->isNewFlg($editRow)) {
                return redirect(route('cc.customer.post-new', $b64_param_url))->withInput($editRow);
            }

            // 名寄せ実行フラグが１の場合
            if (isset($editRow['name_match_exec_flag']) && $editRow['name_match_exec_flag'] == self::NAME_MATCH_EXEC_FLAG) {
                return redirect(route('cc.customer.post-new', $b64_param_url))->withInput($editRow);
            }

            $url = route('cc.customer.post-edit', ['id' => $editRow['m_cust_id']]);
            return  redirect($url .'?'. $b64_param_url)->withInput($editRow);
        }
    }

    /**
     * submitボタン内容の取得
     */
    protected function getSubmitName($req)
    {
        $submitName = '';
        if (!empty($req['submit'])) {
            $submitName = $req['submit'];
        }
        return $submitName;
    }

    /**
     * URLパラメータ編集
     * @param object $request
     * @param array $editRow
     * @return string
     */
    protected function makeUrlParams($request, $editRow)
    {
        $params = '';
        $sessionKeyId = 'data_key_id';
        $sessionKey = $this->getSessionKey($request, $editRow, $sessionKeyId);
        if (!empty($sessionKey)) {
            $params = $this->makeSessionParams($sessionKeyId, $sessionKey);
        } else {
            if (!empty($editRow[self::GET_PARAM_KEY])) {
                $params = self::GET_PARAM_KEY . '=' . $editRow[self::GET_PARAM_KEY];
            }
        }
        return $params;
    }

    /**
     * セッションキー取得
     * @param object $request
     * @param array $editRow
     * @param string $keyName
     * @return string
     */
    protected function getSessionKey($request, $editRow, $keyName)
    {
        // requestデータ
        $requestData = $request->all();
        $requestData = $this->decodeGetParameter($requestData);
        $sessionKey = $requestData[$keyName] ?? '';
        if (!empty($sessionKey)) {
            return $sessionKey;
        }
        // flashデータ
        $sessionKey = $request->old($keyName) ?? '';
        if (!empty($sessionKey)) {
            return $sessionKey;
        }
        // 編集中データ
        $sessionKey = $editRow[$keyName] ?? '';
        return $sessionKey;
    }

    /**
     * GETパラメータを複合する
     */
    protected function decodeGetParameter($requestRow)
    {
        if (isset($requestRow[self::GET_PARAM_KEY]) && strlen($requestRow[self::GET_PARAM_KEY]) > self::EMPTY_LENGTH_CHECK) {
            $b64Param = $requestRow[self::GET_PARAM_KEY];
            $jsonParam = base64_decode($b64Param);
            $requestRow = array_merge($requestRow, (array)json_decode($jsonParam));
        }

        return $requestRow;
    }

    /**
     * ランダム値作成
     * @return string
     */
    protected function makeRandomKey()
    {
        return Str::random(self::RANDOM_CHARACTER_LENGTH);
    }

    /**
     * 新規登録時の初期データを用意する
     */
    protected function setNewPresetData($editRow, $request = null)
    {
        //使用区分
        if (!isset($editRow["delete_flg"])) {
            $editRow["delete_flg"] = self::INITIAL_DEFAULT_VALUE;
        } else {
            $editRow["delete_flg"] = implode(',', $editRow['delete_flg']);
        }
        //性別
        if (!isset($editRow["sex_type"])) {
            $editRow["sex_type"] = self::INITIAL_DEFAULT_VALUE;
        } else {
            $editRow["sex_type"] = implode(',', $editRow['sex_type']);
        }
        //要注意区分
        if (!isset($editRow["alert_cust_type"])) {
            $editRow["alert_cust_type"] = self::INITIAL_DEFAULT_VALUE;
        } else {
            $editRow["alert_cust_type"] = implode(',', $editRow['alert_cust_type']);
        }

        //前画面よりセットされた項目
        //TEL
        if (isset($editRow["tel"])) {
            $editRow["tel1"] = $editRow["tel"];
        }
        //email
        if (isset($editRow["email"])) {
            $editRow["email1"] = $editRow["email"];
        }

        return $editRow;
    }

    /**
     * editRowを編集
     */
    public function setEditRow($editRow)
    {
        //使用区分
        if (isset($editRow["delete_flg"]) && is_array($editRow["delete_flg"])) {
            $editRow["delete_flg"] = implode(',', $editRow['delete_flg']);
        }
        //性別
        if (isset($editRow["sex_type"]) && is_array($editRow["sex_type"])) {
            $editRow["sex_type"] = implode(',', $editRow['sex_type']);
        }
        //要注意区分
        if (isset($editRow["alert_cust_type"]) && is_array($editRow["alert_cust_type"])) {
            $editRow["alert_cust_type"] = implode(',', $editRow['alert_cust_type']);
        }
        //生年月日
        if (isset($editRow["birthday"]) && $editRow["birthday"] == self::BIRTHDAY_DEFAULT_VALUE) {
            $editRow["birthday"] = '';
        }
        //郵便番号（ハイフン付加）
        if (isset($editRow["postal"]) && strlen($editRow['postal']) > self::EMPTY_LENGTH_CHECK && strpos($editRow["postal"], '-') === false) {
            if (mb_strlen($editRow["postal"]) == self::POSTAL_LENGTH) {
                $editRow["postal"] = substr($editRow["postal"], self::POSTAL_START_INDEX, self::POSTAL_END_INDEX).'-'.substr($editRow["postal"], self::POSTAL_END_INDEX);
            }
        }

        return $editRow;
    }

    public function dataPrepare($editRow)
    {
        // 郵便番号 - ハイフン取り除く
        if (isset($editRow['postal'])) {
            $editRow['postal'] = str_replace('-', '', $editRow['postal']);
        }

        return $editRow;
    }

    /**
     * セッションキー情報パラメータ編集
     * @param string $keyId
     * @param string $keyValue
     * @return string
     */
    protected function makeSessionParams($keyId, $keyValue)
    {
        $paramUrl = [
            $keyId  => $keyValue
        ];
        $json_param_url = json_encode($paramUrl);
        $b64_param_url = base64_encode($json_param_url);
        $params = self::GET_PARAM_KEY . '=' . $b64_param_url;

        return $params;
    }

    /**
     * 新規か編集かどうか判断する
     */
    public function isNewFlg($editRow)
    {
        // 主キーがセットされていれば編集、そうでない場合は新規扱い
        return empty($editRow[$this->searchPrimaryKey]);
    }

    public function getPostal($postal, GetPostalCodeInterface $getPostalCode)
    {
        $postalCode = $getPostalCode->execute($postal);

        if (isset($postalCode['error'])) {
            return response()->json(['success' => false], 500);
        }

        if (isset($postalCode['success']) && $postalCode['success']) {

            return response()->json([
                'success' => true,
                'address1' => $postalCode['address1'] ?? '',
                'address2' => $postalCode['address2'] ?? '',
                'address3' => $postalCode['address3'] ?? '',
                'address4' => $postalCode['address4'] ?? '',
                'address5' => $postalCode['address5'] ?? '',
            ]);

        }
    }
}
