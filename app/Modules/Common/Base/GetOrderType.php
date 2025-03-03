<?php
namespace App\Modules\Common\Base;

use App\Modules\Common\Base\GetOrderTypeInterface;
use App\Models\Master\Base\ItemnameTypeModel;
use App\Services\EsmSessionManager;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Log;

class GetOrderType implements GetOrderTypeInterface
{
	/**
	 * ESMセッション管理クラス
	 */
	protected $esmSessionManager;
	public function __construct(EsmSessionManager $esmSessionManager)
	{
		$this->esmSessionManager = $esmSessionManager;
	}

	/**
	 * To get order type data
	 */
	public function execute(){
		try {
			$itemNameData = ItemnameTypeModel::query()
			->where('delete_flg', 0)
			->where('m_itemname_type', 1)
			->where('m_account_id',  $this->esmSessionManager->getAccountId())
			->orderBy('m_itemname_type', 'asc')
			->orderBy('m_itemname_type_sort', 'asc')
			->get();
			$dataList = json_decode(json_encode($itemNameData), true);
			return $dataList;
		} catch (QueryException $e) {
			Log::error('Database connection error: ' . $e->getMessage());
			return ['error' => 'Database connection error. Please try again later.'];
		}
	}
}
