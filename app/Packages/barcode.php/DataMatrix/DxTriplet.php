<?php
class DxTriplet {
	protected $_value;	// byte[]
	public static function constructor__ () 
	{
		$me = new self();
		return $me;
	}
	public function getValue () 
	{
		return ( (($this->_value != NULL)) ? $this->_value : ($this->_value = array()) );
	}
}
?>
