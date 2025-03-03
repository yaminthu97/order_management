<?php

namespace App\Modules\Master\Gfh1207;

use App\Enums\DeleteFlg;
use App\Enums\ItemNameType;
use App\Exceptions\DataNotFoundException;
use App\Models\Master\Base\NoshiModel;
use App\Models\Master\Base\NoshiFormatModel;
use App\Modules\Common\CommonModule;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\Log;

use Config;
use DB;

/**
 * 熨斗マスタ取得
 */
class FindNoshiModule extends CommonModule
{
    public function execute( $id ){
        $query = NoshiModel::query()->with('attachmentItemGroup');
        $query->where('m_account_id', $this->getAccountId());
        $query->where('m_noshi_id', $id);
        return $query->first();
    }
}
