<?php
require_once("pao/barcode/dmatrix/DxChannel.php");
class DxChannelGroup {
	protected $_channels;	// DxChannel[]
	public function getChannels () 
	{
		if (($this->_channels == NULL))
		{
			$this->_channels = array();
			for ($i = 0; ($i < 6); ++$i) 
			{
				$this->_channels[$i] = new DxChannel();
			}
		}
		return $this->_channels;
	}
}
?>
