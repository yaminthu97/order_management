<?php

namespace App\Modules\Ami\Gfh1207;

use App\Events\ModuleCompleted;
use App\Events\ModuleFailed;
use App\Events\ModuleStarted;
use App\Exceptions\DataNotFoundException;
use App\Models\Ami\Base\AmiPageAttachmentItemModel;
use App\Modules\Ami\Base\FindAmiPageAttachmentInterface;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class FindAmiPageAttachment implements FindAmiPageAttachmentInterface
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

            $query = AmiPageAttachmentItemModel::query()
                    ->with([
                        'attachmentItem:m_ami_attachment_item_id,attachment_item_cd,attachment_item_name',
                        'category:m_itemname_types_id,m_itemname_type_name'
                    ])
                    ->where('m_ami_page_id', $id)
                    ->select(
                        'm_ami_page_attachment_items.m_ami_page_attachment_item_id',
                        'm_ami_page_attachment_items.m_ami_page_id',
                        'm_ami_page_attachment_items.m_ami_attachment_item_id',
                        'm_ami_page_attachment_items.category_id',
                        'm_ami_page_attachment_items.group_id',
                        'm_ami_page_attachment_items.item_vol',
                    )
                    ->get();

                } catch(ModelNotFoundException $e) {
                    ModuleFailed::dispatch(__CLASS__, [$id], $e);
                    throw new DataNotFoundException(__('messages.error.data_not_found', ['data' => 'ページ付属品', 'id' => $id]), 0, $e);
                }

        ModuleCompleted::dispatch(__CLASS__, [$query->toArray()]);
        return $query;
    }


}
