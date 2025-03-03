<?php

namespace App\Modules\Order\Gfh1207;

use App\Models\Order\Base\OrderTagColumnDictModel;
use App\Modules\Order\Base\GetOrderTagColDictInterface;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Log;

class GetOrderTagColDict implements GetOrderTagColDictInterface
{
    public function execute($table_id = null)
    {
        try {
            if (isset($table_id)) {
                $query = OrderTagColumnDictModel::where('table_id', $table_id)->pluck('column_id', 'column_name');

            } else {
                $query = OrderTagColumnDictModel::pluck('column_id', 'column_name');
            }

            return $query;

        } catch (QueryException $e) {
            Log::error('Database connection error: ' . $e->getMessage());
            return ['error' => 'Database connection error. Please try again later.'];
        }
    }
}
