<?php
namespace App\Modules\Common\Base;

use App\Modules\Common\Base\GetStoreGroupInterface;
use App\Models\Master\Base\ItemnameTypeModel;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Log;

class GetStoreGroup implements GetStoreGroupInterface
{
	
	/**
	 * To get store group data
	 */
	public function execute()
	{
		try {
			$query = ItemnameTypeModel::query()
			->select('m_itemname_type_code')
			->where('m_itemname_type', 3)
			->where('delete_flg', 0)
			->whereNotNull('m_itemname_type_code')
			->groupBy('m_itemname_type_code')
			->orderBy('m_itemname_type_code', 'asc');
			$itemNameData = $query->get();

			$dataList = json_decode(json_encode($itemNameData), true);
			return $dataList;
		} catch (QueryException $e) {
			Log::error('Database connection error: ' . $e->getMessage());
			return ['error' => 'Database connection error. Please try again later.'];
		}
	}

}
