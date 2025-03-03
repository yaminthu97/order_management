<?php

namespace App\Http\Requests\Master\Base;

use App\Services\EsmSessionManager;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

use Lang;

/**
 * 熨斗マスタ用フォームリクエスト
 */
class UpdateNoshiRequest extends FormRequest
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