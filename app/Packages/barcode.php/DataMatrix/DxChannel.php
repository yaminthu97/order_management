<?php
require_once("pao/barcode/dmatrix/DxChannelStatus.php");
require_once("pao/barcode/dmatrix/DxScheme.php");
class DxChannel {
	protected $_encodedWords;	// byte[]
	protected $__Input;	// byte[]
	protected $__EncScheme;	// DxScheme
	protected $__Invalid;	// DxChannelStatus
	protected $__InputIndex;	// int
	protected $__EncodedLength;	// int
	protected $__CurrentLength;	// int
	protected $__FirstCodeWord;	// int
	private function __init() { // default class members
		$this->__EncScheme = DxScheme::$DxSchemeAutoFast;
		$this->__Invalid = DxChannelStatus::$DxChannelValid;
	}
	public function getInput () 
	{
		return $this->__Input;
	}
	public function setInput ($value) // [byte[] value]
	{
		$this->__Input = $value;
	}
	public function getEncScheme () 
	{
		return $this->__EncScheme;
	}
	public function setEncScheme ($value) // [DxScheme value]
	{
		$this->__EncScheme = $value;
	}
	public function getInvalid () 
	{
		return $this->__Invalid;
	}
	public function setInvalid ($value) // [DxChannelStatus value]
	{
		$this->__Invalid = $value;
	}
	public function getInputIndex () 
	{
		return $this->__InputIndex;
	}
	public function setInputIndex ($value) // [int value]
	{
		$this->__InputIndex = $value;
	}
	public function getEncodedLength () 
	{
		return $this->__EncodedLength;
	}
	public function setEncodedLength ($value) // [int value]
	{
		$this->__EncodedLength = $value;
	}
	public function getCurrentLength () 
	{
		return $this->__CurrentLength;
	}
	public function setCurrentLength ($value) // [int value]
	{
		$this->__CurrentLength = $value;
	}
	public function getFirstCodeWord () 
	{
		return $this->__FirstCodeWord;
	}
	public function setFirstCodeWord ($value) // [int value]
	{
		$this->__FirstCodeWord = $value;
	}
	public function getEncodedWords () 
	{
		return ( (($this->_encodedWords != NULL)) ? $this->_encodedWords : ($this->_encodedWords = array()) );
	}
}
?>
