<?php

namespace App\Modules\Customer\Gfh1207;

use App\Models\Cc\Gfh1207\CustModel;
use App\Modules\Customer\Base\CheckCustomerInterface;
use Illuminate\Support\Facades\Log;

class CheckCustomer implements CheckCustomerInterface
{
    public const INITIAL_DEFAULT_VALUE = '0';

    /**
     * 顧客取得
     */
    public function execute($request)
    {
        $data = [];
        $getDuplicateCustomerId = self::INITIAL_DEFAULT_VALUE;
        $checkColumns = [
            'tel1',
            'tel2',
            'tel3',
            'tel4',
            'email1',
            'email2',
            'email3',
            'email4',
            'email5'
        ];

        try {
            if (!empty($request)) {
                // 全半角スペースを削除
                $nameKanji = str_replace([" ", "　"], "", $request['name_kanji']);
                $nameKana = str_replace([" ", "　"], "", $request['name_kana']);
                $reserve10 = $request['reserve10'];

                foreach ($checkColumns as $checkColumn) {
                    $col = $request[$checkColumn];

                    if (strpos($checkColumn, 'tel') !== false) {
                        // ハイフンを削除
                        $col = str_replace("-", "", $col);
                    } elseif (strpos($checkColumn, 'email') !== false) {
                        // 小文字変換
                        $col = mb_strtolower($col);
                    }

                    if (empty($col)) {
                        continue;
                    }

                    $data = CustModel::query()
                            ->leftJoin('m_cust_tel', 'm_cust.m_cust_id', '=', 'm_cust_tel.m_cust_id')
                            ->leftJoin('m_cust_email', 'm_cust.m_cust_id', '=', 'm_cust_email.m_cust_id')
                            ->where(function ($query) use ($nameKanji, $nameKana) {
                                if (!is_null($nameKanji)) {
                                    // 全半角スペースを削除のためwhereRawを使いました
                                    $query->whereRaw("REPLACE(REPLACE(m_cust.name_kanji, ' ', ''), '　', '') = ?", [$nameKanji]);
                                }
                                if (!is_null($nameKana)) {
                                    // 全半角スペースを削除のためwhereRawを使いました
                                    $query->orWhereRaw("REPLACE(REPLACE(m_cust.name_kana, ' ', ''), '　', '') = ?", [$nameKana]);
                                }
                            })
                            ->where(function ($query) use ($col) {
                                // ハイフンを削除のためwhereRawを使いました
                                $query->whereRaw("REPLACE(m_cust_tel.tel, '-', '') = ?", [$col])
                                // 小文字変換のためwhereRawを使いました
                                    ->orWhereRaw("LOWER(m_cust_email.email) = ?", [mb_strtolower($col)]);
                            })
                            ->orWhere(function ($query) use ($reserve10) {
                                if (!is_null($reserve10)) {
                                    $query->where('reserve10', '=', $reserve10);
                                }
                            })
                            ->where(function($query) {
                                $query->where('m_cust.delete_operator_id', 0)
                                        ->orWhereNull('m_cust.delete_operator_id');
                            })
                            ->select('m_cust.m_cust_id')
                            ->first();

                    if ($data) {
                        $getDuplicateCustomerId = $data->m_cust_id;
                        return $getDuplicateCustomerId;
                    }
                }
            }
            return $getDuplicateCustomerId;

        } catch (QueryException $e) {
            Log::error('Database connection error: ' . $e->getMessage());
            return ['error' => 'Database connection error. Please try again later.'];
        }
    }
}
