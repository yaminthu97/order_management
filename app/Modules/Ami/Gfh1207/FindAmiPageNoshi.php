<?php

namespace App\Modules\Ami\Gfh1207;

use App\Events\ModuleCompleted;
use App\Events\ModuleFailed;
use App\Events\ModuleStarted;
use App\Exceptions\DataNotFoundException;
use App\Models\Ami\Base\AmiPageNoshiModel;
use App\Modules\Ami\Base\FindAmiPageNoshiInterface;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class FindAmiPageNoshi implements FindAmiPageNoshiInterface
{
    /**
     * デフォルトの検索条件
     */
    protected $defaultConditions = [
        //'delete_flg' => '0'
    ];

    /**
     * デフォルトの検索オプション
     */
    protected $defaultOptions = [
    ];

    public function execute(string|int $id)
    {
        ModuleStarted::dispatch(__CLASS__, compact('id'));
        try {
            $query = AmiPageNoshiModel::query();
            $query = $query->where('m_ami_page_id', $id)->get();

        } catch(ModelNotFoundException $e) {
            ModuleFailed::dispatch(__CLASS__, [$id], $e);
            throw new DataNotFoundException(__('messages.error.data_not_found', ['data' => 'ページ熨斗', 'id' => $id]), 0, $e);
        }

        ModuleCompleted::dispatch(__CLASS__, [$query->toArray()]);
        return $query;
    }


}
