<?php

namespace App\Modules\Customer\Gfh1207;

use App\Modules\Customer\Base\FindCustCommunicationDtlInterface;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Log;

class FindCustCommunicationDtl implements FindCustCommunicationDtlInterface
{
    public function execute($editRow)
    {
        try {
            $defaultOperatorId = 0;
            $custCommunicationDtls = $editRow->custCommunicationDtl()
                ->where('delete_operator_id', $defaultOperatorId)
                ->get();

            return $custCommunicationDtls;
        } catch (QueryException $e) {
            Log::error('Database connection error: ' . $e->getMessage());
            return ['error' => 'Database connection error. Please try again later.'];
        }
    }
}
