<?php

namespace App\Http\Requests\Order\Base;

use App\Http\Requests\SearchRequestInterface;
use Illuminate\Foundation\Http\FormRequest;

class SearchOrderTagMasterRequest extends FormRequest implements SearchRequestInterface
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
            //
        ];
    }

    /**
     * 検索条件の取得
     * @return array 検索条件
     */
    public function getSearchConditions(): array
    {
        return [
            //
        ];
    }

    /**
     * 検索オプションの取得
     * @return array 検索オプション
     */
    public function getSearchOptions(): array
    {
        return [
            //
        ];
    }
}
