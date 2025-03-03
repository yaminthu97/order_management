<?php
require_once("pao/barcode/dmatrix/DxCodeSize.php");
require_once("pao/barcode/dmatrix/DxScheme.php");
class DxImageEncoderOptions {
	protected $__MarginSize;	// int
	protected $__ModuleSize;	// int
	protected $__Scheme;	// DxScheme
	protected $__SizeIdx;	// DxCodeSize
	protected $__ForeColor;	// java.awt.Color
	protected $__BackColor;	// java.awt.Color
	protected $__Encoding;	// String
	protected $__EncodingVal;	// int
	private function __init() { // default class members
		$this->__Scheme = DxScheme::$DxSchemeAutoFast;
		$this->__SizeIdx = DxCodeSize::$DxSzRectAuto;
	}
	public static function constructor__ () 
	{
		$me = new self();
		$me->__init();
		$me->setBackColor($java->awt->Color->WHITE);
		$me->setForeColor($java->awt->Color->BLACK);
		$me->setSizeIdx(DxCodeSize::$DxSzAuto);
		$me->setScheme(DxScheme::$DxSchemeAscii);
		$me->setModuleSize(5);
		$me->setMarginSize(10);
		$me->setEncodingString("UTF-8");
		return $me;
	}
	public function getMarginSize () 
	{
		return $this->__MarginSize;
	}
	public function setMarginSize ($value) // [int value]
	{
		$this->__MarginSize = $value;
	}
	public function getModuleSize () 
	{
		return $this->__ModuleSize;
	}
	public function setModuleSize ($value) // [int value]
	{
		$this->__ModuleSize = $value;
	}
	public function getScheme () 
	{
		return $this->__Scheme;
	}
	public function setScheme ($value) // [DxScheme value]
	{
		$this->__Scheme = $value;
	}
	public function getSizeIdx () 
	{
		return $this->__SizeIdx;
	}
	public function setSizeIdx ($value) // [DxCodeSize value]
	{
		$this->__SizeIdx = $value;
	}
	public function getForeColor () 
	{
		return $this->__ForeColor;
	}
	public function setForeColor ($value) // [java.awt.Color value]
	{
		$this->__ForeColor = $value;
	}
	public function getBackColor () 
	{
		return $this->__BackColor;
	}
	public function setBackColor ($value) // [java.awt.Color value]
	{
		$this->__BackColor = $value;
	}
	public function getEncodingString () 
	{
		return $this->__Encoding;
	}
	public function setEncodingString ($value) // [String value]
	{
		$this->__Encoding = $value;
	}
	public function getEncodingVal () 
	{
		return $this->__EncodingVal;
	}
	public function setEncodingVal ($value) // [int value]
	{
		$this->__Encoding = NULL;
		$this->__EncodingVal = $value;
	}
}
?>
