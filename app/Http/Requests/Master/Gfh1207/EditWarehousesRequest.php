<?php

namespace App\Http\Requests\Master\Gfh1207;

use App\Http\Requests\Master\Base\EditWarehousesRequest as BaseEditWarehousesRequest;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class EditWarehousesRequest extends BaseEditWarehousesRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $warehousesId = request()->m_warehouses_id;

        try {
            // Try to check if the DB connection is available
            DB::connection()->getPdo();
            $uniqueRule = Rule::unique('m_warehouses')->ignore($warehousesId, 'm_warehouses_id');

        } catch (\Exception $e) {
            $uniqueRule = [];
        }

        return [
            'm_warehouses_id'     =>  ['nullable', 'numeric'],
            'm_warehouse_cd'      =>  ['nullable', 'string', 'max:50'],
            'm_warehouse_name'    =>  ['required', 'string', 'max:100', $uniqueRule],
            'm_warehouse_type'    =>  ['required', Rule::in(array_column(\App\Modules\Master\Gfh1207\Enums\WarehouseTypesEnum::cases(), 'value'))],
            'm_warehouse_sort'    =>  ['required', 'integer', 'max:9999'],
            'm_warehouse_priority_flg'   =>  ['required', Rule::in(array_column(\App\Modules\Master\Gfh1207\Enums\PriorityFlg::cases(), 'value'))],
            'cash_on_delivery_flg' =>  ['required', Rule::in(array_column(\App\Modules\Master\Gfh1207\Enums\DeliveryFlg::cases(), 'value'))],
            'deliveryslip_bundle_flg' =>  ['required', Rule::in(array_column(\App\Modules\Master\Gfh1207\Enums\BundleFlg::cases(), 'value'))],
            'm_warehouse_priority' =>  ['required', 'integer', 'max:9999'],
            'warehouse_personnel_name' =>  ['nullable', 'string', 'max:100'],
            'warehouse_personnel_name_kana' =>  ['nullable', 'string', 'max:100'],
            'warehouse_company' =>  ['nullable', 'string', 'max:100'],
            'warehouse_postal'  =>  ['required', 'max:8'],
            'warehouse_prefectural'  =>  ['required', 'string', 'max:100'],
            'warehouse_address' =>  ['required', 'string', 'max:100'],
            'warehouse_house_number' =>  ['nullable', 'string', 'max:100'],
            'warehouse_adding_building' =>  ['nullable', 'string', 'max:100'],
            'warehouse_telephone'  =>  ['required', 'string', 'max:14', 'tel'],
            'warehouse_fax'  =>  ['nullable', 'string', 'max:14', 'regex:/^[0-9-]+$/'],
            'delivery_futoff_time'  =>  ['nullable', 'date_format:H:i'],
            'delivery_fee.*.*' =>   ['required', 'integer', 'min:0', 'max:999999999'],
            'delivery_readtime.*.*' => ['required', 'integer', 'min:0', 'max:999'],
        ];
    }

    /**
     * Get the validation messages that apply to the request.
     *
     * @return array<string, string>
     */
    public function messages()
    {
        return [
            'warehouse_telephone.tel'          => ':attributeには正しい電話番号を入力してください。',
        ];
    }

    public function attributes(): array
    {
        return [
            'delivery_fee.*.*'   =>  '配送送料',
            'delivery_readtime.*.*'   =>  '配送リードタイム',
        ];
    }

    public function prepareForValidation()
    {
        $this->merge([
            'operator_id' => $this->esmSessionManager->getOperatorId(),
        ]);
    }
}
