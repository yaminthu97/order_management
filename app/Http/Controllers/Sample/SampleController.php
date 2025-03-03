<?php

namespace App\Http\Controllers\Sample;

use App\Http\Requests\Sample\Base\EditNotifySampleRequest;
use App\Http\Requests\Sample\Base\EditSampleRequest;
use App\Http\Requests\Sample\Base\NewNotifySampleRequest;
use App\Http\Requests\Sample\Base\SearchSampleRequest;
use App\Http\Requests\Sample\Gfh1207\NewSampleRequest;
use App\Modules\Customer\Base\CreateSessionParamsInterface;
use App\Modules\Customer\Base\SearchCustCommunicationInterface;
use App\Modules\Order\Base\SearchInterface;
use App\Modules\Order\Base\SerchMailSendHistoryInterface;
use App\Modules\Sample\Base\DeleteSampleInterface;
use App\Modules\Sample\Base\FindSample;
use App\Modules\Sample\Base\FindSampleInterface;
use App\Modules\Sample\Base\GetCustomerRankSampleInterface;
use App\Modules\Sample\Base\GetSamplePrefecturalInterface;
use App\Modules\Sample\Base\NewSampleInterface;
use App\Modules\Sample\Base\NotifySampleInterface;
use App\Modules\Sample\Base\SearchSampleInterface;
use App\Modules\Sample\Base\StoreCheckSampleInterface;
use App\Modules\Sample\Base\StoreSampleInterface;
use App\Modules\Sample\Base\UpdateCheckSampleInterface;
use App\Modules\Sample\Base\UpdateSampleInterface;
use Illuminate\Contracts\Cache\Store;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class SampleController
{
    //

    public function __construct(
        private \App\Services\EsmSessionManager $esmSessionManager
    )
    {}

    /**
     * サンプル検索画面表示
     */
    public function list(
        Request $request,
        GetSamplePrefecturalInterface $getPrefectural,
        GetCustomerRankSampleInterface $getCustomerRank
    )
    {
        // 画面表示のためのデータ取得
        // 都道府県
        $prefectuals = $getPrefectural->execute();
        // 顧客ランク
        $custRunks = $getCustomerRank->execute();

        return account_view('sample.base.list', [
            'searchForm' => [],
            'prefectuals' => $prefectuals,
            'custRunks' => $custRunks,
        ]);
    }

    /**
     * サンプル検索画面 検索処理
     */
    public function postList(
        SearchSampleRequest $request,
        SearchSampleInterface $searchSample,
        GetCustomerRankSampleInterface $getCustomerRank,
        GetSamplePrefecturalInterface $getPrefectural
    )
    {
        $input = $request->input();
        // 検索処理
        $samples = $searchSample->execute(
            $request->getSearchConditions(),
            array_merge(
                $request->getSearchOptions(),
                [
                    // 'with' => [],
                    // 'columns' => ['*'], // 取得カラムを指定する場合
                ]
            )
        );

        // 画面表示のためのデータ取得

        // 都道府県
        $prefectual = $getPrefectural->execute();
        // 顧客ランク
        $custRunks = $getCustomerRank->execute();

        return account_view('sample.base.list', [
            'searchForm' => $input,
            'prefectuals' => $prefectual,
            'custRunks' => $custRunks,
            'samples' => $samples,
        ]);
    }

    /**
     * サンプル新規登録画面
     */
    public function new(
        Request $request,
        NewSampleInterface $newSample,
        GetCustomerRankSampleInterface $getCustomerRank,
        GetSamplePrefecturalInterface $getPrefectural
    )
    {
        // 画面表示のためのデータ取得
        $sample = $newSample->execute();
        // 都道府県
        $prefectuals = $getPrefectural->execute();
        // 顧客ランク
        $custRunks = $getCustomerRank->execute();

        return account_view('sample.base.new', [
            'sample' => $sample,
            'prefectuals' => $prefectuals,
            'custRunks' => $custRunks,
            'previousUrl' => url()->previous(),
        ]);
    }

    /**
     * サンプル新規登録確認処理
     */
    public function postNew(
        NewSampleRequest $request,
        StoreCheckSampleInterface $storeCheckSample
    )
    {
        $input = $request->validated();
        try{
            // バリデーションチェック
            $storeCheckSample->execute($input);
        }catch(\App\Exceptions\ModuleValidationException $e){
            return redirect()->back()->withErrors($e->getValidationErrors());
        }

        $encodedParams = $this->esmSessionManager->setSessionKeyName(
            config('define.cc.customer_register_request'),
            config('define.session_key_id'),
            $input + [
                'previousUrl' => route('sample.sample.new'),
                'mode' => 'new',
            ]
        );
        return redirect()->route('sample.sample.notify', ['params' => $encodedParams])->withInput($input);
    }

    /**
     * サンプル編集画面
     */
    public function edit(
        Request $request,
        FindSampleInterface $findSample,
        GetCustomerRankSampleInterface $getCustomerRank,
        GetSamplePrefecturalInterface $getPrefectural
    )
    {
        $input = $request->input();
        // 編集対象のデータ取得
        $sample = $findSample->execute($request->route('id'));

        // 画面表示のためのデータ取得
        // 都道府県
        $prefectuals = $getPrefectural->execute();
        // 顧客ランク
        $custRunks = $getCustomerRank->execute();

        return account_view('sample.base.edit', [
            'sample' => $sample,
            'prefectuals' => $prefectuals,
            'custRunks' => $custRunks,
            'previousUrl' => url()->previous(),
        ]);
    }

    /**
     * サンプル編集確認処理
     */
    public function postEdit(
        EditSampleRequest $request,
        FindSampleInterface $findSample,
        UpdateCheckSampleInterface $updateCheckSample,
    )
    {
        $input = $request->validated();
        // 編集対象のデータ取得
        $sample = $findSample->execute($request->route('id'));
        try{
            // バリデーションチェック
            $updateCheckSample->execute($sample->m_cust_id, $input);
        }catch(\App\Exceptions\ModuleValidationException $e){
            return redirect()->back()->withErrors($e->getValidationErrors());
        }

        $encodedParams = $this->esmSessionManager->setSessionKeyName(
            config('define.cc.customer_register_request'),
            config('define.session_key_id'),
            $input + [
                'previousUrl' => route('sample.sample.edit', ['id' => $sample->m_cust_id]),
                'm_cust_id' => $sample->m_cust_id,
                'mode' => $request->input('submit') === 'delete' ? 'delete' : 'edit',
            ],
        );
        return redirect()->route('sample.sample.notify', ['params' => $encodedParams])->withInput($input);
    }

    /**
     * サンプル確認画面
     */
    public function notify(
        Request $request,
        NotifySampleInterface $notifySample
    )
    {
        $previousInput = $this->esmSessionManager->getSessionKeyName(
            config('define.cc.customer_register_request'),
            config('define.session_key_id'),
            $request->input('params')
        );

        // 前画面の入力情報が取得できない場合はリダイレクト
        if(empty($previousInput) && empty($request->old())){
            return redirect()->route('sample.sample.list');
        }

        // 確認画面のデータ設定
        // 必要に応じてfillable外のデータを移す。
        // fillableに定義されていない項目はエラーとなる。
        $exFillData = [];
        $sample = $notifySample->execute($previousInput, $exFillData, $previousInput['m_cust_id'] ?? null);

        return account_view('sample.base.notify', [
            'input' => $previousInput,
            'mode' => $previousInput['mode'] ?? null,
            'sample' => $sample,
            'param' => $request->input('params'),
            'previousUrl' => $previousInput['previousUrl'] ?? route('sample.sample.list'),
        ]);
    }

    /**
     * サンプル登録処理
     */
    public function postNotify(
        NewNotifySampleRequest $request,
        StoreSampleInterface $storeSample
    )
    {
        $input = $request->validated();

        $sample = $storeSample->execute($input, [
            'm_account_id' => $this->esmSessionManager->getAccountId(),
        ]);

        // セッション情報を削除する。
        $this->esmSessionManager->forgetSessionKeyName(
            config('define.cc.customer_register_request'),
            config('define.session_key_id'),
            $request->input('params')
        );
        return redirect()->route('sample.sample.list');
    }

    /**
     * サンプル更新処理
     */
    public function putNotify(
        EditNotifySampleRequest $request,
        UpdateSampleInterface $updateSample,
    )
    {
        $input = $request->validated();

        $sample = $updateSample->execute($input['m_cust_id'], $input, []);

        // セッション情報を削除する。
        $this->esmSessionManager->forgetSessionKeyName(
            config('define.cc.customer_register_request'),
            config('define.session_key_id'),
            $request->input('params')
        );

        return redirect()->route('sample.sample.list');
    }

    /**
     * サンプル削除処理
     */
    public function deleteNotify(
        Request $request,
        DeleteSampleInterface $deleteSample
    )
    {
        $previousInput = $this->esmSessionManager->getSessionKeyName(
            config('define.cc.customer_register_request'),
            config('define.session_key_id'),
            $request->input('params')
        );
        $deleteSample->execute($previousInput['m_cust_id']);

        // セッション情報を削除する。
        $this->esmSessionManager->forgetSessionKeyName(
            config('define.cc.customer_register_request'),
            config('define.session_key_id'),
            $request->input('params')
        );

        return redirect()->route('sample.sample.list');
    }


    /**
     * サンプル詳細画面
     * 一部のモジュールは既にあるものを使用している
     */
    public function info(
        Request $request,
        FindSampleInterface $findSample,
        SearchInterface $searchOrder,
        SearchCustCommunicationInterface $searchCustCommunication,
        SerchMailSendHistoryInterface $serchMailSendHistory,
        CreateSessionParamsInterface $createSessionParams
    )
    {
        $sample = $findSample->execute($request->route('id'));

        $orders = $searchOrder->execute([
            'm_cust_id' => $sample->m_cust_id,
        ]);
        $custCommunications = $searchCustCommunication->execute([
            'm_cust_id' => $sample->m_cust_id,
        ]);
        $mailSendHistories = $serchMailSendHistory->execute([
            'm_account_id' => $this->esmSessionManager->getAccountId(),
            'm_cust_id' => $sample->m_cust_id,
        ],[
            'should_paginate' => true,
            'with' => [
                'emailTemplates',
                'entryOperator',
            ]
        ]);
        $params = $createSessionParams->execute($sample->toArray());
        $encodedParams = $this->esmSessionManager->setSessionKeyName(
            config('define.cc.send_mail_parameter_session'),
            config('define.session_key_id'),
            $params
        );

        return account_view('sample.base.info', [
            'sample' => $sample,
            'orders' => $orders,
            'custCommunications' => $custCommunications,
            'mailSendHistories' => $mailSendHistories,
            'params' => $encodedParams,
        ]);
    }
}
