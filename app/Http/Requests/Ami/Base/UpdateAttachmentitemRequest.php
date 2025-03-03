<?php

namespace App\Http\Requests\Ami\Base;

use App\Http\Requests\Common\CommonRequests; 
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

use Illuminate\Http\Request;
use App\Services\EsmSessionManager;

/**
 * 値チェック
 *
 * 
 * 
 * @category Cc
 * @package Validator
 * 
 * 追加
 */

/*付属品マスタ登録編集*/

class UpdateAttachmentitemRequest extends FormRequest
{

    protected $service = null;

    public function __construct()
    {
        $this->service = app(EsmSessionManager::class); // サービスコンテナから取得
    }

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [];
    }
}
