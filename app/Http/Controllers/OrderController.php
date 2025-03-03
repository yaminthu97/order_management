<?php

namespace App\Http\Controllers;

use App\Models\Order\Base\OrderListModel;

use App\Models\Order\Base\OrderModel;
use App\Modules\Common\Base\GetPrefecturalInterface;

use App\Modules\Master\Base\GetCancelReasonInterface;

use App\Modules\Master\Base\GetCustomerRankInterface;
use App\Modules\Master\Base\GetDeliveryTimeHopeMapInterface;
use App\Modules\Master\Base\GetEcsDetailInterface;


use App\Modules\Master\Base\GetOrderTypesInterface;

use App\Modules\Order\Base\GetOrderListConditionsInterface;
use App\Modules\Order\Base\SearchInterface;
use App\Modules\OrderModule;

use Config;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;

class OrderController
{
	protected $model;
	protected $service;

	public function __construct()
	{
		$this->model = new OrderListModel();
		$this->service = new OrderModule();
	}

	public function list(Request $request)
	{
        $search = app(SearchInterface::class);
		// view 向けデータ
		$viewExtendData = $this->service->setSearchExtendData();
		$viewExtendData['order_cond_list'] = [];
		$viewExtendData['list_sort'] = [
			'column_name' => 'm_cust_id',
			'sorting_shift' => 'asc'
		];
		$viewExtendData['page_list_count'] = Config::get('Common.const.disp_limits');

		$req = $request->all();
		$submit = $this->getSubmitName($req);

		$inputData = [
			['search_info' => $request]
		];

		$rowArray = array();

		// 検索処理
		if($request->isMethod('post')) {
			//$paginator = $this->search($req);
            $paginator = $search->execute($req);
		}

		// view 向け不足項目ダミー
		if(!isset($searchResult)) {$searchResult = [];}
		if(!isset($dataList)) {$dataList = null;}
		if(!isset($errorResult)) {$errorResult = null;}
		if(!isset($paginator)) {$paginator = null;}
		if(!isset($viewName)) {$viewName = null;}
		if(!isset($pageRows)) {$pageRows = null;}
		if(!isset($viewExtendData)) {$viewExtendData = null;}
		if(!isset($searchRow)) {$searchRow = null;}

		$viewMessage = $this->viewMessage;

		// AJAX による検索の場合
		if (request()->ajax()) {
			$viewContent = view('order.list_search', compact(
				'searchResult', 'dataList', 'errorResult', 'paginator', 'viewName', 'pageRows', 'viewExtendData', 'searchRow', 'viewMessage'
			))->render();

			return response()->json(['html' => $viewContent]);
		}

		return view('order.list',
			compact('searchResult','dataList', 'errorResult', 'paginator', 'viewName', 'pageRows', 'viewExtendData', 'searchRow', 'viewMessage')
		);
	}

	/**
	 * submitボタン内容の取得
	 */
	protected function getSubmitName($req)
	{
		$submitName = '';
		foreach($req as $key => $row)
		{
			if(strpos($key, 'submit_') !== false)
			{
				$submitName = str_replace('submit_', '', $key);
			}
		}
		return $submitName;
	}
	public function search($req)
	{
		// Model の検索を使用(getRows相当)
		$this->model->setAccount($req);
		$this->model->dbSelect = OrderListModel::query();
		$this->model->addWhere($req);
		// sort
		// pagination
		$limit = $req['page_list_count'] ?? 10;
		$page = $req['hidden_next_page_no'] ?? 1;
		$dbRow = $this->model->dbSelect->paginate($limit, '*', 'hidden_next_page_no', $page);
		//$dbRow = $this->model->dbSelect->get();

		return $dbRow;
	}

	// GET /new
	public function new(Request $request) {
		// Eloquent で1件取得
		$req = $request->all();
		$this->model->setAccount($req);
		$editRow = $this->model->find($request->id);
		$editRow['register_destination'] = [];
		// キャンセルで戻ってきた場合パラメーターを復元

		Session::forget('edit_data');

		$viewExtendData = $this->service->setRegisterExtendData($editRow, null);
		if(!isset($errorResult)) {$errorResult = null;}

		return view('order.order.edit',
			compact('viewExtendData', 'editRow', 'errorResult')
		);
	}

	// POST /new
	public function postNew(Request $request) {
		$editRow = $request->all();
		// TODO: ダミーデータ
		$register_destination = [
			'order_destination_seq' => 1,
			'destination_tel' => $editRow['order_tel1'],
			'destination_name_kana' => $editRow['order_name_kana'],
			'destination_name' => $editRow['order_name'],
			'm_delivery_type_id' => 1,
			'destination_postal' => $editRow['order_postal'],
			'destination_address1' => $editRow['order_address1'],
			'destination_address2' => $editRow['order_address2'],
			'destination_address3' => $editRow['order_address3'],
			'destination_address4' => $editRow['order_address4'],
			'register_detail' => [
				0 => [
					"t_order_dtl_id" => null,
					"order_dtl_seq" => "1",
					"t_deli_hdr_id" => null,
					"cancel_timestamp" => null,
					"cancel_flg" => null,
					"reservation_date" => null,
					"variation_values" => null,
					"sell_id" => "1",
					"sell_checked" => "1",
					"sku_data" => '{"ecs_id":"1","sell_id":"1","sell_cd":"TEST01","sell_option":"","sell_type":1,"sku_dtl":[{"t_order_dtl_sku_id":null,"item_id":1,"item_cd":"TEST","compose_vol":1}]} ',
					"tax_rate" => "0.100",
					//'sell_cd' => $editRow['sell_cd'],
					"sell_cd" => "TEST01",
					"sell_name" => "販売名111",
					"order_sell_price" => "10,000",
					"order_sell_vol" => "1",
					"order_sell_amount" => null,
					"drawing_status_name" => null,
					"order_dtl_coupon_id" => null,
					"order_dtl_coupon_price" => null,
					"btn_delete_visible" => "1",
				],
				1 => [
					"t_order_dtl_id" => null,
					"order_dtl_seq" => "2",
					"t_deli_hdr_id" => null,
					"cancel_timestamp" => null,
					"cancel_flg" => null,
					"reservation_date" => null,
					"variation_values" => null,
					"sell_id" => null,
					"sell_checked" => null,
					"sku_data" => null,
					"tax_rate" => null,
					"sell_cd" => null,
					"sell_name" => null,
					"order_sell_price" => null,
					"order_sell_vol" => "1",
					"order_sell_amount" => null,
					"drawing_status_name" => null,
					"order_dtl_coupon_id" => null,
					"order_dtl_coupon_price" => null,
					"btn_delete_visible" => null,
				],
			],
			'sum_sell_total' => 1000,
			'shipping_fee' => 0,
			'payment_fee' => 0,
			'wrapping_fee' => 0,
			'm_delivery_time_hope_id' => null,
		];
		$editRow['register_destination'] = [$register_destination];
		$editRow['sell_total_price'] = 1000;
		$editRow['tax_price'] = 100;
		$editRow['shipping_fee'] = 0;
		$editRow['payment_fee'] = 0;
		$editRow['package_fee'] = 0;
		$editRow['total_price'] = 1000;

		$editRow['discount'] = 0;
		$editRow['use_coupon_store'] = 0;
		$editRow['use_coupon_mall'] = 0;
		$editRow['total_use_coupon'] = 0;
		$editRow['use_point'] = 0;
		$editRow['order_total_price'] = 1000;

		$editRow['m_delivery_time_hope_id'] = null;



		// セッションに入力内容を保持
		Session::put('edit_data', $editRow);

		// idをbase64エンコードしてリダイレクト
		//$params = '?params=' . base64_encode($id);
		return redirect($request->url().'/../notify')->withInput($editRow);

	}

	public function notify(Request $request) {
        //$id = base64_decode($request->input('params'));
        $editRow = Session::get('edit_data');
		//$editRow['data_key_id'] = $id;
		$viewExtendData = $this->service->setRegisterExtendData($editRow);
		return view('order.order.notify',
			compact('viewExtendData', 'editRow')
		);
	}

	public function postNotify(Request $request) {
        if ($request->has('submit_register')) {
            $editRow = Session::get('edit_data');
            // 変更を保存する処理
            $this->model->setAccount($request->all());

            //登録用にデータ整形
            $editRow = $this->service->formatRegisterData($editRow);

            // 登録処理
            $resultRow = $this->service->registerData($editRow);
            dd($resultRow);
            // セッションをクリア
            Session::forget('edit_data');
            return redirect($request->url().'/../list/');
        } elseif ($request->has('submit_cancel')) {
            //$params = '?params=' . $request->input('params');
            return redirect($request->url().'/../new/');
        }
	}
}
