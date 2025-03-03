<?php
require_once("pao/barcode/dmatrix/DxConstants.php");
require_once("pao/barcode/dmatrix/DxMatrix3.php");
require_once("pao/barcode/dmatrix/DxVector2.php");
class DxMatrix3 {
	protected $_data;	// double[][]
	private function __init() { // default class members
		$this->_data = array();
	}
	public static function constructor__ () 
	{
		$me = new self();
		$me->__init();
		return $me;
	}
	public static function constructor__DxMatrix3 ($src) // [DxMatrix3 src]
	{
		$me = new self();
		$me->__init();
		$me->_data = array( $src->get___idx(0, 0), $src->get___idx(0, 1), $src->get___idx(0, 2), $src->get___idx(1, 0), $src->get___idx(1, 1), $src->get___idx(1, 2), $src->get___idx(2, 0), $src->get___idx(2, 1), $src->get___idx(2, 2) );
		return $me;
	}
	public static function identity () 
	{
		return DxMatrix3::translate(0, 0);
	}
	public static function translate ($tx, $ty) // [double tx, double ty]
	{
		$result = DxMatrix3::constructor__();
		$result->_data = array( doubleval(1.0), doubleval(0.0), doubleval(0.0), doubleval(0.0), doubleval(1.0), doubleval(0.0), $tx, $ty, doubleval(1.0) );
		return $result;
	}
	public static function rotate ($angle) // [double angle]
	{
		$result = DxMatrix3::constructor__();
		$result->_data = array( cos($angle), sin($angle), doubleval(0.0), (-sin($angle)), cos($angle), doubleval(0.0), 0, 0, doubleval(1.0) );
		return $result;
	}
	public static function scale ($sx, $sy) // [double sx, double sy]
	{
		$result = DxMatrix3::constructor__();
		$result->_data = array( $sx, doubleval(0.0), doubleval(0.0), doubleval(0.0), $sy, doubleval(0.0), 0, 0, doubleval(1.0) );
		return $result;
	}
	public static function shear ($shx, $shy) // [double shx, double shy]
	{
		$result = DxMatrix3::constructor__();
		$result->_data = array( doubleval(1.0), $shy, doubleval(0.0), $shx, doubleval(1.0), doubleval(0.0), 0, 0, doubleval(1.0) );
		return $result;
	}
	public static function lineSkewTop ($b0, $b1, $sz) // [double b0, double b1, double sz]
	{
		if (($b0 < DxConstants::$DxAlmostZero))
		{
			throw new Exception("b0 must be larger than zero in top line skew transformation");
		}
		$result = DxMatrix3::constructor__();
		$result->_data = array( ($b1 / $b0), doubleval(0.0), ((($b1 - $b0)) / (($sz * $b0))), doubleval(0.0), ($sz / $b0), doubleval(0.0), 0, 0, doubleval(1.0) );
		return $result;
	}
	public static function lineSkewTopInv ($b0, $b1, $sz) // [double b0, double b1, double sz]
	{
		if (($b1 < DxConstants::$DxAlmostZero))
		{
			throw new Exception("b1 must be larger than zero in top line skew transformation (inverse)");
		}
		$result = DxMatrix3::constructor__();
		$result->_data = array( ($b0 / $b1), doubleval(0.0), ((($b0 - $b1)) / (($sz * $b1))), doubleval(0.0), ($b0 / $sz), doubleval(0.0), 0, 0, doubleval(1.0) );
		return $result;
	}
	public static function lineSkewSide ($b0, $b1, $sz) // [double b0, double b1, double sz]
	{
		if (($b0 < DxConstants::$DxAlmostZero))
		{
			throw new Exception("b0 must be larger than zero in side line skew transformation (inverse)");
		}
		$result = DxMatrix3::constructor__();
		$result->_data = array( ($sz / $b0), doubleval(0.0), doubleval(0.0), doubleval(0.0), ($b1 / $b0), ((($b1 - $b0)) / (($sz * $b0))), 0, 0, doubleval(1.0) );
		return $result;
	}
	public static function lineSkewSideInv ($b0, $b1, $sz) // [double b0, double b1, double sz]
	{
		if (($b1 < DxConstants::$DxAlmostZero))
		{
			throw new Exception("b1 must be larger than zero in top line skew transformation (inverse)");
		}
		$result = DxMatrix3::constructor__();
		$result->_data = array( ($b0 / $sz), doubleval(0.0), doubleval(0.0), doubleval(0.0), ($b0 / $b1), ((($b0 - $b1)) / (($sz * $b1))), 0, 0, doubleval(1.0) );
		return $result;
	}
	public static function multiply ($vector, $matrix) // [DxVector2 vector, DxMatrix3 matrix]
	{
		$w = abs((($vector->getX() * $matrix->_data[0][2]) + ($vector->getY() * $matrix->_data[1][2])) + $matrix->_data[2][2]);
		if (($w <= DxConstants::$DxAlmostZero))
		{
			try 
			{
				throw new Exception("Multiplication of vector and matrix resulted in invalid result");
			}
			catch (Exception $ex)
			{ /* empty */ }
		}
		$result = NULL;
		try 
		{
			$result = DxVector2::constructor__D_D(((((($vector->getX() * $matrix->_data[0][0]) + ($vector->getY() * $matrix->_data[1][0])) + $matrix->_data[2][0])) / $w), ((((($vector->getX() * $matrix->_data[0][1]) + ($vector->getY() * $matrix->_data[1][1])) + $matrix->_data[2][1])) / $w));
		}
		catch (Exception $ex)
		{ /* empty */ }
		return $result;
	}
	public static function multiply3 ($m1, $m2) // [DxMatrix3 m1, DxMatrix3 m2]
	{
		$result = DxMatrix3::constructor__();
		$result->_data = array( doubleval(0.0), 0, 0, 0, 0, 0, 0, 0, 0 );
		for ($i = 0; ($i < 3); ++$i) 
		{
			for ($j = 0; ($j < 3); ++$j) 
			{
				for ($k = 0; ($k < 3); ++$k) 
				{
					$result->_data[$i][$j] += ($m1->_data[$i][$k] * $m2->_data[$k][$j]);
				}
			}
		}
		return $result;
	}
	public function toString () 
	{
		try 
		{
			return $String->format("{0}\t{1}\t{2}\n{3}\t{4}\t{5}\n{6}\t{7}\t{8}\n", $this->_data[0][0], $this->_data[0][1], $this->_data[0][2], $this->_data[1][0], $this->_data[1][1], $this->_data[1][2], $this->_data[2][0], $this->_data[2][1], $this->_data[2][2]);
		}
		catch (RuntimeException $__dummyCatchVar0)
		{
			throw $__dummyCatchVar0;
		}
		catch (Exception $__dummyCatchVar0)
		{
			throw new RuntimeException($__dummyCatchVar0);
		}
	}
	public function get___idx ($i, $j) // [int i, int j]
	{
		return $this->_data[$i][$j];
	}
	public function set___idx ($i, $j, $value) // [int i, int j, double value]
	{
		$this->_data[$i][$j] = $value;
	}
}
?>
