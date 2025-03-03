<?php

namespace App\Modules\Ami\Base;

use App\Exceptions\DataNotFoundException;
use App\Models\Ami\Gfh1207\AttachmentitemModel;
use App\Modules\Common\CommonModule;
use Config;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\Log;

use App\Modules\Ami\Base\GetOneAttachmentitemModuleInterface;


class GetOneAttachmentitemModule extends CommonModule implements GetOneAttachmentitemModuleInterface
{

    public function execute($id){
        $query = AttachmentitemModel::query();
        $query->where('m_account_id',$this->getAccountId());
        $query->where('m_ami_attachment_item_id',$id);
        return $query->first();
    }
}