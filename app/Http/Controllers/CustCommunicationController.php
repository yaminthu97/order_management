<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Common\CommonController;

use App\Models\CustCommunication\CustCommunicationModel;
use App\Modules\CustomerModule;

class CustCommunicationController extends CommonController
{
	protected $model;
	protected $service;

	public function __construct()
	{
		$this->model = new CustCommunicationModel();
		$this->service = new CustomerModule();
		
	}
	
	public function list(Request $request)
	{
		// Eloquent で全件取得
		/*
		$req = $request->all();
		$this->model->setAccount($req);
		$cc = $this->model->all();
		*/
		// Model の検索を使用(getRows相当)
		$req = $request->all();
		$this->model->setAccount($req);
		$this->model->dbSelect = CustCommunicationModel::query();
		$this->model->addWhere($req);
		$cc = $this->model->dbSelect->get();


		dd($cc);
		exit;
	}

	public function edit($id = 0, Request $request) {
		// Eloquent で1件取得
		$req = $request->all();
		$this->model->setAccount($req);
		$cc = $this->model->find($request->id);
		dd($cc);
		exit;
	}
}