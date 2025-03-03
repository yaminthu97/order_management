<?php

namespace App\Http\Requests\Order\Gfh1207;

use App\Http\Requests\Order\Base\NewNotifyOrderTagMasterRequest as BaseNewNotifyOrderTagMasterRequest;
use Symfony\Component\Routing\Exception\InvalidParameterException;
use Illuminate\Validation\Rule;

class NewNotifyOrderTagMasterRequest extends BaseNewNotifyOrderTagMasterRequest
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
        // Perform str_replace on 'tag_color' before validation
        $tagColor = str_replace('#', '', request('tag_color'));  // Removes '#' if present
        $this->merge(['tag_color' => $tagColor]);  // Update the request with the modified value

        $rules = [
            'm_order_tag_id'        => [ 'nullable', 'integer' ],
            'tag_name'              => [ 'required', 'string', 'max:100' ],
            'tag_display_name'      => [ 'required', 'string', 'max:50' ],
            'm_order_tag_sort'      => [ 'required', 'integer', 'max:99999' ],
            'tag_color'             => [ 'required', 'string', 'max:6' ],
            'font_color'            => [ 'required', Rule::in(array_column(app(\App\Modules\Order\Base\Enums\FontColorEnumInterface::class)::cases(), 'value'))  ],
            'tag_context'           => [ 'nullable', 'string', 'max:1000' ],
            'and_or'                => [ 'required', Rule::in(array_column(app(\App\Modules\Order\Base\Enums\AndOrEnumInterface::class)::cases(), 'value'))  ],
            'auto_timming'          => [ 'required',  Rule::in(array_column(app(\App\Modules\Order\Base\Enums\AutoTimmingEnumInterface::class)::cases(), 'value')) ],
            'deli_stop_flg'         => [ 'nullable', Rule::in(['-1' => '-1'] + array_column(\App\Enums\ProgressTypeEnum::cases(), 'value')) ],
        ];
        // Dynamically adding cond1 and cond2, .. rules
        for ($i = 1; $i <= 10; $i++) {
            $rules["cond{$i}_table_id"]     = [ 'nullable', 'string', 'max:100' ];
            $rules["cond{$i}_column_id"]    = [ 'nullable', 'string', 'max:100' ];
            $rules["cond{$i}_length_flg"]   = [ 'nullable', 'boolean' ];
            $rules["cond{$i}_operator"]     = [ 'nullable', Rule::in(array_column(app(\App\Modules\Order\Base\Enums\OperatorEnumInterface::class)::cases(), 'value')) ];
            $rules["cond{$i}_value"]        = [ 'nullable', 'string', 'max:100' ];
        }

        return $rules;
    }

    public function prepareForValidation()
    {
        $param = $this->input(config('define.session_key_id'));
        if (empty($param)) {
            throw new InvalidParameterException('Invalid parameter');
        }
        $previousData = $this->esmSessionManager->getSessionKeyName(
            config('define.master.ordertag_request'),
            config('define.session_key_id'),
            $param
        );
        $this->merge(
            $previousData
        );
    }

    public function attributes()
    {
        $attributes = [
            'tag_name'          => '受注タグ名称',
            'tag_display_name'  => '表示用名称',
            'm_order_tag_sort'  => '表示順',
            'auto_timming'      => '自動付与タイミング',
            'tag_color'         => '背景色',
            'font_color'        => '文字色',
            'deli_stop_flg'     => '進捗停止区分',
            'tag_context'       => '説明',
            'and_or'            => '各条件の結合',
        ];

        for ($i = 1; $i <= 10; $i++) {
            $attributes["cond{$i}_table_id"] = "条件{$i}テーブルID";
            $attributes["cond{$i}_column_id"] = "条件{$i}項目ID";
            $attributes["cond{$i}_length_flg"] = "条件{$i}バイト数フラグ";
            $attributes["cond{$i}_operator"] = "条件{$i}演算子";
            $attributes["cond{$i}_value"] = "条件{$i}値";
        }

        return $attributes;
    }
}
