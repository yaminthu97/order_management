<?php

namespace App\Http\Controllers\Master;

use App\Modules\Master\Base\UpdateTemplateMasterInterface;
use App\Modules\Master\Gfh1207\Enums\TemplateFileNameEnum;
use App\Modules\Master\Gfh1207\Enums\TemplateFileTypeEnum;
use App\Modules\Order\Base\GetTemplateDataInterface;
use App\Services\EsmSessionManager;
use App\Services\FileUploadManager;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class TemplateMasterListController
{
    // for account code
    protected $accountCode;

    /**
     * Create a new command instance.
     */
    public function __construct(
        EsmSessionManager $esmSessionManager,
    ) {
        $this->accountCode = $esmSessionManager->getAccountCode();
    }

    /**
    * Get data that is template list to show at formload
    */
    public function list(
        Request $request,
        GetTemplateDataInterface $getTemplateData,
    ) {

        $idArray = array_map(fn ($case) => $case->id(), TemplateFileNameEnum::cases());
        $getData = $getTemplateData->execute($idArray);  // to get template data
        $dataList = [];
        foreach ($getData as $key => $value) {
            if (TemplateFileTypeEnum::tryFrom($key)) {
                $mappedKey = TemplateFileTypeEnum::from($key)->label();
                $dataList[$mappedKey] = $value;
            }
        }

        session()->forget('messages.info'); // Clear the  message
        session()->forget('messages.error'); // Clear the  message
        return account_view('master.templatemaster.list', compact('dataList'));
    }


    /**
    * Get data that is clicked to show at edit formload
    */
    public function edit(Request $request, GetTemplateDataInterface $getTemplateData, $id)
    {
        $searchData = [];
        $req = $request->all();
        $getData = $getTemplateData->execute([$id]); // to search template data by clicked id
        if (count($getData) > 0) {
            $searchData = array_values($getData)[0];
        }

        session()->forget('messages.error'); // Clear the error message
        if (count($searchData) == 0) {
            session()->flash('messages.error', ['message' => __('messages.error.data_not_found', ['data' => 'テンプレート','id' => $id])]);
        }

        return account_view('master.templatemaster.edit', compact('searchData', 'req', 'id'));
    }


    /**
    * Update for template data
    */
    public function postUpdate(Request $request, UpdateTemplateMasterInterface $updateTemplateMaster, GetTemplateDataInterface $getTemplateData, FileUploadManager $fileUploadManager, $id)
    {
        $searchData = [];
        $req = $request->all(); // form request
        $getData = $getTemplateData->execute([$id]); // to search template data by clicked id
        if (array_key_exists('error', $getData)) {
            session()->flash('messages.error', ['message' => __('messages.error.process_something_wrong', ['process' => '登録'])]);
            return account_view('master.templatemaster.edit', compact('searchData', 'req', 'id'));
        } else {
            $searchData = array_values($getData)[0];
            $req = $request->all(); // form request
            $originalName = $req['ref_file_path']->getClientOriginalName();

            if (isset($req['ref_file_path']) && $originalName != "未登録") { // If template file selected

                $oldFilePath = $searchData[0]['template_file_name'] != null ? $this->accountCode . '/' . 'template/'. $searchData[0]['report_type'] .'/' . $searchData[0]['template_file_name'] : "";   // old template file path
                $updateTemplateData = $updateTemplateMaster->execute($id, $req);
                $getData = $getTemplateData->execute([$id]); // to search template data by clicked id
                $searchData = array_values($getData)[0];
                $savePath = 'template/'. $searchData[0]['report_type'] ;    // new image save path in s3
                $file = $req['ref_file_path']; // file data from request
                $fileName = $searchData[0]['template_file_name'];

                // // new file uploaded, delete old file in s3
                if (!is_null($oldFilePath)) {
                    $filePath = $fileUploadManager->delete($oldFilePath);
                }

                // new file is uploaded, save in s3
                $savePath = $fileUploadManager->upload($file, $savePath, $fileName);

                // check to upload permission allow or not
                if ($savePath == "") {
                    session()->forget('messages.info');
                    session()->flash('messages.error', ['message' => __('messages.error.upload_s3_failed')]);
                } else {
                    session()->forget('messages.error');
                    session()->flash('messages.info', ['message' => __('messages.info.create_completed', ['data' => 'ファイル'])]);
                }
            } else {
                session()->flash('messages.error', ['message' => __('messages.error.order_search.no_import_file')]);
            }

            return account_view('master.templatemaster.edit', compact('searchData', 'req', 'id'));
        }
    }

    /**
     * Download the template file
    */
    public function postDownload(Request $request, GetTemplateDataInterface $getTemplateData)
    {
        $req = $request->all();
        $getData = $getTemplateData->execute([$req['submit']]); // to search template data by clicked id
        $searchData = array_values($getData)[0];
        $accountCode = $this->accountCode;  // account code
        $report_type = $searchData[0]['report_type'];   // report type
        $template_file_name = $searchData[0]['template_file_name'];    // template file name
        session()->forget('messages.error');
        // s3 path
        $path = "{$accountCode}/template/$report_type/$template_file_name";
        try {
            // check the file exists in s3
            if (Storage::disk('s3')->exists($path)) {
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
            } else {
                session()->flash('messages.error', ['message' => __('messages.error.process_something_wrong2', ['target' => 'ファイル' , 'process' => 'ダウンロード'])]);
            }

        } catch (\Exception $e) {
            session()->flash('messages.error', ['message' => __('messages.error.download_file_not_found', ['filename' => $template_file_name])]);
        }

        $idArray = array_map(fn ($case) => $case->id(), TemplateFileNameEnum::cases());
        $dataList = $getTemplateData->execute($idArray);  // to get template data
        return account_view('master.templatemaster.list', compact('dataList'));

    }
}
