<?php
require_once("pao/barcode/dmatrix/DxConstants.php");
require_once("pao/barcode/dmatrix/DxRay2.php");
require_once("pao/barcode/dmatrix/DxVector2.php");
class DxVector2 {
	protected $__X;	// double
	protected $__Y;	// double
	public static function constructor__ () 
	{
		$me = new self();
		$me->setX(doubleval(0.0));
		$me->setY(doubleval(0.0));
		return $me;
	}
	public static function constructor__D_D ($x, $y) // [double x, double y]
	{
		$me = new self();
		$me->setX($x);
		$me->setY($y);
		return $me;
	}
	public function plus ($v2) // [DxVector2 v2]
	{
		$result = NULL;
		try 
		{
			$result = DxVector2::constructor__();
		}
		catch (Exception $ex)
		{ /* empty */ }
		$result->setX(($result->getX() + $v2->getX()));
		$result->setY(($result->getY() + $v2->getY()));
		return $result;
	}
	public function minus ($v2) // [DxVector2 v2]
	{
		$result = NULL;
		try 
		{
			$result = DxVector2::constructor__();
		}
		catch (Exception $ex)
		{ /* empty */ }
		$result->setX(($result->getX() - $v2->getX()));
		$result->setY(($result->getY() - $v2->getY()));
		return $result;
	}
	public function multiply ($factor) // [double factor]
	{
		$result = NULL;
		try 
		{
			$result = DxVector2::constructor__();
		}
		catch (Exception $ex)
		{ /* empty */ }
		$result->setX(($result->getX() * $factor));
		$result->setY(($result->getY() * $factor));
		return $result;
	}
	public function cross ($v2) // [DxVector2 v2]
	{
		return ((($this->getX() * $v2->getY()) - ($this->getY() * $v2->getX())));
	}
	public function norm () 
	{
		$mag = $this->mag();
		if (($mag <= DxConstants::$DxAlmostZero))
		{
			return -doubleval(1.0);
		}
		$this->setX(($this->getX() / $mag));
		$this->setY(($this->getY() / $mag));
		return $mag;
	}
	public function dot ($v2) // [DxVector2 v2]
	{
		return sqrt(($me->getX() * $v2->getX()) + ($me->getY() * $v2->getY()));
	}
	public function mag () 
	{
		return sqrt(($me->getX() * $me->getX()) + ($me->getY() * $me->getY()));
	}
	public function distanceFromRay2 ($ray) // [DxRay2 ray]
	{
		if ((abs(doubleval(1.0) - $ray->getV()->mag()) > DxConstants::$DxAlmostZero))
		{
			throw new Exception("DistanceFromRay2: The ray's V vector must be a unit vector");
		}
		return $ray->getV()->cross($this->minus($ray->getP()));
	}
	public function distanceAlongRay2 ($ray) // [DxRay2 ray]
	{
		if ((abs(doubleval(1.0) - $ray->getV()->mag()) > DxConstants::$DxAlmostZero))
		{
			throw new Exception("DistanceAlongRay2: The ray's V vector must be a unit vector");
		}
		return ($this->minus($ray->getP()))->dot($ray->getV());
	}
	public function intersect ($p0, $p1) // [DxRay2 p0, DxRay2 p1]
	{
		$denominator = $p1->getV()->cross($p0->getV());
		if ((abs($denominator) < DxConstants::$DxAlmostZero))
		{
			return  FALSE ;
		}
		$numerator = $p1->getV()->cross($p1->getP()->minus($p0->getP()));
		return $this->pointAlongRay2($p0, ($numerator / $denominator));
	}
	public function pointAlongRay2 ($ray, $t) // [DxRay2 ray, double t]
	{
		if ((abs(doubleval(1.0) - $ray->getV()->mag()) > DxConstants::$DxAlmostZero))
		{
			throw new Exception("PointAlongRay: The ray's V vector must be a unit vector");
		}
		$tmp = DxVector2::constructor__D_D(($ray->getV()->getX() * $t), ($ray->getV()->getY() * $t));
		$this->setX(($ray->getP()->getX() + $tmp->getX()));
		$this->setY(($ray->getP()->getY() + $tmp->getY()));
		return  TRUE ;
	}
	public function getX () 
	{
		return $this->__X;
	}
	public function setX ($value) // [double value]
	{
		$this->__X = $value;
	}
	public function getY () 
	{
		return $this->__Y;
	}
	public function setY ($value) // [double value]
	{
		$this->__Y = $value;
	}
}
?>
