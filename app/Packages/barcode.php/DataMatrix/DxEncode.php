<?php
require_once("java/util/ArrayList.php");
require_once("java/util/List.php");
class DxEncode {
	protected $_method;	// int
	protected $_scheme;	// DxScheme
	protected $_sizeIdxRequest;	// DxCodeSize
	protected $_marginSize;	// int
	protected $_moduleSize;	// int
	protected $_pixelPacking;	// DxPackOrder
	protected $_imageFlip;	// DxFlip
	protected $_rowPadBytes;	// int
	protected $_message;	// DxMessage
	protected $_image;	// DxImage
	protected $_region;	// DxRegion
	protected $_rawData;	// boolean[][]
	public $flgGS1;	// boolean
	private function __init() { // default class members
		$this->_scheme = DxScheme::$DxSchemeAutoFast;
		$this->_sizeIdxRequest = DxCodeSize::$DxSzRectAuto;
		$this->_pixelPacking = DxPackOrder::$DxPackCustom;
		$this->_imageFlip = DxFlip::$DxFlipNone;
		$this->flgGS1 =  FALSE ;
	}
	public static function constructor__ () 
	{
		$me = new self();
		$me->__init();
		$me->_scheme = DxScheme::$DxSchemeAscii;
		$me->_sizeIdxRequest = DxCodeSize::$DxSzAuto;
		$me->_marginSize = 10;
		$me->_moduleSize = 5;
		$me->_pixelPacking = DxPackOrder::$DxPack24bppRGB;
		$me->_imageFlip = DxFlip::$DxFlipNone;
		$me->_rowPadBytes = 0;
		return $me;
	}
	public static function constructor__DxEncode ($src) // [DxEncode src]
	{
		$me = new self();
		$me->__init();
		$me->_scheme = $src->_scheme;
		$me->_sizeIdxRequest = $src->_sizeIdxRequest;
		$me->_marginSize = $src->_marginSize;
		$me->_moduleSize = $src->_moduleSize;
		$me->_pixelPacking = $src->_pixelPacking;
		$me->_imageFlip = $src->_imageFlip;
		$me->_rowPadBytes = $src->_rowPadBytes;
		$me->_image = $src->_image;
		$me->_message = $src->_message;
		$me->_method = $src->_method;
		$me->_region = $src->_region;
		return $me;
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
		$sizeIdx = $this->_sizeIdxRequest;
		$sizeIdxP = array();
		$sizeIdxP[0] = $this->_sizeIdxRequest;
		$dataWordCount = $this->encodeDataCodewords($buf, $inputString, $sizeIdxP);
		$sizeIdx = $sizeIdxP[0];
		if (($dataWordCount <= 0))
		{
			return  FALSE ;
		}
		if ((($sizeIdx == DxCodeSize::$DxSzAuto) || ($sizeIdx == DxCodeSize::$DxSzRectAuto)))
		{
			throw new Exception("Invalid symbol size for encoding!");
		}
		$dataWordCountP = $dataWordCount;
		$padCount = $this->addPadChars($buf, $dataWordCountP, DxCommon::getSymbolAttribute(DxSymAttribute::$DxSymAttribSymbolDataWords, $sizeIdx));
		$dataWordCount = $dataWordCountP[0];
		$this->_region = DxRegion::constructor__();
		$this->_region->setSizeIdx($sizeIdx);
		$this->_region->setSymbolRows(DxCommon::getSymbolAttribute(DxSymAttribute::$DxSymAttribSymbolRows, $sizeIdx));
		$this->_region->setSymbolCols(DxCommon::getSymbolAttribute(DxSymAttribute::$DxSymAttribSymbolCols, $sizeIdx));
		$this->_region->setMappingRows(DxCommon::getSymbolAttribute(DxSymAttribute::$DxSymAttribMappingMatrixRows, $sizeIdx));
		$this->_region->setMappingCols(DxCommon::getSymbolAttribute(DxSymAttribute::$DxSymAttribMappingMatrixCols, $sizeIdx));
		$this->_message = DxMessage::constructor__DxCodeSize_DxFormat($sizeIdx, DxFormat::$Matrix);
		$this->_message->setPadCount($padCount);
		for ($i = 0; ($i < $dataWordCount); ++$i) 
		{
			$this->_message->getCode()[$i] = $buf[$i];
		}
		DxCommon::genReedSolEcc($this->_message, $this->_region->getSizeIdx());
		$this->modulePlacementEcc200($this->_message->getArray(), $this->_message->getCode(), $this->_region->getSizeIdx(), DxConstants::$DxModuleOnRGB);
		$width = ((2 * $this->_marginSize) + (($this->_region->getSymbolCols() * $this->_moduleSize)));
		$height = ((2 * $this->_marginSize) + (($this->_region->getSymbolRows() * $this->_moduleSize)));
		$bitsPerPixel = DxCommon::getBitsPerPixel($this->_pixelPacking);
		if (($bitsPerPixel == DxConstants::$DxUndefined))
			return  FALSE ;
		if ((($bitsPerPixel % 8) != 0))
		{
			throw new Exception("Invalid java.awt.Color  depth for encoding!");
		}
		$pxl = array();
		$this->_image = DxImage::constructor__aB_I_I_DxPackOrder($pxl, $width, $height, $this->_pixelPacking);
		$this->_image->setImageFlip($this->_imageFlip);
		$this->_image->setRowPadBytes($this->_rowPadBytes);
		if ($encodeRaw)
			$this->printPatternRaw();
		else
			$this->printPattern($foreColor, $backColor);
		return  TRUE ;
	}
	protected function modulePlacementEcc200 ($modules, $codewords, $sizeIdx, $moduleOnColor) // [byte[] modules, byte[] codewords, DxCodeSize sizeIdx, int moduleOnColor]
	{
		if (((($moduleOnColor & (((DxConstants::$DxModuleOnRed | DxConstants::$DxModuleOnGreen) | DxConstants::$DxModuleOnBlue)))) == 0))
		{
			throw new Exception("Error with module placement ECC 200");
		}
		$mappingRows = DxCommon::getSymbolAttribute(DxSymAttribute::$DxSymAttribMappingMatrixRows, $sizeIdx);
		$mappingCols = DxCommon::getSymbolAttribute(DxSymAttribute::$DxSymAttribMappingMatrixCols, $sizeIdx);
		$chr = 0;
		$row = 4;
		$col = 0;
		do 
		{
			if (((($row == $mappingRows)) && (($col == 0))))
				DxEncode::patternShapeSpecial1($modules, $mappingRows, $mappingCols, $codewords, ++$chr, $moduleOnColor);
			else
				if ((((($row == ($mappingRows - 2))) && (($col == 0))) && ((($mappingCols % 4) != 0))))
					DxEncode::patternShapeSpecial2($modules, $mappingRows, $mappingCols, $codewords, ++$chr, $moduleOnColor);
				else
					if ((((($row == ($mappingRows - 2))) && (($col == 0))) && ((($mappingCols % 8) == 4))))
						DxEncode::patternShapeSpecial3($modules, $mappingRows, $mappingCols, $codewords, ++$chr, $moduleOnColor);
					else
						if ((((($row == ($mappingRows + 4))) && (($col == 2))) && ((($mappingCols % 8) == 0))))
							DxEncode::patternShapeSpecial4($modules, $mappingRows, $mappingCols, $codewords, ++$chr, $moduleOnColor);
			do 
			{
				if ((((($row < $mappingRows)) && (($col >= 0))) && ((($modules[(($row * $mappingCols) + $col)] & DxConstants::$DxModuleVisited)) == 0)))
					DxEncode::patternShapeStandard($modules, $mappingRows, $mappingCols, $row, $col, $codewords, ++$chr, $moduleOnColor);
				$row -= 2;
				$col += 2;
			}
			while (((($row >= 0)) && (($col < $mappingCols))));
			$row += 1;
			$col += 3;
			do 
			{
				if ((((($row >= 0)) && (($col < $mappingCols))) && ((($modules[(($row * $mappingCols) + $col)] & DxConstants::$DxModuleVisited)) == 0)))
					DxEncode::patternShapeStandard($modules, $mappingRows, $mappingCols, $row, $col, $codewords, ++$chr, $moduleOnColor);
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
	public static function patternShapeStandard ($modules, $mappingRows, $mappingCols, $row, $col, $codeword, $codeWordIndex, $moduleOnColor) // [byte[] modules, int mappingRows, int mappingCols, int row, int col, byte[] codeword, int codeWordIndex, int moduleOnColor]
	{
		DxEncode::placeModule($modules, $mappingRows, $mappingCols, ($row - 2), ($col - 2), $codeword, $codeWordIndex, DxMaskBit::$DxMaskBit1, $moduleOnColor);
		DxEncode::placeModule($modules, $mappingRows, $mappingCols, ($row - 2), ($col - 1), $codeword, $codeWordIndex, DxMaskBit::$DxMaskBit2, $moduleOnColor);
		DxEncode::placeModule($modules, $mappingRows, $mappingCols, ($row - 1), ($col - 2), $codeword, $codeWordIndex, DxMaskBit::$DxMaskBit3, $moduleOnColor);
		DxEncode::placeModule($modules, $mappingRows, $mappingCols, ($row - 1), ($col - 1), $codeword, $codeWordIndex, DxMaskBit::$DxMaskBit4, $moduleOnColor);
		DxEncode::placeModule($modules, $mappingRows, $mappingCols, ($row - 1), $col, $codeword, $codeWordIndex, DxMaskBit::$DxMaskBit5, $moduleOnColor);
		DxEncode::placeModule($modules, $mappingRows, $mappingCols, $row, ($col - 2), $codeword, $codeWordIndex, DxMaskBit::$DxMaskBit6, $moduleOnColor);
		DxEncode::placeModule($modules, $mappingRows, $mappingCols, $row, ($col - 1), $codeword, $codeWordIndex, DxMaskBit::$DxMaskBit7, $moduleOnColor);
		DxEncode::placeModule($modules, $mappingRows, $mappingCols, $row, $col, $codeword, $codeWordIndex, DxMaskBit::$DxMaskBit8, $moduleOnColor);
	}
	public static function patternShapeSpecial1 ($modules, $mappingRows, $mappingCols, $codeword, $codeWordIndex, $moduleOnColor) // [byte[] modules, int mappingRows, int mappingCols, byte[] codeword, int codeWordIndex, int moduleOnColor]
	{
		DxEncode::placeModule($modules, $mappingRows, $mappingCols, ($mappingRows - 1), 0, $codeword, $codeWordIndex, DxMaskBit::$DxMaskBit1, $moduleOnColor);
		DxEncode::placeModule($modules, $mappingRows, $mappingCols, ($mappingRows - 1), 1, $codeword, $codeWordIndex, DxMaskBit::$DxMaskBit2, $moduleOnColor);
		DxEncode::placeModule($modules, $mappingRows, $mappingCols, ($mappingRows - 1), 2, $codeword, $codeWordIndex, DxMaskBit::$DxMaskBit3, $moduleOnColor);
		DxEncode::placeModule($modules, $mappingRows, $mappingCols, 0, ($mappingCols - 2), $codeword, $codeWordIndex, DxMaskBit::$DxMaskBit4, $moduleOnColor);
		DxEncode::placeModule($modules, $mappingRows, $mappingCols, 0, ($mappingCols - 1), $codeword, $codeWordIndex, DxMaskBit::$DxMaskBit5, $moduleOnColor);
		DxEncode::placeModule($modules, $mappingRows, $mappingCols, 1, ($mappingCols - 1), $codeword, $codeWordIndex, DxMaskBit::$DxMaskBit6, $moduleOnColor);
		DxEncode::placeModule($modules, $mappingRows, $mappingCols, 2, ($mappingCols - 1), $codeword, $codeWordIndex, DxMaskBit::$DxMaskBit7, $moduleOnColor);
		DxEncode::placeModule($modules, $mappingRows, $mappingCols, 3, ($mappingCols - 1), $codeword, $codeWordIndex, DxMaskBit::$DxMaskBit8, $moduleOnColor);
	}
	public static function patternShapeSpecial2 ($modules, $mappingRows, $mappingCols, $codeword, $codeWordIndex, $moduleOnColor) // [byte[] modules, int mappingRows, int mappingCols, byte[] codeword, int codeWordIndex, int moduleOnColor]
	{
		DxEncode::placeModule($modules, $mappingRows, $mappingCols, ($mappingRows - 3), 0, $codeword, $codeWordIndex, DxMaskBit::$DxMaskBit1, $moduleOnColor);
		DxEncode::placeModule($modules, $mappingRows, $mappingCols, ($mappingRows - 2), 0, $codeword, $codeWordIndex, DxMaskBit::$DxMaskBit2, $moduleOnColor);
		DxEncode::placeModule($modules, $mappingRows, $mappingCols, ($mappingRows - 1), 0, $codeword, $codeWordIndex, DxMaskBit::$DxMaskBit3, $moduleOnColor);
		DxEncode::placeModule($modules, $mappingRows, $mappingCols, 0, ($mappingCols - 4), $codeword, $codeWordIndex, DxMaskBit::$DxMaskBit4, $moduleOnColor);
		DxEncode::placeModule($modules, $mappingRows, $mappingCols, 0, ($mappingCols - 3), $codeword, $codeWordIndex, DxMaskBit::$DxMaskBit5, $moduleOnColor);
		DxEncode::placeModule($modules, $mappingRows, $mappingCols, 0, ($mappingCols - 2), $codeword, $codeWordIndex, DxMaskBit::$DxMaskBit6, $moduleOnColor);
		DxEncode::placeModule($modules, $mappingRows, $mappingCols, 0, ($mappingCols - 1), $codeword, $codeWordIndex, DxMaskBit::$DxMaskBit7, $moduleOnColor);
		DxEncode::placeModule($modules, $mappingRows, $mappingCols, 1, ($mappingCols - 1), $codeword, $codeWordIndex, DxMaskBit::$DxMaskBit8, $moduleOnColor);
	}
	public static function patternShapeSpecial3 ($modules, $mappingRows, $mappingCols, $codeword, $codeWordIndex, $moduleOnColor) // [byte[] modules, int mappingRows, int mappingCols, byte[] codeword, int codeWordIndex, int moduleOnColor]
	{
		DxEncode::placeModule($modules, $mappingRows, $mappingCols, ($mappingRows - 3), 0, $codeword, $codeWordIndex, DxMaskBit::$DxMaskBit1, $moduleOnColor);
		DxEncode::placeModule($modules, $mappingRows, $mappingCols, ($mappingRows - 2), 0, $codeword, $codeWordIndex, DxMaskBit::$DxMaskBit2, $moduleOnColor);
		DxEncode::placeModule($modules, $mappingRows, $mappingCols, ($mappingRows - 1), 0, $codeword, $codeWordIndex, DxMaskBit::$DxMaskBit3, $moduleOnColor);
		DxEncode::placeModule($modules, $mappingRows, $mappingCols, 0, ($mappingCols - 2), $codeword, $codeWordIndex, DxMaskBit::$DxMaskBit4, $moduleOnColor);
		DxEncode::placeModule($modules, $mappingRows, $mappingCols, 0, ($mappingCols - 1), $codeword, $codeWordIndex, DxMaskBit::$DxMaskBit5, $moduleOnColor);
		DxEncode::placeModule($modules, $mappingRows, $mappingCols, 1, ($mappingCols - 1), $codeword, $codeWordIndex, DxMaskBit::$DxMaskBit6, $moduleOnColor);
		DxEncode::placeModule($modules, $mappingRows, $mappingCols, 2, ($mappingCols - 1), $codeword, $codeWordIndex, DxMaskBit::$DxMaskBit7, $moduleOnColor);
		DxEncode::placeModule($modules, $mappingRows, $mappingCols, 3, ($mappingCols - 1), $codeword, $codeWordIndex, DxMaskBit::$DxMaskBit8, $moduleOnColor);
	}
	public static function patternShapeSpecial4 ($modules, $mappingRows, $mappingCols, $codeword, $codeWordIndex, $moduleOnColor) // [byte[] modules, int mappingRows, int mappingCols, byte[] codeword, int codeWordIndex, int moduleOnColor]
	{
		DxEncode::placeModule($modules, $mappingRows, $mappingCols, ($mappingRows - 1), 0, $codeword, $codeWordIndex, DxMaskBit::$DxMaskBit1, $moduleOnColor);
		DxEncode::placeModule($modules, $mappingRows, $mappingCols, ($mappingRows - 1), ($mappingCols - 1), $codeword, $codeWordIndex, DxMaskBit::$DxMaskBit2, $moduleOnColor);
		DxEncode::placeModule($modules, $mappingRows, $mappingCols, 0, ($mappingCols - 3), $codeword, $codeWordIndex, DxMaskBit::$DxMaskBit3, $moduleOnColor);
		DxEncode::placeModule($modules, $mappingRows, $mappingCols, 0, ($mappingCols - 2), $codeword, $codeWordIndex, DxMaskBit::$DxMaskBit4, $moduleOnColor);
		DxEncode::placeModule($modules, $mappingRows, $mappingCols, 0, ($mappingCols - 1), $codeword, $codeWordIndex, DxMaskBit::$DxMaskBit5, $moduleOnColor);
		DxEncode::placeModule($modules, $mappingRows, $mappingCols, 1, ($mappingCols - 3), $codeword, $codeWordIndex, DxMaskBit::$DxMaskBit6, $moduleOnColor);
		DxEncode::placeModule($modules, $mappingRows, $mappingCols, 1, ($mappingCols - 2), $codeword, $codeWordIndex, DxMaskBit::$DxMaskBit7, $moduleOnColor);
		DxEncode::placeModule($modules, $mappingRows, $mappingCols, 1, ($mappingCols - 1), $codeword, $codeWordIndex, DxMaskBit::$DxMaskBit8, $moduleOnColor);
	}
	public static function placeModule ($modules, $mappingRows, $mappingCols, $row, $col, $codeword, $codeWordIndex, $mask, $moduleOnColor) // [byte[] modules, int mappingRows, int mappingCols, int row, int col, byte[] codeword, int codeWordIndex, DxMaskBit mask, int moduleOnColor]
	{
		$bit = 0;
		if (($mask == DxMaskBit::$DxMaskBit8))
		{
			$bit = ((0x01 << 0));
		}
		else
			if (($mask == DxMaskBit::$DxMaskBit7))
			{
				$bit = ((0x01 << 1));
			}
			else
				if (($mask == DxMaskBit::$DxMaskBit6))
				{
					$bit = ((0x01 << 2));
				}
				else
					if (($mask == DxMaskBit::$DxMaskBit5))
					{
						$bit = ((0x01 << 3));
					}
					else
						if (($mask == DxMaskBit::$DxMaskBit4))
						{
							$bit = ((0x01 << 4));
						}
						else
							if (($mask == DxMaskBit::$DxMaskBit3))
							{
								$bit = ((0x01 << 5));
							}
							else
								if (($mask == DxMaskBit::$DxMaskBit2))
								{
									$bit = ((0x01 << 6));
								}
								else
									if (($mask == DxMaskBit::$DxMaskBit1))
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
				$codeword[$codeWordIndex] &= ((0xff ^ $mask->getIntVal()));
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
		$sizeIdxRequest = DxCodeSize::$DxSzRectAuto;
		$splitSizeIdxAttempt = DxCodeSize::$DxSzRectAuto;
		$splitSizeIdxLast = DxCodeSize::$DxSzRectAuto;
		$buf = new ArrayList(3);
		for ($i = 0; ($i < 3); ++$i) 
		{
			$buf->add(array());
		}
		$row = null;
		$col = null;
		$sizeIdx = $this->_sizeIdxRequest;
		$sizeIdxP = array();
		$sizeIdxP[0] = $this->_sizeIdxRequest;
		$dataWordCount = $this->encodeDataCodewords(($buf->get(0)), $inputString, $sizeIdxP);
		$sizeIdx = $sizeIdxP[0];
		if (($dataWordCount <= 0))
			return  FALSE ;
		$tmpInputSize = (((count($inputString) /*from: inputString.length*/ + 2)) / 3);
		$splitInputSize[0] = $tmpInputSize;
		$splitInputSize[1] = $tmpInputSize;
		$splitInputSize[2] = (count($inputString) /*from: inputString.length*/ - (($splitInputSize[0] + $splitInputSize[1])));
		$splitSizeIdxFirst = $this->findCorrectSymbolSize($tmpInputSize, $sizeIdxRequest);
		if (($splitSizeIdxFirst == DxCodeSize::$DxSzShapeAuto))
			return  FALSE ;
		if (($sizeIdxRequest == DxCodeSize::$DxSzAuto))
		{
			$splitSizeIdxLast = DxCodeSize::$DxSz144x144;
		}
		else
			if (($sizeIdxRequest == DxCodeSize::$DxSzRectAuto))
			{
				$splitSizeIdxLast = DxCodeSize::$DxSz16x48;
			}
			else
			{
				$splitSizeIdxLast = $splitSizeIdxFirst;
			}
		$tmpRed = array();
		for ($i = 0; ($i < $splitInputSize[0]); ++$i) 
		{
			$tmpRed[$i] = $inputString[$i];
		}
		$tmpGreen = array();
		for ($i = $splitInputSize[0]; ($i < ($splitInputSize[0] + $splitInputSize[1])); ++$i) 
		{
			$tmpGreen[($i - $splitInputSize[0])] = $inputString[$i];
		}
		$tmpBlue = array();
		for ($i = ($splitInputSize[0] + $splitInputSize[1]); ($i < count($inputString) /*from: inputString.length*/); ++$i) 
		{
			$tmpBlue[(($i - $splitInputSize[0]) - $splitInputSize[1])] = $inputString[$i];
		}
		for ($ii = $splitSizeIdxFirst->getIntVal(); ($ii <= $splitSizeIdxLast->getIntVal()); ++$ii) 
		{
			$splitSizeIdxAttempt = $splitSizeIdxAttempt->fromIntVal($ii);
			$sizeIdxP[0] = $splitSizeIdxAttempt;
			$this->encodeDataCodewords(($buf->get(0)), $tmpRed, $sizeIdxP);
			$sizeIdx = $sizeIdxP[0];
			if (($sizeIdx != $splitSizeIdxAttempt))
				continue;
			$sizeIdxP[0] = $splitSizeIdxAttempt;
			$this->encodeDataCodewords(($buf->get(1)), $tmpGreen, $sizeIdxP);
			$sizeIdx = $sizeIdxP[0];
			if (($sizeIdx != $splitSizeIdxAttempt))
				continue;
			$sizeIdxP[0] = $splitSizeIdxAttempt;
			$this->encodeDataCodewords(($buf->get(2)), $tmpBlue, $sizeIdxP);
			$sizeIdx = $sizeIdxP[0];
			if (($sizeIdx != $splitSizeIdxAttempt))
				continue;
			break;
		}
		$this->_sizeIdxRequest = $splitSizeIdxAttempt;
		$encGreen = DxEncode::constructor__DxEncode($this);
		$encBlue = DxEncode::constructor__DxEncode($this);
			/* match: Colorawtjava_Colorawtjava_aB */
		$this->encodeDataMatrix_Colorawtjava_Colorawtjava_aB(NULL, NULL, $tmpRed);
			/* match: Colorawtjava_Colorawtjava_aB */
		$encGreen->encodeDataMatrix_Colorawtjava_Colorawtjava_aB(NULL, NULL, $tmpGreen);
			/* match: Colorawtjava_Colorawtjava_aB */
		$encBlue->encodeDataMatrix_Colorawtjava_Colorawtjava_aB(NULL, NULL, $tmpBlue);
		$mappingRows = DxCommon::getSymbolAttribute(DxSymAttribute::$DxSymAttribMappingMatrixRows, $splitSizeIdxAttempt);
		$mappingCols = DxCommon::getSymbolAttribute(DxSymAttribute::$DxSymAttribMappingMatrixCols, $splitSizeIdxAttempt);
		for ($i = 0; ($i < ($this->_region->getMappingCols() * $this->_region->getMappingRows())); ++$i) 
		{
			$this->_message->getArray()[$i] = 0;
		}
		$this->modulePlacementEcc200($this->_message->getArray(), $this->_message->getCode(), $this->_region->getSizeIdx(), DxConstants::$DxModuleOnRed);
		for ($row = 0; ($row < $mappingRows); ++$row) 
		{
			for ($col = 0; ($col < $mappingCols); ++$col) 
			{
				$this->_message->getArray()[(($row * $mappingCols) + $col)] &= ((0xff ^ ((DxConstants::$DxModuleAssigned | DxConstants::$DxModuleVisited))));
			}
		}
		$this->modulePlacementEcc200($this->_message->getArray(), $encGreen->getMessage()->getCode(), $this->_region->getSizeIdx(), DxConstants::$DxModuleOnGreen);
		for ($row = 0; ($row < $mappingRows); ++$row) 
		{
			for ($col = 0; ($col < $mappingCols); ++$col) 
			{
				$this->_message->getArray()[(($row * $mappingCols) + $col)] &= ((0xff ^ ((DxConstants::$DxModuleAssigned | DxConstants::$DxModuleVisited))));
			}
		}
		$this->modulePlacementEcc200($this->_message->getArray(), $encBlue->getMessage()->getCode(), $this->_region->getSizeIdx(), DxConstants::$DxModuleOnBlue);
		$this->printPattern(NULL, NULL);
		return  TRUE ;
	}
	protected function printPatternRaw () 
	{
		$this->_rawData = array();
		for ($i3 = 0; ($i3 < $this->_region->getSymbolCols()); ++$i3) 
		{
			$this->_rawData[$i3] = array();
		}
		for ($symbolRow = 0; ($symbolRow < $this->_region->getSymbolRows()); ++$symbolRow) 
		{
			for ($symbolCol = 0; ($symbolCol < $this->_region->getSymbolCols()); ++$symbolCol) 
			{
				$moduleStatus = $this->_message->symbolModuleStatus($this->_region->getSizeIdx(), $symbolRow, $symbolCol);
				$this->_rawData[$symbolCol][(($this->_region->getSymbolRows() - $symbolRow) - 1)] = (((($moduleStatus & DxConstants::$DxModuleOnBlue)) != 0x00));
			}
		}
	}
	protected function printPattern ($foreColor, $backColor) // [java.awt.Color foreColor, java.awt.Color backColor]
	{
		$symbolRow = null;
		$rgb = array();
		$txy = $this->_marginSize;
		$m1 = DxMatrix3::translate($txy, $txy);
		$m2 = DxMatrix3::scale($this->_moduleSize, $this->_moduleSize);
		$rxfrm = DxMatrix3::multiply3($m1, $m2);
		$rowSize = $this->_image->getRowSizeBytes();
		$height = $this->_image->getHeight();
		for ($pxlIndex = 0; ($pxlIndex < ($rowSize * $height)); ++$pxlIndex) 
		{
			$this->_image->getPxl()[$pxlIndex] = 0xff;
		}
		for ($symbolRow = 0; ($symbolRow < $this->_region->getSymbolRows()); ++$symbolRow) 
		{
			$symbolCol = null;
			for ($symbolCol = 0; ($symbolCol < $this->_region->getSymbolCols()); ++$symbolCol) 
			{
				$vIn = DxVector2::constructor__D_D($symbolCol, $symbolRow);
				$vOut = DxMatrix3::multiply($vIn, $rxfrm);
				$pixelCol = ($vOut->getX());
				$pixelRow = ($vOut->getY());
				$moduleStatus = $this->_message->symbolModuleStatus($this->_region->getSizeIdx(), $symbolRow, $symbolCol);
				for ($i = $pixelRow; ($i < ($pixelRow + $this->_moduleSize)); ++$i) 
				{
					for ($j = $pixelCol; ($j < ($pixelCol + $this->_moduleSize)); ++$j) 
					{
						if ((($foreColor != NULL) && ($backColor != NULL)))
						{
							$rgb[0] = ( ((((($moduleStatus & DxConstants::$DxModuleOnRed)) != 0x00))) ? $foreColor->getBlue() : $backColor->getBlue() );
							$rgb[1] = ( ((((($moduleStatus & DxConstants::$DxModuleOnGreen)) != 0x00))) ? $foreColor->getGreen() : $backColor->getGreen() );
							$rgb[2] = ( ((((($moduleStatus & DxConstants::$DxModuleOnBlue)) != 0x00))) ? $foreColor->getRed() : $backColor->getRed() );
						}
						else
						{
							$rgb[0] = ( ((((($moduleStatus & DxConstants::$DxModuleOnBlue)) != 0x00))) ? 0 : 255 );
							$rgb[1] = ( ((((($moduleStatus & DxConstants::$DxModuleOnGreen)) != 0x00))) ? 0 : 255 );
							$rgb[2] = ( ((((($moduleStatus & DxConstants::$DxModuleOnRed)) != 0x00))) ? 0 : 255 );
						}
						$this->_image->setPixelValue($j, $i, 0, $rgb[0]);
						$this->_image->setPixelValue($j, $i, 1, $rgb[1]);
						$this->_image->setPixelValue($j, $i, 2, $rgb[2]);
					}
				}
			}
		}
	}
	protected function addPadChars ($buf, $dataWordCountP, $paddedSize) // [byte[] buf, int[] dataWordCountP, int paddedSize]
	{
		$padCount = 0;
		if (($dataWordCountP[0] < $paddedSize))
		{
			++$padCount;
			$buf[++$dataWordCountP[0]] = DxConstants::$DxCharAsciiPad;
		}
		while (($dataWordCountP[0] < $paddedSize)) 
		{
			++$padCount;
			$buf[$dataWordCountP[0]] = $this->randomize253State(DxConstants::$DxCharAsciiPad, ($dataWordCountP[0] + 1));
			++$dataWordCountP[0];
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
	protected function encodeDataCodewords ($buf, $inputString, $sizeIdxP) // [byte[] buf, byte[] inputString, DxCodeSize[] sizeIdxP]
	{
		$dataWordCount = null;
		switch ($this->_scheme) {
			case $DxSchemeAutoBest:
				$dataWordCount = $this->encodeAutoBest($buf, $inputString);
				break;
			case $DxSchemeAutoFast:
				$dataWordCount = 0;
				break;
			default:
				$dataWordCount = $this->encodeSingleScheme($buf, $inputString, $this->_scheme);
				break;
		}
		$sizeIdxP[0] = $this->findCorrectSymbolSize($dataWordCount, $sizeIdxP[0]);
		if (($sizeIdxP[0] == DxCodeSize::$DxSzShapeAuto))
			return 0;
		return $dataWordCount;
	}
	protected function findCorrectSymbolSize ($dataWords, $sizeIdxRequest) // [int dataWords, DxCodeSize sizeIdxRequest]
	{
		$sizeIdx = DxCodeSize::$DxSzRectAuto;
		if (($dataWords <= 0))
			return DxCodeSize::$DxSzShapeAuto;
		if ((($sizeIdxRequest == DxCodeSize::$DxSzAuto) || ($sizeIdxRequest == DxCodeSize::$DxSzRectAuto)))
		{
			$idxBeg = null;
			$idxEnd = null;
			if (($sizeIdxRequest == DxCodeSize::$DxSzAuto))
			{
				$idxBeg = 0;
				$idxEnd = DxConstants::$DxSzSquareCount;
			}
			else
			{
				$idxBeg = DxConstants::$DxSzSquareCount;
				$idxEnd = (DxConstants::$DxSzSquareCount + DxConstants::$DxSzRectCount);
			}
			for ($ii = DxCodeSize->values()[$idxBeg]->getIntVal(); ($ii < DxCodeSize->values()[$idxEnd]->getIntVal()); ++$ii) 
			{
				$sizeIdx = $sizeIdx->fromIntVal($ii);
				if ((DxCommon::getSymbolAttribute(DxSymAttribute::$DxSymAttribSymbolDataWords, $sizeIdx) >= $dataWords))
				{
					break;
				}
			}
			if (($sizeIdx == DxCodeSize->values()[$idxEnd]))
			{
				return DxCodeSize::$DxSzShapeAuto;
			}
		}
		else
		{
			$sizeIdx = $sizeIdxRequest;
		}
		if (($dataWords > DxCommon::getSymbolAttribute(DxSymAttribute::$DxSymAttribSymbolDataWords, $sizeIdx)))
		{
			return DxCodeSize::$DxSzShapeAuto;
		}
		return $sizeIdx;
	}
	protected function encodeSingleScheme ($buf, $codewords, $scheme) // [byte[] buf, byte[] codewords, DxScheme scheme]
	{
		$channel = new DxChannel();
		DxEncode::initChannel($channel, $codewords);
		while (($channel->getInputIndex() < count($channel->getInput()) /*from: channel.getInput().length*/)) 
		{
			$err = $this->encodeNextWord($channel, $scheme);
			if (!$err)
				return 0;
			if (($channel->getInvalid() != DxChannelStatus::$DxChannelValid))
			{
				return 0;
			}
		}
		$size = ($channel->getEncodedLength() / 12);
		for ($i = 0; ($i < $size); ++$i) 
		{
			$buf[$i] = $channel->getEncodedWords()[$i];
		}
		return $size;
	}
	protected function encodeAutoBest ($buf, $codewords) // [byte[] buf, byte[] codewords]
	{
		$targetScheme = DxScheme::$DxSchemeAutoFast;
		$optimal = new DxChannelGroup();
		$best = new DxChannelGroup();
		for ($ii = DxScheme::$DxSchemeAscii->getIntVal(); ($ii <= DxScheme::$DxSchemeBase256->getIntVal()); ++$ii) 
		{
			$targetScheme = $targetScheme->fromIntVal($ii);
			$channel = ($optimal->getChannels()[$targetScheme->getIntVal()]);
			DxEncode::initChannel($channel, $codewords);
			$err = $this->encodeNextWord($channel, $targetScheme);
			if ($err)
				return 0;
		}
		while (($optimal->getChannels()[0]->getInputIndex() < count($optimal->getChannels()[0]->getInput()) /*from: optimal.getChannels()[0].getInput().length*/)) 
		{
			for ($ii = DxScheme::$DxSchemeAscii->getIntVal(); ($ii <= DxScheme::$DxSchemeBase256->getIntVal()); ++$ii) 
			{
				$targetScheme = $targetScheme->fromIntVal($ii);
				$best->getChannels()[$targetScheme->getIntVal()] = $this->findBestChannel($optimal, $targetScheme);
			}
			$optimal = $best;
		}
		$winner = $optimal->getChannels()[DxScheme::$DxSchemeAscii->getIntVal()];
		for ($ii = DxScheme::$DxSchemeAscii->getIntVal(); ($ii <= DxScheme::$DxSchemeBase256->getIntVal()); ++$ii) 
		{
			$targetScheme = $targetScheme->fromIntVal($ii);
			if (($optimal->getChannels()[$targetScheme->getIntVal()]->getInvalid() != DxChannelStatus::$DxChannelValid))
			{
				continue;
			}
			if (($optimal->getChannels()[$targetScheme->getIntVal()]->getEncodedLength() < $winner->getEncodedLength()))
			{
				$winner = $optimal->getChannels()[$targetScheme->getIntVal()];
			}
		}
		$winnerSize = ($winner->getEncodedLength() / 12);
		for ($i = 0; ($i < $winnerSize); ++$i) 
		{
			$buf[$i] = $winner->getEncodedWords()[$i];
		}
		return $winnerSize;
	}
	protected function findBestChannel ($group, $targetScheme) // [DxChannelGroup group, DxScheme targetScheme]
	{
		$winner = NULL;
		$encFrom = DxScheme::$DxSchemeAscii;
		for ($ii = DxScheme::$DxSchemeAscii->getIntVal(); ($ii <= DxScheme::$DxSchemeBase256->getIntVal()); ++$ii) 
		{
			$encFrom = $encFrom->fromIntVal($ii);
			$channel = $group->getChannels()[$encFrom->getIntVal()];
			if (($channel->getInvalid() != DxChannelStatus::$DxChannelValid))
			{
				continue;
			}
			if (($channel->getInputIndex() == count($channel->getInput()) /*from: channel.getInput().length*/))
				continue;
			$err = $this->encodeNextWord($channel, $targetScheme);
			if (($err ==  FALSE ))
			if (((($channel->getInvalid()->getIntVal() & DxChannelStatus::$DxChannelUnsupportedChar->getIntVal())) != 0))
			{
				$winner = $channel;
				break;
			}
			if (((($channel->getInvalid()->getIntVal() & DxChannelStatus::$DxChannelCannotUnlatch->getIntVal())) != 0))
			{
				continue;
			}
			if ((($winner == NULL) || ($channel->getCurrentLength() < $winner->getCurrentLength())))
			{
				$winner = $channel;
			}
		}
		return $winner;
	}
	protected function encodeNextWord ($channel, $targetScheme) // [DxChannel channel, DxScheme targetScheme]
	{
		if (($channel->getEncScheme() != $targetScheme))
		{
			$this->changeEncScheme($channel, $targetScheme, DxUnlatch::$Explicit);
			if (($channel->getInvalid() != DxChannelStatus::$DxChannelValid))
				return  FALSE ;
		}
		if (($channel->getEncScheme() != $targetScheme))
		{
			throw new Exception("For encoding, channel scheme must equal target scheme!");
		}
		switch ($channel->getEncScheme()) {
			case $DxSchemeAscii:
				return $this->encodeAsciiCodeword($channel);
			case $DxSchemeC40:
				return $this->encodeTripletCodeword($channel);
			case $DxSchemeText:
				return $this->encodeTripletCodeword($channel);
			case $DxSchemeX12:
				return $this->encodeTripletCodeword($channel);
			case $DxSchemeEdifact:
				return $this->encodeEdifactCodeword($channel);
			case $DxSchemeBase256:
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
		if (($channel->getEncScheme() != DxScheme::$DxSchemeBase256))
		{
			throw new Exception("Invalid encoding scheme selected!");
		}
		$firstBytePtrIndex = ($channel->getFirstCodeWord() / 12);
		$headerByte[0] = DxMessage::unRandomize255State($channel->getEncodedWords()[$firstBytePtrIndex], (($channel->getFirstCodeWord() / 12) + 1));
		if (($headerByte[0] <= 249))
		{
			$newDataLength = $headerByte[0];
		}
		else
		{
			$newDataLength = (250 * (($headerByte[0] - 249)));
			$newDataLength += DxMessage::unRandomize255State($channel->getEncodedWords()[($firstBytePtrIndex + 1)], (($channel->getFirstCodeWord() / 12) + 2));
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
			for ($i = (($channel->getCurrentLength() / 12) - 1); ($i > ($channel->getFirstCodeWord() / 12)); --$i) 
			{
				$valueTmp = DxMessage::unRandomize255State($channel->getEncodedWords()[$i], ($i + 1));
				$channel->getEncodedWords()[($i + 1)] = $this->randomize255State($valueTmp, ($i + 2));
			}
			$this->incrementProgress($channel, 12);
			$channel->setEncodedLength(($channel->getEncodedLength() + 12));
		}
		for ($i = 0; ($i < $headerByteCount); ++$i) 
		{
			$channel->getEncodedWords()[($firstBytePtrIndex + $i)] = $this->randomize255State($headerByte[$i], ((($channel->getFirstCodeWord() / 12) + $i) + 1));
		}
		$this->pushInputWord($channel, $this->randomize255State($channel->getInput()[$channel->getInputIndex()], (($channel->getCurrentLength() / 12) + 1)));
		$this->incrementProgress($channel, 12);
		$channel->setInputIndex(($channel->getInputIndex() + 1));
		return  TRUE ;
	}
	protected function encodeEdifactCodeword ($channel) // [DxChannel channel]
	{
		if (($channel->getEncScheme() != DxScheme::$DxSchemeEdifact))
		{
			throw new Exception("Invalid encoding scheme selected!");
		}
		$inputValue = $channel->getInput()[$channel->getInputIndex()];
		if ((($inputValue < 32) || ($inputValue > 94)))
		{
			$channel->setInvalid(DxChannelStatus::$DxChannelUnsupportedChar);
			return  FALSE ;
		}
		$this->pushInputWord($channel, (($inputValue & 0x3f)));
		$this->incrementProgress($channel, 9);
		$channel->setInputIndex(($channel->getInputIndex() + 1));
		$this->checkForEndOfSymbolEdifact($channel);
		return  TRUE ;
	}
	protected function checkForEndOfSymbolEdifact ($channel) // [DxChannel channel]
	{
		if (($channel->getInputIndex() > count($channel->getInput()) /*from: channel.getInput().length*/))
		{
			throw new Exception("Input index out of range while encoding!");
		}
		$edifactValues = (count($channel->getInput()) /*from: channel.getInput().length*/ - $channel->getInputIndex());
		if (($edifactValues > 4))
			return ;
		$currentByte = ($channel->getCurrentLength() / 12);
		$sizeIdx = $this->findCorrectSymbolSize($currentByte, DxCodeSize::$DxSzAuto);
		$symbolCodewords = (DxCommon::getSymbolAttribute(DxSymAttribute::$DxSymAttribSymbolDataWords, $sizeIdx) - $currentByte);
		if (((($channel->getCurrentLength() % 12) == 0) && ((($symbolCodewords == 1) || ($symbolCodewords == 2)))))
		{
			$asciiCodewords = $edifactValues;
			if (($asciiCodewords <= $symbolCodewords))
			{
				$this->changeEncScheme($channel, DxScheme::$DxSchemeAscii, DxUnlatch::$Implicit);
				for ($i = 0; ($i < $edifactValues); ++$i) 
				{
					$err = $this->encodeNextWord($channel, DxScheme::$DxSchemeAscii);
					if (($err ==  FALSE ))
					{
						return ;
					}
					if (($channel->getInvalid() != DxChannelStatus::$DxChannelValid))
					{
						throw new Exception("Error checking for end of symbol edifact");
					}
				}
			}
		}
		else
			if (($edifactValues == 0))
			{
				$this->changeEncScheme($channel, DxScheme::$DxSchemeAscii, DxUnlatch::$Explicit);
			}
		return ;
	}
	protected function pushInputWord ($channel, $codeword) // [DxChannel channel, byte codeword]
	{
		if (((($channel->getEncodedLength() / 12) > (3 * 1558))))
		{
			throw new Exception("Can't push input word, encoded length exceeds limits!");
		}
		switch ($channel->getEncScheme()) {
			case $DxSchemeAscii:
				$channel->getEncodedWords()[($channel->getCurrentLength() / 12)] = $codeword;
				$channel->setEncodedLength(($channel->getEncodedLength() + 12));
				break;
			case $DxSchemeC40:
				$channel->getEncodedWords()[($channel->getEncodedLength() / 12)] = $codeword;
				$channel->setEncodedLength(($channel->getEncodedLength() + 12));
				break;
			case $DxSchemeText:
				$channel->getEncodedWords()[($channel->getEncodedLength() / 12)] = $codeword;
				$channel->setEncodedLength(($channel->getEncodedLength() + 12));
				break;
			case $DxSchemeX12:
				$channel->getEncodedWords()[($channel->getEncodedLength() / 12)] = $codeword;
				$channel->setEncodedLength(($channel->getEncodedLength() + 12));
				break;
			case $DxSchemeEdifact:
				$pos = ($channel->getCurrentLength() % 4);
				$startByte = ((((($channel->getCurrentLength() + 9)) / 12)) - $pos);
				$quad = $this->getQuadrupletValues($channel->getEncodedWords()[$startByte], $channel->getEncodedWords()[($startByte + 1)], $channel->getEncodedWords()[($startByte + 2)]);
				$quad->getValue()[$pos] = $codeword;
				for ($i = ($pos + 1); ($i < 4); ++$i) 
					$quad->getValue()[$i] = 0;
				switch ($pos) {
					case 3:
						$channel->getEncodedWords()[($startByte + 2)] = (((((($quad->getValue()[2] & 0x03)) << 6)) | $quad->getValue()[3]));
						break;
					case 2:
						$channel->getEncodedWords()[($startByte + 2)] = (((((($quad->getValue()[2] & 0x03)) << 6)) | $quad->getValue()[3]));
						break;
					case 1:
						$channel->getEncodedWords()[($startByte + 1)] = (((((($quad->getValue()[1] & 0x0f)) << 4)) | (($quad->getValue()[2] >> 2))));
						break;
					case 0:
						$channel->getEncodedWords()[$startByte] = (((($quad->getValue()[0] << 2)) | (($quad->getValue()[1] >> 4))));
						break;
				}
				$channel->setEncodedLength(($channel->getEncodedLength() + 9));
				break;
			case $DxSchemeBase256:
				$channel->getEncodedWords()[($channel->getCurrentLength() / 12)] = $codeword;
				$channel->setEncodedLength(($channel->getEncodedLength() + 12));
				break;
			default:
				break;
		}
	}
	protected function encodeTripletCodeword ($channel) // [DxChannel channel]
	{
		$outputWords = array();
		$buffer = array();
		$triplet = DxTriplet::constructor__();
		if (((($channel->getEncScheme() != DxScheme::$DxSchemeX12) && ($channel->getEncScheme() != DxScheme::$DxSchemeText)) && ($channel->getEncScheme() != DxScheme::$DxSchemeC40)))
		{
			throw new Exception("Invalid encoding scheme selected!");
		}
		if (($channel->getCurrentLength() > $channel->getEncodedLength()))
		{
			throw new Exception("Encoding length out of range!");
		}
		if (($channel->getCurrentLength() == $channel->getEncodedLength()))
		{
			if ((($channel->getCurrentLength() % 12) != 0))
			{
				throw new Exception("Invalid encoding length!");
			}
			$ptrIndex = $channel->getInputIndex();
			$tripletCount = 0;
			for (; ; ) 
			{
				while ((($tripletCount < 3) && ($ptrIndex < count($channel->getInput()) /*from: channel.getInput().length*/))) 
				{
					$inputWord = $channel->getInput()[++$ptrIndex];
					$count = $this->getC40TextX12Words($outputWords, $inputWord, $channel->getEncScheme());
					if (($count == 0))
					{
						$channel->setInvalid(DxChannelStatus::$DxChannelUnsupportedChar);
						return  FALSE ;
					}
					for ($i = 0; ($i < $count); ++$i) 
					{
						$buffer[++$tripletCount] = $outputWords[$i];
					}
				}
				$triplet->getValue()[0] = $buffer[0];
				$triplet->getValue()[1] = $buffer[1];
				$triplet->getValue()[2] = $buffer[2];
				if (($tripletCount >= 3))
				{
					$this->pushTriplet($channel, $triplet);
					$buffer[0] = $buffer[3];
					$buffer[1] = $buffer[4];
					$buffer[2] = $buffer[5];
					$tripletCount -= 3;
				}
				if (($ptrIndex == count($channel->getInput()) /*from: channel.getInput().length*/))
				{
					while (($channel->getCurrentLength() < $channel->getEncodedLength())) 
					{
						$this->incrementProgress($channel, 8);
						$channel->setInputIndex(($channel->getInputIndex() + 1));
					}
					if (($channel->getCurrentLength() == ($channel->getEncodedLength() + 8)))
					{
						$channel->setCurrentLength($channel->getEncodedLength());
						$channel->setInputIndex(($channel->getInputIndex() - 1));
					}
					if ((count($channel->getInput()) /*from: channel.getInput().length*/ < $channel->getInputIndex()))
					{
						throw new Exception("Channel input index exceeds range!");
					}
					$inputCount = (count($channel->getInput()) /*from: channel.getInput().length*/ - $channel->getInputIndex());
					$err = $this->processEndOfSymbolTriplet($channel, $triplet, $tripletCount, $inputCount);
					if (($err ==  FALSE ))
						return  FALSE ;
					break;
				}
				if (($tripletCount == 0))
					break;
			}
		}
		if (($channel->getCurrentLength() < $channel->getEncodedLength()))
		{
			$this->incrementProgress($channel, 8);
			$channel->setInputIndex(($channel->getInputIndex() + 1));
		}
		return  TRUE ;
	}
	protected function getC40TextX12Words ($outputWords, $inputWord, $encScheme) // [int[] outputWords, byte inputWord, DxScheme encScheme]
	{
		if (((($encScheme != DxScheme::$DxSchemeX12) && ($encScheme != DxScheme::$DxSchemeText)) && ($encScheme != DxScheme::$DxSchemeC40)))
		{
			throw new Exception("Invalid encoding scheme selected!");
		}
		$count = 0;
		if (($inputWord > 127))
		{
			if (($encScheme == DxScheme::$DxSchemeX12))
			{
				return 0;
			}
			$outputWords[++$count] = DxConstants::$DxCharTripletShift2;
			$outputWords[++$count] = 30;
			$inputWord -= 128;
		}
		if (($encScheme == DxScheme::$DxSchemeX12))
		{
			if (($inputWord == 13))
				$outputWords[++$count] = 0;
			else
				if (($inputWord == 42))
					$outputWords[++$count] = 1;
				else
					if (($inputWord == 62))
						$outputWords[++$count] = 2;
					else
						if (($inputWord == 32))
							$outputWords[++$count] = 3;
						else
							if ((($inputWord >= 48) && ($inputWord <= 57)))
								$outputWords[++$count] = ($inputWord - 44);
							else
								if ((($inputWord >= 65) && ($inputWord <= 90)))
									$outputWords[++$count] = ($inputWord - 51);
		}
		else
		{
			if (($inputWord <= 31))
			{
				$outputWords[++$count] = DxConstants::$DxCharTripletShift1;
				$outputWords[++$count] = $inputWord;
			}
			else
				if (($inputWord == 32))
				{
					$outputWords[++$count] = 3;
				}
				else
					if (($inputWord <= 47))
					{
						$outputWords[++$count] = DxConstants::$DxCharTripletShift2;
						$outputWords[++$count] = ($inputWord - 33);
					}
					else
						if (($inputWord <= 57))
						{
							$outputWords[++$count] = ($inputWord - 44);
						}
						else
							if (($inputWord <= 64))
							{
								$outputWords[++$count] = DxConstants::$DxCharTripletShift2;
								$outputWords[++$count] = ($inputWord - 43);
							}
							else
								if ((($inputWord <= 90) && ($encScheme == DxScheme::$DxSchemeC40)))
								{
									$outputWords[++$count] = ($inputWord - 51);
								}
								else
									if ((($inputWord <= 90) && ($encScheme == DxScheme::$DxSchemeText)))
									{
										$outputWords[++$count] = DxConstants::$DxCharTripletShift3;
										$outputWords[++$count] = ($inputWord - 64);
									}
									else
										if (($inputWord <= 95))
										{
											$outputWords[++$count] = DxConstants::$DxCharTripletShift2;
											$outputWords[++$count] = ($inputWord - 69);
										}
										else
											if ((($inputWord == 96) && ($encScheme == DxScheme::$DxSchemeText)))
											{
												$outputWords[++$count] = DxConstants::$DxCharTripletShift3;
												$outputWords[++$count] = 0;
											}
											else
												if ((($inputWord <= 122) && ($encScheme == DxScheme::$DxSchemeText)))
												{
													$outputWords[++$count] = ($inputWord - 83);
												}
												else
													if (($inputWord <= 127))
													{
														$outputWords[++$count] = DxConstants::$DxCharTripletShift3;
														$outputWords[++$count] = ($inputWord - 96);
													}
		}
		return $count;
	}
	protected function processEndOfSymbolTriplet ($channel, $triplet, $tripletCount, $inputCount) // [DxChannel channel, DxTriplet triplet, int tripletCount, int inputCount]
	{
		$err = null;
		if ((($channel->getCurrentLength() % 12) != 0))
		{
			throw new Exception("Invalid current length for encoding!");
		}
		$inputAdjust = ($tripletCount - $inputCount);
		$currentByte = ($channel->getCurrentLength() / 12);
		$sizeIdx = $this->findCorrectSymbolSize(($currentByte + (( ((($inputCount == 3))) ? 2 : $inputCount ))), $this->_sizeIdxRequest);
		if (($sizeIdx == DxCodeSize::$DxSzShapeAuto))
			return  FALSE ;
		$remainingCodewords = (DxCommon::getSymbolAttribute(DxSymAttribute::$DxSymAttribSymbolDataWords, $sizeIdx) - $currentByte);
		if ((($inputCount == 1) && ($remainingCodewords == 1)))
		{
			$this->changeEncScheme($channel, DxScheme::$DxSchemeAscii, DxUnlatch::$Implicit);
			$err = $this->encodeNextWord($channel, DxScheme::$DxSchemeAscii);
			if (($err ==  FALSE ))
				return  FALSE ;
			if ((($channel->getInvalid() != DxChannelStatus::$DxChannelValid) || ($channel->getInputIndex() != count($channel->getInput()) /*from: channel.getInput().length*/)))
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
					$channel->setEncScheme(DxScheme::$DxSchemeAscii);
					$channel->setInputIndex(($channel->getInputIndex() + 3));
					$channel->setInputIndex(($channel->getInputIndex() - $inputAdjust));
				}
				else
					if (($tripletCount == 2))
					{
						$triplet->getValue()[2] = 0;
						$this->pushTriplet($channel, $triplet);
						$this->incrementProgress($channel, 24);
						$channel->setEncScheme(DxScheme::$DxSchemeAscii);
						$channel->setInputIndex(($channel->getInputIndex() + 2));
						$channel->setInputIndex(($channel->getInputIndex() - $inputAdjust));
					}
					else
						if (($tripletCount == 1))
						{
							$this->changeEncScheme($channel, DxScheme::$DxSchemeAscii, DxUnlatch::$Explicit);
							$err = $this->encodeNextWord($channel, DxScheme::$DxSchemeAscii);
							if (($err ==  FALSE ))
								return  FALSE ;
							if (($channel->getInvalid() != DxChannelStatus::$DxChannelValid))
							{
								throw new Exception("Error processing end of symbol triplet!");
							}
						}
			}
			else
			{
				$currentByte = ($channel->getCurrentLength() / 12);
				$remainingCodewords = (DxCommon::getSymbolAttribute(DxSymAttribute::$DxSymAttribSymbolDataWords, $sizeIdx) - $currentByte);
				if (($remainingCodewords > 0))
				{
					$this->changeEncScheme($channel, DxScheme::$DxSchemeAscii, DxUnlatch::$Explicit);
					while (($channel->getInputIndex() < count($channel->getInput()) /*from: channel.getInput().length*/)) 
					{
						$err = $this->encodeNextWord($channel, DxScheme::$DxSchemeAscii);
						if (($err ==  FALSE ))
							return  FALSE ;
						if (($channel->getInvalid() != DxChannelStatus::$DxChannelValid))
						{
							throw new Exception("Error processing end of symbol triplet!");
						}
					}
				}
			}
		if (($channel->getInputIndex() != count($channel->getInput()) /*from: channel.getInput().length*/))
		{
			throw new Exception("Could not fully process end of symbol triplet!");
		}
		return  TRUE ;
	}
	protected function pushTriplet ($channel, $triplet) // [DxChannel channel, DxTriplet triplet]
	{
		$tripletValue = (((((1600 * $triplet->getValue()[0])) + ((40 * $triplet->getValue()[1]))) + $triplet->getValue()[2]) + 1);
		$this->pushInputWord($channel, (($tripletValue / 256)));
		$this->pushInputWord($channel, (($tripletValue % 256)));
	}
	protected function encodeAsciiCodeword ($channel) // [DxChannel channel]
	{
		if (($channel->getEncScheme() != DxScheme::$DxSchemeAscii))
		{
			throw new Exception("Invalid encoding scheme selected!");
		}
		$inputValue = $channel->getInput()[$channel->getInputIndex()];
		if (($this->isDigit($inputValue) && ($channel->getCurrentLength() >= ($channel->getFirstCodeWord() + 12))))
		{
			$prevIndex = ((($channel->getCurrentLength() - 12)) / 12);
			$prevValue = (($channel->getEncodedWords()[$prevIndex] - 1));
			$prevPrevValue = (( ((($prevIndex > ($channel->getFirstCodeWord() / 12)))) ? $channel->getEncodedWords()[($prevIndex - 1)] : 0 ));
			if ((($prevPrevValue != 235) && $this->isDigit($prevValue)))
			{
				$channel->getEncodedWords()[$prevIndex] = ((((10 * (($prevValue . '0'))) + (($inputValue . '0'))) + 130));
				$channel->setInputIndex(($channel->getInputIndex() + 1));
				return  TRUE ;
			}
		}
		if (($this->flgGS1 ==  TRUE ))
		{
			if (((($inputValue & 0xff)) == ((DxConstants::$DxCharFNC1 & 0xff))))
			{
				$this->pushInputWord($channel, DxConstants::$DxCharFNC1);
				$this->incrementProgress($channel, 12);
				$channel->setInputIndex(($channel->getInputIndex() + 1));
				return  TRUE ;
			}
		}
		if (((($inputValue & 0xff)) >= 128))
		{
			$this->pushInputWord($channel, DxConstants::$DxCharAsciiUpperShift);
			$this->incrementProgress($channel, 12);
			$inputValue -= 128;
		}
		$this->pushInputWord($channel, (($inputValue + 1)));
		$this->incrementProgress($channel, 12);
		$channel->setInputIndex(($channel->getInputIndex() + 1));
		return  TRUE ;
	}
	protected function isDigit ($inputValue) // [byte inputValue]
	{
		return (((($inputValue & 0xff)) >= 48) && ((($inputValue & 0xff)) <= 57));
	}
	protected function changeEncScheme ($channel, $targetScheme, $unlatchType) // [DxChannel channel, DxScheme targetScheme, DxUnlatch unlatchType]
	{
		if (($channel->getEncScheme() == $targetScheme))
		{
			throw new Exception("Target scheme already equals channel scheme, cannot be changed!");
		}
		switch ($channel->getEncScheme()) {
			case $DxSchemeAscii:
				if ((($channel->getCurrentLength() % 12) != 0))
				{
					throw new Exception("Invalid current length detected encoding ascii code");
				}
				break;
			case $DxSchemeC40:
			case $DxSchemeText:
			case $DxSchemeX12:
				if (((($channel->getCurrentLength() % 12)) != 0))
				{
					$channel->setInvalid(DxChannelStatus::$DxChannelCannotUnlatch);
					return ;
				}
				if (($channel->getCurrentLength() != $channel->getEncodedLength()))
				{
					$channel->setInvalid(DxChannelStatus::$DxChannelCannotUnlatch);
					return ;
				}
				if (($unlatchType == DxUnlatch::$Explicit))
				{
					$this->pushInputWord($channel, DxConstants::$DxCharTripletUnlatch);
					$this->incrementProgress($channel, 12);
				}
				break;
			case $DxSchemeEdifact:
				if ((($channel->getCurrentLength() % 3) != 0))
				{
					throw new Exception("Error changing encryption scheme, current length is invalid!");
				}
				if (($unlatchType == DxUnlatch::$Explicit))
				{
					$this->pushInputWord($channel, DxConstants::$DxCharEdifactUnlatch);
					$this->incrementProgress($channel, 9);
				}
				$advance = ((($channel->getCurrentLength() % 4)) * 3);
				$channel->setCurrentLength(($channel->getCurrentLength() + $advance));
				$channel->setEncodedLength(($channel->getEncodedLength() + $advance));
				break;
			case $DxSchemeBase256:
				break;
			default:
				break;
		}
		$channel->setEncScheme(DxScheme::$DxSchemeAscii);
		switch ($targetScheme) {
			case $DxSchemeAscii:
				break;
			case $DxSchemeC40:
				$this->pushInputWord($channel, DxConstants::$DxCharC40Latch);
				$this->incrementProgress($channel, 12);
				break;
			case $DxSchemeText:
				$this->pushInputWord($channel, DxConstants::$DxCharTextLatch);
				$this->incrementProgress($channel, 12);
				break;
			case $DxSchemeX12:
				$this->pushInputWord($channel, DxConstants::$DxCharX12Latch);
				$this->incrementProgress($channel, 12);
				break;
			case $DxSchemeEdifact:
				$this->pushInputWord($channel, DxConstants::$DxCharEdifactLatch);
				$this->incrementProgress($channel, 12);
				break;
			case $DxSchemeBase256:
				$this->pushInputWord($channel, DxConstants::$DxCharBase256Latch);
				$this->incrementProgress($channel, 12);
				$this->pushInputWord($channel, $this->randomize255State(0, 2));
				$this->incrementProgress($channel, 12);
				break;
			default:
				break;
		}
		$channel->setEncScheme($targetScheme);
		$channel->setFirstCodeWord(($channel->getCurrentLength() - 12));
		if ((($channel->getFirstCodeWord() % 12) != 0))
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
		if ((($channel->getEncScheme() == DxScheme::$DxSchemeC40) || ($channel->getEncScheme() == DxScheme::$DxSchemeText)))
		{
			$pos = ((($channel->getCurrentLength() % 6)) / 2);
			$startByte = ((($channel->getCurrentLength() / 12)) - (($pos >> 1)));
			$triplet = DxEncode::getTripletValues($channel->getEncodedWords()[$startByte], $channel->getEncodedWords()[($startByte + 1)]);
			if (($triplet->getValue()[$pos] <= 2))
				$channel->setCurrentLength(($channel->getCurrentLength() + 8));
		}
		$channel->setCurrentLength(($channel->getCurrentLength() + $encodedUnits));
	}
	protected static function getTripletValues ($cw1, $cw2) // [byte cw1, byte cw2]
	{
		$triplet = DxTriplet::constructor__();
		$compact = ((($cw1 << 8)) | $cw2);
		$triplet->getValue()[0] = (((($compact - 1)) / 1600));
		$triplet->getValue()[1] = (((((($compact - 1)) / 40)) % 40));
		$triplet->getValue()[2] = (((($compact - 1)) % 40));
		return $triplet;
	}
	protected function getQuadrupletValues ($cw1, $cw2, $cw3) // [byte cw1, byte cw2, byte cw3]
	{
		$quad = DxQuadruplet::constructor__();
		$quad->getValue()[0] = (($cw1 >> 2));
		$quad->getValue()[1] = (((((($cw1 & 0x03)) << 4)) | (((($cw2 & 0xf0)) >> 4))));
		$quad->getValue()[2] = (((((($cw2 & 0x0f)) << 2)) | (((($cw3 & 0xc0)) >> 6))));
		$quad->getValue()[3] = (($cw3 & 0x3f));
		return $quad;
	}
	protected static function initChannel ($channel, $codewords) // [DxChannel channel, byte[] codewords]
	{
		$channel->setEncScheme(DxScheme::$DxSchemeAscii);
		$channel->setInvalid(DxChannelStatus::$DxChannelValid);
		$channel->setInputIndex(0);
		$channel->setInput($codewords);
	}
	public function getMethod () 
	{
		return $this->_method;
	}
	public function setMethod ($value) // [int value]
	{
		$this->_method = $value;
	}
	public function getScheme () 
	{
		return $this->_scheme;
	}
	public function setScheme ($value) // [DxScheme value]
	{
		$this->_scheme = $value;
	}
	public function getSizeIdxRequest () 
	{
		return $this->_sizeIdxRequest;
	}
	public function setSizeIdxRequest ($value) // [DxCodeSize value]
	{
		$this->_sizeIdxRequest = $value;
	}
	public function getMarginSize () 
	{
		return $this->_marginSize;
	}
	public function setMarginSize ($value) // [int value]
	{
		$this->_marginSize = $value;
	}
	public function getModuleSize () 
	{
		return $this->_moduleSize;
	}
	public function setModuleSize ($value) // [int value]
	{
		$this->_moduleSize = $value;
	}
	public function getPixelPacking () 
	{
		return $this->_pixelPacking;
	}
	public function setPixelPacking ($value) // [DxPackOrder value]
	{
		$this->_pixelPacking = $value;
	}
	public function getImageFlip () 
	{
		return $this->_imageFlip;
	}
	public function setImageFlip ($value) // [DxFlip value]
	{
		$this->_imageFlip = $value;
	}
	public function getRowPadBytes () 
	{
		return $this->_rowPadBytes;
	}
	public function setRowPadBytes ($value) // [int value]
	{
		$this->_rowPadBytes = $value;
	}
	public function getMessage () 
	{
		return $this->_message;
	}
	public function setMessage ($value) // [DxMessage value]
	{
		$this->_message = $value;
	}
	public function getImage () 
	{
		return $this->_image;
	}
	public function setImage ($value) // [DxImage value]
	{
		$this->_image = $value;
	}
	public function getRegion () 
	{
		return $this->_region;
	}
	public function setRegion ($value) // [DxRegion value]
	{
		$this->_region = $value;
	}
	public function getRawData () 
	{
		return $this->_rawData;
	}
	public function setRawData ($value) // [boolean[][] value]
	{
		$this->_rawData = $value;
	}
}
?>
