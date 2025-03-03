<?php

namespace App\Http\Requests\Master\Gfh1207;

use App\Http\Requests\Master\Base\EditDeliveryTypeRequest as BaseEditDeliveryTypeRequest;
use Illuminate\Validation\Rule;

class EditDeliveryTypeRequest extends BaseEditDeliveryTypeRequest
{
    public function __construct(
        protected \App\Services\EsmSessionManager $esmSessionManager
    ) {
    }

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
        return [
            'm_delivery_types_id'                   => ['required', 'integer'], // 配送方法マスタID
            'm_delivery_unique_setting_seino_id'    => ['nullable', 'integer'], // 配送会社固有設定-西濃運輸ID
            'm_delivery_unique_setting_yamato_id'   => ['nullable', 'integer'], // 配送会社固有設定-ヤマト運輸ID
            'delete_flg'                            => [ 'required', Rule::in(array_column(\App\Enums\DeleteFlg::cases(), 'value'))], // 使用区分
            'm_delivery_type_name'                  => ['required', 'string', 'max:100'], // 配送方法名
            'm_delivery_type_code'                  => ['nullable', 'string', 'max:100'], // 配送方法コード
            'delivery_type'                         => [ 'required', Rule::in(array_column(app(\App\Modules\Master\Base\Enums\DeliveryCompanyEnumInterface::class)::cases(), 'value'))], // 配送方法種類
            'm_delivery_sort'                       => ['required', 'integer', 'min:1', 'max:9999'], // 並び順
            'delivery_date_output_type'             => ['nullable', 'boolean'], // 配送予定日出力内容
            'delivery_date_create_type'             => ['nullable', 'boolean'], // 配送予定日出力内容
            'deferred_payment_delivery_id'          => ['nullable', 'string', 'max:5'], // 後払い.com配送会社ID
            'standard_fee'                          => ['nullable', 'integer', 'between:0,999999999.99'], // 手数料（常温）;常温の手数料
            'frozen_fee'                            => ['nullable', 'integer', 'between:0,999999999.99'], // 手数料（冷凍）;冷凍の手数料
            'chilled_fee'                           => ['nullable', 'integer', 'between:0,999999999.99'], // 手数料（冷蔵）;冷蔵の手数料
            'delivery_tracking_url'                 => ['nullable', 'string', 'max:500'], // 配送追跡確認URL
            'shipper_cd'                            => ['nullable', 'string', 'max:100'], // 荷送人コード
            'correct_info'                          => ['nullable', 'string', 'max:255'], // コレクトお客様情報
            'masterpack_import_datetime'            => ['nullable', 'date'], // マスタパック最終取込日時
            'file'                                  => ['nullable', 'file', 'mimes:zip'], // ヤマトマスタパック
        ];
    }

    public function prepareForValidation()
    {
        $this->merge([
            'entry_operator_id' => $this->esmSessionManager->getOperatorId(),
            'update_operator_id' => $this->esmSessionManager->getOperatorId()
        ]);
    }

    /**
     * Get custom attributes for validator errors.
     *
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'delete_flg'               =>  '使用区分',
            'm_delivery_sort'          =>  '並び順',
            'm_delivery_type_name'     =>  '配送方法名',
            'delivery_type'            =>  '配送方法種類',
            'file'                     => 'ファイル',
        ];
    }
}
