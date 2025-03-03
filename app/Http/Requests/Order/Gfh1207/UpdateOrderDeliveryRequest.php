<?php

namespace App\Http\Requests\Order\Gfh1207;

use App\Enums\ThreeTemperatureZoneTypeEnum;
use App\Http\Requests\Order\Base\UpdateOrderDeliveryRequest as BaseUpdateOrderDeliveryRequest;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Enum;

class UpdateOrderDeliveryRequest extends BaseUpdateOrderDeliveryRequest
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
        return [
            //
            'deli_decision_date' => ['nullable', 'date'],
            'shipping_label_numbers' => ['nullable', 'array'],
            'shipping_label_numbers.*' => ['required', 'string', 'max:20'],
            'three_temperature_zone_types' => ['nullable', 'array'],
            'three_temperature_zone_types.*' => ['required', Rule::enum(ThreeTemperatureZoneTypeEnum::class)],
            'deli_package_vol' => ['nullable', 'numeric'],
            'invoice_num1' => ['nullable', 'string', 'max:20'],
            'invoice_num2' => ['nullable', 'string', 'max:20'],
            'invoice_num3' => ['nullable', 'string', 'max:20'],
            'invoice_num4' => ['nullable', 'string', 'max:20'],
            'invoice_num5' => ['nullable', 'string', 'max:20'],
        ];
    }

    /**
     * 項目名
     */
    public function attributes()
    {
        return [
            'deli_decision_date' => '配送決定日',
            'shipping_label_numbers' => '送り状番号',
            'shipping_label_numbers.*' => '送り状番号',
            'three_temperature_zone_types' => '温度帯',
            'three_temperature_zone_types.*' => '温度帯',
            'deli_package_vol' => '個口数',
            'invoice_num1' => '送り状番号1',
            'invoice_num2' => '送り状番号2',
            'invoice_num3' => '送り状番号3',
            'invoice_num4' => '送り状番号4',
            'invoice_num5' => '送り状番号5',
        ];
    }

    /**
     * バリデーションメッセージ
     * デフォルトから変更したい場合はここに追加する
     */
    public function messages()
    {
        return [
            'three_temperature_zone_types.*.Illuminate\Validation\Rules\Enum' => ':attributeが正しくありません。',
        ];
    }

    /**
     * バリデーション前処理
     * ハラダ版では、送り状番号はshipping_label.shippingu_label_numberが正。
     * ただし1~5までは、t_delivery_hdr.invoice_num1~5にも格納するために、ここでマージしている。
     */
    public function prepareForValidation()
    {
        if(isset($this->shipping_label_numbers)) {
            // shipping_label_numbersのキーを昇順で並び替え、先頭から順にinvoice_num1~5に格納する
            $shipping_label_numbers = $this->shipping_label_numbers;
            ksort($shipping_label_numbers);
            $this->merge([
                'invoice_num1' => array_shift($shipping_label_numbers),
                'invoice_num2' => array_shift($shipping_label_numbers),
                'invoice_num3' => array_shift($shipping_label_numbers),
                'invoice_num4' => array_shift($shipping_label_numbers),
                'invoice_num5' => array_shift($shipping_label_numbers),
            ]);
        }
    }
}
