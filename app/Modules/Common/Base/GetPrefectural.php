<?php

namespace App\Modules\Common\Base;

use App\Models\Master\Base\PrefecturalModel;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Log;

class GetPrefectural implements GetPrefecturalInterface
{
    public function execute()
    {
        try {
            $query = PrefecturalModel::query();
            return $query->get();

        } catch (QueryException $e) {
            Log::error('Database connection error: ' . $e->getMessage());
            return ['error' => 'Database connection error. Please try again later.'];
        }
    }
}
