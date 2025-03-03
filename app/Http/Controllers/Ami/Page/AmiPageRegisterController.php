<?php

namespace App\Http\Controllers\Ami\Page;

use App\Enums\ItemNameType;
use App\Http\Requests\Ami\Gfh1207\AmiPageRequest;
use App\Modules\Ami\Base\DeleteAmiPageAttachmentInterface;
use App\Modules\Ami\Base\DeleteAmiPageNoshiInterface;
use App\Modules\Ami\Base\FindAmiPageAttachmentInterface;
use App\Modules\Ami\Base\FindAmiPageInterface;
use App\Modules\Ami\Base\FindAmiPageNoshiInterface;
use App\Modules\Ami\Base\FindAmiSkuInterface;
use App\Modules\Ami\Base\GetNoshiFormatInterface;
use App\Modules\Ami\Base\StoreAmiPageAttachmentInterface;
use App\Modules\Ami\Base\StoreAmiPageNoshiInterface;
use App\Modules\Ami\Base\UpdateAmiPageAttachmentInterface;
use App\Modules\Ami\Base\UpdateAmiPageInterface;
use App\Modules\Ami\Base\UpdateAmiPageNoshiInterface;
use App\Modules\Master\Base\GetItemnameTypeInterface;
use App\Services\EsmSessionManager;
use App\Services\FileUploadManager;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * ページマスタ追加項目編集
 */
class AmiPageRegisterController
{
    /**
     * NameSpace
     * @var string
     */
    private $namespace = 'Page';

    /**
     * 登録画面テンプレート
     * @var string
     */
    private $regist_tpl = 'page.GFISMB0110';

    /**
     * 変更画面URL
     * @var string
     */
    private $edit_path = 'page/edit/';

    private const INDEX_AMI = 4;    // for index number 4
    private const INDEX_NOSHI = 1;  // for index number 1
    private const IS_IMAGE_DELETE = "1";   // for image is deleted

    // for error code
    private const PRIVATE_THROW_ERR_CODE = -1;

    protected $accountCode;  // for account code

    protected $resourcesDir;

    /**
     * Create a new command instance.
     */
    public function __construct(
        EsmSessionManager $esmSessionManager,
    ) {
        $this->accountCode = $esmSessionManager->getAccountCode();
        $this->resourcesDir = config('filesystems.resources_dir', 'resources');
    }

    /**
     * 変更画面表示
     *
     * @param int $page_id ページマスタ管理ID
     * @return type
     */
    public function edit(
        $page_id,
        GetItemnameTypeInterface $getItemnameType,
        GetNoshiFormatInterface $getNoshiFormat,
        FindAmiSkuInterface $findAmiSku,
        FindAmiPageInterface $findAmiPage,
        FindAmiPageNoshiInterface $findAmiPageNoshi,
        FindAmiPageAttachmentInterface $findAmiPageAttachment,
    ) {
        $form = [];
        $amiPage = $findAmiPage->execute($page_id);
        $amiSku = $findAmiSku->execute($page_id);
        $amiNoshi = $findAmiPageNoshi->execute($page_id);
        $amiAttachment = $findAmiPageAttachment->execute($page_id);

        // for ami attachment data format (join method is using "with")
        $amiAttachmentFormat = [];
        if (!isset($amiAttachment['error']) && !empty($amiAttachment)) {
            $amiAttachmentFormat = array_map(function ($item) {
                $attachmentItem = $item['attachment_item'];
                $category = $item['category'];

                // merge the extracted values into the parent array
                return array_merge($item, [
                    "attachment_item_cd" => $attachmentItem['attachment_item_cd'],
                    "attachment_item_name" => $attachmentItem['attachment_item_name'],
                    "attachment_item_category_name" => $category['m_itemname_type_name'],
                ]);
            }, $amiAttachment->toArray());

            // Remove the original nested arrays
            foreach ($amiAttachmentFormat as &$item) {
                unset($item['attachment_item'], $item['category']);
            }
        }

        // 付属品グループ
        $attachmentGroup = $getItemnameType->execute(ItemNameType::AttachmentGroup->value);

        // 付属品カテゴリ
        $attachmentCategory = $getItemnameType->execute(ItemNameType::AttachmentCategory->value);

        // 熨斗種類
        $noshiFormat = $getNoshiFormat->execute();

        if (isset($amiPage['error']) || isset($amiSku['error']) || isset($amiNoshi['error']) || isset($amiAttachment['error']) ||
        isset($attachmentGroup['error']) || isset($attachmentCategory['error']) || isset($noshiFormat['error'])) {

            $errors = [
                'amiPage' => $amiPage['error'] ?? null,
                'amiSku' => $amiSku['error'] ?? null,
                'amiNoshi' => $amiNoshi['error'] ?? null,
                'amiAttachment' => $amiAttachment['error'] ?? null,
                'attachmentGroup' => $attachmentGroup['error'] ?? null,
                'attachmentCategory' => $attachmentCategory['error'] ?? null,
                'noshiFormats' => $noshiFormat['error'] ?? null,
            ];

            // Find the first error
            foreach ($errors as $key => $error) {
                if ($error) {
                    session()->flash('messages.error', ['message' => __($error)]);
                    break;
                }
            }

            // initial value
            $form = [
                'ami_attachment' => [],
                'ami_noshi' => [],
                'attachment_group' => [],
                'attachment_category' => [],
                'noshi_format' => [],
                'm_ami_sku_id' => '',
                'sku_cd' => '',
                'sku_name' => '',
                'accountCode' => '',
                'resourcesDir' => '',
                'mode' => 'new',
            ];

        } else {
            $form = $amiPage->toArray();
            $form += [
                'ami_attachment' => $amiAttachmentFormat,
                'ami_noshi' => $amiNoshi,
                'attachment_group' => $attachmentGroup,
                'attachment_category' => $attachmentCategory,
                'noshi_format' => $noshiFormat,
                'm_ami_sku_id' => $amiSku['m_ami_sku_id'],
                'sku_cd' => $amiSku['sku_cd'],
                'sku_name' => $amiSku['sku_name'],
                'accountCode' => $this->accountCode,
                'resourcesDir' => $this->resourcesDir,
                'mode' => 'edit',
            ];

        }
        return account_view('ami.gfh_1207.edit', compact('form'));
    }

    /**
     * 変更
     *
     * @param Request $request 画面情報
     * @return type
     */
    public function postEdit(
        $page_id,
        AmiPageRequest $request,
        EsmSessionManager $esmSessionManager,
        FileUploadManager $fileUploadManager,
        FindAmiPageInterface $findAmiPage,
        StoreAmiPageNoshiInterface $storeAmiPageNoshi,
        StoreAmiPageAttachmentInterface $storeAmiPageAttachment,
        UpdateAmiPageInterface $updateAmiPage,
        UpdateAmiPageNoshiInterface $updateAmiPageNoshi,
        UpdateAmiPageAttachmentInterface $updateAmiPageAttachment,
        DeleteAmiPageNoshiInterface $deleteAmiPageNoshi,
        DeleteAmiPageAttachmentInterface $deleteAmiPageAttachment,
    ) {
        $requestData = $request->all();
        // get current data with $page_id
        $amiData = $findAmiPage->execute($page_id);
        $oldImageData = $amiData['image_path'];  // get old image_path

        // data update for m_ami_page
        $updateAmiData = $updateAmiPage->execute($page_id, $requestData);
        $amiImagePath = $updateAmiData['image_path'];  // get new image_path

        $savePath = 'image/page/' . $page_id;    // new image save path in s3
        $oldImagePath = $this->accountCode . '/' . $savePath . '/' . $oldImageData; // old image path in s3

        if (isset($requestData['product_img'])) {
            $file = $requestData['product_img']; // file data from request
            $amiImagePath = $updateAmiData['image_path']; // image_path from database

            // new image uploaded, delete old image in s3
            if (!is_null($oldImageData)) {
                $filePath = $fileUploadManager->resourcesDelete($oldImagePath);
            }

            // new image is uploaded, save in s3
            $savePath = $fileUploadManager->resourcesUpload($file, $savePath, $amiImagePath);

            // check to upload permission allow or not
            if ($savePath == "") {
                // to show [AWS S3へのファイルのアップロードに失敗しました。] message at log
                Log::error('error_message : ' . __('messages.error.upload_s3_failed'));

            }
        }

        // if 商品画像の削除 button is click
        if (!isset($requestData['product_img']) && $requestData['is_delete_ami_page_img'] == self::IS_IMAGE_DELETE) {
            $filePath = $fileUploadManager->resourcesDelete($oldImagePath);

        }

        $amiNoshiData = [];     // for new ami_noshi data
        $oldAmiNoshiData = [];  // for existed ami_noshi data
        $deletedNoshiIds = [];  // for deleted ami_noshi_id save
        foreach ($requestData as $key => $value) {
            if (str_starts_with($key, 'm_noshi_format_id')) {
                $parts = explode('_', $key);
                $index = end($parts);  // get latest part number

                $m_ami_page_noshi_id_key = "m_ami_page_noshi_id_{$index}";
                $m_noshi_format_id_key = "m_noshi_format_id_{$index}";
                $isExist = $requestData["old_m_ami_page_noshi_id_{$index}"];

                $m_ami_page_noshi_id = $requestData[$m_ami_page_noshi_id_key] ?? null;
                $m_noshi_format_id = $requestData[$m_noshi_format_id_key] ?? null;

                if (!is_null($m_ami_page_noshi_id) && !is_null($m_noshi_format_id)) {
                    // save new data, both m_ami_page_noshi_id and m_noshi_format_id is not null
                    if ($isExist == 'new') {
                        $amiNoshiData[] = [
                            'm_ami_page_id' => $page_id,
                            'm_noshi_id' => $requestData["m_noshi_id_{$index}"],
                            'm_noshi_format_id' => $m_noshi_format_id,
                        ];
                    } else {
                        // save old data
                        $oldAmiNoshiData[] = [
                            'm_ami_page_noshi_id' => $requestData["m_ami_page_noshi_id_{$index}"] ?? null,
                            'm_ami_page_id' => $page_id,
                            'm_noshi_id' => $requestData["m_noshi_id_{$index}"] ?? null, // Check if it exists
                            'm_noshi_format_id' => $m_noshi_format_id,
                        ];
                    }
                }

                // save delete id when it exist data
                if ($isExist == 'old' && !is_null($m_ami_page_noshi_id) && is_null($m_noshi_format_id)) {
                    $deletedNoshiIds[] = $m_ami_page_noshi_id;
                }
            }
        }

        // 登録 熨斗設定
        if (!empty($amiNoshiData)) {
            foreach ($amiNoshiData as $record) {
                // data create for m_ami_page_noshi
                $storeAmiPageNoshi->execute($record);
            }
        }

        // 編集 熨斗設定
        if (!empty($oldAmiNoshiData)) {
            foreach ($oldAmiNoshiData as $record) {
                $id = $record['m_ami_page_noshi_id'];
                // data update for m_ami_page_noshi
                $updateAmiPageNoshi->execute($id, $record);
            }
        }

        // 削除 熨斗設定
        if (!empty($deletedNoshiIds)) {
            foreach ($deletedNoshiIds as $id) {
                // data delete for m_ami_page_noshi
                $deleteAmiPageNoshi->execute($id);
            }
        }

        $amiAttachmentData = [];    // for new ami_attachment_items data
        $oldAmiAttachmentData = []; // for deleted ami_attachment_items_id save
        foreach ($requestData as $key => $value) {
            if (preg_match('/^m_ami_attachment_item_id_(\d+)$/', $key, $matches)) {
                $index_slice = self::INDEX_NOSHI;
                $index = $matches[$index_slice];
                if (!is_null($value)) {
                    $amiAttachmentExist = $requestData["m_ami_page_attachment_item_id_{$index}"] ?? null;
                    if ($amiAttachmentExist) {
                        // save old data
                        $oldAmiAttachmentData[] = [
                            'm_ami_attachment_item_id' => $value,
                            'attachment_item_group_id' => $requestData["attachment_item_group_id_{$index}"] ?? null,
                            'attachment_item_category_name' => $requestData["attachment_item_category_name_{$index}"] ?? null,
                            'attachment_item_category_id' => $requestData["attachment_item_category_id_{$index}"] ?? null,
                            'attachment_item_cd' => $requestData["attachment_item_cd_{$index}"] ?? null,
                            'attachment_item_name' => $requestData["attachment_item_name_{$index}"] ?? null,
                            'attachment_item_vol' => $requestData["attachment_item_vol_{$index}"] ?? null,
                            'm_ami_page_id' => $page_id,
                        ];
                    } else {
                        // save new data, m_ami_page_attachment_item_id is null
                        $amiAttachmentData[] = [
                            'm_ami_attachment_item_id' => $value,
                            'attachment_item_group_id' => $requestData["attachment_item_group_id_{$index}"] ?? null,
                            'attachment_item_category_name' => $requestData["attachment_item_category_name_{$index}"] ?? null,
                            'attachment_item_category_id' => $requestData["attachment_item_category_id_{$index}"] ?? null,
                            'attachment_item_cd' => $requestData["attachment_item_cd_{$index}"] ?? null,
                            'attachment_item_name' => $requestData["attachment_item_name_{$index}"] ?? null,
                            'attachment_item_vol' => $requestData["attachment_item_vol_{$index}"] ?? null,
                            'm_ami_page_id' => $page_id,
                        ];
                    }
                }
            }
        }

        // 登録 付属品
        if (!empty($amiAttachmentData)) {
            foreach ($amiAttachmentData as $record) {
                // data create for m_ami_page_attachment_items
                $storeAmiPageAttachment->execute($record);
            }
        }

        // 編集 付属品
        if (!empty($oldAmiAttachmentData)) {
            foreach ($oldAmiAttachmentData as $record) {
                $id = $record['m_ami_attachment_item_id'];
                // data update for m_ami_page_attachment_items
                $updateAmiPageAttachment->execute($id, $record);
            }
        }

        // 削除 付属品
        $deletedAttachmentIds = json_decode($requestData['deleted_attachment_ids'], true);
        if (!empty($deletedAttachmentIds)) {
            foreach ($deletedAttachmentIds as $id) {
                // data delete for m_ami_page_attachment_items
                $deleteAmiPageAttachment->execute($id);
            }
        }

        return redirect(esm_external_route('ami/page/list', []));
    }


    /**
     * 画像返却
     */
    public function image(
        Request $request,
        FindAmiPageInterface $findAmiPage,
    ) {
        // get image and page id
        $page_id = $request->input('m_ami_page_id');
        // get account code
        $accountCode = $this->accountCode;
        // get current data with $page_id
        $amiData = $findAmiPage->execute($page_id);
        $imagePath = $amiData['image_path'];  // get image_path value

        if (!$accountCode || !$page_id || !$imagePath) {
            abort(400, 'Missing parameters');
        }

        // s3 path
        $path = "{$this->resourcesDir}/{$accountCode}/image/page/{$page_id}/{$imagePath}";

        // check the file exists in s3
        if (!Storage::disk('s3')->exists($path)) {
            abort(404, 'File not found');
        }

        // read stream for image file
        $stream = Storage::disk('s3')->readStream($path);

        return new StreamedResponse(function () use ($stream) {

            if (!$stream) {
                abort(500, 'Failed to read file from storage');
            }

            // ストリームを直接出力
            fpassthru($stream);
            fclose($stream);

        }, 200, [
            'Content-Type' => Storage::disk('s3')->mimeType($path),
            'Content-Disposition' => 'inline; filename="' . basename($path) . '"',
        ]);
    }


}
