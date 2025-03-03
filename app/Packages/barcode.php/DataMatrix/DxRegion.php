<?php
require_once("pao/barcode/dmatrix/DxCodeSize.php");
require_once("pao/barcode/dmatrix/DxMatrix3.php");
require_once("pao/barcode/dmatrix/DxPixelLoc.php");
require_once("pao/barcode/dmatrix/DxPointFlow.php");
require_once("pao/barcode/dmatrix/DxRegion.php");
class DxRegion {
	protected $__JumpToPos;	// int
	protected $__JumpToNeg;	// int
	protected $__StepsTotal;	// int
	protected $__FinalPos;	// DxPixelLoc
	protected $__FinalNeg;	// DxPixelLoc
	protected $__BoundMin;	// DxPixelLoc
	protected $__BoundMax;	// DxPixelLoc
	protected $__FlowBegin;	// DxPointFlow
	protected $__Polarity;	// int
	protected $__StepR;	// int
	protected $__StepT;	// int
	protected $__LocR;	// DxPixelLoc
	protected $__LocT;	// DxPixelLoc
	protected $__LeftKnown;	// int
	protected $__LeftAngle;	// int
	protected $__LeftLoc;	// DxPixelLoc
	protected $__LeftLine;	// DxBestLine
	protected $__BottomKnown;	// int
	protected $__BottomAngle;	// int
	protected $__BottomLoc;	// DxPixelLoc
	protected $__BottomLine;	// DxBestLine
	protected $__TopKnown;	// int
	protected $__TopAngle;	// int
	protected $__TopLoc;	// DxPixelLoc
	protected $__RightKnown;	// int
	protected $__RightAngle;	// int
	protected $__RightLoc;	// DxPixelLoc
	protected $__OnColor;	// int
	protected $__OffColor;	// int
	protected $__SizeIdx;	// DxCodeSize
	protected $__SymbolRows;	// int
	protected $__SymbolCols;	// int
	protected $__MappingRows;	// int
	protected $__MappingCols;	// int
	protected $__Raw2Fit;	// DxMatrix3
	protected $__Fit2Raw;	// DxMatrix3
	private function __init() { // default class members
		$this->__SizeIdx = DxCodeSize::$DxSzRectAuto;
	}
	public static function constructor__ () 
	{
		$me = new self();
		$me->__init();
		return $me;
	}
	public static function constructor__DxRegion ($src) // [DxRegion src]
	{
		$me = new self();
		$me->__init();
		$me->setBottomAngle($src->getBottomAngle());
		$me->setBottomKnown($src->getBottomKnown());
		$me->setBottomLine($src->getBottomLine());
		$me->setBottomLoc($src->getBottomLoc());
		$me->setBoundMax($src->getBoundMax());
		$me->setBoundMin($src->getBoundMin());
		$me->setFinalNeg($src->getFinalNeg());
		$me->setFinalPos($src->getFinalPos());
		$me->setFit2Raw(DxMatrix3::constructor__DxMatrix3($src->getFit2Raw()));
		$me->setFlowBegin($src->getFlowBegin());
		$me->setJumpToNeg($src->getJumpToNeg());
		$me->setJumpToPos($src->getJumpToPos());
		$me->setLeftAngle($src->getLeftAngle());
		$me->setLeftKnown($src->getLeftKnown());
		$me->setLeftLine($src->getLeftLine());
		$me->setLeftLoc($src->getLeftLoc());
		$me->setLocR($src->getLocR());
		$me->setLocT($src->getLocT());
		$me->setMappingCols($src->getMappingCols());
		$me->setMappingRows($src->getMappingRows());
		$me->setOffColor($src->getOffColor());
		$me->setOnColor($src->getOnColor());
		$me->setPolarity($src->getPolarity());
		$me->setRaw2Fit(DxMatrix3::constructor__DxMatrix3($src->getRaw2Fit()));
		$me->setRightAngle($src->getRightAngle());
		$me->setRightKnown($src->getRightKnown());
		$me->setRightLoc($src->getRightLoc());
		$me->setSizeIdx($src->getSizeIdx());
		$me->setStepR($src->getStepR());
		$me->setStepsTotal($src->getStepsTotal());
		$me->setStepT($src->getStepT());
		$me->setSymbolCols($src->getSymbolCols());
		$me->setSymbolRows($src->getSymbolRows());
		$me->setTopAngle($src->getTopAngle());
		$me->setTopKnown($src->getTopKnown());
		$me->setTopLoc($src->getTopLoc());
		return $me;
	}
	public function getJumpToPos () 
	{
		return $this->__JumpToPos;
	}
	public function setJumpToPos ($value) // [int value]
	{
		$this->__JumpToPos = $value;
	}
	public function getJumpToNeg () 
	{
		return $this->__JumpToNeg;
	}
	public function setJumpToNeg ($value) // [int value]
	{
		$this->__JumpToNeg = $value;
	}
	public function getStepsTotal () 
	{
		return $this->__StepsTotal;
	}
	public function setStepsTotal ($value) // [int value]
	{
		$this->__StepsTotal = $value;
	}
	public function getFinalPos () 
	{
		return $this->__FinalPos;
	}
	public function setFinalPos ($value) // [DxPixelLoc value]
	{
		$this->__FinalPos = $value;
	}
	public function getFinalNeg () 
	{
		return $this->__FinalNeg;
	}
	public function setFinalNeg ($value) // [DxPixelLoc value]
	{
		$this->__FinalNeg = $value;
	}
	public function getBoundMin () 
	{
		return $this->__BoundMin;
	}
	public function setBoundMin ($value) // [DxPixelLoc value]
	{
		$this->__BoundMin = $value;
	}
	public function getBoundMax () 
	{
		return $this->__BoundMax;
	}
	public function setBoundMax ($value) // [DxPixelLoc value]
	{
		$this->__BoundMax = $value;
	}
	public function getFlowBegin () 
	{
		return $this->__FlowBegin;
	}
	public function setFlowBegin ($value) // [DxPointFlow value]
	{
		$this->__FlowBegin = $value;
	}
	public function getPolarity () 
	{
		return $this->__Polarity;
	}
	public function setPolarity ($value) // [int value]
	{
		$this->__Polarity = $value;
	}
	public function getStepR () 
	{
		return $this->__StepR;
	}
	public function setStepR ($value) // [int value]
	{
		$this->__StepR = $value;
	}
	public function getStepT () 
	{
		return $this->__StepT;
	}
	public function setStepT ($value) // [int value]
	{
		$this->__StepT = $value;
	}
	public function getLocR () 
	{
		return $this->__LocR;
	}
	public function setLocR ($value) // [DxPixelLoc value]
	{
		$this->__LocR = $value;
	}
	public function getLocT () 
	{
		return $this->__LocT;
	}
	public function setLocT ($value) // [DxPixelLoc value]
	{
		$this->__LocT = $value;
	}
	public function getLeftKnown () 
	{
		return $this->__LeftKnown;
	}
	public function setLeftKnown ($value) // [int value]
	{
		$this->__LeftKnown = $value;
	}
	public function getLeftAngle () 
	{
		return $this->__LeftAngle;
	}
	public function setLeftAngle ($value) // [int value]
	{
		$this->__LeftAngle = $value;
	}
	public function getLeftLoc () 
	{
		return $this->__LeftLoc;
	}
	public function setLeftLoc ($value) // [DxPixelLoc value]
	{
		$this->__LeftLoc = $value;
	}
	public function getLeftLine () 
	{
		return $this->__LeftLine;
	}
	public function setLeftLine ($value) // [DxBestLine value]
	{
		$this->__LeftLine = $value;
	}
	public function getBottomKnown () 
	{
		return $this->__BottomKnown;
	}
	public function setBottomKnown ($value) // [int value]
	{
		$this->__BottomKnown = $value;
	}
	public function getBottomAngle () 
	{
		return $this->__BottomAngle;
	}
	public function setBottomAngle ($value) // [int value]
	{
		$this->__BottomAngle = $value;
	}
	public function getBottomLoc () 
	{
		return $this->__BottomLoc;
	}
	public function setBottomLoc ($value) // [DxPixelLoc value]
	{
		$this->__BottomLoc = $value;
	}
	public function getBottomLine () 
	{
		return $this->__BottomLine;
	}
	public function setBottomLine ($value) // [DxBestLine value]
	{
		$this->__BottomLine = $value;
	}
	public function getTopKnown () 
	{
		return $this->__TopKnown;
	}
	public function setTopKnown ($value) // [int value]
	{
		$this->__TopKnown = $value;
	}
	public function getTopAngle () 
	{
		return $this->__TopAngle;
	}
	public function setTopAngle ($value) // [int value]
	{
		$this->__TopAngle = $value;
	}
	public function getTopLoc () 
	{
		return $this->__TopLoc;
	}
	public function setTopLoc ($value) // [DxPixelLoc value]
	{
		$this->__TopLoc = $value;
	}
	public function getRightKnown () 
	{
		return $this->__RightKnown;
	}
	public function setRightKnown ($value) // [int value]
	{
		$this->__RightKnown = $value;
	}
	public function getRightAngle () 
	{
		return $this->__RightAngle;
	}
	public function setRightAngle ($value) // [int value]
	{
		$this->__RightAngle = $value;
	}
	public function getRightLoc () 
	{
		return $this->__RightLoc;
	}
	public function setRightLoc ($value) // [DxPixelLoc value]
	{
		$this->__RightLoc = $value;
	}
	public function getOnColor () 
	{
		return $this->__OnColor;
	}
	public function setOnColor ($value) // [int value]
	{
		$this->__OnColor = $value;
	}
	public function getOffColor () 
	{
		return $this->__OffColor;
	}
	public function setOffColor ($value) // [int value]
	{
		$this->__OffColor = $value;
	}
	public function getSizeIdx () 
	{
		return $this->__SizeIdx;
	}
	public function setSizeIdx ($value) // [DxCodeSize value]
	{
		$this->__SizeIdx = $value;
	}
	public function getSymbolRows () 
	{
		return $this->__SymbolRows;
	}
	public function setSymbolRows ($value) // [int value]
	{
		$this->__SymbolRows = $value;
	}
	public function getSymbolCols () 
	{
		return $this->__SymbolCols;
	}
	public function setSymbolCols ($value) // [int value]
	{
		$this->__SymbolCols = $value;
	}
	public function getMappingRows () 
	{
		return $this->__MappingRows;
	}
	public function setMappingRows ($value) // [int value]
	{
		$this->__MappingRows = $value;
	}
	public function getMappingCols () 
	{
		return $this->__MappingCols;
	}
	public function setMappingCols ($value) // [int value]
	{
		$this->__MappingCols = $value;
	}
	public function getRaw2Fit () 
	{
		return $this->__Raw2Fit;
	}
	public function setRaw2Fit ($value) // [DxMatrix3 value]
	{
		$this->__Raw2Fit = $value;
	}
	public function getFit2Raw () 
	{
		return $this->__Fit2Raw;
	}
	public function setFit2Raw ($value) // [DxMatrix3 value]
	{
		$this->__Fit2Raw = $value;
	}
}
?>
