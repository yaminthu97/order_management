<?php
class C40TextState {
	protected $__Shift;	// int
	protected $__UpperShift;	// boolean
	public static function constructor__ () 
	{
		$me = new self();
		return $me;
	}
	public function getShift () 
	{
		return $this->__Shift;
	}
	public function setShift ($value) // [int value]
	{
		$this->__Shift = $value;
	}
	public function getUpperShift () 
	{
		return $this->__UpperShift;
	}
	public function setUpperShift ($value) // [boolean value]
	{
		$this->__UpperShift = $value;
	}
}
?>
