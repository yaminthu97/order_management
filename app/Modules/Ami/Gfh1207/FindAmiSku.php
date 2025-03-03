<?php

namespace App\Modules\Ami\Gfh1207;

use App\Events\ModuleCompleted;
use App\Events\ModuleStarted;
use App\Models\Ami\Base\AmiPageSkuModel;
use App\Models\Ami\Base\AmiSkuModel;
use App\Modules\Ami\Base\FindAmiSkuInterface;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\Log;

class FindAmiSku implements FindAmiSkuInterface
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
            $ami_page_sku_id = AmiPageSkuModel::query()
                    ->where('m_ami_page_id', $id)
                    ->select('m_ami_sku_id')
                    ->pluck('m_ami_sku_id')
                    ->first();

            $query = AmiSkuModel::query();
            $query = $query->findOrFail($ami_page_sku_id);

        } catch (ModelNotFoundException $e) {
            Log::error('Database connection error: ' . $e->getMessage());
            return ['error' => '指定されたSKUは存在しません。'];
        }

        ModuleCompleted::dispatch(__CLASS__, [$query->toArray()]);
        return $query;
    }


}
