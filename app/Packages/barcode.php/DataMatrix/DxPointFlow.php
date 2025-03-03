<?php
require_once("pao/barcode/dmatrix/DxConstants.php");
require_once("pao/barcode/dmatrix/DxPixelLoc.php");
class DxPointFlow {
	protected $loc;	// DxPixelLoc
	protected $__Plane;	// int
	protected $__Arrive;	// int
	protected $__Depart;	// int
	protected $__Mag;	// int
	public static function constructor__ () 
	{
		$me = new self();
		return $me;
	}
	public static function constructor__I ($i) // [int i]
	{
		$me = new self();
		$me->setPlane(0);
		$me->setArrive(0);
		$me->setDepart(0);
		$me->setMag(DxConstants::$DxUndefined);
		$me->loc = DxPixelLoc::constructor__();
		$me->loc->setX(-1);
		$me->loc->setY(-1);
		return $me;
	}
	public function getPlane () 
	{
		return $this->__Plane;
	}
	public function setPlane ($value) // [int value]
	{
		$this->__Plane = $value;
	}
	public function getArrive () 
	{
		return $this->__Arrive;
	}
	public function setArrive ($value) // [int value]
	{
		$this->__Arrive = $value;
	}
	public function getDepart () 
	{
		return $this->__Depart;
	}
	public function setDepart ($value) // [int value]
	{
		$this->__Depart = $value;
	}
	public function getMag () 
	{
		return $this->__Mag;
	}
	public function setMag ($value) // [int value]
	{
		$this->__Mag = $value;
	}
	public function getLoc () 
	{
		return $this->loc;
	}
	public function setLoc ($value) // [DxPixelLoc value]
	{
		$this->loc = $value;
	}
}
?>
