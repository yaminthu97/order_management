<?php

namespace App\Http\Controllers\Customer;

use App\Enums\ItemNameType;
use App\Modules\Common\Base\GetPrefecturalInterface;
use App\Modules\Customer\Base\FindCustomerInfoInterface;
use App\Modules\Master\Base\GetItemnameTypeInterface;
use Illuminate\Http\Request;

class CustomerEditController
{
    public function edit(
        Request $request,
        GetPrefecturalInterface $GetPrefectural,
        GetItemnameTypeInterface $GetItemnameType,
        FindCustomerInfoInterface $findCustomerInfo
    ) {

        $viewExtendData = [];
        $editRow = [];
        $errorResult = [];
        $cust_runk_list = [];
        $pref = [];

        $cust_runk_list =  $GetItemnameType->execute(ItemNameType::CustomerRank->value);

        $getPrefecture = $GetPrefectural->execute()->toArray();
        foreach ($getPrefecture as $p_key => $p_value) {
            $pref[$p_value['prefectual_name']] = $p_value['prefectual_name'];
        }

        $viewExtendData = [
            'cust_runk_list' => $cust_runk_list,
            'pref' => $pref,
        ];

        $editRow = $findCustomerInfo->execute($request->route('id'))->toArray();

        return account_view('customer.gfh_1207.edit', compact('viewExtendData', 'editRow', 'errorResult'));
    }
}
