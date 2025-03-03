<?php

namespace App\Modules\Common\Gfh1207;

use App\Modules\Common\Base\CheckBatchParameterInterface;

class CheckBatchParameter implements CheckBatchParameterInterface
{
    /**
     * check the batch parameter json
     */
    public function execute(?string $jsonData, ?array $checkArray)
    {
        try {
            if($jsonData == null) {
                return false;
            } else {
                $searchCondition = (json_decode($jsonData, true))['search_info'];
                $paramFields = array_keys($searchCondition);
                $missingFields = array_diff($checkArray, $paramFields);

                if(count($missingFields) > 0) {
                    return false;
                } else {
                    return true;
                }

            }
        } catch (\Throwable $th) {
            return false;
        }

    }
}
