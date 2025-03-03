<?php

namespace App\Modules\Ami\Gfh1207;

use App\Events\ModuleCompleted;
use App\Events\ModuleFailed;
use App\Events\ModuleStarted;
use App\Exceptions\ModuleValidationException;
use App\Models\Ami\Base\AmiPageModel;
use App\Modules\Ami\Base\UpdateAmiPageInterface;
use App\Services\EsmSessionManager;
use App\Services\FileUploadManager;
use Illuminate\Support\Facades\DB;

class UpdateAmiPage implements UpdateAmiPageInterface
{
    /**
    * ESMセッション管理クラス
    */
    protected $esmSessionManager;
    protected $fileUploadManager;

    private const IS_IMAGE_DELETE = "1"; // for image is deleted

    public function __construct(
        EsmSessionManager $esmSessionManager,
        FileUploadManager $fileUploadManager
    ) {
        $this->esmSessionManager = $esmSessionManager;
        $this->fileUploadManager = $fileUploadManager;
    }

    //DBへの保存コード
    public function execute(string|int $id, array $data)
    {
        ModuleStarted::dispatch(__CLASS__, compact('id', 'data'));

        try {
            // トランザクションを張る
            $new = DB::transaction(function () use ($id, $data) {
                $operatorId = $this->esmSessionManager->getOperatorId();
                $new = AmiPageModel::findOrFail($id);

                $errors = [];
                // データを設定
                $new->page_title = $data['page_title'];
                $new->sales_price = $data['sales_price'];
                $new->tax_rate = $data['tax_rate'];
                $new->print_page_title = $data['print_page_title'];
                $new->sales_start_datetime = $data['sales_start_datetime'];
                $new->search_result_display_flg = $data['search_result_display_flg'];
                $new->page_desc = $data['page_desc'];
                $new->remarks1 = $data['remarks1'];
                $new->remarks2 = $data['remarks2'];
                $new->remarks3 = $data['remarks3'];
                $new->remarks4 = $data['remarks4'];
                $new->remarks5 = $data['remarks5'];
                $new->update_operator_id = $operatorId;

                if (isset($data['product_img'])) {
                    $file = $data['product_img'];
                    $originalName = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
                    $extension = $file->getClientOriginalExtension();
                    $uniqueImgName = $originalName . '_' . uniqid() . '.' . $extension;

                    $new->image_path = $uniqueImgName;
                }

                if (!isset($data['product_img']) && $data['is_delete_ami_page_img'] == self::IS_IMAGE_DELETE) {
                    $new->image_path = null;
                }

                // Error handling
                if(count($errors) > 0) {
                    // ただし、確認画面経由後の登録処理の場合、バリデーションメッセージとして画面に表示することはないと思われる
                    throw new ModuleValidationException(__CLASS__, 0, null, $errors);
                }

                // 保存
                $new->save();
                return $new;
            });
        } catch(\Exception $e) {
            ModuleFailed::dispatch(__CLASS__, $e);
            throw $e;
        }

        ModuleCompleted::dispatch(__CLASS__, [$new->toArray()]);
        return $new;
    }

}
