<?php
require_once("pao/barcode/dmatrix/C40TextState.php");
require_once("pao/barcode/dmatrix/DxCodeSize.php");
require_once("pao/barcode/dmatrix/DxCommon.php");
require_once("pao/barcode/dmatrix/DxConstants.php");
require_once("pao/barcode/dmatrix/DxFormat.php");
require_once("pao/barcode/dmatrix/DxScheme.php");
require_once("pao/barcode/dmatrix/DxSymAttribute.php");
class DxMessage {
	protected $_outputIdx;	// int
	protected $__PadCount;	// int
	protected $__Array;	// byte[]
	protected $__Code;	// byte[]
	protected $__Output;	// byte[]
	public static function constructor__DxCodeSize_DxFormat ($sizeIdx, $symbolFormat) // [DxCodeSize sizeIdx, DxFormat symbolFormat]
	{
		$me = new self();
		if ((($symbolFormat != DxFormat::$Matrix) && ($symbolFormat != DxFormat::$Mosaic)))
		{
			throw new Exception("Only DxFormats Matrix and Mosaic are currently supported");
		}
		$mappingRows = DxCommon::getSymbolAttribute(DxSymAttribute::$DxSymAttribMappingMatrixRows, $sizeIdx);
		$mappingCols = DxCommon::getSymbolAttribute(DxSymAttribute::$DxSymAttribMappingMatrixCols, $sizeIdx);
		$me->setArray(array());
		$codeSize = (DxCommon::getSymbolAttribute(DxSymAttribute::$DxSymAttribSymbolDataWords, $sizeIdx) + DxCommon::getSymbolAttribute(DxSymAttribute::$DxSymAttribSymbolErrorWords, $sizeIdx));
		$me->setCode(array());
		$me->setOutput(array());
		return $me;
	}
	public function decodeDataStream ($sizeIdx, $outputStart) // [DxCodeSize sizeIdx, byte[] outputStart]
	{
		$macro =  FALSE ;
		$this->setOutput(( (($outputStart != NULL)) ? $outputStart : $this->getOutput() ));
		$this->_outputIdx = 0;
		$ptr = $this->getCode();
		$dataEndIndex = DxCommon::getSymbolAttribute(DxSymAttribute::$DxSymAttribSymbolDataWords, $sizeIdx);
		if ((($ptr[0] == DxConstants::$DxChar05Macro) || ($ptr[0] == DxConstants::$DxChar06Macro)))
		{
			$this->pushOutputMacroHeader($ptr[0]);
			$macro =  TRUE ;
		}
		for ($codeIter = 0; ($codeIter < $dataEndIndex); ) 
		{
			$encScheme = DxMessage::getEncodationScheme($this->getCode()[$codeIter]);
			if (($encScheme != DxScheme::$DxSchemeAscii))
				++$codeIter;
			switch ($encScheme) {
				case $DxSchemeAscii:
					$codeIter = $this->decodeSchemeAscii($codeIter, $dataEndIndex);
					break;
				case $DxSchemeC40:
				case $DxSchemeText:
					$codeIter = $this->decodeSchemeC40Text($codeIter, $dataEndIndex, $encScheme);
					break;
				case $DxSchemeX12:
					$codeIter = $this->decodeSchemeX12($codeIter, $dataEndIndex);
					break;
				case $DxSchemeEdifact:
					$codeIter = $this->decodeSchemeEdifact($codeIter, $dataEndIndex);
					break;
				case $DxSchemeBase256:
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
		$this->pushOutputWord('[');
		$this->pushOutputWord(')');
		$this->pushOutputWord('>');
		$this->pushOutputWord(30);
		$this->pushOutputWord('0');
		if (($macroType == DxConstants::$DxChar05Macro))
		{
			$this->pushOutputWord('5');
		}
		else
			if (($macroType == DxConstants::$DxChar06Macro))
			{
				$this->pushOutputWord('6');
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
		$this->getOutput()[++$this->_outputIdx] = $value;
	}
	protected static function getEncodationScheme ($val) // [byte val]
	{
		if (($val == DxConstants::$DxCharC40Latch))
		{
			return DxScheme::$DxSchemeC40;
		}
		if (($val == DxConstants::$DxCharBase256Latch))
		{
			return DxScheme::$DxSchemeBase256;
		}
		if (($val == DxConstants::$DxCharEdifactLatch))
		{
			return DxScheme::$DxSchemeEdifact;
		}
		if (($val == DxConstants::$DxCharTextLatch))
		{
			return DxScheme::$DxSchemeText;
		}
		if (($val == DxConstants::$DxCharX12Latch))
		{
			return DxScheme::$DxSchemeX12;
		}
		return DxScheme::$DxSchemeAscii;
	}
	protected function decodeSchemeAscii ($startIndex, $endIndex) // [int startIndex, int endIndex]
	{
		$upperShift =  FALSE ;
		while (($startIndex < $endIndex)) 
		{
			$codeword = $this->getCode()[$startIndex];
			if ((DxMessage::getEncodationScheme($this->getCode()[$startIndex]) != DxScheme::$DxSchemeAscii))
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
						$this->setPadCount(($endIndex - $startIndex));
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
								$this->pushOutputWord(((($digits / 10) . '0')));
								$this->pushOutputWord(((($digits - ((($digits / 10)) * 10)) . '0')));
							}
		}
		return $startIndex;
	}
	protected function decodeSchemeC40Text ($startIndex, $endIndex, $encScheme) // [int startIndex, int endIndex, DxScheme encScheme]
	{
		$c40Values = array();
		$state = C40TextState::constructor__();
		$stateP = $state;
		$state->setShift(DxConstants::$DxC40TextBasicSet);
		$state->setUpperShift( FALSE );
		if (!((($encScheme == DxScheme::$DxSchemeC40) || ($encScheme == DxScheme::$DxSchemeText))))
		{
			throw new Exception("Invalid scheme selected for decodind!");
		}
		while (($startIndex < $endIndex)) 
		{
			$packed = ((($this->getCode()[$startIndex] << 8)) | $this->getCode()[($startIndex + 1)]);
			$c40Values[0] = (((($packed - 1)) / 1600));
			$c40Values[1] = ((((($packed - 1)) / 40)) % 40);
			$c40Values[2] = ((($packed - 1)) % 40);
			$startIndex += 2;
			$i = null;
			for ($i = 0; ($i < 3); ++$i) 
			{
				if (($state->getShift() == DxConstants::$DxC40TextBasicSet))
				{
					if (($c40Values[$i] <= 2))
					{
						$state->setShift(($c40Values[$i] + 1));
					}
					else
						if (($c40Values[$i] == 3))
						{
							$this->pushOutputC40TextWord($stateP, ' ');
							$state = $stateP[0];
						}
						else
							if (($c40Values[$i] <= 13))
							{
								$this->pushOutputC40TextWord($stateP, (($c40Values[$i] - 13) . '9'));
								$state = $stateP[0];
							}
							else
								if (($c40Values[$i] <= 39))
								{
									if (($encScheme == DxScheme::$DxSchemeC40))
									{
										$this->pushOutputC40TextWord($stateP, (($c40Values[$i] - 39) . 'Z'));
										$state = $stateP[0];
									}
									else
										if (($encScheme == DxScheme::$DxSchemeText))
										{
											$this->pushOutputC40TextWord($stateP, (($c40Values[$i] - 39) . 'z'));
											$state = $stateP[0];
										}
								}
				}
				else
					if (($state->getShift() == DxConstants::$DxC40TextShift1))
					{
						$this->pushOutputC40TextWord($stateP, $c40Values[$i]);
						$state = $stateP[0];
					}
					else
						if (($state->getShift() == DxConstants::$DxC40TextShift2))
						{
							if (($c40Values[$i] <= 14))
							{
								$this->pushOutputC40TextWord($stateP, ($c40Values[$i] + 33));
								$state = $stateP[0];
							}
							else
								if (($c40Values[$i] <= 21))
								{
									$this->pushOutputC40TextWord($stateP, ($c40Values[$i] + 43));
									$state = $stateP[0];
								}
								else
									if (($c40Values[$i] <= 26))
									{
										$this->pushOutputC40TextWord($stateP, ($c40Values[$i] + 69));
										$state = $stateP[0];
									}
									else
										if (($c40Values[$i] == 27))
										{
											$this->pushOutputC40TextWord($stateP, 0x1d);
											$state = $stateP[0];
										}
										else
											if (($c40Values[$i] == 30))
											{
												$state->setUpperShift( TRUE );
												$state->setShift(DxConstants::$DxC40TextBasicSet);
											}
						}
						else
							if (($state->getShift() == DxConstants::$DxC40TextShift3))
							{
								if (($encScheme == DxScheme::$DxSchemeC40))
								{
									$this->pushOutputC40TextWord($stateP, ($c40Values[$i] + 96));
									$state = $stateP[0];
								}
								else
									if (($encScheme == DxScheme::$DxSchemeText))
									{
										if (($c40Values[$i] == 0))
										{
											$this->pushOutputC40TextWord($stateP, ($c40Values[$i] + 96));
											$state = $stateP[0];
										}
										else
											if (($c40Values[$i] <= 26))
											{
												$this->pushOutputC40TextWord($stateP, (($c40Values[$i] - 26) . 'Z'));
												$state = $stateP[0];
											}
											else
											{
												$this->pushOutputC40TextWord($stateP, (($c40Values[$i] - 31) + 127));
												$state = $stateP[0];
											}
									}
							}
			}
			if (($this->getCode()[$startIndex] == DxConstants::$DxCharTripletUnlatch))
				return ($startIndex + 1);
			if ((($endIndex - $startIndex) == 1))
				return $startIndex;
		}
		return $startIndex;
	}
	protected function pushOutputC40TextWord ($stateP, $value) // [C40TextState[] stateP, int value]
	{
		if (!((($value >= 0) && ($value < 256))))
		{
			throw new Exception("Invalid value: Exceeds range for conversion to byte");
		}
		$this->getOutput()[$this->_outputIdx] = $value;
		if ($stateP[0]->getUpperShift())
		{
			if (!((($value >= 0) && ($value < 256))))
			{
				throw new Exception("Invalid value: Exceeds range for conversion to upper case character");
			}
			$this->getOutput()[$this->_outputIdx] += 128;
		}
		++$this->_outputIdx;
		$stateP[0]->setShift(DxConstants::$DxC40TextBasicSet);
		$stateP[0]->setUpperShift( FALSE );
	}
	protected function decodeSchemeX12 ($startIndex, $endIndex) // [int startIndex, int endIndex]
	{
		$x12Values = array();
		while (($startIndex < $endIndex)) 
		{
			$packed = ((($this->getCode()[$startIndex] << 8)) | $this->getCode()[($startIndex + 1)]);
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
			if (($this->getCode()[$startIndex] == DxConstants::$DxCharTripletUnlatch))
				return ($startIndex + 1);
			if ((($endIndex - $startIndex) == 1))
				return $startIndex;
		}
		return $startIndex;
	}
	protected function decodeSchemeEdifact ($startIndex, $endIndex) // [int startIndex, int endIndex]
	{
		$unpacked = array();
		while (($startIndex < $endIndex)) 
		{
			$unpacked[0] = (((($this->getCode()[$startIndex] & 0xfc)) >> 2));
			$unpacked[1] = ((((($this->getCode()[$startIndex] & 0x03)) << 4) | ((($this->getCode()[($startIndex + 1)] & 0xf0)) >> 4)));
			$unpacked[2] = ((((($this->getCode()[($startIndex + 1)] & 0x0f)) << 2) | ((($this->getCode()[($startIndex + 2)] & 0xc0)) >> 6)));
			$unpacked[3] = (($this->getCode()[($startIndex + 2)] & 0x3f));
			for ($i = 0; ($i < 4); ++$i) 
			{
				if (($i < 3))
					++$startIndex;
				if (($unpacked[$i] == DxConstants::$DxCharEdifactUnlatch))
				{
					if (($this->getOutput()[$this->_outputIdx] != 0))
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
		$d0 = DxMessage::unRandomize255State($this->getCode()[++$startIndex], ++$idx);
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
				$d1 = DxMessage::unRandomize255State($this->getCode()[++$startIndex], ++$idx);
				$tempEndIndex = (($startIndex + ((($d0 - 249)) * 250)) + $d1);
			}
		if (($tempEndIndex > $endIndex))
		{
			throw new Exception("Error decoding scheme base 256");
		}
		while (($startIndex < $tempEndIndex)) 
		{
			$this->pushOutputWord(DxMessage::unRandomize255State($this->getCode()[++$startIndex], ++$idx));
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
		$dataRegionRows = DxCommon::getSymbolAttribute(DxSymAttribute::$DxSymAttribDataRegionRows, $sizeIdx);
		$dataRegionCols = DxCommon::getSymbolAttribute(DxSymAttribute::$DxSymAttribDataRegionCols, $sizeIdx);
		$symbolRows = DxCommon::getSymbolAttribute(DxSymAttribute::$DxSymAttribSymbolRows, $sizeIdx);
		$mappingCols = DxCommon::getSymbolAttribute(DxSymAttribute::$DxSymAttribMappingMatrixCols, $sizeIdx);
		$symbolRowReverse = (($symbolRows - $symbolRow) - 1);
		$mappingRow = (($symbolRowReverse - 1) - (2 * (($symbolRowReverse / (($dataRegionRows + 2))))));
		$mappingCol = (($symbolCol - 1) - (2 * (($symbolCol / (($dataRegionCols + 2))))));
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
		return (($this->getArray()[(($mappingRow * $mappingCols) + $mappingCol)] | DxConstants::$DxModuleData));
	}
	public function getPadCount () 
	{
		return $this->__PadCount;
	}
	public function setPadCount ($value) // [int value]
	{
		$this->__PadCount = $value;
	}
	public function getArray () 
	{
		return $this->__Array;
	}
	public function setArray ($value) // [byte[] value]
	{
		$this->__Array = $value;
	}
	public function getCode () 
	{
		return $this->__Code;
	}
	public function setCode ($value) // [byte[] value]
	{
		$this->__Code = $value;
	}
	public function getOutput () 
	{
		return $this->__Output;
	}
	public function setOutput ($value) // [byte[] value]
	{
		$this->__Output = $value;
	}
	public function getArraySize () 
	{
		return count($this->getArray()) /*from: this.getArray().length*/;
	}
	public function getCodeSize () 
	{
		return count($this->getCode()) /*from: this.getCode().length*/;
	}
	public function getOutputSize () 
	{
		return count($this->getOutput()) /*from: this.getOutput().length*/;
	}
}
?>
