<?php
require_once("pao/barcode/dmatrix/DxVector2.php");
class DxRay2 {
	protected $_p;	// DxVector2
	protected $_v;	// DxVector2
	protected $__TMin;	// double
	protected $__TMax;	// double
	public function getP () 
	{
		return ( (($this->_p != NULL)) ? $this->_p : ($this->_p = DxVector2::constructor__()) );
	}
	public function setP ($value) // [DxVector2 value]
	{
		$this->_p = $value;
	}
	public function getV () 
	{
		return ( (($this->_v != NULL)) ? $this->_v : ($this->_v = DxVector2::constructor__()) );
	}
	public function setV ($value) // [DxVector2 value]
	{
		$this->_v = $value;
	}
	public function getTMin () 
	{
		return $this->__TMin;
	}
	public function setTMin ($value) // [double value]
	{
		$this->__TMin = $value;
	}
	public function getTMax () 
	{
		return $this->__TMax;
	}
	public function setTMax ($value) // [double value]
	{
		$this->__TMax = $value;
	}
}
?>
