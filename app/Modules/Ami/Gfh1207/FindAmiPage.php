<?php

namespace App\Modules\Ami\Gfh1207;

use App\Events\ModuleCompleted;
use App\Events\ModuleFailed;
use App\Events\ModuleStarted;
use App\Models\Ami\Base\AmiPageModel;
use App\Modules\Ami\Base\FindAmiPageInterface;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class FindAmiPage implements FindAmiPageInterface
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
            $query = AmiPageModel::query();
            $query = $query->findOrFail($id);

        } catch(ModelNotFoundException $e) {
            ModuleFailed::dispatch(__CLASS__, [$id], $e);
            return ['error' => __('messages.error.data_not_found', ['data' => 'ページ', 'id' => $id])];
        }

        ModuleCompleted::dispatch(__CLASS__, [$query->toArray()]);
        return $query;
    }


}
