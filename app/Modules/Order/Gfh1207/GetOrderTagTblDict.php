<?php

namespace App\Modules\Order\Gfh1207;

use App\Models\Order\Base\OrderTagTableDictModel;
use App\Modules\Order\Base\GetOrderTagTblDictInterface;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Log;

class GetOrderTagTblDict implements GetOrderTagTblDictInterface
{
    public function execute()
    {
        try {
            $query = OrderTagTableDictModel::pluck('table_id', 'table_name');

            return $query;

        } catch (QueryException $e) {
            throw new \Exception($e->getMessage());
            Log::error('Database connection error: ' . $e->getMessage());
            return ['error' => 'Database connection error. Please try again later.'];
        }
    }
}
