<?php

namespace App\Modules\Order\Gfh1207;

use App\Modules\Order\Base\GetTemplateFilePathInterface;
use Illuminate\Support\Facades\Storage;

class GetTemplateFilePath implements GetTemplateFilePathInterface
{
    /**
     * To get template file path
     */
    public function execute(string $accountCode, array $fileData)
    {
        if (count($fileData) > 0) {

            $path = $accountCode . '/template/'. $fileData[0]['report_type'] . '/' . $fileData[0]['template_file_name'];  //  to save template file path

            // to check template file have or not at server
            if (!Storage::disk(config('filesystems.default', 'local'))->exists($path)) {
                return "";
            } else {
                return $path;
            }
        } else {
            return "";
        }

    }
}
