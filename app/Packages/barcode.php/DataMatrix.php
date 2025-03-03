<?php

require_once("CheckDigit.php");

/**
 * シンボル コードサイズ
 * 
 * シンボル コードサイズ
 */
class DxCodeSize {
	/* シンボル コードサイズ */
	const DxSzRectAuto = -3;
	const DxSzAuto = -2;
	const DxSzShapeAuto = -1;
	const DxSz10x10 = 0;
	const DxSz12x12 = 1;
	const DxSz14x14 = 2;
	const DxSz16x16 = 3;
	const DxSz18x18 = 4;
	const DxSz20x20 = 5;
	const DxSz22x22 = 6;
	const DxSz24x24 = 7;
	const DxSz26x26 = 8;
	const DxSz32x32 = 9;
	const DxSz36x36 = 10;
	const DxSz40x40 = 11;
	const DxSz44x44 = 12;
	const DxSz48x48 = 13;
	const DxSz52x52 = 14;
	const DxSz64x64 = 15;
	const DxSz72x72 = 16;
	const DxSz80x80 = 17;
	const DxSz88x88 = 18;
	const DxSz96x96 = 19;
	const DxSz104x104 = 20;
	const DxSz120x120 = 21;
	const DxSz132x132 = 22;
	const DxSz144x144 = 23;
	const DxSz8x18 = 24;
	const DxSz8x32 = 25;
	const DxSz12x26 = 26;
	const DxSz12x36 = 27;
	const DxSz16x36 = 28;
	const DxSz16x48 = 29;
}


/**
 * DataMatrix作成クラス
 */
class DataMatrix {

	/*! データ列数・行数決定方法 : AUTO/COLUMNS/ROWS/COLUMNS_AND_ROWS */
	var $CodeSize = DxCodeSize::DxSzAuto;

	/*! 全角文字コード 例："utf-8" / "shift-jis" / "932" ...等... */
	var $StringEncoding = "utf-8";

	/**
	* 指定された幅に伸縮したDataMatrixを描画します。(読み取り精度は低下します。)
	* @param $code 描画を行うバーコードのコード(テキスト)
	* @param $width DataMatrixの幅を指定(単位：ドット)
	* @return DataMatrixのイメージを返します。
	*/
	function Draw_by_width($code, $width)
	{
		try {
			$wk = (int)$code;
			if($wk < 0)
			{
				trigger_error("DataMatrix : width is not plus.",E_USER_ERROR);
				exit;
			}
		} catch (Exception $ex) {
			trigger_error("DataMatrix : width is not number.",E_USER_ERROR);
			exit;
		}

		if(isOK()) {
			$img_output =ImageCreate($width, $width);
		} else {
			$img_output =ImageCreate($width, $width+5);
			$white = ImageColorAllocate($img_output, 0xff, 0xff, 0xff);
			ImageFilledRectangle($img_output, $width,$width, $width,$width+5, $white);
		}
		$img_base = $this->draw($code);
		$w_base = imagesx($img_base);
		$h_base = imagesy($img_base);
		ImageCopyResized($img_output,$img_base,0,0,0,0, $width, $width * $h_base / $w_base, $w_base, $h_base);

		if(!isOK()) {
			//SAMPLE 描画
			$red = ImageColorAllocate($img_output, 0xFF, 0x00, 0x00);
			ImageTTFText($img_output, 5, 0, 0, $width+5 ,$red, "./font/mplus-1p-black.ttf", "SAMPLE");
		}

		return $img_output;

	}


	/**
	* サイズ(1,2,4,8,16等)を指定して読み取り精度の高いDataMatrixを描画します。
	* @param $code 描画を行うDataMatrixのコード(テキスト)
	* @param $size 1,2,4,8,16 等を指定
	* @return DataMatrixのイメージを返します。
	*/
	function draw_by_size($code, $size)
	{

		$img_base = $this->draw($code);
		//$w_base=$this->mm1+8;
		$w_base = imagesx($img_base);
		$h_base = imagesy($img_base);

		$width = $w_base*$size;
		if(isOK()) {
			$img_output =ImageCreate($width, $width);
		} else {
			$img_output =ImageCreate($width, $width+5);
			$white = ImageColorAllocate($img_output, 0xff, 0xff, 0xff);
			ImageFilledRectangle($img_output, $width,$width, $width,$width+5, $white);
		}


		ImageCopyResized($img_output,$img_base,0,0,0,0, $w_base * $size, $h_base * $size, $w_base, $h_base);

		if(!isOK()) {
			//SAMPLE 描画
			$red = ImageColorAllocate($img_output, 0xFF, 0x00, 0x00);
			ImageTTFText($img_output, 5, 0, 0, $width+5 ,$red, "./font/mplus-1p-black.ttf", "SAMPLE");
		}

		return $img_output;

	}


	/**
	 * バーコードの描画を行います。バーコード全体の幅を指定するのではなく、バーを描画する最小単位のドット数を指定します。(1～)
	 * @param $code 描画を行うバーコードのコード(テキスト)
	 * @param $minLinePitch 最少描画ドット数
	 * @return バーコードのイメージを返します。
	 */
	function draw ($code) 
	{
		$minLinePitch = 1;
		
		global $CodeSize, $StringEncoding;

		$s = $this->CalDataMatrix_String($code);
		
		if ($s == null)
		{
			throw new Exception("Length over!");
		}

		// 本当は Width と Height は違う
		$pWidth = (int)(count($s) * $minLinePitch);
		$pHeight = (int)(count($s[0]) * $minLinePitch);
		$img = ImageCreate($pWidth, $pHeight);
		$white = ImageColorAllocate($img, 0xFF, 0xFF, 0xFF);
		$black = ImageColorAllocate($img, 0x00, 0x00, 0x00);
		for ($i = 0; ((($i < count($s) /*from: s.length*/)) && (($i < count($s[$i]) /*from: s[i].length*/))); ++$i) 
		{
			for ($j = 0; ($j < count($s) /*from: s.length*/); ++$j) 
			{
				if ($s[$j][$i])
				{
					imagesetpixel ($img, $j * $minLinePitch, $i * $minLinePitch , $black );
					//imagefilledrectangle ($img, $j * $minLinePitch, $i * $minLinePitch
					//, $j * $minLinePitch + $minLinePitch, $i * $minLinePitch + $minLinePitch , $black );

					imagefilledrectangle ($img, $j * $minLinePitch, $i * $minLinePitch
						, $j * $minLinePitch, $i * $minLinePitch , $black );

				}
			}
		}
		
		return $img;

	}

	function __construct(){
		$this->__init();
	}


	protected $ppFile;	// String
	protected $pG;	// Graphics2D
	protected $pMinLinePitch;	// float
	protected $pCode;	// String
	protected $pX;	// float
	protected $pY;	// float
	protected $pHeight;	// float
	protected $pWidth;	// float
	protected $cm;	// Common
	protected $pAngle;	// float
	protected $pFilePath;	// String
	protected $pWidthDelicate;	// float
	protected $pWidthDirect;	// float
	protected $imgFilePath;	// String
	protected $imgMargin;	// int
	protected $stringEncoding;	// String
	protected $codeSize;	// DxCodeSize
	protected $pData;	// byte[]
	private function __init() { // default class members
		$this->pCode = "";
		$this->pX = 0;
		$this->pY = 0;
		$this->pAngle = 0;
		$this->pFilePath = "";
		$this->imgMargin = 1;
		
		$this->CodeSize = DxCodeSize::DxSzAuto;
		$this->StringEncoding = "UTF-8";

	}

	private function CalDataMatrix_String ($val) // [String val]
	{
		return $this->encodeRawData_String_DxImageEncoderOptions($val, new DxImageEncoderOptions());
	}
	private function CalDataMatrix_aB ($bVal) // [byte[] bVal]
	{
		return $this->encodeRawData_aB_DxImageEncoderOptions($bVal, new DxImageEncoderOptions());
	}
	private function encodeRawData_String_DxImageEncoderOptions ($val, $options) // [String val, DxImageEncoderOptions options]
	{
		$encode = new DxEncode();
		$encode->moduleSize = 1;
		$encode->marginSize = 0;
		$encode->sizeIdxRequest = $this->CodeSize;
		$encode->scheme = $options->Scheme;
		$options->EncodingString = $this->StringEncoding;
		$options->SizeIdx = $this->CodeSize;
		$encode->sizeIdxRequest = $this->CodeSize;
		$valAsByteArray = DataMatrix::getRawDataAndSetEncoding($val, $options, $encode);
		$encode->encodeDataMatrixRaw($valAsByteArray);
		return $encode->rawData;
	}
	private function encodeRawData_aB_DxImageEncoderOptions ($bVal, $options) // [byte[] bVal, DxImageEncoderOptions options]
	{
		$encode = new DxEncode();
		$encode->moduleSize = 1;
		$encode->marginSize = 0;
		$encode->sizeIdxRequest = $this->CodeSize;
		$encode->scheme = $options->Scheme;
		$options->SizeIdx = $this->CodeSize;
		$encode->sizeIdxRequest = $this->CodeSize;
		$encode->encodeDataMatrixRaw($bVal);
		return $encode->rawData;
	}
	private function getRawDataAndSetEncoding ($code, $options, $encode) // [String code, DxImageEncoderOptions options, DxEncode encode]
	{
		if ((strpos($code, "{FNC1}") == false))
		{
			$result = $this->Str2Bytes($code, $this->StringEncoding);
			$encode->scheme = $options->Scheme;
			return $result;
		}
		else
		{
			$b_tab = $this->Str2Bytes("\t", "ASCII");
			$code = str_replace("{FNC1}", "\t", $code);
			$result = $this->Str2Bytes($code, "ASCII");
			$encode->flgGS1 =  TRUE ;
			for ($i = 0; ($i < count($result) /*from: result.length*/); ++$i) 
			{
				if (($result[$i] == $b_tab[0]))
				{
					$result[$i] = DxConstants::$DxCharFNC1;
				}
			}
			$encode->scheme = DxScheme::DxSchemeAscii;
			return $result;
		}
	}
	
	private function Str2Bytes($s, $encofing)
	{
		$s2 = mb_convert_encoding($s, $encofing, "UTF-8, EUC-JP, JIS, SJIS, eucjp-win, sjis-win");
		$bytes = array();
		for($i = 0; $i < strlen($s2); $i++){
			$bytes[] = ord($s2[$i]);
		}
		return $bytes;
	}
}

class DxPackOrder {
	const DxPackCustom = 100;
	const DxPack1bppK = 200;
	const DxPack8bppK = 300;
	const DxPack16bppRGB = 400;
	const DxPack16bppRGBX = 401;
	const DxPack16bppXRGB = 402;
	const DxPack16bppBGR = 403;
	const DxPack16bppBGRX = 404;
	const DxPack16bppXBGR = 405;
	const DxPack16bppYCbCr = 406;
	const DxPack24bppRGB = 500;
	const DxPack24bppBGR = 501;
	const DxPack24bppYCbCr = 502;
	const DxPack32bppRGBX = 600;
	const DxPack32bppXRGB = 601;
	const DxPack32bppBGRX = 602;
	const DxPack32bppXBGR = 603;
	const DxPack32bppCMYK = 604;
}

class DxFlip {
	const DxFlipNone = 0x00;
	const DxFlipX =  0x01;
	const DxFlipY =  0x10;
}


class DxScheme {
	const DxSchemeAutoFast = -2;
	const DxSchemeAutoBest = -1;
	const DxSchemeAscii = 0;
	const DxSchemeC40 = 1;
	const DxSchemeText = 2;
	const DxSchemeX12 = 3;
	const DxSchemeEdifact = 4;
	const DxSchemeBase256 = 5;
	const DxSchemeAsciiGS1 = 6;
}

class DxImageEncoderOptions {
	public $MarginSize;	// int
	public $ModuleSize;	// int
	public $Scheme;	// DxScheme
	public $SizeIdx;	// DxCodeSize
	public $ForeColor;	// java.awt.Color
	public $BackColor;	// java.awt.Color
	public $Encoding;	// String
	public $EncodingVal;	// int
	private function __init() { // default class members
		$this->Scheme = DxScheme::DxSchemeAutoFast;
		$this->SizeIdx = DxCodeSize::DxSzRectAuto;
	}
	function __construct () 
	{
		//$white = ImageColorAllocate($img, 0xFF, 0xFF, 0xFF);
		//$black = ImageColorAllocate($img, 0x00, 0x00, 0x00);
		$white = 0xFFFFFF;
		$black = 0x0;

		$this->__init();
		$this->BackColor = $white;
		$this->ForeColor = $black;
		$this->SizeIdx = DxCodeSize::DxSzAuto;
		$this->Scheme = DxScheme::DxSchemeAscii;
		$this->ModuleSize = 5;
		$this->MarginSize = 10;
		$this->EncodingString = "UTF-8";
	}
}


class DxEncode {
	public $method;	// int
	public $scheme;	// DxScheme
	public $sizeIdxRequest;	// DxCodeSize
	public $marginSize;	// int
	public $moduleSize;	// int
	public $pixelPacking;	// DxPackOrder
	public $imageFlip;	// DxFlip
	public $rowPadBytes;	// int
	public $message;	// DxMessage
	public $image;	// DxImage
	public $region;	// DxRegion
	public $rawData;	// boolean[][]
	public $flgGS1;	// boolean
	private function __init() { // default class members
		$this->scheme = DxScheme::DxSchemeAutoFast;
		$this->sizeIdxRequest = DxCodeSize::DxSzRectAuto;
		$this->pixelPacking = DxPackOrder::DxPackCustom;
		$this->imageFlip = DxFlip::DxFlipNone;
		$this->flgGS1 =  FALSE ;
	}
	function __construct () 
	{
		$this->__init();
		$this->scheme = DxScheme::DxSchemeAscii;
		$this->sizeIdxRequest = DxCodeSize::DxSzAuto;
		$this->marginSize = 10;
		$this->moduleSize = 5;
		$this->pixelPacking = DxPackOrder::DxPack24bppRGB;
		$this->imageFlip = DxFlip::DxFlipNone;
		$this->rowPadBytes = 0;
	}
	public static function constructor__DxEncode ($src) // [DxEncode src]
	{
		$this->__init();

		$this->scheme = $src->scheme;
		$this->sizeIdxRequest = $src->sizeIdxRequest;
		$this->marginSize = $src->marginSize;
		$this->moduleSize = $src->moduleSize;
		$this->pixelPacking = $src->pixelPacking;
		$this->imageFlip = $src->imageFlip;
		$this->rowPadBytes = $src->rowPadBytes;
		$this->image = $src->image;
		$this->message = $src->message;
		$this->method = $src->method;
		$this->region = $src->region;
	}
	public function encodeDataMatrixRaw ($inputString) // [byte[] inputString]
	{
		/* match: Colorawtjava_Colorawtjava_aB_b */
		return $this->encodeDataMatrix_Colorawtjava_Colorawtjava_aB_b(NULL, NULL, $inputString,  TRUE );
	}
	public function encodeDataMatrix_Colorawtjava_Colorawtjava_aB ($foreColor, $backColor, $inputString) // [java.awt.Color foreColor, java.awt.Color backColor, byte[] inputString]
	{
		/* match: Colorawtjava_Colorawtjava_aB_b */
		return $this->encodeDataMatrix_Colorawtjava_Colorawtjava_aB_b($foreColor, $backColor, $inputString,  FALSE );
	}
	public function encodeDataMatrix_Colorawtjava_Colorawtjava_aB_b ($foreColor, $backColor, $inputString, $encodeRaw) // [java.awt.Color foreColor, java.awt.Color backColor, byte[] inputString, boolean encodeRaw]
	{
		$buf = array();
		for($ii = 0; $ii < 4096; $ii++)
		{
			$buf[$ii] = 0;
		}
			
		$sizeIdx = $this->sizeIdxRequest;
		$dataWordCount = $this->encodeDataCodewords($buf, $inputString, $sizeIdx);
		if (($dataWordCount <= 0))
		{
			return  FALSE ;
		}
		if ((($sizeIdx == DxCodeSize::DxSzAuto) || ($sizeIdx == DxCodeSize::DxSzRectAuto)))
		{
			throw new Exception("Invalid symbol size for encoding!");
		}
		$padCount = $this->addPadChars($buf, $dataWordCount, DxCommon::getSymbolAttribute(DxSymAttribute::DxSymAttribSymbolDataWords, $sizeIdx));
		$this->region = new DxRegion(null);
		$this->region->SizeIdx = $sizeIdx;
		$this->region->SymbolRows = DxCommon::getSymbolAttribute(DxSymAttribute::DxSymAttribSymbolRows, $sizeIdx);
		$this->region->SymbolCols = DxCommon::getSymbolAttribute(DxSymAttribute::DxSymAttribSymbolCols, $sizeIdx);
		$this->region->MappingRows = DxCommon::getSymbolAttribute(DxSymAttribute::DxSymAttribMappingMatrixRows, $sizeIdx);
		$this->region->MappingCols = DxCommon::getSymbolAttribute(DxSymAttribute::DxSymAttribMappingMatrixCols, $sizeIdx);
		$this->message = new DxMessage($sizeIdx, DxFormat::Matrix);
		$this->message->PadCount = $padCount;
		for ($i = 0; ($i < $dataWordCount); ++$i) 
		{
			$this->message->Code[$i] = $buf[$i];
		}
		DxCommon::genReedSolEcc($this->message, $this->region->SizeIdx);
		$this->modulePlacementEcc200($this->message->Array, $this->message->Code, $this->region->SizeIdx, DxConstants::$DxModuleOnRGB);
		$width = ((2 * $this->marginSize) + (($this->region->SymbolCols * $this->moduleSize)));
		$height = ((2 * $this->marginSize) + (($this->region->SymbolRows * $this->moduleSize)));
		$bitsPerPixel = DxCommon::getBitsPerPixel($this->pixelPacking);
		if (($bitsPerPixel == DxConstants::$DxUndefined))
			return  FALSE ;
		if ((($bitsPerPixel % 8) != 0))
		{
			throw new Exception("Invalid java.awt.Color  depth for encoding!");
		}
		$pxl = array();
		for($ii = 0; $ii < $width * $height * ($bitsPerPixel / 8) + $this->rowPadBytes; $ii++)
		{
			$pxl[$ii] = 0x00;
		}
		
		$this->image = new DxImage($pxl, $width, $height, $this->pixelPacking);
		$this->image->ImageFlip = $this->imageFlip;
		$this->image->RowPadBytes = $this->rowPadBytes;
		if ($encodeRaw)
			$this->printPatternRaw();
		else
			$this->printPattern($foreColor, $backColor);
		return  TRUE ;
	}
	
	protected function modulePlacementEcc200 (&$modules, $codewords, $sizeIdx, $moduleOnColor) // [byte[] modules, byte[] codewords, DxCodeSize sizeIdx, int moduleOnColor]
	{
		if (((($moduleOnColor & (((DxConstants::$DxModuleOnRed | DxConstants::$DxModuleOnGreen) | DxConstants::$DxModuleOnBlue)))) == 0))
		{
			throw new Exception("Error with module placement ECC 200");
		}
		$mappingRows = DxCommon::getSymbolAttribute(DxSymAttribute::DxSymAttribMappingMatrixRows, $sizeIdx);
		$mappingCols = DxCommon::getSymbolAttribute(DxSymAttribute::DxSymAttribMappingMatrixCols, $sizeIdx);
		$chr = 0;
		$row = 4;
		$col = 0;
		do 
		{
			if (((($row == $mappingRows)) && (($col == 0))))
				DxEncode::patternShapeSpecial1($modules, $mappingRows, $mappingCols, $codewords, $chr++, $moduleOnColor);
			else
				if ((((($row == ($mappingRows - 2))) && (($col == 0))) && ((($mappingCols % 4) != 0))))
					DxEncode::patternShapeSpecial2($modules, $mappingRows, $mappingCols, $codewords, $chr++, $moduleOnColor);
				else
					if ((((($row == ($mappingRows - 2))) && (($col == 0))) && ((($mappingCols % 8) == 4))))
						DxEncode::patternShapeSpecial3($modules, $mappingRows, $mappingCols, $codewords, $chr++, $moduleOnColor);
					else
						if ((((($row == ($mappingRows + 4))) && (($col == 2))) && ((($mappingCols % 8) == 0))))
							DxEncode::patternShapeSpecial4($modules, $mappingRows, $mappingCols, $codewords, $chr++, $moduleOnColor);
			do 
			{
				if ((((($row < $mappingRows)) && (($col >= 0))) && ((($modules[(($row * $mappingCols) + $col)] & DxConstants::$DxModuleVisited)) == 0)))
					DxEncode::patternShapeStandard($modules, $mappingRows, $mappingCols, $row, $col, $codewords, $chr++, $moduleOnColor);
				$row -= 2;
				$col += 2;
			}
			while (((($row >= 0)) && (($col < $mappingCols))));
			$row += 1;
			$col += 3;
			do 
			{
				if ((((($row >= 0)) && (($col < $mappingCols))) && ((($modules[(($row * $mappingCols) + $col)] & DxConstants::$DxModuleVisited)) == 0)))
					DxEncode::patternShapeStandard($modules, $mappingRows, $mappingCols, $row, $col, $codewords, $chr++, $moduleOnColor);
				$row += 2;
				$col -= 2;
			}
			while (((($row < $mappingRows)) && (($col >= 0))));
			$row += 3;
			$col += 1;
		}
		while (((($row < $mappingRows)) || (($col < $mappingCols))));
		if (((($modules[(($mappingRows * $mappingCols) - 1)] & DxConstants::$DxModuleVisited)) == 0))
		{
			$modules[(($mappingRows * $mappingCols) - 1)] |= $moduleOnColor;
			$modules[(((($mappingRows * $mappingCols)) - $mappingCols) - 2)] |= $moduleOnColor;
		}
		return $chr;
	}
	
	public static function patternShapeStandard (&$modules, $mappingRows, $mappingCols, $row, $col, $codeword, $codeWordIndex, $moduleOnColor) // [byte[] modules, int mappingRows, int mappingCols, int row, int col, byte[] codeword, int codeWordIndex, int moduleOnColor]
	{
		DxEncode::placeModule($modules, $mappingRows, $mappingCols, ($row - 2), ($col - 2), $codeword, $codeWordIndex, DxMaskBit::DxMaskBit1, $moduleOnColor);
		DxEncode::placeModule($modules, $mappingRows, $mappingCols, ($row - 2), ($col - 1), $codeword, $codeWordIndex, DxMaskBit::DxMaskBit2, $moduleOnColor);
		DxEncode::placeModule($modules, $mappingRows, $mappingCols, ($row - 1), ($col - 2), $codeword, $codeWordIndex, DxMaskBit::DxMaskBit3, $moduleOnColor);
		DxEncode::placeModule($modules, $mappingRows, $mappingCols, ($row - 1), ($col - 1), $codeword, $codeWordIndex, DxMaskBit::DxMaskBit4, $moduleOnColor);
		DxEncode::placeModule($modules, $mappingRows, $mappingCols, ($row - 1), $col, $codeword, $codeWordIndex, DxMaskBit::DxMaskBit5, $moduleOnColor);
		DxEncode::placeModule($modules, $mappingRows, $mappingCols, $row, ($col - 2), $codeword, $codeWordIndex, DxMaskBit::DxMaskBit6, $moduleOnColor);
		DxEncode::placeModule($modules, $mappingRows, $mappingCols, $row, ($col - 1), $codeword, $codeWordIndex, DxMaskBit::DxMaskBit7, $moduleOnColor);
		DxEncode::placeModule($modules, $mappingRows, $mappingCols, $row, $col, $codeword, $codeWordIndex, DxMaskBit::DxMaskBit8, $moduleOnColor);
	}
	public static function patternShapeSpecial1 (&$modules, $mappingRows, $mappingCols, $codeword, $codeWordIndex, $moduleOnColor) // [byte[] modules, int mappingRows, int mappingCols, byte[] codeword, int codeWordIndex, int moduleOnColor]
	{
		DxEncode::placeModule($modules, $mappingRows, $mappingCols, ($mappingRows - 1), 0, $codeword, $codeWordIndex, DxMaskBit::DxMaskBit1, $moduleOnColor);
		DxEncode::placeModule($modules, $mappingRows, $mappingCols, ($mappingRows - 1), 1, $codeword, $codeWordIndex, DxMaskBit::DxMaskBit2, $moduleOnColor);
		DxEncode::placeModule($modules, $mappingRows, $mappingCols, ($mappingRows - 1), 2, $codeword, $codeWordIndex, DxMaskBit::DxMaskBit3, $moduleOnColor);
		DxEncode::placeModule($modules, $mappingRows, $mappingCols, 0, ($mappingCols - 2), $codeword, $codeWordIndex, DxMaskBit::DxMaskBit4, $moduleOnColor);
		DxEncode::placeModule($modules, $mappingRows, $mappingCols, 0, ($mappingCols - 1), $codeword, $codeWordIndex, DxMaskBit::DxMaskBit5, $moduleOnColor);
		DxEncode::placeModule($modules, $mappingRows, $mappingCols, 1, ($mappingCols - 1), $codeword, $codeWordIndex, DxMaskBit::DxMaskBit6, $moduleOnColor);
		DxEncode::placeModule($modules, $mappingRows, $mappingCols, 2, ($mappingCols - 1), $codeword, $codeWordIndex, DxMaskBit::DxMaskBit7, $moduleOnColor);
		DxEncode::placeModule($modules, $mappingRows, $mappingCols, 3, ($mappingCols - 1), $codeword, $codeWordIndex, DxMaskBit::DxMaskBit8, $moduleOnColor);
	}
	public static function patternShapeSpecial2 (&$modules, $mappingRows, $mappingCols, $codeword, $codeWordIndex, $moduleOnColor) // [byte[] modules, int mappingRows, int mappingCols, byte[] codeword, int codeWordIndex, int moduleOnColor]
	{
		DxEncode::placeModule($modules, $mappingRows, $mappingCols, ($mappingRows - 3), 0, $codeword, $codeWordIndex, DxMaskBit::DxMaskBit1, $moduleOnColor);
		DxEncode::placeModule($modules, $mappingRows, $mappingCols, ($mappingRows - 2), 0, $codeword, $codeWordIndex, DxMaskBit::DxMaskBit2, $moduleOnColor);
		DxEncode::placeModule($modules, $mappingRows, $mappingCols, ($mappingRows - 1), 0, $codeword, $codeWordIndex, DxMaskBit::DxMaskBit3, $moduleOnColor);
		DxEncode::placeModule($modules, $mappingRows, $mappingCols, 0, ($mappingCols - 4), $codeword, $codeWordIndex, DxMaskBit::DxMaskBit4, $moduleOnColor);
		DxEncode::placeModule($modules, $mappingRows, $mappingCols, 0, ($mappingCols - 3), $codeword, $codeWordIndex, DxMaskBit::DxMaskBit5, $moduleOnColor);
		DxEncode::placeModule($modules, $mappingRows, $mappingCols, 0, ($mappingCols - 2), $codeword, $codeWordIndex, DxMaskBit::DxMaskBit6, $moduleOnColor);
		DxEncode::placeModule($modules, $mappingRows, $mappingCols, 0, ($mappingCols - 1), $codeword, $codeWordIndex, DxMaskBit::DxMaskBit7, $moduleOnColor);
		DxEncode::placeModule($modules, $mappingRows, $mappingCols, 1, ($mappingCols - 1), $codeword, $codeWordIndex, DxMaskBit::DxMaskBit8, $moduleOnColor);
	}
	public static function patternShapeSpecial3 (&$modules, $mappingRows, $mappingCols, $codeword, $codeWordIndex, $moduleOnColor) // [byte[] modules, int mappingRows, int mappingCols, byte[] codeword, int codeWordIndex, int moduleOnColor]
	{
		DxEncode::placeModule($modules, $mappingRows, $mappingCols, ($mappingRows - 3), 0, $codeword, $codeWordIndex, DxMaskBit::DxMaskBit1, $moduleOnColor);
		DxEncode::placeModule($modules, $mappingRows, $mappingCols, ($mappingRows - 2), 0, $codeword, $codeWordIndex, DxMaskBit::DxMaskBit2, $moduleOnColor);
		DxEncode::placeModule($modules, $mappingRows, $mappingCols, ($mappingRows - 1), 0, $codeword, $codeWordIndex, DxMaskBit::DxMaskBit3, $moduleOnColor);
		DxEncode::placeModule($modules, $mappingRows, $mappingCols, 0, ($mappingCols - 2), $codeword, $codeWordIndex, DxMaskBit::DxMaskBit4, $moduleOnColor);
		DxEncode::placeModule($modules, $mappingRows, $mappingCols, 0, ($mappingCols - 1), $codeword, $codeWordIndex, DxMaskBit::DxMaskBit5, $moduleOnColor);
		DxEncode::placeModule($modules, $mappingRows, $mappingCols, 1, ($mappingCols - 1), $codeword, $codeWordIndex, DxMaskBit::DxMaskBit6, $moduleOnColor);
		DxEncode::placeModule($modules, $mappingRows, $mappingCols, 2, ($mappingCols - 1), $codeword, $codeWordIndex, DxMaskBit::DxMaskBit7, $moduleOnColor);
		DxEncode::placeModule($modules, $mappingRows, $mappingCols, 3, ($mappingCols - 1), $codeword, $codeWordIndex, DxMaskBit::DxMaskBit8, $moduleOnColor);
	}
	public static function patternShapeSpecial4 (&$modules, $mappingRows, $mappingCols, $codeword, $codeWordIndex, $moduleOnColor) // [byte[] modules, int mappingRows, int mappingCols, byte[] codeword, int codeWordIndex, int moduleOnColor]
	{
		DxEncode::placeModule($modules, $mappingRows, $mappingCols, ($mappingRows - 1), 0, $codeword, $codeWordIndex, DxMaskBit::DxMaskBit1, $moduleOnColor);
		DxEncode::placeModule($modules, $mappingRows, $mappingCols, ($mappingRows - 1), ($mappingCols - 1), $codeword, $codeWordIndex, DxMaskBit::DxMaskBit2, $moduleOnColor);
		DxEncode::placeModule($modules, $mappingRows, $mappingCols, 0, ($mappingCols - 3), $codeword, $codeWordIndex, DxMaskBit::DxMaskBit3, $moduleOnColor);
		DxEncode::placeModule($modules, $mappingRows, $mappingCols, 0, ($mappingCols - 2), $codeword, $codeWordIndex, DxMaskBit::DxMaskBit4, $moduleOnColor);
		DxEncode::placeModule($modules, $mappingRows, $mappingCols, 0, ($mappingCols - 1), $codeword, $codeWordIndex, DxMaskBit::DxMaskBit5, $moduleOnColor);
		DxEncode::placeModule($modules, $mappingRows, $mappingCols, 1, ($mappingCols - 3), $codeword, $codeWordIndex, DxMaskBit::DxMaskBit6, $moduleOnColor);
		DxEncode::placeModule($modules, $mappingRows, $mappingCols, 1, ($mappingCols - 2), $codeword, $codeWordIndex, DxMaskBit::DxMaskBit7, $moduleOnColor);
		DxEncode::placeModule($modules, $mappingRows, $mappingCols, 1, ($mappingCols - 1), $codeword, $codeWordIndex, DxMaskBit::DxMaskBit8, $moduleOnColor);
	}

	public static function placeModule (&$modules, $mappingRows, $mappingCols, $row, $col, $codeword, $codeWordIndex, $mask, $moduleOnColor) // [byte[] modules, int mappingRows, int mappingCols, int row, int col, byte[] codeword, int codeWordIndex, DxMaskBit mask, int moduleOnColor]
	{
		$bit = 0;
		if (($mask == DxMaskBit::DxMaskBit8))
		{
			$bit = ((0x01 << 0));
		}
		else
			if (($mask == DxMaskBit::DxMaskBit7))
			{
				$bit = ((0x01 << 1));
			}
			else
				if (($mask == DxMaskBit::DxMaskBit6))
				{
					$bit = ((0x01 << 2));
				}
				else
					if (($mask == DxMaskBit::DxMaskBit5))
					{
						$bit = ((0x01 << 3));
					}
					else
						if (($mask == DxMaskBit::DxMaskBit4))
						{
							$bit = ((0x01 << 4));
						}
						else
							if (($mask == DxMaskBit::DxMaskBit3))
							{
								$bit = ((0x01 << 5));
							}
							else
								if (($mask == DxMaskBit::DxMaskBit2))
								{
									$bit = ((0x01 << 6));
								}
								else
									if (($mask == DxMaskBit::DxMaskBit1))
									{
										$bit = ((0x01 << 7));
									}
		if (($row < 0))
		{
			$row += $mappingRows;
			$col += (4 - (((($mappingRows + 4)) % 8)));
		}
		if (($col < 0))
		{
			$col += $mappingCols;
			$row += (4 - (((($mappingCols + 4)) % 8)));
		}
		if (((($modules[(($row * $mappingCols) + $col)] & DxConstants::$DxModuleAssigned)) != 0))
		{
			if (((($modules[(($row * $mappingCols) + $col)] & $moduleOnColor)) != 0))
				$codeword[$codeWordIndex] |= $bit;
			else
				$codeword[$codeWordIndex] &= ((0xff ^ $mask->IntVal));
		}
		else
		{
			if (((($codeword[$codeWordIndex] & $bit)) != 0x00))
				$modules[(($row * $mappingCols) + $col)] |= $moduleOnColor;
			$modules[(($row * $mappingCols) + $col)] |= DxConstants::$DxModuleAssigned;
		}
		$modules[(($row * $mappingCols) + $col)] |= DxConstants::$DxModuleVisited;
	}
	
	public function encodeDataMosaic ($inputString) // [byte[] inputString]
	{
		$splitInputSize = array();
		for($ii = 0; $ii < 3; $ii++)
		{
			$splitInputSize[$ii] = 0;
		}
		$sizeIdxRequest = DxCodeSize::DxSzRectAuto;
		$splitSizeIdxAttempt = DxCodeSize::DxSzRectAuto;
		$splitSizeIdxLast = DxCodeSize::DxSzRectAuto;
		$buf = new ArrayList(3);
		for ($i = 0; ($i < 3); ++$i) 
		{
			$buf->add(array());
		}
		$row = null;
		$col = null;
		$sizeIdx = $this->sizeIdxRequest;
		$dataWordCount = $this->encodeDataCodewords(($buf->get(0)), $inputString, $sizeIdx);
		if (($dataWordCount <= 0))
			return  FALSE ;
		$tmpInputSize = (((count($inputString) /*from: inputString.length*/ + 2)) / 3);
		$splitInputSize[0] = $tmpInputSize;
		$splitInputSize[1] = $tmpInputSize;
		$splitInputSize[2] = (count($inputString) /*from: inputString.length*/ - (($splitInputSize[0] + $splitInputSize[1])));
		$splitSizeIdxFirst = $this->findCorrectSymbolSize($tmpInputSize, $sizeIdxRequest);
		if (($splitSizeIdxFirst == DxCodeSize::DxSzShapeAuto))
			return  FALSE ;
		if (($sizeIdxRequest == DxCodeSize::DxSzAuto))
		{
			$splitSizeIdxLast = DxCodeSize::DxSz144x144;
		}
		else
			if (($sizeIdxRequest == DxCodeSize::DxSzRectAuto))
			{
				$splitSizeIdxLast = DxCodeSize::DxSz16x48;
			}
			else
			{
				$splitSizeIdxLast = $splitSizeIdxFirst;
			}
		$tmpRed = array();
		for($ii = 0; $ii < $splitInputSize[0]; $ii++)
		{
			$tmpRed[$ii] = 0x00;
		}

		for ($i = 0; ($i < $splitInputSize[0]); ++$i) 
		{
			$tmpRed[$i] = $inputString[$i];
		}
		$tmpGreen = array();
		for($ii = 0; $ii < $splitInputSize[1]; $ii++)
		{
			$tmpGreen[$ii] = 0x00;
		}
		for ($i = $splitInputSize[0]; ($i < ($splitInputSize[0] + $splitInputSize[1])); ++$i) 
		{
			$tmpGreen[($i - $splitInputSize[0])] = $inputString[$i];
		}
		$tmpBlue = array();
		for($ii = 0; $ii < $splitInputSize[2]; $ii++)
		{
			$tmpBlue[$ii] = 0x00;
		}
		for ($i = ($splitInputSize[0] + $splitInputSize[1]); ($i < count($inputString) /*from: inputString.length*/); ++$i) 
		{
			$tmpBlue[(($i - $splitInputSize[0]) - $splitInputSize[1])] = $inputString[$i];
		}
		for ($splitSizeIdxAttempt = $splitSizeIdxFirst; ($splitSizeIdxAttempt <= $splitSizeIdxLast); ++$splitSizeIdxAttempt) 
		{
			$sizeIdx = $splitSizeIdxAttempt;
			$this->encodeDataCodewords(($buf->get(0)), $tmpRed, $sizeIdx);
			if (($sizeIdx != $splitSizeIdxAttempt))
				continue;
			$sizeIdx = $splitSizeIdxAttempt;
			$this->encodeDataCodewords(($buf->get(1)), $tmpGreen, $sizeIdx);
			if (($sizeIdx != $splitSizeIdxAttempt))
				continue;
			$sizeIdx = $splitSizeIdxAttempt;
			$this->encodeDataCodewords(($buf->get(2)), $tmpBlue, $sizeIdx);
			if (($sizeIdx != $splitSizeIdxAttempt))
				continue;
			break;
		}
		$this->sizeIdxRequest = $splitSizeIdxAttempt;
		$encGreen = DxEncode::constructor__DxEncode($this);
		$encBlue = DxEncode::constructor__DxEncode($this);
			/* match: Colorawtjava_Colorawtjava_aB */
		$this->encodeDataMatrix_Colorawtjava_Colorawtjava_aB(NULL, NULL, $tmpRed);
			/* match: Colorawtjava_Colorawtjava_aB */
		$encGreen->encodeDataMatrix_Colorawtjava_Colorawtjava_aB(NULL, NULL, $tmpGreen);
			/* match: Colorawtjava_Colorawtjava_aB */
		$encBlue->encodeDataMatrix_Colorawtjava_Colorawtjava_aB(NULL, NULL, $tmpBlue);
		$mappingRows = DxCommon::getSymbolAttribute(DxSymAttribute::DxSymAttribMappingMatrixRows, $splitSizeIdxAttempt);
		$mappingCols = DxCommon::getSymbolAttribute(DxSymAttribute::DxSymAttribMappingMatrixCols, $splitSizeIdxAttempt);
		for ($i = 0; ($i < ($this->region->MappingCols * $this->region->MappingRows)); ++$i) 
		{
			$this->message->Array[$i] = 0;
		}
		$this->modulePlacementEcc200($this->message->Array, $this->message->Code, $this->region->SizeIdx, DxConstants::$DxModuleOnRed);
		for ($row = 0; ($row < $mappingRows); ++$row) 
		{
			for ($col = 0; ($col < $mappingCols); ++$col) 
			{
				$this->message->Array[(($row * $mappingCols) + $col)] &= ((0xff ^ ((DxConstants::$DxModuleAssigned | DxConstants::$DxModuleVisited))));
			}
		}
		$this->modulePlacementEcc200($this->message->Array, $encGreen->Message->Code, $this->region->SizeIdx, DxConstants::$DxModuleOnGreen);
		for ($row = 0; ($row < $mappingRows); ++$row) 
		{
			for ($col = 0; ($col < $mappingCols); ++$col) 
			{
				$this->message->Array[(($row * $mappingCols) + $col)] &= ((0xff ^ ((DxConstants::$DxModuleAssigned | DxConstants::$DxModuleVisited))));
			}
		}
		$this->modulePlacementEcc200($this->message->Array, $encBlue->Message->Code, $this->region->SizeIdx, DxConstants::$DxModuleOnBlue);
		$this->printPattern(NULL, NULL);
		return  TRUE ;
	}
	
		protected function printPatternRaw () 
	{
		$this->rawData = array();
		for ($i3 = 0; ($i3 < $this->region->SymbolCols); ++$i3) 
		{
			$this->rawData[$i3] = array();
			for($ii = 0; $ii < $this->region->SymbolRows; $ii++)
			{
				$this->rawData[$i3][$ii] = false;
			}
		}
		for ($symbolRow = 0; ($symbolRow < $this->region->SymbolRows); ++$symbolRow) 
		{
			for ($symbolCol = 0; ($symbolCol < $this->region->SymbolCols); ++$symbolCol) 
			{
				$moduleStatus = $this->message->symbolModuleStatus($this->region->SizeIdx, $symbolRow, $symbolCol);
				$this->rawData[$symbolCol][(($this->region->SymbolRows - $symbolRow) - 1)] = (((($moduleStatus & DxConstants::$DxModuleOnBlue)) != 0x00));
			}
		}
	}
	protected function printPattern ($foreColor, $backColor) // [java.awt.Color foreColor, java.awt.Color backColor]
	{
		$symbolRow = null;
		$rgb = array();
		for($ii = 0; $ii < 3; $ii++)
		{
			$rgb[$ii] = 0;
		}

		$txy = $this->marginSize;
		$m1 = DxMatrix3::translate($txy, $txy);
		$m2 = DxMatrix3::scale($this->moduleSize, $this->moduleSize);
		$rxfrm = DxMatrix3::multiply3($m1, $m2);
		$rowSize = $this->image->RowSizeBytes;
		$height = $this->image->Height;
		for ($pxlIndex = 0; ($pxlIndex < ($rowSize * $height)); ++$pxlIndex) 
		{
			$this->image->Pxl[$pxlIndex] = 0xff;
		}
		for ($symbolRow = 0; ($symbolRow < $this->region->SymbolRows); ++$symbolRow) 
		{
			$symbolCol = null;
			for ($symbolCol = 0; ($symbolCol < $this->region->SymbolCols); ++$symbolCol) 
			{
				$vIn = DxVector2::constructor__D_D($symbolCol, $symbolRow);
				$vOut = DxMatrix3::multiply($vIn, $rxfrm);
				$pixelCol = ($vOut->X);
				$pixelRow = ($vOut->Y);
				$moduleStatus = $this->message->symbolModuleStatus($this->region->SizeIdx, $symbolRow, $symbolCol);
				for ($i = $pixelRow; ($i < ($pixelRow + $this->moduleSize)); ++$i) 
				{
					for ($j = $pixelCol; ($j < ($pixelCol + $this->moduleSize)); ++$j) 
					{
						if ((($foreColor != NULL) && ($backColor != NULL)))
						{
							$rgb[0] = ( ((((($moduleStatus & DxConstants::$DxModuleOnRed)) != 0x00))) ? $foreColor->Blue : $backColor->Blue );
							$rgb[1] = ( ((((($moduleStatus & DxConstants::$DxModuleOnGreen)) != 0x00))) ? $foreColor->Green : $backColor->Green );
							$rgb[2] = ( ((((($moduleStatus & DxConstants::$DxModuleOnBlue)) != 0x00))) ? $foreColor->Red : $backColor->Red );
						}
						else
						{
							$rgb[0] = ( ((((($moduleStatus & DxConstants::$DxModuleOnBlue)) != 0x00))) ? 0 : 255 );
							$rgb[1] = ( ((((($moduleStatus & DxConstants::$DxModuleOnGreen)) != 0x00))) ? 0 : 255 );
							$rgb[2] = ( ((((($moduleStatus & DxConstants::$DxModuleOnRed)) != 0x00))) ? 0 : 255 );
						}
						$this->image->setPixelValue($j, $i, 0, $rgb[0]);
						$this->image->setPixelValue($j, $i, 1, $rgb[1]);
						$this->image->setPixelValue($j, $i, 2, $rgb[2]);
					}
				}
			}
		}
	}
	protected function addPadChars (&$buf, &$dataWordCountP, $paddedSize) // [byte[] buf, int[] dataWordCountP, int paddedSize]
	{
		$padCount = 0;
		if (($dataWordCountP < $paddedSize))
		{
			++$padCount;
			$buf[$dataWordCountP++] = DxConstants::$DxCharAsciiPad;
		}
		while (($dataWordCountP < $paddedSize)) 
		{
			++$padCount;
			$buf[$dataWordCountP] = $this->randomize253State(DxConstants::$DxCharAsciiPad, ($dataWordCountP + 1));
			++$dataWordCountP;
		}
		return $padCount;
	}
	protected function randomize253State ($codewordValue, $codewordPosition) // [byte codewordValue, int codewordPosition]
	{
		$pseudoRandom = (((((149 * $codewordPosition)) % 253)) + 1);
		$tmp = ((($codewordValue & 0xff)) + $pseudoRandom);
		if (($tmp > 254))
			$tmp -= 254;
		if ((($tmp < 0) || ($tmp > 255)))
		{
			throw new Exception("Error randomizing 253 state!");
		}
		return $tmp;
	}
	protected function encodeDataCodewords (&$buf, $inputString, &$sizeIdx) // [byte[] buf, byte[] inputString, DxCodeSize[] sizeIdxP]
	{
		$dataWordCount = null;
		switch ($this->scheme) {
			case DxScheme::DxSchemeAutoBest:
				$dataWordCount = $this->encodeAutoBest($buf, $inputString);
				break;
			case DxScheme::DxSchemeAutoFast:
				$dataWordCount = 0;
				break;
			default:
				$dataWordCount = $this->encodeSingleScheme($buf, $inputString, $this->scheme);
				break;
		}
		$sizeIdx = $this->findCorrectSymbolSize($dataWordCount, $sizeIdx);
		if (($sizeIdx == DxCodeSize::DxSzShapeAuto))
			return 0;
		return $dataWordCount;
	}

		protected function findCorrectSymbolSize ($dataWords, $sizeIdxRequest) // [int dataWords, DxCodeSize sizeIdxRequest]
	{
		$sizeIdx = DxCodeSize::DxSzRectAuto;
		if (($dataWords <= 0))
			return DxCodeSize::DxSzShapeAuto;
		if ((($sizeIdxRequest == DxCodeSize::DxSzAuto) || ($sizeIdxRequest == DxCodeSize::DxSzRectAuto)))
		{
			$idxBeg = null;
			$idxEnd = null;
			if (($sizeIdxRequest == DxCodeSize::DxSzAuto))
			{
				$idxBeg = 0;
				$idxEnd = DxConstants::$DxSzSquareCount;
			}
			else
			{
				$idxBeg = DxConstants::$DxSzSquareCount;
				$idxEnd = (DxConstants::$DxSzSquareCount + DxConstants::$DxSzRectCount);
			}
			for ($sizeIdx = $idxBeg; $sizeIdx < $idxEnd; ++$sizeIdx) 
			{
				if ((DxCommon::getSymbolAttribute(DxSymAttribute::DxSymAttribSymbolDataWords, $sizeIdx) >= $dataWords))
				{
					break;
				}
			}
			if ($sizeIdx == $idxEnd)
			{
				return DxCodeSize::DxSzShapeAuto;
			}
		}
		else
		{
			$sizeIdx = $sizeIdxRequest;
		}
		if (($dataWords > DxCommon::getSymbolAttribute(DxSymAttribute::DxSymAttribSymbolDataWords, $sizeIdx)))
		{
			return DxCodeSize::DxSzShapeAuto;
		}
		return $sizeIdx;
	}
	protected function encodeSingleScheme (&$buf, $codewords, $scheme) // [byte[] buf, byte[] codewords, DxScheme scheme]
	{
		$channel = new DxChannel();
		DxEncode::initChannel($channel, $codewords);
		while (($channel->InputIndex < count($channel->Input) /*from: channel.getInput().length*/)) 
		{
			$err = $this->encodeNextWord($channel, $scheme);
			if (!$err)
				return 0;
			if (($channel->Invalid != DxChannelStatus::DxChannelValid))
			{
				return 0;
			}
		}
		$size = ($channel->EncodedLength / 12);
		for ($i = 0; ($i < $size); ++$i) 
		{
			$buf[$i] = $channel->EncodedWords[$i];
		}
		return $size;
	}
	protected function encodeAutoBest (&$buf, $codewords) // [byte[] buf, byte[] codewords]
	{
		$targetScheme = DxScheme::DxSchemeAutoFast;
		$optimal = new DxChannelGroup();
		$best = new DxChannelGroup();
		for ($targetScheme = DxScheme::DxSchemeAscii; ($targetScheme <= DxScheme::DxSchemeBase256); ++$targetScheme) 
		{
			$channel = ($optimal->Channels[$targetScheme]);
			DxEncode::initChannel($channel, $codewords);
			$err = $this->encodeNextWord($channel, $targetScheme);
			if ($err)
				return 0;
		}
		while (($optimal->Channels[0]->InputIndex < count($optimal->Channels[0]->Input) /*from: optimal.getChannels()[0].getInput().length*/)) 
		{
			for ($targetScheme = DxScheme::DxSchemeAscii; ($targetScheme <= DxScheme::DxSchemeBase256); ++$targetScheme) 
			{
				$best->Channels[$targetScheme] = $this->findBestChannel($optimal, $targetScheme);
			}
			$optimal = $best;
		}
		$winner = $optimal->Channels[DxScheme::DxSchemeAscii];
		for ($targetScheme = DxScheme::DxSchemeAscii; ($targetScheme <= DxScheme::DxSchemeBase256); ++$targetScheme) 
		{
			if (($optimal->Channels[$targetScheme]->Invalid != DxChannelStatus::DxChannelValid))
			{
				continue;
			}
			if (($optimal->Channels[$targetScheme]->EncodedLength < $winner->EncodedLength))
			{
				$winner = $optimal->Channels[$targetScheme];
			}
		}
		$winnerSize = ($winner->EncodedLength / 12);
		for ($i = 0; ($i < $winnerSize); ++$i) 
		{
			$buf[$i] = $winner->EncodedWords[$i];
		}
		return $winnerSize;
	}
	protected function findBestChannel ($group, $targetScheme) // [DxChannelGroup group, DxScheme targetScheme]
	{
		$winner = NULL;
		$encFrom = DxScheme::DxSchemeAscii;
		for ($encFrom = DxScheme::DxSchemeAscii; ($encFrom <= DxScheme::DxSchemeBase256); ++$encFrom) 
		{
			$channel = $group->Channels[$encFrom];
			if (($channel->Invalid != DxChannelStatus::DxChannelValid))
			{
				continue;
			}
			if (($channel->InputIndex == count($channel->Input) /*from: channel.getInput().length*/))
				continue;
			$err = $this->encodeNextWord($channel, $targetScheme);
			if (($err ==  FALSE ))
			if (((($channel->Invalid & DxChannelStatus::DxChannelUnsupportedChar)) != 0))
			{
				$winner = $channel;
				break;
			}
			if (((($channel->Invalid & DxChannelStatus::DxChannelCannotUnlatch)) != 0))
			{
				continue;
			}
			if ((($winner == NULL) || ($channel->CurrentLength < $winner->CurrentLength)))
			{
				$winner = $channel;
			}
		}
		return $winner;
	}
	protected function encodeNextWord ($channel, $targetScheme) // [DxChannel channel, DxScheme targetScheme]
	{
		if (($channel->EncScheme != $targetScheme))
		{
			$this->changeEncScheme($channel, $targetScheme, DxUnlatch::$Explicit);
			if (($channel->Invalid != DxChannelStatus::DxChannelValid))
				return  FALSE ;
		}
		if (($channel->EncScheme != $targetScheme))
		{
			throw new Exception("For encoding, channel scheme must equal target scheme!");
		}
		switch ($channel->EncScheme) {
			case DxScheme::DxSchemeAscii:
				return $this->encodeAsciiCodeword($channel);
			case DxScheme::DxSchemeC40:
				return $this->encodeTripletCodeword($channel);
			case DxScheme::DxSchemeText:
				return $this->encodeTripletCodeword($channel);
			case DxScheme::DxSchemeX12:
				return $this->encodeTripletCodeword($channel);
			case DxScheme::DxSchemeEdifact:
				return $this->encodeEdifactCodeword($channel);
			case DxScheme::DxSchemeBase256:
				return $this->encodeBase256Codeword($channel);
			default:
				return  FALSE ;
		}
	}
	protected function encodeBase256Codeword ($channel) // [DxChannel channel]
	{
		$i = null;
		$newDataLength = null;
		$headerByteCount = null;
		$headerByte = array();
		for($ii = 0; $ii < 2; $ii++)
		{
			$headerByte[$ii] = 0x00;
		}

		if (($channel->EncScheme != DxScheme::DxSchemeBase256))
		{
			throw new Exception("Invalid encoding scheme selected!");
		}
		$firstBytePtrIndex = ($channel->FirstCodeWord / 12);
		$headerByte[0] = DxMessage::unRandomize255State($channel->EncodedWords[$firstBytePtrIndex], (($channel->FirstCodeWord / 12) + 1));
		if (($headerByte[0] <= 249))
		{
			$newDataLength = $headerByte[0];
		}
		else
		{
			$newDataLength = (250 * (($headerByte[0] - 249)));
			$newDataLength += DxMessage::unRandomize255State($channel->EncodedWords[($firstBytePtrIndex + 1)], (($channel->FirstCodeWord / 12) + 2));
		}
		++$newDataLength;
		if (($newDataLength <= 249))
		{
			$headerByteCount = 1;
			$headerByte[0] = $newDataLength;
			$headerByte[1] = 0;
		}
		else
		{
			$headerByteCount = 2;
			$headerByte[0] = ((($newDataLength / 250) + 249));
			$headerByte[1] = (($newDataLength % 250));
		}
		if ((($newDataLength <= 0) || ($newDataLength > 1555)))
		{
			throw new Exception("Encoding failed, data length out of range!");
		}
		if (($newDataLength == 250))
		{
			for ($i = (($channel->CurrentLength / 12) - 1); ($i > ($channel->FirstCodeWord / 12)); --$i) 
			{
				$valueTmp = DxMessage::unRandomize255State($channel->EncodedWords[$i], ($i + 1));
				$channel->EncodedWords[($i + 1)] = $this->randomize255State($valueTmp, ($i + 2));
			}
			$this->incrementProgress($channel, 12);
			$channel->EncodedLength = $channel->EncodedLength + 12;
		}
		for ($i = 0; ($i < $headerByteCount); ++$i) 
		{
			$channel->EncodedWords[($firstBytePtrIndex + $i)] = $this->randomize255State($headerByte[$i], ((($channel->FirstCodeWord / 12) + $i) + 1));
		}
		$this->pushInputWord($channel, $this->randomize255State($channel->Input[$channel->InputIndex], (($channel->CurrentLength / 12) + 1)));
		$this->incrementProgress($channel, 12);
		$channel->InputIndex = $channel->InputIndex + 1;
		return  TRUE ;
	}
	protected function encodeEdifactCodeword ($channel) // [DxChannel channel]
	{
		if (($channel->EncScheme != DxScheme::DxSchemeEdifact))
		{
			throw new Exception("Invalid encoding scheme selected!");
		}
		$inputValue = $channel->Input[$channel->InputIndex];
		if ((($inputValue < 32) || ($inputValue > 94)))
		{
			$channel->Invalid = DxChannelStatus::DxChannelUnsupportedChar;
			return  FALSE ;
		}
		$this->pushInputWord($channel, (($inputValue & 0x3f)));
		$this->incrementProgress($channel, 9);
		$channel->InputIndex = $channel->InputIndex + 1;
		$this->checkForEndOfSymbolEdifact($channel);
		return  TRUE ;
	}
	protected function checkForEndOfSymbolEdifact ($channel) // [DxChannel channel]
	{
		if (($channel->InputIndex > count($channel->Input) /*from: channel.getInput().length*/))
		{
			throw new Exception("Input index out of range while encoding!");
		}
		$edifactValues = (count($channel->Input) /*from: channel.getInput().length*/ - $channel->InputIndex);
		if (($edifactValues > 4))
			return ;
		$currentByte = ($channel->CurrentLength / 12);
		$sizeIdx = $this->findCorrectSymbolSize($currentByte, DxCodeSize::DxSzAuto);
		$symbolCodewords = (DxCommon::getSymbolAttribute(DxSymAttribute::DxSymAttribSymbolDataWords, $sizeIdx) - $currentByte);
		if (((($channel->CurrentLength % 12) == 0) && ((($symbolCodewords == 1) || ($symbolCodewords == 2)))))
		{
			$asciiCodewords = $edifactValues;
			if (($asciiCodewords <= $symbolCodewords))
			{
				$this->changeEncScheme($channel, DxScheme::DxSchemeAscii, DxUnlatch::$Implicit);
				for ($i = 0; ($i < $edifactValues); ++$i) 
				{
					$err = $this->encodeNextWord($channel, DxScheme::DxSchemeAscii);
					if (($err ==  FALSE ))
					{
						return ;
					}
					if (($channel->Invalid != DxChannelStatus::DxChannelValid))
					{
						throw new Exception("Error checking for end of symbol edifact");
					}
				}
			}
		}
		else
			if (($edifactValues == 0))
			{
				$this->changeEncScheme($channel, DxScheme::DxSchemeAscii, DxUnlatch::$Explicit);
			}
		return ;
	}
	protected function pushInputWord ($channel, $codeword) // [DxChannel channel, byte codeword]
	{
		if (((($channel->EncodedLength / 12) > (3 * 1558))))
		{
			throw new Exception("Can't push input word, encoded length exceeds limits!");
		}
		switch ($channel->EncScheme) {
			case DxScheme::DxSchemeAscii:
				$channel->EncodedWords[($channel->CurrentLength / 12)] = $codeword;
				$channel->EncodedLength = $channel->EncodedLength + 12;
				break;
			case DxScheme::DxSchemeC40:
				$channel->EncodedWords[($channel->EncodedLength / 12)] = $codeword;
				$channel->EncodedLength = $channel->EncodedLength + 12;
				break;
			case DxScheme::DxSchemeText:
				$channel->EncodedWords[($channel->EncodedLength / 12)] = $codeword;
				$channel->EncodedLength = $channel->EncodedLength + 12;
				break;
			case DxScheme::DxSchemeX12:
				$channel->EncodedWords[($channel->EncodedLength / 12)] = $codeword;
				$channel->EncodedLength = $channel->EncodedLength + 12;
				break;
			case DxScheme::DxSchemeEdifact:
				$pos = ($channel->CurrentLength % 4);
				$startByte = ((((($channel->CurrentLength + 9)) / 12)) - $pos);
				$quad = $this->getQuadrupletValues($channel->EncodedWords[$startByte]
																	, $channel->EncodedWords[($startByte + 1)]
																	, $channel->EncodedWords[($startByte + 2)]);
				$quad->Value[$pos] = $codeword;
				for ($i = ($pos + 1); ($i < 4); ++$i) 
					$quad->Value[$i] = 0;
				switch ($pos) {
					case 3:
						$channel->EncodedWords[($startByte + 2)] = (((((($quad->Value[2] & 0x03)) << 6)) | $quad->Value[3]));
						break;
					case 2:
						$channel->EncodedWords[($startByte + 2)] = (((((($quad->Value[2] & 0x03)) << 6)) | $quad->Value[3]));
						break;
					case 1:
						$channel->EncodedWords[($startByte + 1)] = (((((($quad->Value[1] & 0x0f)) << 4)) | (($quad->Value[2] >> 2))));
						break;
					case 0:
						$channel->EncodedWords[$startByte] = (((($quad->Value[0] << 2)) | (($quad->Value[1] >> 4))));
						break;
				}
				$channel->EncodedLength = $channel->EncodedLength + 9;
				break;
			case DxScheme::DxSchemeBase256:
				$channel->EncodedWords[($channel->CurrentLength / 12)] = $codeword;
				$channel->EncodedLength = $channel->EncodedLength + 12;
				break;
			default:
				break;
		}
	}
	protected function encodeTripletCodeword ($channel) // [DxChannel channel]
	{
		$outputWords = array();
		for($ii = 0; $ii < 4; $ii++)
		{
			$outputWords[$ii] = 0;
		}

		$buffer = array();
		for($ii = 0; $ii < 6; $ii++)
		{
			$buffer[$ii] = x00;
		}

		$triplet = DxTriplet::constructor__();
		if (((($channel->EncScheme != DxScheme::DxSchemeX12) && ($channel->EncScheme != DxScheme::DxSchemeText)) && ($channel->EncScheme != DxScheme::DxSchemeC40)))
		{
			throw new Exception("Invalid encoding scheme selected!");
		}
		if (($channel->CurrentLength > $channel->EncodedLength))
		{
			throw new Exception("Encoding length out of range!");
		}
		if (($channel->CurrentLength == $channel->EncodedLength))
		{
			if ((($channel->CurrentLength % 12) != 0))
			{
				throw new Exception("Invalid encoding length!");
			}
			$ptrIndex = $channel->InputIndex;
			$tripletCount = 0;
			for (; ; ) 
			{
				while ((($tripletCount < 3) && ($ptrIndex < count($channel->Input) /*from: channel.getInput().length*/))) 
				{
					$inputWord = $channel->Input[$ptrIndex++];
					$count = $this->getC40TextX12Words($outputWords, $inputWord, $channel->EncScheme);
					if (($count == 0))
					{
						$channel->Invalid = DxChannelStatus::DxChannelUnsupportedChar;
						return  FALSE ;
					}
					for ($i = 0; ($i < $count); ++$i) 
					{
						$buffer[$tripletCount++] = $outputWords[$i];
					}
				}
				$triplet->Value[0] = $buffer[0];
				$triplet->Value[1] = $buffer[1];
				$triplet->Value[2] = $buffer[2];
				if (($tripletCount >= 3))
				{
					$this->pushTriplet($channel, $triplet);
					$buffer[0] = $buffer[3];
					$buffer[1] = $buffer[4];
					$buffer[2] = $buffer[5];
					$tripletCount -= 3;
				}
				if (($ptrIndex == count($channel->Input) /*from: channel.getInput().length*/))
				{
					while (($channel->CurrentLength < $channel->EncodedLength)) 
					{
						$this->incrementProgress($channel, 8);
						$channel->InputIndex = $channel->InputIndex + 1;
					}
					if (($channel->CurrentLength == ($channel->EncodedLength + 8)))
					{
						$channel->CurrentLength = $channel->EncodedLength;
						$channel->InputIndex = $channel->InputIndex - 1;
					}
					if ((count($channel->Input) /*from: channel.getInput().length*/ < $channel->InputIndex))
					{
						throw new Exception("Channel input index exceeds range!");
					}
					$inputCount = (count($channel->Input) /*from: channel.getInput().length*/ - $channel->InputIndex);
					$err = $this->processEndOfSymbolTriplet($channel, $triplet, $tripletCount, $inputCount);
					if (($err ==  FALSE ))
						return  FALSE ;
					break;
				}
				if (($tripletCount == 0))
					break;
			}
		}
		if (($channel->CurrentLength < $channel->EncodedLength))
		{
			$this->incrementProgress($channel, 8);
			$channel->InputIndex = $channel->InputIndex + 1;
		}
		return  TRUE ;
	}
	protected function getC40TextX12Words (&$outputWords, $inputWord, $encScheme) // [int[] outputWords, byte inputWord, DxScheme encScheme]
	{
		if (((($encScheme != DxScheme::DxSchemeX12) && ($encScheme != DxScheme::DxSchemeText)) && ($encScheme != DxScheme::DxSchemeC40)))
		{
			throw new Exception("Invalid encoding scheme selected!");
		}
		$count = 0;
		if (($inputWord > 127))
		{
			if (($encScheme == DxScheme::DxSchemeX12))
			{
				return 0;
			}
			$outputWords[$count++] = DxConstants::$DxCharTripletShift2;
			$outputWords[$count++] = 30;
			$inputWord -= 128;
		}
		if (($encScheme == DxScheme::DxSchemeX12))
		{
			if (($inputWord == 13))
				$outputWords[$count++] = 0;
			else
				if (($inputWord == 42))
					$outputWords[$count++] = 1;
				else
					if (($inputWord == 62))
						$outputWords[$count++] = 2;
					else
						if (($inputWord == 32))
							$outputWords[$count++] = 3;
						else
							if ((($inputWord >= 48) && ($inputWord <= 57)))
								$outputWords[$count++] = ($inputWord - 44);
							else
								if ((($inputWord >= 65) && ($inputWord <= 90)))
									$outputWords[$count++] = ($inputWord - 51);
		}
		else
		{
			if (($inputWord <= 31))
			{
				$outputWords[$count++] = DxConstants::$DxCharTripletShift1;
				$outputWords[$count++] = $inputWord;
			}
			else
				if (($inputWord == 32))
				{
					$outputWords[$count++] = 3;
				}
				else
					if (($inputWord <= 47))
					{
						$outputWords[$count++] = DxConstants::$DxCharTripletShift2;
						$outputWords[$count++] = ($inputWord - 33);
					}
					else
						if (($inputWord <= 57))
						{
							$outputWords[$count++] = ($inputWord - 44);
						}
						else
							if (($inputWord <= 64))
							{
								$outputWords[$count++] = DxConstants::$DxCharTripletShift2;
								$outputWords[$count++] = ($inputWord - 43);
							}
							else
								if ((($inputWord <= 90) && ($encScheme == DxScheme::DxSchemeC40)))
								{
									$outputWords[$count++] = ($inputWord - 51);
								}
								else
									if ((($inputWord <= 90) && ($encScheme == DxScheme::DxSchemeText)))
									{
										$outputWords[$count++] = DxConstants::$DxCharTripletShift3;
										$outputWords[$count++] = ($inputWord - 64);
									}
									else
										if (($inputWord <= 95))
										{
											$outputWords[$count++] = DxConstants::$DxCharTripletShift2;
											$outputWords[$count++] = ($inputWord - 69);
										}
										else
											if ((($inputWord == 96) && ($encScheme == DxScheme::DxSchemeText)))
											{
												$outputWords[$count++] = DxConstants::$DxCharTripletShift3;
												$outputWords[$count++] = 0;
											}
											else
												if ((($inputWord <= 122) && ($encScheme == DxScheme::DxSchemeText)))
												{
													$outputWords[$count++] = ($inputWord - 83);
												}
												else
													if (($inputWord <= 127))
													{
														$outputWords[$count++] = DxConstants::$DxCharTripletShift3;
														$outputWords[$count++] = ($inputWord - 96);
													}
		}
		return $count;
	}
	protected function processEndOfSymbolTriplet ($channel, $triplet, $tripletCount, $inputCount) // [DxChannel channel, DxTriplet triplet, int tripletCount, int inputCount]
	{
		$err = null;
		if ((($channel->CurrentLength % 12) != 0))
		{
			throw new Exception("Invalid current length for encoding!");
		}
		$inputAdjust = ($tripletCount - $inputCount);
		$currentByte = ($channel->CurrentLength / 12);
		$sizeIdx = $this->findCorrectSymbolSize(($currentByte + (( ((($inputCount == 3))) ? 2 : $inputCount ))), $this->sizeIdxRequest);
		if (($sizeIdx == DxCodeSize::DxSzShapeAuto))
			return  FALSE ;
		$remainingCodewords = (DxCommon::getSymbolAttribute(DxSymAttribute::DxSymAttribSymbolDataWords, $sizeIdx) - $currentByte);
		if ((($inputCount == 1) && ($remainingCodewords == 1)))
		{
			$this->changeEncScheme($channel, DxScheme::DxSchemeAscii, DxUnlatch::$Implicit);
			$err = $this->encodeNextWord($channel, DxScheme::DxSchemeAscii);
			if (($err ==  FALSE ))
				return  FALSE ;
			if ((($channel->Invalid != DxChannelStatus::DxChannelValid) || ($channel->InputIndex != count($channel->Input) /*from: channel.getInput().length*/)))
			{
				throw new Exception("Error processing end of symbol triplet!");
			}
		}
		else
			if (($remainingCodewords == 2))
			{
				if (($tripletCount == 3))
				{
					$this->pushTriplet($channel, $triplet);
					$this->incrementProgress($channel, 24);
					$channel->EncScheme = DxScheme::DxSchemeAscii;
					$channel->InputIndex = $channel->InputIndex + 3;
					$channel->InputIndex = $channel->InputIndex - $inputAdjust;
				}
				else
					if (($tripletCount == 2))
					{
						$triplet->Value[2] = 0;
						$this->pushTriplet($channel, $triplet);
						$this->incrementProgress($channel, 24);
						$channel->EncScheme = DxScheme::DxSchemeAscii;
						$channel->InputIndex = $channel->InputIndex + 2;
						$channel->InputIndex = $channel->InputIndex - $inputAdjust;
					}
					else
						if (($tripletCount == 1))
						{
							$this->changeEncScheme($channel, DxScheme::DxSchemeAscii, DxUnlatch::$Explicit);
							$err = $this->encodeNextWord($channel, DxScheme::DxSchemeAscii);
							if (($err ==  FALSE ))
								return  FALSE ;
							if (($channel->Invalid != DxChannelStatus::DxChannelValid))
							{
								throw new Exception("Error processing end of symbol triplet!");
							}
						}
			}
			else
			{
				$currentByte = ($channel->CurrentLength / 12);
				$remainingCodewords = (DxCommon::getSymbolAttribute(DxSymAttribute::DxSymAttribSymbolDataWords, $sizeIdx) - $currentByte);
				if (($remainingCodewords > 0))
				{
					$this->changeEncScheme($channel, DxScheme::DxSchemeAscii, DxUnlatch::$Explicit);
					while (($channel->InputIndex < count($channel->Input) /*from: channel.getInput().length*/)) 
					{
						$err = $this->encodeNextWord($channel, DxScheme::DxSchemeAscii);
						if (($err ==  FALSE ))
							return  FALSE ;
						if (($channel->Invalid != DxChannelStatus::DxChannelValid))
						{
							throw new Exception("Error processing end of symbol triplet!");
						}
					}
				}
			}
		if (($channel->InputIndex != count($channel->Input) /*from: channel.getInput().length*/))
		{
			throw new Exception("Could not fully process end of symbol triplet!");
		}
		return  TRUE ;
	}
	protected function pushTriplet ($channel, $triplet) // [DxChannel channel, DxTriplet triplet]
	{
		$tripletValue = (((((1600 * $triplet->Value[0])) + ((40 * $triplet->Value[1]))) + $triplet->Value[2]) + 1);
		$this->pushInputWord($channel, (($tripletValue / 256)));
		$this->pushInputWord($channel, (($tripletValue % 256)));
	}
	protected function encodeAsciiCodeword ($channel) // [DxChannel channel]
	{
		if (($channel->EncScheme != DxScheme::DxSchemeAscii))
		{
			throw new Exception("Invalid encoding scheme selected!");
		}
		$inputValue = $channel->Input[$channel->InputIndex];
		if (($this->isDigit($inputValue) && ($channel->CurrentLength >= ($channel->FirstCodeWord + 12))))
		{
			$prevIndex = ((($channel->CurrentLength - 12)) / 12);
			$prevValue = (($channel->EncodedWords[$prevIndex] - 1));
			$prevPrevValue = (( ((($prevIndex > ($channel->FirstCodeWord / 12)))) ? $channel->EncodedWords[($prevIndex - 1)] : 0 ));
			if ((($prevPrevValue != DxConstants::$DxCharAsciiUpperShift) && $this->isDigit($prevValue)))
			{
				$channel->EncodedWords[$prevIndex] = ((((10 * (($prevValue - ord('0')))) + (($inputValue - ord('0')))) + 130));
				$channel->InputIndex = $channel->InputIndex + 1;
				return  TRUE ;
			}
		}
		if (($this->flgGS1 ==  TRUE ))
		{
			if (((($inputValue & 0xff)) == ((DxConstants::$DxCharFNC1 & 0xff))))
			{
				$this->pushInputWord($channel, DxConstants::$DxCharFNC1);
				$this->incrementProgress($channel, 12);
				$channel->InputIndex = $channel->InputIndex + 1;
				return  TRUE ;
			}
		}
		if (((($inputValue & 0xff)) >= 128))
		{
			$this->pushInputWord($channel, DxConstants::$DxCharAsciiUpperShift);
			//$this->pushInputWord($channel, 235);
			$this->incrementProgress($channel, 12);
			$inputValue -= 128;
		}
		$this->pushInputWord($channel, (($inputValue + 1)));
		$this->incrementProgress($channel, 12);
		$channel->InputIndex = $channel->InputIndex + 1;
		return  TRUE ;
	}
	protected function isDigit ($inputValue) // [byte inputValue]
	{
		return (((($inputValue & 0xff)) >= 48) && ((($inputValue & 0xff)) <= 57));
	}
	protected function changeEncScheme ($channel, $targetScheme, $unlatchType) // [DxChannel channel, DxScheme targetScheme, DxUnlatch unlatchType]
	{
		if (($channel->EncScheme == $targetScheme))
		{
			throw new Exception("Target scheme already equals channel scheme, cannot be changed!");
		}
		switch ($channel->EncScheme) {
			case DxScheme::DxSchemeAscii:
				if ((($channel->CurrentLength % 12) != 0))
				{
					throw new Exception("Invalid current length detected encoding ascii code");
				}
				break;
			case DxScheme::DxSchemeC40:
			case DxScheme::DxSchemeText:
			case DxScheme::DxSchemeX12:
				if (((($channel->CurrentLength % 12)) != 0))
				{
					$channel->Invalid = DxChannelStatus::DxChannelCannotUnlatch;
					return ;
				}
				if (($channel->CurrentLength != $channel->EncodedLength))
				{
					$channel->Invalid = DxChannelStatus::DxChannelCannotUnlatch;
					return ;
				}
				if (($unlatchType == DxUnlatch::$Explicit))
				{
					$this->pushInputWord($channel, DxConstants::$DxCharTripletUnlatch);
					$this->incrementProgress($channel, 12);
				}
				break;
			case DxScheme::DxSchemeEdifact:
				if ((($channel->CurrentLength % 3) != 0))
				{
					throw new Exception("Error changing encryption scheme, current length is invalid!");
				}
				if (($unlatchType == DxUnlatch::$Explicit))
				{
					$this->pushInputWord($channel, DxConstants::$DxCharEdifactUnlatch);
					$this->incrementProgress($channel, 9);
				}
				$advance = ((($channel->CurrentLength % 4)) * 3);
				$channel->CurrentLength = $channel->CurrentLength + $advance;
				$channel->EncodedLength = $channel->EncodedLength + $advance;
				break;
			case DxScheme::DxSchemeBase256:
				break;
			default:
				break;
		}
		$channel->EncScheme = DxScheme::DxSchemeAscii;
		switch ($targetScheme) {
			case DxScheme::DxSchemeAscii:
				break;
			case DxScheme::DxSchemeC40:
				$this->pushInputWord($channel, DxConstants::$DxCharC40Latch);
				$this->incrementProgress($channel, 12);
				break;
			case DxScheme::DxSchemeText:
				$this->pushInputWord($channel, DxConstants::$DxCharTextLatch);
				$this->incrementProgress($channel, 12);
				break;
			case DxScheme::DxSchemeX12:
				$this->pushInputWord($channel, DxConstants::$DxCharX12Latch);
				$this->incrementProgress($channel, 12);
				break;
			case DxScheme::DxSchemeEdifact:
				$this->pushInputWord($channel, DxConstants::$DxCharEdifactLatch);
				$this->incrementProgress($channel, 12);
				break;
			case DxScheme::DxSchemeBase256:
				$this->pushInputWord($channel, DxConstants::$DxCharBase256Latch);
				$this->incrementProgress($channel, 12);
				$this->pushInputWord($channel, $this->randomize255State(0, 2));
				$this->incrementProgress($channel, 12);
				break;
			default:
				break;
		}
		$channel->EncScheme = $targetScheme;
		$channel->FirstCodeWord = $channel->CurrentLength - 12;
		if ((($channel->FirstCodeWord % 12) != 0))
		{
			throw new Exception("Error while changin encoding scheme, invalid first code word!");
		}
	}
	protected function randomize255State ($codewordValue, $codewordPosition) // [byte codewordValue, int codewordPosition]
	{
		$pseudoRandom = (((((149 * $codewordPosition)) % 255)) + 1);
		$tmp = ((($codewordValue & 0xff)) + $pseudoRandom);
		return (( ((($tmp <= 255))) ? $tmp : ($tmp - 256) ));
	}
	protected function incrementProgress ($channel, $encodedUnits) // [DxChannel channel, int encodedUnits]
	{
		if ((($channel->EncScheme == DxScheme::DxSchemeC40) || ($channel->EncScheme == DxScheme::DxSchemeText)))
		{
			$pos = ((($channel->CurrentLength % 6)) / 2);
			$startByte = ((($channel->CurrentLength / 12)) - (($pos >> 1)));
			$triplet = DxEncode::getTripletValues($channel->EncodedWords[$startByte], $channel->EncodedWords[($startByte + 1)]);
			if (($triplet->Value[$pos] <= 2))
				$channel->CurrentLength = $channel->CurrentLength + 8;
		}
		$channel->CurrentLength = $channel->CurrentLength + $encodedUnits;
	}
	protected static function getTripletValues ($cw1, $cw2) // [byte cw1, byte cw2]
	{
		$triplet = DxTriplet::constructor__();
		$compact = ((($cw1 << 8)) | $cw2);
		$triplet->Value[0] = (((($compact - 1)) / 1600));
		$triplet->Value[1] = (((((($compact - 1)) / 40)) % 40));
		$triplet->Value[2] = (((($compact - 1)) % 40));
		return $triplet;
	}
	protected function getQuadrupletValues ($cw1, $cw2, $cw3) // [byte cw1, byte cw2, byte cw3]
	{
		$quad = new DxQuadruplet();
		$quad->Value[0] = (($cw1 >> 2));
		$quad->Value[1] = (((((($cw1 & 0x03)) << 4)) | (((($cw2 & 0xf0)) >> 4))));
		$quad->Value[2] = (((((($cw2 & 0x0f)) << 2)) | (((($cw3 & 0xc0)) >> 6))));
		$quad->Value[3] = (($cw3 & 0x3f));
		return $quad;
	}
	protected function initChannel (DxChannel $channel, $codewords) // [DxChannel channel, byte[] codewords]
	{
		$channel->EncScheme = 0;
		$channel->EncScheme = DxScheme::DxSchemeAscii;
		$channel->Invalid = DxChannelStatus::DxChannelValid;
		$channel->InputIndex = 0;
		$channel->Input = $codewords;
	}
}

class DxQuadruplet {
	public $Value;	// byte[]
	public function DxQuadruplet () 
	{
		$this->Value = array();
		for($ii = 0; $ii < 4; $ii++)
		{
			$this->Value[$ii] = x00;
		}
	}
}

class DxRegion {
	public $JumpToPos;	// int
	public $JumpToNeg;	// int
	public $StepsTotal;	// int
	public $FinalPos;	// DxPixelLoc
	public $FinalNeg;	// DxPixelLoc
	public $BoundMin;	// DxPixelLoc
	public $BoundMax;	// DxPixelLoc
	public $FlowBegin;	// DxPointFlow
	public $Polarity;	// int
	public $StepR;	// int
	public $StepT;	// int
	public $LocR;	// DxPixelLoc
	public $LocT;	// DxPixelLoc
	public $LeftKnown;	// int
	public $LeftAngle;	// int
	public $LeftLoc;	// DxPixelLoc
	public $LeftLine;	// DxBestLine
	public $BottomKnown;	// int
	public $BottomAngle;	// int
	public $BottomLoc;	// DxPixelLoc
	public $BottomLine;	// DxBestLine
	public $TopKnown;	// int
	public $TopAngle;	// int
	public $TopLoc;	// DxPixelLoc
	public $RightKnown;	// int
	public $RightAngle;	// int
	public $RightLoc;	// DxPixelLoc
	public $OnColor;	// int
	public $OffColor;	// int
	public $SizeIdx;	// DxCodeSize
	public $SymbolRows;	// int
	public $SymbolCols;	// int
	public $MappingRows;	// int
	public $MappingCols;	// int
	public $Raw2Fit;	// DxMatrix3
	public $Fit2Raw;	// DxMatrix3
	function __construct ($src) // [DxRegion src]
	{
		$this->SizeIdx = DxCodeSize::DxSzRectAuto;
		if($src != null)
		{
			$this->BottomAngle = $src->BottomAngle;
			$this->BottomKnown = $src->BottomKnown;
			$this->BottomLine = $src->BottomLine;
			$this->BottomLoc = $src->BottomLoc;
			$this->BoundMax = $src->BoundMax;
			$this->BoundMin = $src->BoundMin;
			$this->FinalNeg = $src->FinalNeg;
			$this->FinalPos = $src->FinalPos;
			$this->Fit2Raw = new DxMatrix3($src->Fit2Raw);
			$this->FlowBegin = $src->FlowBegin;
			$this->JumpToNeg = $src->JumpToNeg;
			$this->JumpToPos = $src->JumpToPos;
			$this->LeftAngle = $src->LeftAngle;
			$this->LeftKnown = $src->LeftKnown;
			$this->LeftLine = $src->LeftLine;
			$this->LeftLoc = $src->LeftLoc;
			$this->LocR = $src->LocR;
			$this->LocT = $src->LocT;
			$this->MappingCols = $src->MappingCols;
			$this->MappingRows = $src->MappingRows;
			$this->OffColor = $src->OffColor;
			$this->OnColor = $src->OnColor;
			$this->Polarity = $src->Polarity;
			$this->Raw2Fit = new DxMatrix3($src->Raw2Fit);
			$this->RightAngle = $src->RightAngle;
			$this->RightKnown = $src->RightKnown;
			$this->RightLoc = $src->RightLoc;
			$this->SizeIdx = $src->SizeIdx;
			$this->StepR = $src->StepR;
			$this->StepsTotal = $src->StepsTotal;
			$this->StepT = $src->StepT;
			$this->SymbolCols = $src->SymbolCols;
			$this->SymbolRows = $src->SymbolRows;
			$this->TopAngle = $src->TopAngle;
			$this->TopKnown = $src->TopKnown;
			$this->TopLoc = $src->TopLoc;
		}
	}
}








    class DxConstants
    {

        public static $DxAlmostZero = 0.000001;

        public static $DxModuleOff = 0x00;
        public static $DxModuleOnRed = 0x01;
        public static $DxModuleOnGreen = 0x02;
        public static $DxModuleOnBlue = 0x04;
        public static $DxModuleOnRGB = 0x07; /* OnRed | OnGreen | OnBlue */
        public static $DxModuleOn = 0x07;
        public static $DxModuleUnsure = 0x08;
        public static $DxModuleAssigned = 0x10;
        public static $DxModuleVisited = 0x20;
        public static $DxModuleData = 0x40;

        public static $DxCharAsciiPad = 129;
        public static $DxCharAsciiUpperShift = 235;
        public static $DxCharTripletShift1 = 0;
        public static $DxCharTripletShift2 = 1;
        public static $DxCharTripletShift3 = 2;
        public static $DxCharFNC1 = 232;
        public static $DxCharStructuredAppend = 233;
        public static $DxChar05Macro = 236;
        public static $DxChar06Macro = 237;

        public static $DxC40TextBasicSet = 0;
        public static $DxC40TextShift1 = 1;
        public static $DxC40TextShift2 = 2;
        public static $DxC40TextShift3 = 3;

        public static $DxCharTripletUnlatch = 254;
        public static $DxCharEdifactUnlatch = 31;

        public static $DxCharC40Latch = 230;
        public static $DxCharTextLatch = 239;
        public static $DxCharX12Latch = 238;
        public static $DxCharEdifactLatch = 240;
        public static $DxCharBase256Latch = 231;

        public static $SymbolRows = array(  10, 12, 14, 16, 18, 20,  22,  24,  26,
                                                 32, 36, 40,  44,  48,  52,
                                                 64, 72, 80,  88,  96, 104,
                                                        120, 132, 144,
                                                  8,  8, 12,  12,  16,  16 );

        public static $SymbolCols = array(  10, 12, 14, 16, 18, 20,  22,  24,  26,
                                                 32, 36, 40,  44,  48,  52,
                                                 64, 72, 80,  88,  96, 104,
                                                        120, 132, 144,
                                                 18, 32, 26,  36,  36,  48 );

        public static $DataRegionRows = array(  8, 10, 12, 14, 16, 18, 20, 22, 24,
                                                    14, 16, 18, 20, 22, 24,
                                                    14, 16, 18, 20, 22, 24,
                                                            18, 20, 22,
                                                     6,  6, 10, 10, 14, 14 );

        public static $DataRegionCols = array(  8, 10, 12, 14, 16, 18, 20, 22, 24,
                                                    14, 16, 18, 20, 22, 24,
                                                    14, 16, 18, 20, 22, 24,
                                                            18, 20, 22,
                                                    16, 14, 24, 16, 16, 22 );

        public static $HorizDataRegions = array(  1, 1, 1, 1, 1, 1, 1, 1, 1,
                                                    2, 2, 2, 2, 2, 2,
                                                    4, 4, 4, 4, 4, 4,
                                                          6, 6, 6,
                                                    1, 2, 1, 2, 2, 2 );

        public static $InterleavedBlocks = array(  1, 1, 1, 1, 1, 1, 1,  1, 1,
                                                     1, 1, 1, 1,  1, 2,
                                                     2, 4, 4, 4,  4, 6,
                                                           6, 8, 10,
                                                     1, 1, 1, 1,  1, 1 );

        public static $SymbolDataWords = array(  3, 5, 8,  12,   18,   22,   30,   36,  44,
                                                    62,   86,  114,  144,  174, 204,
                                                   280,  368,  456,  576,  696, 816,
                                                              1050, 1304, 1558,
                                                     5,   10,   16,   22,   32,  49 );

        public static $BlockErrorWords = array(  5, 7, 10, 12, 14, 18, 20, 24, 28,
                                                    36, 42, 48, 56, 68, 42,
                                                    56, 36, 48, 56, 68, 56,
                                                            68, 62, 62,
                                                     7, 11, 14, 18, 24, 28 );

        public static $BlockMaxCorrectable = array(  2, 3, 5,  6,  7,  9,  10,  12,  14,
                                                       18, 21, 24,  28,  34,  21,
                                                       28, 18, 24,  28,  34,  28,
                                                               34,  31,  31,
                                                   3,  5,  7,   9,  12,  14 );
        public static $DxSzSquareCount = 24;
        public static $DxSzRectCount = 6;
        public static $DxUndefined = -1;

        public static $DxPatternX = array(  -1, 0, 1, 1, 1, 0, -1, -1 );
        public static $DxPatternY = array(  -1, -1, -1, 0, 1, 1, 1, 0 );
        //internal static readonly DxPointFlow DxBlankEdge = new DxPointFlow(0) { Plane = 0, Arrive = 0, Depart = 0, Mag = DxConstants.DxUndefined, Loc = new DxPixelLoc() { X = -1, Y = -1 } );
        public static $DxBlankEdge = null; //new DxPointFlow(0);

        public static $DxHoughRes = 180;
        public static $DxNeighborNone = 8;

        public static $rHvX =
    array(  256,  256,  256,  256,  255,  255,  255,  254,  254,  253,  252,  251,  250,  249,  248,
       247,  246,  245,  243,  242,  241,  239,  237,  236,  234,  232,  230,  228,  226,  224,
       222,  219,  217,  215,  212,  210,  207,  204,  202,  199,  196,  193,  190,  187,  184,
       181,  178,  175,  171,  168,  165,  161,  158,  154,  150,  147,  143,  139,  136,  132,
       128,  124,  120,  116,  112,  108,  104,  100,   96,   92,   88,   83,   79,   75,   71,
        66,   62,   58,   53,   49,   44,   40,   36,   31,   27,   22,   18,   13,    9,    4,
         0,   -4,   -9,  -13,  -18,  -22,  -27,  -31,  -36,  -40,  -44,  -49,  -53,  -58,  -62,
       -66,  -71,  -75,  -79,  -83,  -88,  -92,  -96, -100, -104, -108, -112, -116, -120, -124,
      -128, -132, -136, -139, -143, -147, -150, -154, -158, -161, -165, -168, -171, -175, -178,
      -181, -184, -187, -190, -193, -196, -199, -202, -204, -207, -210, -212, -215, -217, -219,
      -222, -224, -226, -228, -230, -232, -234, -236, -237, -239, -241, -242, -243, -245, -246,
      -247, -248, -249, -250, -251, -252, -253, -254, -254, -255, -255, -255, -256, -256, -256 );

        public static $rHvY =
    array(    0,    4,    9,   13,   18,   22,   27,   31,   36,   40,   44,   49,   53,   58,   62,
        66,   71,   75,   79,   83,   88,   92,   96,  100,  104,  108,  112,  116,  120,  124,
       128,  132,  136,  139,  143,  147,  150,  154,  158,  161,  165,  168,  171,  175,  178,
       181,  184,  187,  190,  193,  196,  199,  202,  204,  207,  210,  212,  215,  217,  219,
       222,  224,  226,  228,  230,  232,  234,  236,  237,  239,  241,  242,  243,  245,  246,
       247,  248,  249,  250,  251,  252,  253,  254,  254,  255,  255,  255,  256,  256,  256,
       256,  256,  256,  256,  255,  255,  255,  254,  254,  253,  252,  251,  250,  249,  248,
       247,  246,  245,  243,  242,  241,  239,  237,  236,  234,  232,  230,  228,  226,  224,
       222,  219,  217,  215,  212,  210,  207,  204,  202,  199,  196,  193,  190,  187,  184,
       181,  178,  175,  171,  168,  165,  161,  158,  154,  150,  147,  143,  139,  136,  132,
       128,  124,  120,  116,  112,  108,  104,  100,   96,   92,   88,   83,   79,   75,   71,
        66,   62,   58,   53,   49,   44,   40,   36,   31,   27,   22,   18,   13,    9,    4 );

        public static $aLogVal =
   array(   1,   2,   4,   8,  16,  32,  64, 128,  45,  90, 180,  69, 138,  57, 114, 228,
     229, 231, 227, 235, 251, 219, 155,  27,  54, 108, 216, 157,  23,  46,  92, 184,
      93, 186,  89, 178,  73, 146,   9,  18,  36,  72, 144,  13,  26,  52, 104, 208,
     141,  55, 110, 220, 149,   7,  14,  28,  56, 112, 224, 237, 247, 195, 171, 123,
     246, 193, 175, 115, 230, 225, 239, 243, 203, 187,  91, 182,  65, 130,  41,  82,
     164, 101, 202, 185,  95, 190,  81, 162, 105, 210, 137,  63, 126, 252, 213, 135,
      35,  70, 140,  53, 106, 212, 133,  39,  78, 156,  21,  42,  84, 168, 125, 250,
     217, 159,  19,  38,  76, 152,  29,  58, 116, 232, 253, 215, 131,  43,  86, 172,
     117, 234, 249, 223, 147,  11,  22,  44,  88, 176,  77, 154,  25,  50, 100, 200,
     189,  87, 174, 113, 226, 233, 255, 211, 139,  59, 118, 236, 245, 199, 163, 107,
     214, 129,  47,  94, 188,  85, 170, 121, 242, 201, 191,  83, 166,  97, 194, 169,
     127, 254, 209, 143,  51, 102, 204, 181,  71, 142,  49,  98, 196, 165, 103, 206,
     177,  79, 158,  17,  34,  68, 136,  61, 122, 244, 197, 167,  99, 198, 161, 111,
     222, 145,  15,  30,  60, 120, 240, 205, 183,  67, 134,  33,  66, 132,  37,  74,
     148,   5,  10,  20,  40,  80, 160, 109, 218, 153,  31,  62, 124, 248, 221, 151,
       3,   6,  12,  24,  48,  96, 192, 173, 119, 238, 241, 207, 179,  75, 150,   1 );

        public static $logVal =
   array(-255, 255,   1, 240,   2, 225, 241,  53,   3,  38, 226, 133, 242,  43,  54, 210,
       4, 195,  39, 114, 227, 106, 134,  28, 243, 140,  44,  23,  55, 118, 211, 234,
       5, 219, 196,  96,  40, 222, 115, 103, 228,  78, 107, 125, 135,   8,  29, 162,
     244, 186, 141, 180,  45,  99,  24,  49,  56,  13, 119, 153, 212, 199, 235,  91,
       6,  76, 220, 217, 197,  11,  97, 184,  41,  36, 223, 253, 116, 138, 104, 193,
     229,  86,  79, 171, 108, 165, 126, 145, 136,  34,   9,  74,  30,  32, 163,  84,
     245, 173, 187, 204, 142,  81, 181, 190,  46,  88, 100, 159,  25, 231,  50, 207,
      57, 147,  14,  67, 120, 128, 154, 248, 213, 167, 200,  63, 236, 110,  92, 176,
       7, 161,  77, 124, 221, 102, 218,  95, 198,  90,  12, 152,  98,  48, 185, 179,
      42, 209,  37, 132, 224,  52, 254, 239, 117, 233, 139,  22, 105,  27, 194, 113,
     230, 206,  87, 158,  80, 189, 172, 203, 109, 175, 166,  62, 127, 247, 146,  66,
     137, 192,  35, 252,  10, 183,  75, 216,  31,  83,  33,  73, 164, 144,  85, 170,
     246,  65, 174,  61, 188, 202, 205, 157, 143, 169,  82,  72, 182, 215, 191, 251,
      47, 178,  89, 151, 101,  94, 160, 123,  26, 112, 232,  21,  51, 238, 208, 131,
      58,  69, 148,  18,  15,  16,  68,  17, 121, 149, 129,  19, 155,  59, 249,  70,
     214, 250, 168,  71, 201, 156,  64,  60, 237, 130, 111,  20,  93, 122, 177, 150 );

    }





class DxCommon {
	public static function genReedSolEcc ($message, $sizeIdx) // [DxMessage message, DxCodeSize sizeIdx]
	{
		$g = array();
		$b = array();
		$symbolDataWords = DxCommon::getSymbolAttribute(DxSymAttribute::DxSymAttribSymbolDataWords, $sizeIdx);
		$symbolErrorWords = DxCommon::getSymbolAttribute(DxSymAttribute::DxSymAttribSymbolErrorWords, $sizeIdx);
		$symbolTotalWords = ($symbolDataWords + $symbolErrorWords);
		$blockErrorWords = DxCommon::getSymbolAttribute(DxSymAttribute::DxSymAttribBlockErrorWords, $sizeIdx);
		$step = DxCommon::getSymbolAttribute(DxSymAttribute::DxSymAttribInterleavedBlocks, $sizeIdx);
		if (($blockErrorWords != ($symbolErrorWords / $step)))
		{
			throw new Exception("Error generation reed solomon error correction");
		}
		for ($gI = 0; ($gI < 69 /*from: g.length*/); ++$gI) 
		{
			$g[$gI] = 0x01;
		}
		for ($i = 1; ($i <= $blockErrorWords); ++$i) 
		{
			for ($j = ($i - 1); ($j >= 0); --$j) 
			{
				if ((($i == 5) && ($j == 2)))
				{
					$j = 2;
				}
				$g[$j] = DxCommon::gfDoublify($g[$j], $i);
				if (($j > 0))
					$g[$j] = DxCommon::gfSum($g[$j], $g[($j - 1)]);
			}
		}
		for ($block = 0; ($block < $step); ++$block) 
		{
			for ($bI = 0; ($bI < 69 /*from: b.length*/); ++$bI) 
			{
				$b[$bI] = 0x00;
			}
			for ($i = $block; ($i < $symbolDataWords); $i += $step) 
			{
				
				$val = DxCommon::gfSum($b[($blockErrorWords - 1)], $message->Code[$i]);
				for ($j = ($blockErrorWords - 1); ($j > 0); --$j) 
				{
					$b[$j] = DxCommon::gfSum($b[($j - 1)], DxCommon::gfProduct($g[$j], $val));
				}
				$b[0] = DxCommon::gfProduct($g[0], $val);
			}
			$blockDataWords = DxCommon::getBlockDataSize($sizeIdx, $block);
			$bIndex = $blockErrorWords;
			for ($i = ($block + (($step * $blockDataWords))); ($i < $symbolTotalWords); $i += $step) 
			{
				$message->Code[$i] = $b[--$bIndex];
			}
			if (($bIndex != 0))
			{
				throw new Exception("Error generation error correction code!");
			}
		}
	}
	
		protected static function gfProduct ($a, $b) // [byte a, int b]
	{
		if ((($a == 0) || ($b == 0)))
			return 0;
		$intA = ($a & 0xFF);
		$intB = ($b & 0xFF);
		return DxConstants::$aLogVal[(((DxConstants::$logVal[$intA] + DxConstants::$logVal[$intB])) % 255)];
	}
	protected static function gfSum ($a, $b) // [byte a, byte b]
	{
		return (($a ^ $b));
	}
	protected static function gfDoublify ($a, $b) // [byte a, int b]
	{
		if (($a == 0))
			return 0;
		if (($b == 0))
			return $a;
		$intA = ($a & 0xFF);
		return DxConstants::$aLogVal[(((DxConstants::$logVal[$intA] + $b)) % 255)];
	}
	public static function getSymbolAttribute ($attribute, $sizeIdx) // [DxSymAttribute attribute, DxCodeSize sizeIdx]
	{
		if ((($sizeIdx < 0) || ($sizeIdx >= (DxConstants::$DxSzSquareCount + DxConstants::$DxSzRectCount))))
			return DxConstants::$DxUndefined;
		switch ($attribute) {
			case DxSymAttribute::DxSymAttribSymbolRows:
				return DxConstants::$SymbolRows[$sizeIdx];
			case DxSymAttribute::DxSymAttribSymbolCols:
				return DxConstants::$SymbolCols[$sizeIdx];
			case DxSymAttribute::DxSymAttribDataRegionRows:
				return DxConstants::$DataRegionRows[$sizeIdx];
			case DxSymAttribute::DxSymAttribDataRegionCols:
				return DxConstants::$DataRegionCols[$sizeIdx];
			case DxSymAttribute::DxSymAttribHorizDataRegions:
				return DxConstants::$HorizDataRegions[$sizeIdx];
			case DxSymAttribute::DxSymAttribVertDataRegions:
				return ( ((($sizeIdx < DxConstants::$DxSzSquareCount))) ? DxConstants::$HorizDataRegions[$sizeIdx] : 1 );
			case DxSymAttribute::DxSymAttribMappingMatrixRows:
				return (DxConstants::$DataRegionRows[$sizeIdx] * DxCommon::getSymbolAttribute(DxSymAttribute::DxSymAttribVertDataRegions, $sizeIdx));
			case DxSymAttribute::DxSymAttribMappingMatrixCols:
				return (DxConstants::$DataRegionCols[$sizeIdx] * DxConstants::$HorizDataRegions[$sizeIdx]);
			case DxSymAttribute::DxSymAttribInterleavedBlocks:
				return DxConstants::$InterleavedBlocks[$sizeIdx];
			case DxSymAttribute::DxSymAttribBlockErrorWords:
				return DxConstants::$BlockErrorWords[$sizeIdx];
			case DxSymAttribute::DxSymAttribBlockMaxCorrectable:
				return DxConstants::$BlockMaxCorrectable[$sizeIdx];
			case DxSymAttribute::DxSymAttribSymbolDataWords:
				return DxConstants::$SymbolDataWords[$sizeIdx];
			case DxSymAttribute::DxSymAttribSymbolErrorWords:
				return (DxConstants::$BlockErrorWords[$sizeIdx] * DxConstants::$InterleavedBlocks[$sizeIdx]);
			case DxSymAttribute::DxSymAttribSymbolMaxCorrectable:
				return (DxConstants::$BlockMaxCorrectable[$sizeIdx] * DxConstants::$InterleavedBlocks[$sizeIdx]);
		}
		return DxConstants::$DxUndefined;
	}
	public static function getBlockDataSize ($sizeIdx, $blockIdx) // [DxCodeSize sizeIdx, int blockIdx]
	{
		$symbolDataWords = DxCommon::getSymbolAttribute(DxSymAttribute::DxSymAttribSymbolDataWords, $sizeIdx);
		$interleavedBlocks = DxCommon::getSymbolAttribute(DxSymAttribute::DxSymAttribInterleavedBlocks, $sizeIdx);
		$count = ($symbolDataWords / $interleavedBlocks);
		if ((($symbolDataWords < 1) || ($interleavedBlocks < 1)))
			return DxConstants::$DxUndefined;
		return ( (((($sizeIdx == DxCodeSize::DxSz144x144) && ($blockIdx < 8)))) ? ($count + 1) : $count );
	}
	public static function findCorrectSymbolSize ($dataWords, $sizeIdxRequest) // [int dataWords, DxCodeSize sizeIdxRequest]
	{
		$sizeIdx = DxCodeSize::DxSzRectAuto;
		if (($dataWords <= 0))
		{
			return DxCodeSize::DxSzShapeAuto;
		}
		if ((($sizeIdxRequest == DxCodeSize::DxSzAuto) || ($sizeIdxRequest == DxCodeSize::DxSzRectAuto)))
		{
			$idxBeg = DxCodeSize::DxSzRectAuto;
			$idxEnd = DxCodeSize::DxSzRectAuto;
			if (($sizeIdxRequest == DxCodeSize::DxSzAuto))
			{
				$idxBeg = 0;
				$idxEnd = DxConstants::$DxSzSquareCount;
			}
			else
			{
				$idxBeg = DxConstants::$DxSzSquareCount;
				$idxEnd = DxConstants::$DxSzSquareCount + DxConstants::$DxSzRectCount;
			}
			for ($ii = $idxBeg->getIntVal(); ($ii < $idxEnd->getIntVal()); ++$ii) 
			{
				$wkSzCodeSize = $sizeIdx;
				$sizeIdx = $wkSzCodeSize->fromIntVal($ii);
				if ((DxCommon::getSymbolAttribute(DxSymAttribute::DxSymAttribSymbolDataWords, $sizeIdx) >= $dataWords))
					break;
			}
			if (($sizeIdx == $idxEnd))
			{
				return DxCodeSize::DxSzShapeAuto;
			}
		}
		else
		{
			$sizeIdx = $sizeIdxRequest;
		}
		if (($dataWords > DxCommon::getSymbolAttribute(DxSymAttribute::DxSymAttribSymbolDataWords, $sizeIdx)))
		{
			return DxCodeSize::DxSzShapeAuto;
		}
		return $sizeIdx;
	}
	public static function getBitsPerPixel ($pack) // [DxPackOrder pack]
	{
		switch ($pack) {
			case DxPackOrder::DxPack1bppK:
				return 1;
			case DxPackOrder::DxPack8bppK:
				return 8;
			case DxPackOrder::DxPack16bppRGB:
			case DxPackOrder::DxPack16bppRGBX:
			case DxPackOrder::DxPack16bppXRGB:
			case DxPackOrder::DxPack16bppBGR:
			case DxPackOrder::DxPack16bppBGRX:
			case DxPackOrder::DxPack16bppXBGR:
			case DxPackOrder::DxPack16bppYCbCr:
				return 16;
			case DxPackOrder::DxPack24bppRGB:
			case DxPackOrder::DxPack24bppBGR:
			case DxPackOrder::DxPack24bppYCbCr:
				return 24;
			case DxPackOrder::DxPack32bppRGBX:
			case DxPackOrder::DxPack32bppXRGB:
			case DxPackOrder::DxPack32bppBGRX:
			case DxPackOrder::DxPack32bppXBGR:
			case DxPackOrder::DxPack32bppCMYK:
				return 32;
			default:
				break;
		}
		return DxConstants::$DxUndefined;
	}
	public static function min ($x, $y) // [T x, T y]
	{
		return ( (($x->compareTo($y) < 0)) ? $x : $y );
	}
	public static function max ($x, $y) // [T x, T y]
	{
		return ( (($x->compareTo($y) < 0)) ? $y : $x );
	}
	public static function decodeCheckErrors (&$code, $codeIndex, $sizeIdx, $fix) // [byte[] code, int codeIndex, DxCodeSize sizeIdx, int fix]
	{
		$data = array();
		for($ii = 0; $ii < 255; $ii++)
		{
			$data[$ii] = 0x00;
		}
		$interleavedBlocks = DxCommon::getSymbolAttribute(DxSymAttribute::DxSymAttribInterleavedBlocks, $sizeIdx);
		$blockErrorWords = DxCommon::getSymbolAttribute(DxSymAttribute::DxSymAttribBlockErrorWords, $sizeIdx);
		$fixedErr = 0;
		$fixedErrSum = 0;
		for ($i = 0; ($i < $interleavedBlocks); ++$i) 
		{
			$blockTotalWords = ($blockErrorWords + DxCommon::getBlockDataSize($sizeIdx, $i));
			$j = null;
			for ($j = 0; ($j < $blockTotalWords); ++$j) 
				$data[$j] = $code[(($j * $interleavedBlocks) + $i)];
			$fixedErrSum += $fixedErr;
			for ($j = 0; ($j < $blockTotalWords); ++$j) 
				$code[(($j * $interleavedBlocks) + $i)] = $data[$j];
		}
		if (((($fix != DxConstants::$DxUndefined) && ($fix >= 0)) && ($fix < $fixedErrSum)))
		{
			return  FALSE ;
		}
		return  TRUE ;
	}
	public static function rightAngleTrueness ($c0, $c1, $c2, $angle) // [DxVector2 c0, DxVector2 c1, DxVector2 c2, double angle]
	{
		$vA = $c0->minus($c1);
		$vB = $c2->minus($c1);
		$vA->norm();
		$vB->norm();
		$m = DxMatrix3::rotate($angle);
		$vB = DxMatrix3::multiply($vB, $m);
		return $vA->dot($vB);
	}
}


class DxMessage {
	protected $_outputIdx;	// int
	public $PadCount;	// int
	public $Array;	// byte[]
	public $Code;	// byte[]
	public $Output;	// byte[]
	function __construct ($sizeIdx, $symbolFormat) // [DxCodeSize sizeIdx, DxFormat symbolFormat]
	{
		if ((($symbolFormat != DxFormat::Matrix) && ($symbolFormat != DxFormat::Mosaic)))
		{
			throw new Exception("Only DxFormats Matrix and Mosaic are currently supported");
		}
		$mappingRows = DxCommon::getSymbolAttribute(DxSymAttribute::DxSymAttribMappingMatrixRows, $sizeIdx);
		$mappingCols = DxCommon::getSymbolAttribute(DxSymAttribute::DxSymAttribMappingMatrixCols, $sizeIdx);
		$this->Array = array();
		for($ii = 0; $ii < $mappingCols * $mappingRows; $ii++)
		{
			$this->Array[$ii] = 0x00;
		}

		$codeSize = (DxCommon::getSymbolAttribute(DxSymAttribute::DxSymAttribSymbolDataWords, $sizeIdx) + DxCommon::getSymbolAttribute(DxSymAttribute::DxSymAttribSymbolErrorWords, $sizeIdx));
		$this->Code = array();
		for($ii = 0; $ii < $codeSize; $ii++)
		{
			$this->Codey[$ii] = 0x00;
		}

		$this->Output = array();
		for($ii = 0; $ii < $codeSize * 10; $ii++)
		{
			$this->Output[$ii] = 0x00;
		}
	}
	public function decodeDataStream ($sizeIdx, $outputStart) // [DxCodeSize sizeIdx, byte[] outputStart]
	{
		$macro =  FALSE ;
		$this->Output = ( (($outputStart != NULL)) ? $outputStart : $this->Output );
		$this->_outputIdx = 0;
		$ptr = $this->Code;
		$dataEndIndex = DxCommon::getSymbolAttribute(DxSymAttribute::DxSymAttribSymbolDataWords, $sizeIdx);
		if ((($ptr[0] == DxConstants::$DxChar05Macro) || ($ptr[0] == DxConstants::$DxChar06Macro)))
		{
			$this->pushOutputMacroHeader($ptr[0]);
			$macro =  TRUE ;
		}
		for ($codeIter = 0; ($codeIter < $dataEndIndex); ) 
		{
			$encScheme = getEncodationScheme($this->Code[$codeIter]);
			if (($encScheme != DxScheme::DxSchemeAscii))
				++$codeIter;
			switch ($encScheme) {
				case DxScheme::DxSchemeAscii:
					$codeIter = $this->decodeSchemeAscii($codeIter, $dataEndIndex);
					break;
				case DxScheme::DxSchemeC40:
				case DxScheme::DxSchemeText:
					$codeIter = $this->decodeSchemeC40Text($codeIter, $dataEndIndex, $encScheme);
					break;
				case DxScheme::DxSchemeX12:
					$codeIter = $this->decodeSchemeX12($codeIter, $dataEndIndex);
					break;
				case DxScheme::DxSchemeEdifact:
					$codeIter = $this->decodeSchemeEdifact($codeIter, $dataEndIndex);
					break;
				case DxScheme::DxSchemeBase256:
					$codeIter = $this->decodeSchemeBase256($codeIter, $dataEndIndex);
					break;
				default:
					break;
			}
		}
		if ($macro)
		{
			$this->pushOutputMacroTrailer();
		}
	}
	protected function pushOutputMacroHeader ($macroType) // [byte macroType]
	{
		$this->pushOutputWord(ord('['));
		$this->pushOutputWord(ord(')'));
		$this->pushOutputWord(ord('>'));
		$this->pushOutputWord(30);
		$this->pushOutputWord(ord('0'));
		if (($macroType == DxConstants::$DxChar05Macro))
		{
			$this->pushOutputWord(ord('5'));
		}
		else
			if (($macroType == DxConstants::$DxChar06Macro))
			{
				$this->pushOutputWord(ord('6'));
			}
			else
			{
				throw new Exception("Macro Header only supported for char05 and char06");
			}
		$this->pushOutputWord(29);
	}
	protected function pushOutputMacroTrailer () 
	{
		$this->pushOutputWord(30);
		$this->pushOutputWord(4);
	}
	protected function pushOutputWord ($value) // [byte value]
	{
		$this->Output[$this->_outputIdx++] = $value;
	}
	protected static function getEncodationScheme ($val) // [byte val]
	{
		if (($val == DxConstants::$DxCharC40Latch))
		{
			return DxScheme::DxSchemeC40;
		}
		if (($val == DxConstants::$DxCharBase256Latch))
		{
			return DxScheme::DxSchemeBase256;
		}
		if (($val == DxConstants::$DxCharEdifactLatch))
		{
			return DxScheme::DxSchemeEdifact;
		}
		if (($val == DxConstants::$DxCharTextLatch))
		{
			return DxScheme::DxSchemeText;
		}
		if (($val == DxConstants::$DxCharX12Latch))
		{
			return DxScheme::DxSchemeX12;
		}
		return DxScheme::DxSchemeAscii;
	}
	protected function decodeSchemeAscii ($startIndex, $endIndex) // [int startIndex, int endIndex]
	{
		$upperShift =  FALSE ;
		while (($startIndex < $endIndex)) 
		{
			$codeword = $this->Code[$startIndex];
			if ((getEncodationScheme($this->Code[$startIndex]) != DxScheme::DxSchemeAscii))
				return $startIndex;
			++$startIndex;
			if ($upperShift)
			{
				$this->pushOutputWord((($codeword + 127)));
				$upperShift =  FALSE ;
			}
			else
				if (($codeword == DxConstants::$DxCharAsciiUpperShift))
				{
					$upperShift =  TRUE ;
				}
				else
					if (($codeword == DxConstants::$DxCharAsciiPad))
					{
						$this->PadCount = $endIndex - $startIndex;
						return $endIndex;
					}
					else
						if (($codeword <= 128))
						{
							$this->pushOutputWord((($codeword - 1)));
						}
						else
							if (($codeword <= 229))
							{
								$digits = (($codeword - 130));
								$this->pushOutputWord(((($digits / 10) + ord('0'))));
								$this->pushOutputWord(((($digits - ((($digits / 10)) * 10)) + ord('0'))));
							}
		}
		return $startIndex;
	}
	protected function decodeSchemeC40Text ($startIndex, $endIndex, $encScheme) // [int startIndex, int endIndex, DxScheme encScheme]
	{
		$c40Values = array();
		for($ii = 0; $ii < 3; $ii++)
		{
			$c40Values[$ii] = 0;
		}

		$state = C40TextState::constructor__();
		$state->Shift = DxConstants::$DxC40TextBasicSet;
		$state->UpperShift = FALSE;
		if (!((($encScheme == DxScheme::DxSchemeC40) || ($encScheme == DxScheme::DxSchemeText))))
		{
			throw new Exception("Invalid scheme selected for decodind!");
		}
		while (($startIndex < $endIndex)) 
		{
			$packed = ((($this->Code[$startIndex] << 8)) | $this->Code[($startIndex + 1)]);
			$c40Values[0] = (((($packed - 1)) / 1600));
			$c40Values[1] = ((((($packed - 1)) / 40)) % 40);
			$c40Values[2] = ((($packed - 1)) % 40);
			$startIndex += 2;
			$i = null;
			for ($i = 0; ($i < 3); ++$i) 
			{
				if (($state->Shift == DxConstants::$DxC40TextBasicSet))
				{
					if (($c40Values[$i] <= 2))
					{
						$state->Shift = ($c40Values[$i] + 1);
					}
					else
						if (($c40Values[$i] == 3))
						{
							$this->pushOutputC40TextWord($state, ' ');
						}
						else
							if (($c40Values[$i] <= 13))
							{
								$this->pushOutputC40TextWord($state, (($c40Values[$i] - 13) . '9'));
							}
							else
								if (($c40Values[$i] <= 39))
								{
									if (($encScheme == DxScheme::DxSchemeC40))
									{
										$this->pushOutputC40TextWord($state, (($c40Values[$i] - 39) . 'Z'));
									}
									else
										if (($encScheme == DxScheme::DxSchemeText))
										{
											$this->pushOutputC40TextWord($state, (($c40Values[$i] - 39) . 'z'));
										}
								}
				}
				else
					if (($state->Shift == DxConstants::$DxC40TextShift1))
					{
						$this->pushOutputC40TextWord($state, $c40Values[$i]);
					}
					else
						if (($state->Shift == DxConstants::$DxC40TextShift2))
						{
							if (($c40Values[$i] <= 14))
							{
								$this->pushOutputC40TextWord($state, ($c40Values[$i] + 33));
							}
							else
								if (($c40Values[$i] <= 21))
								{
									$this->pushOutputC40TextWord($state, ($c40Values[$i] + 43));
								}
								else
									if (($c40Values[$i] <= 26))
									{
										$this->pushOutputC40TextWord($state, ($c40Values[$i] + 69));
									}
									else
										if (($c40Values[$i] == 27))
										{
											$this->pushOutputC40TextWord($state, 0x1d);
										}
										else
											if (($c40Values[$i] == 30))
											{
												$state->UpperShift = TRUE ;
												$state->Shift = DxConstants::$DxC40TextBasicSet;
											}
						}
						else
							if (($state->Shift == DxConstants::$DxC40TextShift3))
							{
								if (($encScheme == DxScheme::DxSchemeC40))
								{
									$this->pushOutputC40TextWord($state, ($c40Values[$i] + 96));
								}
								else
									if (($encScheme == DxScheme::DxSchemeText))
									{
										if (($c40Values[$i] == 0))
										{
											$this->pushOutputC40TextWord($state, ($c40Values[$i] + 96));
										}
										else
											if (($c40Values[$i] <= 26))
											{
												$this->pushOutputC40TextWord($state, (($c40Values[$i] - 26) . 'Z'));
											}
											else
											{
												$this->pushOutputC40TextWord($state, (($c40Values[$i] - 31) + 127));
											}
									}
							}
			}
			if (($this->Code[$startIndex] == DxConstants::$DxCharTripletUnlatch))
				return ($startIndex + 1);
			if ((($endIndex - $startIndex) == 1))
				return $startIndex;
		}
		return $startIndex;
	}
	protected function pushOutputC40TextWord (&$stateP, $value) // [C40TextState[] stateP, int value]
	{
		if (!((($value >= 0) && ($value < 256))))
		{
			throw new Exception("Invalid value: Exceeds range for conversion to byte");
		}
		$this->Output[$this->_outputIdx] = $value;
		if ($stateP->UpperShift)
		{
			if (!((($value >= 0) && ($value < 256))))
			{
				throw new Exception("Invalid value: Exceeds range for conversion to upper case character");
			}
			$this->Output[$this->_outputIdx] += 128;
		}
		++$this->_outputIdx;
		$stateP->Shift = DxConstants::$DxC40TextBasicSet;
		$stateP->UpperShift = FALSE ;
	}
	protected function decodeSchemeX12 ($startIndex, $endIndex) // [int startIndex, int endIndex]
	{
		$x12Values = array();
		for($ii = 0; $ii < 3; $ii++)
		{
			$x12Values[$ii] = 0;
		}

		while (($startIndex < $endIndex)) 
		{
			$packed = ((($this->Code[$startIndex] << 8)) | $this->Code[($startIndex + 1)]);
			$x12Values[0] = (((($packed - 1)) / 1600));
			$x12Values[1] = ((((($packed - 1)) / 40)) % 40);
			$x12Values[2] = ((($packed - 1)) % 40);
			$startIndex += 2;
			for ($i = 0; ($i < 3); ++$i) 
			{
				if (($x12Values[$i] == 0))
					$this->pushOutputWord(13);
				else
					if (($x12Values[$i] == 1))
						$this->pushOutputWord(42);
					else
						if (($x12Values[$i] == 2))
							$this->pushOutputWord(62);
						else
							if (($x12Values[$i] == 3))
								$this->pushOutputWord(32);
							else
								if (($x12Values[$i] <= 13))
									$this->pushOutputWord((($x12Values[$i] + 44)));
								else
									if (($x12Values[$i] <= 90))
										$this->pushOutputWord((($x12Values[$i] + 51)));
			}
			if (($this->Code[$startIndex] == DxConstants::$DxCharTripletUnlatch))
				return ($startIndex + 1);
			if ((($endIndex - $startIndex) == 1))
				return $startIndex;
		}
		return $startIndex;
	}
	protected function decodeSchemeEdifact ($startIndex, $endIndex) // [int startIndex, int endIndex]
	{
		$unpacked = array();
		for($ii = 0; $ii < 4; $ii++)
		{
			$unpacked[$ii] = 0x00;
		}

		while (($startIndex < $endIndex)) 
		{
			$unpacked[0] = (((($this->Code[$startIndex] & 0xfc)) >> 2));
			$unpacked[1] = ((((($this->Code[$startIndex] & 0x03)) << 4) | ((($this->Code[($startIndex + 1)] & 0xf0)) >> 4)));
			$unpacked[2] = ((((($this->Code[($startIndex + 1)] & 0x0f)) << 2) | ((($this->Code[($startIndex + 2)] & 0xc0)) >> 6)));
			$unpacked[3] = (($this->Code[($startIndex + 2)] & 0x3f));
			for ($i = 0; ($i < 4); ++$i) 
			{
				if (($i < 3))
					++$startIndex;
				if (($unpacked[$i] == DxConstants::$DxCharEdifactUnlatch))
				{
					if (($this->Output[$this->_outputIdx] != 0))
					{
						throw new Exception("Error decoding edifact scheme");
					}
					return $startIndex;
				}
				$this->pushOutputWord((($unpacked[$i] ^ (((((($unpacked[$i] & 0x20)) ^ 0x20)) << 1)))));
			}
			if ((($endIndex - $startIndex) < 3))
			{
				return $startIndex;
			}
		}
		return $startIndex;
	}
	protected function decodeSchemeBase256 ($startIndex, $endIndex) // [int startIndex, int endIndex]
	{
		$tempEndIndex = null;
		$idx = ($startIndex + 1);
		$d0 = DxMessage::unRandomize255State($this->Code[$startIndex++], ++$idx);
		if (($d0 == 0))
		{
			$tempEndIndex = $endIndex;
		}
		else
			if (($d0 <= 249))
			{
				$tempEndIndex = ($startIndex + $d0);
			}
			else
			{
				$d1 = DxMessage::unRandomize255State($this->Code[$startIndex++], ++$idx);
				$tempEndIndex = (($startIndex + ((($d0 - 249)) * 250)) + $d1);
			}
		if (($tempEndIndex > $endIndex))
		{
			throw new Exception("Error decoding scheme base 256");
		}
		while (($startIndex < $tempEndIndex)) 
		{
			$this->pushOutputWord(DxMessage::unRandomize255State($this->Code[$startIndex++], ++$idx));
		}
		return $startIndex;
	}
	public static function unRandomize255State ($value, $idx) // [byte value, int idx]
	{
		$pseudoRandom = (((((149 * $idx)) % 255)) + 1);
		$tmp = ($value - $pseudoRandom);
		if (($tmp < 0))
			$tmp += 256;
		if ((($tmp < 0) || ($tmp >= 256)))
		{
			throw new Exception("Error unrandomizing 255 state");
		}
		return $tmp;
	}
	public function symbolModuleStatus ($sizeIdx, $symbolRow, $symbolCol) // [DxCodeSize sizeIdx, int symbolRow, int symbolCol]
	{
		$dataRegionRows = DxCommon::getSymbolAttribute(DxSymAttribute::DxSymAttribDataRegionRows, $sizeIdx);
		$dataRegionCols = DxCommon::getSymbolAttribute(DxSymAttribute::DxSymAttribDataRegionCols, $sizeIdx);
		$symbolRows = DxCommon::getSymbolAttribute(DxSymAttribute::DxSymAttribSymbolRows, $sizeIdx);
		$mappingCols = DxCommon::getSymbolAttribute(DxSymAttribute::DxSymAttribMappingMatrixCols, $sizeIdx);
		$symbolRowReverse = (($symbolRows - $symbolRow) - 1);
		$mappingRow = (($symbolRowReverse - 1) - (2 * (int)(((int)$symbolRowReverse / ((int)($dataRegionRows + 2))))));
		$mappingCol = (($symbolCol - 1) - (2 * (int)(((int)$symbolCol / (((int)$dataRegionCols + 2))))));
		if (((($symbolRow % (($dataRegionRows + 2))) == 0) || (($symbolCol % (($dataRegionCols + 2))) == 0)))
		{
			return DxConstants::$DxModuleOnRGB;
		}
		if ((((($symbolRow + 1)) % (($dataRegionRows + 2))) == 0))
		{
			return (( ((((($symbolCol & 0x01)) != 0))) ? 0 : DxConstants::$DxModuleOnRGB ));
		}
		if ((((($symbolCol + 1)) % (($dataRegionCols + 2))) == 0))
		{
			return (( ((((($symbolRow & 0x01)) != 0))) ? 0 : DxConstants::$DxModuleOnRGB ));
		}
		return (($this->Array[(($mappingRow * $mappingCols) + $mappingCol)] | DxConstants::$DxModuleData));
	}
	public function getArraySize () 
	{
		return count($this->Array) /*from: this.getArray().length*/;
	}
	public function getCodeSize () 
	{
		return count($this->Code) /*from: this.getCode().length*/;
	}
	public function getOutputSize () 
	{
		return count($this->Output) /*from: this.getOutput().length*/;
	}
}

class DxChannel {
	public $EncodedWords;	// byte[]
	public $Input;	// byte[]
	public $EncScheme;	// DxScheme
	public $Invalid;	// DxChannelStatus
	public $InputIndex;	// int
	public $EncodedLength;	// int
	public $CurrentLength;	// int
	public $FirstCodeWord;	// int
	function __construct() { // default class members
		$this->EncScheme = DxScheme::DxSchemeAutoFast;
		$this->Invalid = DxChannelStatus::DxChannelValid;
		$this->EncodedWords = array();
		for($ii = 0; $ii < 1558; $ii++)
		{
			$this->EncodedWords[$ii] = 0x00;
		}
	}

	//public function getEncodedWords () 
	//{
	//	return ( (($this->_encodedWords != NULL)) ? $this->_encodedWords : ($this->_encodedWords = array()) );
	//}
}

class DxChannelGroup {
	public $Channels;	// DxChannel[]
	
	function __construct () 
	{
		if (($this->_channels == NULL))
		{
			$this->_channels = array();
			for ($i = 0; ($i < 6); ++$i) 
			{
				$this->_channels[$i] = new DxChannel();
			}
		}
		return $this->_channels;
	}
}

class DxChannelStatus {
	const DxChannelValid = 0x0;
	const DxChannelUnsupportedChar = 0x01;
	const DxChannelCannotUnlatch = 0x10;
}

class DxSymAttribute {
	const DxSymAttribSymbolRows = 0;
	const DxSymAttribSymbolCols = 1;
	const DxSymAttribDataRegionRows = 2;
	const DxSymAttribDataRegionCols = 3;
	const DxSymAttribHorizDataRegions = 4;
	const DxSymAttribVertDataRegions = 5;
	const DxSymAttribMappingMatrixRows = 6;
	const DxSymAttribMappingMatrixCols = 7;
	const DxSymAttribInterleavedBlocks = 8;
	const DxSymAttribBlockErrorWords = 9;
	const DxSymAttribBlockMaxCorrectable = 10;
	const DxSymAttribSymbolDataWords = 11;
	const DxSymAttribSymbolErrorWords = 12;
	const DxSymAttribSymbolMaxCorrectable = 13;
}

class DxMatrix3 {
	protected $_data;	// double[][]
	private function __init() { // default class members
		$this->_data = array();
		for($ii = 0; $ii < 3; $ii++)
		{
			for($jj = 0; $jj < 3; $jj++)
			{
				$this->_data[$ii][$jj] = 0.0;
			}
		}

	}
	function __construct ($src) // [DxMatrix3 src]
	{
		$this->__init();
		if($src != null)
		{
			for($ii = 0; $ii < 3; $ii++)
			{
				for($jj = 0; $jj < 3; $jj++)
				{
					$this->_data[$ii][$jj] =  $src->get___idx($ii, $jj);
				}
			}


			//$this->_data = array( $src->get___idx(0, 0), $src->get___idx(0, 1), $src->get___idx(0, 2), $src->get___idx(1, 0), $src->get___idx(1, 1), $src->get___idx(1, 2), $src->get___idx(2, 0), $src->get___idx(2, 1), $src->get___idx(2, 2) );
		}
		return $this;
	}

	public static function identity () 
	{
		return DxMatrix3::translate(0, 0);
	}
	public static function translate ($tx, $ty) // [double tx, double ty]
	{
		$this->__init();
		$result->_data = array( doubleval(1.0), doubleval(0.0), doubleval(0.0), doubleval(0.0), doubleval(1.0), doubleval(0.0), $tx, $ty, doubleval(1.0) );
		return $result;
	}
	public static function rotate ($angle) // [double angle]
	{
		$this->__init();
		$result->_data = array( cos($angle), sin($angle), doubleval(0.0), (-sin($angle)), cos($angle), doubleval(0.0), 0, 0, doubleval(1.0) );
		return $result;
	}
	public static function scale ($sx, $sy) // [double sx, double sy]
	{
		$this->__init();
		$result->_data = array( $sx, doubleval(0.0), doubleval(0.0), doubleval(0.0), $sy, doubleval(0.0), 0, 0, doubleval(1.0) );
		return $result;
	}
	public static function shear ($shx, $shy) // [double shx, double shy]
	{
		$this->__init();
		$result->_data = array( doubleval(1.0), $shy, doubleval(0.0), $shx, doubleval(1.0), doubleval(0.0), 0, 0, doubleval(1.0) );
		return $result;
	}
	public static function lineSkewTop ($b0, $b1, $sz) // [double b0, double b1, double sz]
	{
		if (($b0 < DxConstants::$DxAlmostZero))
		{
			throw new Exception("b0 must be larger than zero in top line skew transformation");
		}
		$this->__init();
		$result->_data = array( ($b1 / $b0), doubleval(0.0), ((($b1 - $b0)) / (($sz * $b0))), doubleval(0.0), ($sz / $b0), doubleval(0.0), 0, 0, doubleval(1.0) );
		return $result;
	}
	public static function lineSkewTopInv ($b0, $b1, $sz) // [double b0, double b1, double sz]
	{
		if (($b1 < DxConstants::$DxAlmostZero))
		{
			throw new Exception("b1 must be larger than zero in top line skew transformation (inverse)");
		}
		$this->__init();
		$result->_data = array( ($b0 / $b1), doubleval(0.0), ((($b0 - $b1)) / (($sz * $b1))), doubleval(0.0), ($b0 / $sz), doubleval(0.0), 0, 0, doubleval(1.0) );
		return $result;
	}
	public static function lineSkewSide ($b0, $b1, $sz) // [double b0, double b1, double sz]
	{
		if (($b0 < DxConstants::$DxAlmostZero))
		{
			throw new Exception("b0 must be larger than zero in side line skew transformation (inverse)");
		}
		$this->__init();
		$result->_data = array( ($sz / $b0), doubleval(0.0), doubleval(0.0), doubleval(0.0), ($b1 / $b0), ((($b1 - $b0)) / (($sz * $b0))), 0, 0, doubleval(1.0) );
		return $result;
	}
	public static function lineSkewSideInv ($b0, $b1, $sz) // [double b0, double b1, double sz]
	{
		if (($b1 < DxConstants::$DxAlmostZero))
		{
			throw new Exception("b1 must be larger than zero in top line skew transformation (inverse)");
		}
		$this->__init();
		$result->_data = array( ($b0 / $sz), doubleval(0.0), doubleval(0.0), doubleval(0.0), ($b0 / $b1), ((($b0 - $b1)) / (($sz * $b1))), 0, 0, doubleval(1.0) );
		return $result;
	}
	public static function multiply ($vector, $matrix) // [DxVector2 vector, DxMatrix3 matrix]
	{
		$w = abs((($vector->getX() * $matrix->_data[0][2]) + ($vector->getY() * $matrix->_data[1][2])) + $matrix->_data[2][2]);
		if (($w <= DxConstants::$DxAlmostZero))
		{
			try 
			{
				throw new Exception("Multiplication of vector and matrix resulted in invalid result");
			}
			catch (Exception $ex)
			{ /* empty */ }
		}
		$result = NULL;
		try 
		{
			$result = DxVector2::constructor__D_D(((((($vector->getX() * $matrix->_data[0][0]) + ($vector->getY() * $matrix->_data[1][0])) + $matrix->_data[2][0])) / $w), ((((($vector->getX() * $matrix->_data[0][1]) + ($vector->getY() * $matrix->_data[1][1])) + $matrix->_data[2][1])) / $w));
		}
		catch (Exception $ex)
		{ /* empty */ }
		return $result;
	}
	public static function multiply3 ($m1, $m2) // [DxMatrix3 m1, DxMatrix3 m2]
	{
		$this->__init();
		$result->_data = array( doubleval(0.0), 0, 0, 0, 0, 0, 0, 0, 0 );
		for ($i = 0; ($i < 3); ++$i) 
		{
			for ($j = 0; ($j < 3); ++$j) 
			{
				for ($k = 0; ($k < 3); ++$k) 
				{
					$result->_data[$i][$j] += ($m1->_data[$i][$k] * $m2->_data[$k][$j]);
				}
			}
		}
		return $result;
	}
	public function toString () 
	{
		try 
		{
			return $String->format("{0}\t{1}\t{2}\n{3}\t{4}\t{5}\n{6}\t{7}\t{8}\n", $this->_data[0][0], $this->_data[0][1], $this->_data[0][2], $this->_data[1][0], $this->_data[1][1], $this->_data[1][2], $this->_data[2][0], $this->_data[2][1], $this->_data[2][2]);
		}
		catch (RuntimeException $__dummyCatchVar0)
		{
			throw $__dummyCatchVar0;
		}
		catch (Exception $__dummyCatchVar0)
		{
			throw new RuntimeException($__dummyCatchVar0);
		}
	}
	public function get___idx ($i, $j) // [int i, int j]
	{
		return $this->_data[$i][$j];
	}
	public function set___idx ($i, $j, $value) // [int i, int j, double value]
	{
		$this->_data[$i][$j] = $value;
	}
}

class DxFormat {
	const Matrix = 0;
	const Mosaic = 1;
}

class DxMaskBit {
	const DxMaskBit8 = 0x00000001;
	const DxMaskBit7 = 0x00000010;
	const DxMaskBit6 = 0x00000100;
	const DxMaskBit5 = 0x00001000;
	const DxMaskBit4 = 0x00010000;
	const DxMaskBit3 = 0x00100000;
	const DxMaskBit2 = 0x01000000;
	const DxMaskBit1 = 0x10000000;
}

class DxImage {
	public $RowPadBytes;	// int
	public $Width;	// int
	public $Height;	// int
	public $PixelPacking;	// DxPackOrder
	public $BitsPerPixel;	// int
	public $BytesPerPixel;	// int
	public $RowSizeBytes;	// int
	public $ImageFlip;	// DxFlip
	public $ChannelCount;	// int
	public $ChannelStart;	// int[]
	public $BitsPerChannel;	// int[]
	public $Pxl;	// byte[]
	private function __init() { // default class members
		$this->__PixelPacking = DxPackOrder::DxPackCustom;
		$this->__ImageFlip = DxFlip::DxFlipNone;
	}
	function __construct ($pxl, $width, $height, $pack) // [byte[] pxl, int width, int height, DxPackOrder pack]
	{
		$this->__init();
		$this->BitsPerChannel = array();
		for($ii = 0; $ii < 4; $ii++)
		{
			$this->BitsPerChannel[$ii] = 0;
		}

		$this->ChannelStart = array();
		for($ii = 0; $ii < 4; $ii++)
		{
			$this->ChannelStart[$ii] = 0;
		}
		//if ($pxl == null || $width < 1 || $height < 1)
		if ($width < 1 || $height < 1)
		{
		throw new Exception("Cannot create image of size null");
		}
		$this->Pxl = $pxl;
		$this->Width = $width;
		$this->Height = $height;
		$this->PixelPacking = $pack;
		$this->BitsPerPixel = DxCommon::getBitsPerPixel($pack);
		$this->BytesPerPixel = ($this->BitsPerPixel / 8);
		$this->RowPadBytes = 0;
		$this->RowSizeBytes = ($this->Width * $this->BytesPerPixel) + $this->RowPadBytes;
		$this->ImageFlip = DxFlip::DxFlipNone;
		$this->ChannelCount = 0;

		switch ($pack) {
			case DxPackOrder::DxPackCustom:
				break;
			case DxPackOrder::DxPack1bppK:
				throw new Exception("Cannot create image: not supported pack order!");
			case DxPackOrder::DxPack8bppK:
				$this->setChannel(0, 8);
				break;
			case DxPackOrder::DxPack16bppRGB:
			case DxPackOrder::DxPack16bppBGR:
			case DxPackOrder::DxPack16bppYCbCr:
				$this->setChannel(0, 5);
				$this->setChannel(5, 5);
				$this->setChannel(10, 5);
				break;
			case DxPackOrder::DxPack24bppRGB:
			case DxPackOrder::DxPack24bppBGR:
			case DxPackOrder::DxPack24bppYCbCr:
			case DxPackOrder::DxPack32bppRGBX:
			case DxPackOrder::DxPack32bppBGRX:
				$this->setChannel(0, 8);
				$this->setChannel(8, 8);
				$this->setChannel(16, 8);
				break;
			case DxPackOrder::DxPack16bppRGBX:
			case DxPackOrder::DxPack16bppBGRX:
				$this->setChannel(0, 5);
				$this->setChannel(5, 5);
				$this->setChannel(10, 5);
				break;
			case DxPackOrder::DxPack16bppXRGB:
			case DxPackOrder::DxPack16bppXBGR:
				$this->setChannel(1, 5);
				$this->setChannel(6, 5);
				$this->setChannel(11, 5);
				break;
			case DxPackOrder::DxPack32bppXRGB:
			case DxPackOrder::DxPack32bppXBGR:
				$this->setChannel(8, 8);
				$this->setChannel(16, 8);
				$this->setChannel(24, 8);
				break;
			case DxPackOrder::DxPack32bppCMYK:
				$this->setChannel(0, 8);
				$this->setChannel(8, 8);
				$this->setChannel(16, 8);
				$this->setChannel(24, 8);
				break;
			default:
				throw new Exception("Cannot create image: Invalid Pack Order");
		}
	}
	public function setChannel ($channelStart, $bitsPerChannel) // [int channelStart, int bitsPerChannel]
	{
		if (($this->ChannelCount >= 4))
			return  FALSE ;
		$this->BitsPerChannel[$this->ChannelCount] = $bitsPerChannel;
		$this->ChannelStart[$this->ChannelCount] = $channelStart;
		$this->ChannelCount = ($this->ChannelCount + 1);
		return  TRUE ;
	}
	public function getByteOffset ($x, $y) // [int x, int y]
	{
		if (($this->ImageFlip == DxFlip::DxFlipX))
		{
			throw new Exception("DxFlipX is not an option!");
		}
		if (!$this->containsInt(0, $x, $y))
			return DxConstants::$DxUndefined;
		if (($this->ImageFlip == DxFlip::DxFlipY))
			return ((($y * $this->RowSizeBytes) + ($x * $this->BytesPerPixel)));
		return (((((($this->Height - $y) - 1)) * $this->RowSizeBytes) + ($x * $this->BytesPerPixel)));
	}
	public function getPixelValue ($x, $y, $channel, $value) // [int x, int y, int channel, int[] value]
	{
		if (($channel >= $this->ChannelCount))
		{
			throw new Exception("Channel greater than channel count!");
		}
		$offset = $this->getByteOffset($x, $y);
		if (($offset == DxConstants::$DxUndefined))
		{
			return  FALSE ;
		}
		switch ($this->BitsPerChannel[$channel]) {
			case 1:
				break;
			case 5:
				break;
			case 8:
				if (((($this->ChannelStart[$channel] % 8) != 0) || (($this->BitsPerPixel % 8) != 0)))
				{
					throw new Exception("Error getting pixel value");
				}
				$value[0] = $this->Pxl[($offset + $channel)];
				break;
		}
		return  TRUE ;
	}
	public function setPixelValue ($x, $y, $channel, $value) // [int x, int y, int channel, byte value]
	{
		if (($channel >= $this->ChannelCount))
		{
			throw new Exception("Channel greater than channel count!");
		}
		$offset = $this->getByteOffset($x, $y);
		if (($offset == DxConstants::$DxUndefined))
		{
			return  FALSE ;
		}
		switch ($this->BitsPerChannel[$channel]) {
			case 1:
				break;
			case 5:
				break;
			case 8:
				if (((($this->ChannelStart[$channel] % 8) != 0) || (($this->BitsPerPixel % 8) != 0)))
				{
					throw new Exception("Error getting pixel value");
				}
				$this->Pxl[($offset + $channel)] = $value;
				break;
		}
		return  TRUE ;
	}
	public function containsInt ($margin, $x, $y) // [int margin, int x, int y]
	{
		if (((((($x - $margin) >= 0) && (($x + $margin) < $this->Width)) && (($y - $margin) >= 0)) && (($y + $margin) < $this->Height)))
			return  TRUE ;
		return  FALSE ;
	}
	public function containsFloat ($x, $y) // [double x, double y]
	{
		if ((((($x >= doubleval(0.0)) && ($x < $this->Width)) && ($y >= doubleval(0.0))) && ($y < $this->Height)))
		{
			return  TRUE ;
		}
		return  FALSE ;
	}

	public function setRowPadBytes ($value) // [int value]
	{
		$this->RowPadBytes = $value;
		$this->RowSizeBytes = (($this->Width * (($this->BitsPerPixel / 8))) + $this->RowPadBytes);
	}

}

?>
