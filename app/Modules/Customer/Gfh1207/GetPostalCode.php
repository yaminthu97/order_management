<?php

namespace App\Modules\Customer\Gfh1207;

use App\Models\Master\Base\PostalCodeModel;
use App\Modules\Customer\Base\GetPostalCodeInterface;

class GetPostalCode implements GetPostalCodeInterface
{
    /**
     * 顧客取得
     */
    public function execute($postal)
    {
        try {
            if ($postal) {
                $postal = str_replace('-', '', $postal);
            }

            $address = PostalCodeModel::where('postal_code', $postal)
                    ->select('postal_prefecture', 'postal_city', 'postal_town', 'col10', 'postal_city_kana')
                    ->first();

            if ($address) {
                return [
                    'success'  => true,
                    'address1' => $address->postal_prefecture,
                    'address2' => $address->postal_city,
                    'address3' => $address->postal_town,
                    'address4' => $address->col10,
                    'address5' => $address->postal_city_kana
                ];
            } else {
                return ['success' => false];
            }
        } catch (QueryException $e) {
            Log::error('Database connection error: ' . $e->getMessage());
            return ['error' => 'Database connection error. Please try again later.'];
        }
    }
}
