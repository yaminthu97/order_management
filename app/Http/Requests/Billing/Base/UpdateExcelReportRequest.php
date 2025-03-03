<?php

namespace App\Http\Requests\Billing\Base;

use App\Services\EsmSessionManager;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Contracts\Validation\Validator;

use Lang;

/**
 * 見積書・納品書・請求書出力用フォームリクエスト
 */
class UpdateExcelReportRequest extends FormRequest
{
    protected $service = null;

    public function __construct( EsmSessionManager $service )
    {
        $this->service = $service;
    }

    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [];
    }
}