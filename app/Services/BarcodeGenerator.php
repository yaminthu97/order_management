<?php
namespace App\Services;

use Illuminate\Support\Facades\Log;

class BarcodeGenerator
{
    private function getBarcodeImageFileName(string $code)
    {
        return sys_get_temp_dir() . '/' . $code . '_barcode.png';
    }
    private function getCheckDigit($code){
        $reverse = strrev($code);
        $even = 0;
        $odd = 0;
        for($idx=0;$idx<strlen($reverse);$idx++){
            if($idx % 2 == 0){
                $even += (int)$reverse[$idx];
            } else {
                $odd += (int)$reverse[$idx];
            }
        }
        $even *= 3;
        $c = (string)($even + $odd);
        return substr($c,-1);
    }
    public function appendCheckdigit(string $code = ''){
        return $code . $this->getCheckDigit($code);
    }
    public function generateBarcodeImage(string $code = '')
    {
        require_once __DIR__.'/../Packages/barcode.php/EAN128.php';
        $barcode = new \GS1_128();
        $barcode->TextWrite = false;

        // ↓↓↓黒バーを1ドット細くします。↓↓↓

        // ただし、全体のバーコードのサイズを大きくしないと
        // 1ドット細くすると細くなりすぎてしまいます。
        $barcode->KuroBarCousei = 0;
        // そこで、バーコードの横幅を4倍にします。
        // 具体的には、バーコードを描画する最小ドット幅を
        // 1ドットから4ドットへ変更します。
        $barcode->minWidthDot = 4;
        // できあがったバーコードの画像ファイルが大きくても
        // その後、指定サイズでPDFにその画像を読み込むため問題ありません。

        // ↑↑↑黒バーを1ドット細くします。↑↑↑

        $barcodeData = '{FNC1}' . $code;
        $barcodeDisplay1 = '(' . substr($barcodeData, 6, 2) . ')' . substr($barcodeData, 8, 6) . '-' . substr($barcodeData, 14, 22);
        $barcodeDisplay2 = substr($barcodeData, 36, 6) . '-' . substr($barcodeData, 42, 1) . '-' . substr($barcodeData, 43, 6) . '-' . substr($barcodeData, 49, 1);

        $height = 10.8; //mm 単位
        $img = $barcode->DrawConvenience($barcodeData, 2, $height);
        $fileName = $this->getBarcodeImageFileName($code);
        ImagePNG($img, $fileName);
        $imageSize = GetImageSize($fileName);

        return [
            'barcodeFileName' => $fileName,
            'barcodeImageSize' => $imageSize,
            'barcodeDisplay1' => $barcodeDisplay1,
            'barcodeDisplay2' => $barcodeDisplay2,
        ];
    }

    public function removeBarcodeImageFile(string $code)
    {
        $fileName = $this->getBarcodeImageFileName($code);
        unlink($fileName);
    }
}
