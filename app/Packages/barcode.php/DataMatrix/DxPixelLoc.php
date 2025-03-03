<?php
class DxPixelLoc {
	protected $__X;	// int
	protected $__Y;	// int
	public static function constructor__ () 
	{
		$me = new self();
		return $me;
	}
	public function getX () 
	{
		return $this->__X;
	}
	public function setX ($value) // [int value]
	{
		$this->__X = $value;
	}
	public function getY () 
	{
		return $this->__Y;
	}
	public function setY ($value) // [int value]
	{
		$this->__Y = $value;
	}
}
?>
