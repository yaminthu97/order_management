<?php


	/**
	 * CODE128�̃R�[�h�Z�b�g
	 * 
	 * CODE128�̃R�[�h�Z�b�g(AUTO, A, B, C)
	 */
	class CodeSet128 {
		/* CODE128�̃R�[�h�Z�b�g(AUTO, A, B, C) */
		const AUTO = 0;
		const CODE_A = 1;
		const CODE_B = 2;
		const CODE_C = 3;
	}


	/**
	 * Checks whether string s is parseable to integer or not
	 * @param s String
	 * @param i1 First index in string to be parsed
	 * @param i2 Num
	 * @return true if string could be parsed, false otherwise
	 */
	function isParseAbleInteger($s, $i1, $i2) {
		/*
		$tmpStr1 = substr($s, $i1, $i2);
		$tmpStr2 = (int)$tmpStr1;
		if ( $tmpStr1 == $tmpStr2) {
			return true;
		} else {
			return false;
		}
		*/
		//return is_numeric(substr($s, $i1, $i2));
		
		if(strlen($s) < $i2){
			return false;
		}
		
		if(preg_match("/^[0-9]+$/",substr($s, $i1, $i2 - $i1 ))){
			return true;
		}else{
			return false;
		}

	}

	/**
	 * Converts cm to pixel
	 * @param g Graphics
	 * @param cm Value in cm
	 * @return Value in pixel
	 */
	function CmTo ($cm) {
		return (float)($cm * 10);
	}
	
	/**
	 * Converts point to pixel
	 * @param g Graphics
	 * @param point Value in point
	 * @return Value in pixel
	 */
	function pointTo($point) {
		return $point * 0.352;
	}
	
	function isOK() {
		return true;
	}

	/**
	*
	* @param string $string String to "search" from
	* @param int $index Index of the letter we want.
	* @return string The letter found on $index.
	*/
	function charAt($string, $index){
		if($index < strlen($string)){
			return substr($string, $index, 1);
		}
		else{
			return -1;
		}
	}
	
	function bin2pat ($bin) // [String bin]
	{
		$i = 0;
		$l = 0;
		$pat = "";

		$black =  true ;
		$l = 0;
		for ($i = 0; $i < strlen($bin); $i++) 
		{
			if ($black)
			{
			if (charAt($bin, $i) == '1')
				{
					$l++;
				}
				else
				{
					$black =  false ;
					$pat .= chr($l + 0x30);
					$l = 1;
				}
			}
			else
			{
			if (charAt($bin, $i) == '0')
				{
					++$l;
				}
				else
				{
					$black =  TRUE ;
					$pat .= chr($l + 0x30);
					$l = 1;
				}
			}
		}
		$pat .= chr($l + 0x30);
		return $pat;
	}