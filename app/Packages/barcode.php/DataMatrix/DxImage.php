<?php
require_once("pao/barcode/dmatrix/DxCommon.php");
require_once("pao/barcode/dmatrix/DxConstants.php");
require_once("pao/barcode/dmatrix/DxFlip.php");
require_once("pao/barcode/dmatrix/DxPackOrder.php");
class DxImage {
	protected $_rowPadBytes;	// int
	protected $__Width;	// int
	protected $__Height;	// int
	protected $__PixelPacking;	// DxPackOrder
	protected $__BitsPerPixel;	// int
	protected $__BytesPerPixel;	// int
	protected $__RowSizeBytes;	// int
	protected $__ImageFlip;	// DxFlip
	protected $__ChannelCount;	// int
	protected $__ChannelStart;	// int[]
	protected $__BitsPerChannel;	// int[]
	protected $__Pxl;	// byte[]
	private function __init() { // default class members
		$this->__PixelPacking = DxPackOrder::$DxPackCustom;
		$this->__ImageFlip = DxFlip::$DxFlipNone;
	}
	public static function constructor__aB_I_I_DxPackOrder ($pxl, $width, $height, $pack) // [byte[] pxl, int width, int height, DxPackOrder pack]
	{
		$me = new self();
		$me->__init();
		$me->setBitsPerChannel(array());
		$me->setChannelStart(array());
		if (((($pxl == NULL) || ($width < 1)) || ($height < 1)))
		{
			throw new Exception("Cannot create image of size null");
		}
		$me->setPxl($pxl);
		$me->setWidth($width);
		$me->setHeight($height);
		$me->setPixelPacking($pack);
		$me->setBitsPerPixel(DxCommon::getBitsPerPixel($pack));
		$me->setBytesPerPixel(($me->getBitsPerPixel() / 8));
		$me->_rowPadBytes = 0;
		$me->setRowSizeBytes((($me->getWidth() * $me->getBytesPerPixel()) + $me->_rowPadBytes));
		$me->setImageFlip(DxFlip::$DxFlipNone);
		$me->setChannelCount(0);
		switch ($pack) {
			case $DxPackCustom:
				break;
			case $DxPack1bppK:
				throw new Exception("Cannot create image: not supported pack order!");
			case $DxPack8bppK:
				$me->setChannel(0, 8);
				break;
			case $DxPack16bppRGB:
			case $DxPack16bppBGR:
			case $DxPack16bppYCbCr:
				$me->setChannel(0, 5);
				$me->setChannel(5, 5);
				$me->setChannel(10, 5);
				break;
			case $DxPack24bppRGB:
			case $DxPack24bppBGR:
			case $DxPack24bppYCbCr:
			case $DxPack32bppRGBX:
			case $DxPack32bppBGRX:
				$me->setChannel(0, 8);
				$me->setChannel(8, 8);
				$me->setChannel(16, 8);
				break;
			case $DxPack16bppRGBX:
			case $DxPack16bppBGRX:
				$me->setChannel(0, 5);
				$me->setChannel(5, 5);
				$me->setChannel(10, 5);
				break;
			case $DxPack16bppXRGB:
			case $DxPack16bppXBGR:
				$me->setChannel(1, 5);
				$me->setChannel(6, 5);
				$me->setChannel(11, 5);
				break;
			case $DxPack32bppXRGB:
			case $DxPack32bppXBGR:
				$me->setChannel(8, 8);
				$me->setChannel(16, 8);
				$me->setChannel(24, 8);
				break;
			case $DxPack32bppCMYK:
				$me->setChannel(0, 8);
				$me->setChannel(8, 8);
				$me->setChannel(16, 8);
				$me->setChannel(24, 8);
				break;
			default:
				throw new Exception("Cannot create image: Invalid Pack Order");
		}
		return $me;
	}
	public function setChannel ($channelStart, $bitsPerChannel) // [int channelStart, int bitsPerChannel]
	{
		if (($this->getChannelCount() >= 4))
			return  FALSE ;
		$this->getBitsPerChannel()[$this->getChannelCount()] = $bitsPerChannel;
		$this->getChannelStart()[$this->getChannelCount()] = $channelStart;
		$this->setChannelCount(($this->getChannelCount() + 1));
		return  TRUE ;
	}
	public function getByteOffset ($x, $y) // [int x, int y]
	{
		if (($this->getImageFlip() == DxFlip::$DxFlipX))
		{
			throw new Exception("DxFlipX is not an option!");
		}
		if (!$this->containsInt(0, $x, $y))
			return DxConstants::$DxUndefined;
		if (($this->getImageFlip() == DxFlip::$DxFlipY))
			return ((($y * $this->getRowSizeBytes()) + ($x * $this->getBytesPerPixel())));
		return (((((($this->getHeight() - $y) - 1)) * $this->getRowSizeBytes()) + ($x * $this->getBytesPerPixel())));
	}
	public function getPixelValue ($x, $y, $channel, $value) // [int x, int y, int channel, int[] value]
	{
		if (($channel >= $this->getChannelCount()))
		{
			throw new Exception("Channel greater than channel count!");
		}
		$offset = $this->getByteOffset($x, $y);
		if (($offset == DxConstants::$DxUndefined))
		{
			return  FALSE ;
		}
		switch ($this->getBitsPerChannel()[$channel]) {
			case 1:
				break;
			case 5:
				break;
			case 8:
				if (((($this->getChannelStart()[$channel] % 8) != 0) || (($this->getBitsPerPixel() % 8) != 0)))
				{
					throw new Exception("Error getting pixel value");
				}
				$value[0] = $this->getPxl()[($offset + $channel)];
				break;
		}
		return  TRUE ;
	}
	public function setPixelValue ($x, $y, $channel, $value) // [int x, int y, int channel, byte value]
	{
		if (($channel >= $this->getChannelCount()))
		{
			throw new Exception("Channel greater than channel count!");
		}
		$offset = $this->getByteOffset($x, $y);
		if (($offset == DxConstants::$DxUndefined))
		{
			return  FALSE ;
		}
		switch ($this->getBitsPerChannel()[$channel]) {
			case 1:
				break;
			case 5:
				break;
			case 8:
				if (((($this->getChannelStart()[$channel] % 8) != 0) || (($this->getBitsPerPixel() % 8) != 0)))
				{
					throw new Exception("Error getting pixel value");
				}
				$this->getPxl()[($offset + $channel)] = $value;
				break;
		}
		return  TRUE ;
	}
	public function containsInt ($margin, $x, $y) // [int margin, int x, int y]
	{
		if (((((($x - $margin) >= 0) && (($x + $margin) < $this->getWidth())) && (($y - $margin) >= 0)) && (($y + $margin) < $this->getHeight())))
			return  TRUE ;
		return  FALSE ;
	}
	public function containsFloat ($x, $y) // [double x, double y]
	{
		if ((((($x >= doubleval(0.0)) && ($x < $this->getWidth())) && ($y >= doubleval(0.0))) && ($y < $this->getHeight())))
		{
			return  TRUE ;
		}
		return  FALSE ;
	}
	public function getWidth () 
	{
		return $this->__Width;
	}
	public function setWidth ($value) // [int value]
	{
		$this->__Width = $value;
	}
	public function getHeight () 
	{
		return $this->__Height;
	}
	public function setHeight ($value) // [int value]
	{
		$this->__Height = $value;
	}
	public function getPixelPacking () 
	{
		return $this->__PixelPacking;
	}
	public function setPixelPacking ($value) // [DxPackOrder value]
	{
		$this->__PixelPacking = $value;
	}
	public function getBitsPerPixel () 
	{
		return $this->__BitsPerPixel;
	}
	public function setBitsPerPixel ($value) // [int value]
	{
		$this->__BitsPerPixel = $value;
	}
	public function getBytesPerPixel () 
	{
		return $this->__BytesPerPixel;
	}
	public function setBytesPerPixel ($value) // [int value]
	{
		$this->__BytesPerPixel = $value;
	}
	public function getRowPadBytes () 
	{
		return $this->_rowPadBytes;
	}
	public function setRowPadBytes ($value) // [int value]
	{
		$this->_rowPadBytes = $value;
		$this->setRowSizeBytes((($this->getWidth() * (($this->getBitsPerPixel() / 8))) + $this->_rowPadBytes));
	}
	public function getRowSizeBytes () 
	{
		return $this->__RowSizeBytes;
	}
	public function setRowSizeBytes ($value) // [int value]
	{
		$this->__RowSizeBytes = $value;
	}
	public function getImageFlip () 
	{
		return $this->__ImageFlip;
	}
	public function setImageFlip ($value) // [DxFlip value]
	{
		$this->__ImageFlip = $value;
	}
	public function getChannelCount () 
	{
		return $this->__ChannelCount;
	}
	public function setChannelCount ($value) // [int value]
	{
		$this->__ChannelCount = $value;
	}
	public function getChannelStart () 
	{
		return $this->__ChannelStart;
	}
	public function setChannelStart ($value) // [int[] value]
	{
		$this->__ChannelStart = $value;
	}
	public function getBitsPerChannel () 
	{
		return $this->__BitsPerChannel;
	}
	public function setBitsPerChannel ($value) // [int[] value]
	{
		$this->__BitsPerChannel = $value;
	}
	public function getPxl () 
	{
		return $this->__Pxl;
	}
	public function setPxl ($value) // [byte[] value]
	{
		$this->__Pxl = $value;
	}
}
?>
