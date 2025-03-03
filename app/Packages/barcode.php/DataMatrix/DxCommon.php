<?php
class DxCommon {
	public static function genReedSolEcc ($message, $sizeIdx) // [DxMessage message, DxCodeSize sizeIdx]
	{
		$g = array();
		$b = array();
		$symbolDataWords = DxCommon::getSymbolAttribute(DxSymAttribute::$DxSymAttribSymbolDataWords, $sizeIdx);
		$symbolErrorWords = DxCommon::getSymbolAttribute(DxSymAttribute::$DxSymAttribSymbolErrorWords, $sizeIdx);
		$symbolTotalWords = ($symbolDataWords + $symbolErrorWords);
		$blockErrorWords = DxCommon::getSymbolAttribute(DxSymAttribute::$DxSymAttribBlockErrorWords, $sizeIdx);
		$step = DxCommon::getSymbolAttribute(DxSymAttribute::$DxSymAttribInterleavedBlocks, $sizeIdx);
		if (($blockErrorWords != ($symbolErrorWords / $step)))
		{
			throw new Exception("Error generation reed solomon error correction");
		}
		for ($gI = 0; ($gI < count($g) /*from: g.length*/); ++$gI) 
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
			for ($bI = 0; ($bI < count($b) /*from: b.length*/); ++$bI) 
			{
				$b[$bI] = 0;
			}
			for ($i = $block; ($i < $symbolDataWords); $i += $step) 
			{
				$val = DxCommon::gfSum($b[($blockErrorWords - 1)], $message->getCode()[$i]);
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
				$message->getCode()[$i] = $b[--$bIndex];
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
		if ((($sizeIdx->getIntVal() < DxCodeSize::$DxSz10x10->getIntVal()) || ($sizeIdx->getIntVal() >= (DxConstants::$DxSzSquareCount + DxConstants::$DxSzRectCount))))
			return DxConstants::$DxUndefined;
		switch ($attribute) {
			case $DxSymAttribSymbolRows:
				return DxConstants::$SymbolRows[$sizeIdx->getIntVal()];
			case $DxSymAttribSymbolCols:
				return DxConstants::$SymbolCols[$sizeIdx->getIntVal()];
			case $DxSymAttribDataRegionRows:
				return DxConstants::$DataRegionRows[$sizeIdx->getIntVal()];
			case $DxSymAttribDataRegionCols:
				return DxConstants::$DataRegionCols[$sizeIdx->getIntVal()];
			case $DxSymAttribHorizDataRegions:
				return DxConstants::$HorizDataRegions[$sizeIdx->getIntVal()];
			case $DxSymAttribVertDataRegions:
				return ( ((($sizeIdx->getIntVal() < DxConstants::$DxSzSquareCount))) ? DxConstants::$HorizDataRegions[$sizeIdx->getIntVal()] : 1 );
			case $DxSymAttribMappingMatrixRows:
				return (DxConstants::$DataRegionRows[$sizeIdx->getIntVal()] * DxCommon::getSymbolAttribute(DxSymAttribute::$DxSymAttribVertDataRegions, $sizeIdx));
			case $DxSymAttribMappingMatrixCols:
				return (DxConstants::$DataRegionCols[$sizeIdx->getIntVal()] * DxConstants::$HorizDataRegions[$sizeIdx->getIntVal()]);
			case $DxSymAttribInterleavedBlocks:
				return DxConstants::$InterleavedBlocks[$sizeIdx->getIntVal()];
			case $DxSymAttribBlockErrorWords:
				return DxConstants::$BlockErrorWords[$sizeIdx->getIntVal()];
			case $DxSymAttribBlockMaxCorrectable:
				return DxConstants::$BlockMaxCorrectable[$sizeIdx->getIntVal()];
			case $DxSymAttribSymbolDataWords:
				return DxConstants::$SymbolDataWords[$sizeIdx->getIntVal()];
			case $DxSymAttribSymbolErrorWords:
				return (DxConstants::$BlockErrorWords[$sizeIdx->getIntVal()] * DxConstants::$InterleavedBlocks[$sizeIdx->getIntVal()]);
			case $DxSymAttribSymbolMaxCorrectable:
				return (DxConstants::$BlockMaxCorrectable[$sizeIdx->getIntVal()] * DxConstants::$InterleavedBlocks[$sizeIdx->getIntVal()]);
		}
		return DxConstants::$DxUndefined;
	}
	public static function getBlockDataSize ($sizeIdx, $blockIdx) // [DxCodeSize sizeIdx, int blockIdx]
	{
		$symbolDataWords = DxCommon::getSymbolAttribute(DxSymAttribute::$DxSymAttribSymbolDataWords, $sizeIdx);
		$interleavedBlocks = DxCommon::getSymbolAttribute(DxSymAttribute::$DxSymAttribInterleavedBlocks, $sizeIdx);
		$count = ($symbolDataWords / $interleavedBlocks);
		if ((($symbolDataWords < 1) || ($interleavedBlocks < 1)))
			return DxConstants::$DxUndefined;
		return ( (((($sizeIdx == DxCodeSize::$DxSz144x144) && ($blockIdx < 8)))) ? ($count + 1) : $count );
	}
	public static function findCorrectSymbolSize ($dataWords, $sizeIdxRequest) // [int dataWords, DxCodeSize sizeIdxRequest]
	{
		$sizeIdx = DxCodeSize::$DxSzRectAuto;
		if (($dataWords <= 0))
		{
			return DxCodeSize::$DxSzShapeAuto;
		}
		if ((($sizeIdxRequest == DxCodeSize::$DxSzAuto) || ($sizeIdxRequest == DxCodeSize::$DxSzRectAuto)))
		{
			$idxBeg = DxCodeSize::$DxSzRectAuto;
			$idxEnd = DxCodeSize::$DxSzRectAuto;
			if (($sizeIdxRequest == DxCodeSize::$DxSzAuto))
			{
				$idxBeg = DxCodeSize::$DxSz10x10;
				$idxEnd = DxCodeSize->values()[DxConstants::$DxSzSquareCount];
			}
			else
			{
				$idxBeg = DxCodeSize->values()[DxConstants::$DxSzSquareCount];
				$idxEnd = DxCodeSize->values()[((DxConstants::$DxSzSquareCount + DxConstants::$DxSzRectCount))];
			}
			for ($ii = $idxBeg->getIntVal(); ($ii < $idxEnd->getIntVal()); ++$ii) 
			{
				$wkSzCodeSize = $sizeIdx;
				$sizeIdx = $wkSzCodeSize->fromIntVal($ii);
				if ((DxCommon::getSymbolAttribute(DxSymAttribute::$DxSymAttribSymbolDataWords, $sizeIdx) >= $dataWords))
					break;
			}
			if (($sizeIdx == $idxEnd))
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
	public static function getBitsPerPixel ($pack) // [DxPackOrder pack]
	{
		switch ($pack) {
			case $DxPack1bppK:
				return 1;
			case $DxPack8bppK:
				return 8;
			case $DxPack16bppRGB:
			case $DxPack16bppRGBX:
			case $DxPack16bppXRGB:
			case $DxPack16bppBGR:
			case $DxPack16bppBGRX:
			case $DxPack16bppXBGR:
			case $DxPack16bppYCbCr:
				return 16;
			case $DxPack24bppRGB:
			case $DxPack24bppBGR:
			case $DxPack24bppYCbCr:
				return 24;
			case $DxPack32bppRGBX:
			case $DxPack32bppXRGB:
			case $DxPack32bppBGRX:
			case $DxPack32bppXBGR:
			case $DxPack32bppCMYK:
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
	public static function decodeCheckErrors ($code, $codeIndex, $sizeIdx, $fix) // [byte[] code, int codeIndex, DxCodeSize sizeIdx, int fix]
	{
		$data = array();
		$interleavedBlocks = DxCommon::getSymbolAttribute(DxSymAttribute::$DxSymAttribInterleavedBlocks, $sizeIdx);
		$blockErrorWords = DxCommon::getSymbolAttribute(DxSymAttribute::$DxSymAttribBlockErrorWords, $sizeIdx);
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
?>
