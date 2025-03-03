<?php

namespace App\Http\Requests\Billing\Gfh1207;

use App\Enums\ExcelReportOutputUnitEnum;
use App\Http\Requests\Billing\Base\UpdateExcelReportRequest as BaseUpdateExcelReportRequest;
use App\Models\Order\Base\OrderDestinationModel;
use App\Services\EsmSessionManager;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

use Lang;

/**
 * 見積書・納品書・請求書出力用フォームリクエスト
 */
class UpdateExcelReportRequest extends BaseUpdateExcelReportRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            't_order_destination_id' => [
                'required',
                function ($attribute, $value, $fail) { 
                    $query = OrderDestinationModel::query()
                    ->with(['orderHdr'])
                    ->where('m_account_id', $this->service->getAccountId())
                    ->where('t_order_destination_id', $value);            
                    $query->whereHas('orderHdr', function( $query ) {
                        $query->where('m_account_id', $this->service->getAccountId());
                        $query->where('cancel_timestamp', 'LIKE', '0000%');
                    });
                    if (!$query->exists()) {
                        $fail(__('messages.error.required_parameter',['param'=>'対象受注']));
                    }
                },
            ],
            'output_unit' => [
                'required',
                Rule::in( array_column(ExcelReportOutputUnitEnum::cases(), 'value' ) ),
            ],
            'm_report_template_id' => [
                'required',
                Rule::exists('m_report_templates', 'm_report_template_id')->where(function ($query) {
                    $query->where([
                        ['m_account_id', $this->service->getAccountId() ]
                    ]);
                }),
            ],
        ];
    }

    /**
     * エラーメッセージ
     */
    public function messages()
    {
        return [];
    }

    /**
     * 項目名
     */
    public function attributes()
    {
        return [
            't_order_destination_id' => '対象受注',
            'output_unit' => '出力単位',
            'm_report_template_id' => 'テンプレート',
        ];
    }

    /**
     * @Override
     * @param  \Illuminate\Contracts\Validation\Validator  $validator
     */
    protected function failedValidation(Validator $validator)
    {
        /**
         * 一覧画面で出力処理を実行した際に経由するRequestForm
         * failedになるとリダイレクトされる影響で表示中の一覧が消える為、
         * failedValidationをオーバーライドしてリダイレクトをさせない
         * Controller側で適宜判定する
         */
    }
}