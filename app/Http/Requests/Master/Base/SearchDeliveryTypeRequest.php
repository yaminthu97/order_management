<?php

namespace App\Http\Requests\Master\Base;

use Illuminate\Foundation\Http\FormRequest;

class SearchDeliveryTypeRequest extends FormRequest
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
        return array_merge(
            $this->input(),
            [
                'm_account_id' => $this->esmSessionManager->getAccountId(),
            ]
        );
    }

    /**
     * 検索オプションの取得
     * @return array 検索オプション
     */
    public function getSearchOptions(): array
    {
        return [
            'should_paginate' => true,
            'limit' => $this->input('page_list_count', config('esm.default_page_size.master')),
            'sorts' => !empty($this->input('sorting_column')) && !empty($this->input('sorting_shift')) ? [
                $this->input('sorting_column') => $this->input('sorting_shift')
                ] : [],
            'page' => $this->input('hidden_next_page_no') ?? '1',
        ];
    }
}
