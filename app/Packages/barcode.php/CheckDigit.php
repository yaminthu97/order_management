<?php
/*
 * CheckDigit.php
 *
 */

	require_once("Common.php");

/**
 * 各種チェックデジットの算出を行うライブラリーです。
 * @author PAO
 */
	
	/**p
	 * モジュラス10 ウエイト3 のチェックデジットの計算
	 * @param String data チェックデジットを除いた文字列
	 * @return チェックデジット
	 * @throws ErrCheckDigitBadChar if code cannot be calculated with CheckDigit
	 */
	function modulus10W3($data) {
		$odds = 0;
		$evens = 0;

		$odd = true;
		for ($i = strlen($data); $i > 0; $i--) {
			try {
				if ($odd) {
					$odds += (int)substr($data, $i - 1, 1);
					$odd = false;
				} else {
					$evens += (int)substr($data, $i - 1, 1);
					$odd = true;
				}
			} catch (Exception $ex) {
				throw new Exception('Pao.BarCode.CheckDigit : 数字以外の文字が使用されました。\n使用できる文字は数字のみです。');
			}
		}
		$s = $odds * 3 + $evens;
		$cd = (int)(substr($s, strlen($s) - 1, 1));
		if ($cd == 0) {
			$cd = 0;
		} else {
			$cd = 10 - $cd;
		}
		return $cd;
	}

	/**p
	 * UPC-E用 モジュラス10 ウエイト3 のチェックデジットの計算
	 * @param String data チェックデジットを除いた文字列
	 * @return チェックデジット
	 * @throws ErrCheckDigitBadChar if code cannot be calculated with CheckDigit
	 */
	function modulus10W3_UPC_E($data) {
            $newData = "";

	$len1 = substr($data, 0, 1);
	$len7 = substr($data, 6, 1);

            //■パターン1：1桁目が「0」，7桁目が「0～2」の場合
            //7桁目を3桁目と4桁目の間に移動させて，その後ろに「0」を4つ挿入します。
            //例えば，データが「0013452」の場合は，「00120000345」となります。
			if ($len1 == "0" && ($len7 == "0" || $len7 == "1" || $len7 == "2"))
            {
		$newData = substr($data, 0, 3) . $len7 . "0000" . substr($data, 3, 3);
            }
            
            //■パターン2：1桁目が「0」，7桁目が「3」の場合
            //7桁目を削除して，4桁目と5桁目の間に「0」を5つ挿入します。
            //例えば，データが「0123453」の場合は，「01230000045」となります。
            else if ($len1 == "0" && $len7 == "3")
            {
		$newData = substr($data, 0, 4) . "00000" . substr($data, 4, 2);
            }

            //■パターン3：1桁目が「0」，7桁目が「4」の場合
            //7桁目を削除して，5桁目と6桁目の間に「0」を5つ挿入します。
            //例えば，データが「0123454」の場合は，「01234000005」となります。
            else if ($len1 == "0" && $len7 == "4")
            {
		$newData = substr($data, 0, 5) . "00000" . substr($data, 5, 1);
            }

            
            //■パターン4：1桁目が「0」，7桁目が「5～9」の場合
            //6桁目と7桁目の間に「0」を4つ挿入します。
            //例えば，データが「0123456」の場合は，「01234500006」となります。
            else if ($len1 == "0" && ($len7 == "5" || $len7 == "6" || $len7 == "7" || $len7 == "8" || $len7 == "9"))
            {
		$newData = substr($data, 0, 6) . "0000" . $len7;
            }

            else
            { return "";  }



			return modulus10W3($newData);

        }

	/**
	 * モジュラス103 ウエイト1 のチェックデジットの計算
	 * @param String data チェックデジットを除いた文字列
	 * @return チェックデジット
	 */
function modulus103W1($code, $CodeABC) {
		
	$escString = "\x00\x01\x02\x03\x04\x05\x06\x07\x08\x09\x0A\x0B\x0C\x0D\x0E\x0F\x10\x12\x13\x14\x15\x16\x17\x18\x19\x1A\x1B\x1C\x1D\x1E\x1F";
	$only_codeB = "`abcdefghijklmnopqrstuvwxyz{|}~\x7F";  
	$codeA = " !\"#$%&'()*+,-./0123456789:;<=>?@ABCDEFGHIJKLMNOPQRSTUVWXYZ[\\]^_\x00\x01\x02\x03\x04\x05\x06\x07\x08\x09\x0A\x0B\x0C\x0D\x0E\x0F\x10\x12\x13\x14\x15\x16\x17\x18\x19\x1A\x1B\x1C\x1D\x1E\x1F";
	$codeB = " !\"#$%&'()*+,-./0123456789:;<=>?@ABCDEFGHIJKLMNOPQRSTUVWXYZ[\\]^_`abcdefghijklmnopqrstuvwxyz{|}~\x7F";
	$codeC = array("00", "01", "02", "03", "04", "05", "06", "07", "08", "09"
							, "10", "11", "12", "13", "14", "15", "16", "17", "18", "19"
							, "20", "21", "22", "23", "24", "25", "26", "27", "28", "29"
							, "30", "31", "32", "33", "34", "35", "36", "37", "38", "39"
							, "40", "41", "42", "43", "44", "45", "46", "47", "48", "49"
							, "50", "51", "52", "53", "54", "55", "56", "57", "58", "59"
							, "60", "61", "62", "63", "64", "65", "66", "67", "68", "69"
							, "70", "71", "72", "73", "74", "75", "76", "77", "78", "79"
							, "80", "81", "82", "83", "84", "85", "86", "87", "88", "89"
							, "90", "91", "92", "93", "94", "95", "96", "97", "98", "99");

	$swABC;

	$chkNum = "0123456789";

	$charSum = 0;

	$cntNum = 0;
    if ($CodeABC == CodeSet128::AUTO)
    {
		for ($cntNum = 0; $cntNum < strlen($code) - 1; $cntNum++) {
			if (strpos($chkNum, substr($code, $cntNum, 1)) === false) {
				break;
			}
		}

		if ($cntNum < 3) {
			$swABC = getABC($code);
			if($swABC == 1)
				$charSum += 104;
			else
				$charSum += 103;
		} else {
			$swABC = 2;
			$charSum += 105;
		}
	}
	else if ($CodeABC == CodeSet128::CODE_A)
	{
		$swABC = 0;
		$charSum += 103;
	}
	else if ($CodeABC == CodeSet128::CODE_B)
	{
		$swABC = 1;
		$charSum += 104;
	}
	else //if ($CodeABC == CodeSet128::CODE_C)
	{
		$swABC = 2;
		$charSum += 105;
	}

	$cnt = 0;

	for ($i = 0; $i < strlen($code); $i++) {
		if ($CodeABC == CodeSet128::AUTO)
		{
			if ($swABC == 0) {
				if (isParseAbleInteger($code, $i, $i + 4)) {
					$swABC = 2;
					$charSum += 99 * ++$cnt;
				} elseif (strpos($only_codeB, substr($code, $i, 1)) !== false) {
					$swABC = 1;
					$charSum += 100 * ++$cnt;
				}
			} elseif ($swABC == 1) {
				if (isParseAbleInteger($code, $i, $i + 4)) {
					$swABC = 2;
					$charSum += 99 * ++$cnt;
				} elseif (strpos($escString, substr($code, $i, 1)) !== false) {
					$swABC = 0;
					$charSum += 101 * ++$cnt;
				}
			} else {
				try {
					$cc = substr($code, $i, 2);
					if(strlen($cc) == 1)
					{
						$swABC = getABC(substr($code, $i));
						if($swABC == 1)
							$charSum += 100 * ++$cnt;
						else
							$charSum += 101 * ++$cnt;
					}
					else
					{
						$j;
						for ($j = 0; $j <= 99; $j++) {
							//今回修正箇所(2022.10.16)
							//if ($cc == $codeC[$j]) {
							if (strcmp($cc, $codeC[$j]) == 0) {
								break;
							}
						}
						if ($j >= 100) {
							$swABC = getABC(substr($code, $i));
							if($swABC == 1)
								$charSum += 100 * ++$cnt;
							else
								$charSum += 101 * ++$cnt;
						}
					}
				} catch (Exception $ex) {
					$swABC = getABC(substr($code, $i));
					if($swABC == 1)
						$charSum += 100 * ++$cnt;
					else
						$charSum += 101 * ++$cnt;
				}
			}

		}

		$c;
		$idx;
		if ($CodeABC == CodeSet128::CODE_A)
		{
			$c = substr($code, $i, 1);
			$idx = strpos($codeA, $c);
		}
		else if ($CodeABC == CodeSet128::CODE_B)
		{
			$c = substr($code, $i, 1);
			$idx = strpos($codeB, $c);
		}
		else if ($CodeABC == CodeSet128::AUTO && $swABC != 2)
		{
			$c = substr($code, $i, 1);
			if($swABC == 0)
				$idx = strpos($codeA, $c);
			else
				$idx = strpos($codeB, $c);

		} else {
			$c = substr($code, $i, 2);
			if(strlen($c) == 1) $c = $c."0";

			for ($idx = 0; $idx <= 99; $idx++) {
				//今回修正箇所(2020.11.24)
				if (strcmp($c, $codeC[$idx]) == 0) {
					break;
				}
			}
			$i++;
		}

		$charSum += $idx * ++$cnt;

		$aaa = 1;
	}

	return (int) ($charSum % 103);
}

/**
	* モジュラス103 ウエイト1(UCC/EAN128用) のチェックデジットの計算
	* @param String data チェックデジットを除いた文字列
	* @return チェックデジット
	*/
function modulus103W1EAN128($code, $CodeABC) {
	
	$escString = "\x00\x01\x02\x03\x04\x05\x06\x07\x08\x09\x0A\x0B\x0C\x0D\x0E\x0F\x10\x12\x13\x14\x15\x16\x17\x18\x19\x1A\x1B\x1C\x1D\x1E\x1F";
	$only_codeB = "`abcdefghijklmnopqrstuvwxyz{|}~\x7F";  
	$codeA = " !\"#$%&'()*+,-./0123456789:;<=>?@ABCDEFGHIJKLMNOPQRSTUVWXYZ[\\]^_\x00\x01\x02\x03\x04\x05\x06\x07\x08\x09\x0A\x0B\x0C\x0D\x0E\x0F\x10\x12\x13\x14\x15\x16\x17\x18\x19\x1A\x1B\x1C\x1D\x1E\x1F";
	$codeB = " !\"#$%&'()*+,-./0123456789:;<=>?@ABCDEFGHIJKLMNOPQRSTUVWXYZ[\\]^_`abcdefghijklmnopqrstuvwxyz{|}~\x7F";
	$codeC = array("00", "01", "02", "03", "04", "05", "06", "07", "08", "09"
							, "10", "11", "12", "13", "14", "15", "16", "17", "18", "19"
							, "20", "21", "22", "23", "24", "25", "26", "27", "28", "29"
							, "30", "31", "32", "33", "34", "35", "36", "37", "38", "39"
							, "40", "41", "42", "43", "44", "45", "46", "47", "48", "49"
							, "50", "51", "52", "53", "54", "55", "56", "57", "58", "59"
							, "60", "61", "62", "63", "64", "65", "66", "67", "68", "69"
							, "70", "71", "72", "73", "74", "75", "76", "77", "78", "79"
							, "80", "81", "82", "83", "84", "85", "86", "87", "88", "89"
							, "90", "91", "92", "93", "94", "95", "96", "97", "98", "99");

	$swABC;

	$chkNum = "0123456789";

	$charSum = 0;
	$cntNum = 0;
	$cntF = 0;
	$cntAI = 0;
	if ($CodeABC == CodeSet128::AUTO)
	{
		for($cntNum=0; $cntNum+$cntF+$cntAI < strlen($code) -1; $cntNum++)
		{
			if((strlen($code) >= $cntNum+$cntF+6) && (substr($code, $cntNum+$cntF,6)=="{FNC1}"))
			{
				$cntF += (strlen("{FNC1}") - 1);
					//continue;
			}
			if((strlen($code) >= $cntNum+$cntAI+4) && (substr($code, $cntNum+$cntAI,4)=="{AI}"))
			{
				$cntAI += (strlen("{AI}") - 1);
				//continue;
			}
			if(strpos($chkNum, substr($code, 1+$cntNum+$cntF+$cntAI,1)) === false)
			{
				break;
			}
		}

		if ($cntNum < 3) {
			$swABC = getABC($code);
			if($swABC == 1)
				$charSum += 104;
			else
				$charSum += 103;
		} else {
			$swABC = 2;
			$charSum += 105;
		}
	}
	else if ($CodeABC == CodeSet128::CODE_A)
	{
		$swABC = 0;
		$charSum += 103;
	}
	else if ($CodeABC == CodeSet128::CODE_B)
	{
		$swABC = 1;
		$charSum += 104;
	}
	else //if ($CodeABC == CodeSet128::CODE_C)
	{
		$swABC = 2;
		$charSum += 105;
	}

	$cnt = 0;

	for ($i = 0; $i < strlen($code); $i++) {
		if ($CodeABC == CodeSet128::AUTO)
		{
			if ($swABC == 0) {
				if (isParseAbleInteger($code, $i, $i + 4)) {
					$swABC = 2;
					$charSum += 99 * ++$cnt;
				} elseif (strpos($only_codeB, substr($code, $i, 1)) !== false) {
					$swABC = 1;
					$charSum += 100 * ++$cnt;
				}
			} elseif ($swABC == 1) {
				if (isParseAbleInteger($code, $i, $i + 4)) {
					$swABC = 2;
					$charSum += 99 * ++$cnt;
				} elseif (strpos($escString, substr($code, $i, 1)) !== false) {
					$swABC = 0;
					$charSum += 101 * ++$cnt;
				}
			} else {
				try {
					$cc = substr($code, $i, 2);
					if(strlen($cc) == 1)
					{
						$swABC = getABC(substr($code, $i));
						if($swABC == 1)
							$charSum += 100 * ++$cnt;
						else
							$charSum += 101 * ++$cnt;
					}
					else
					{
						if ($cc != "{F" && $cc != "{A") {
							$j;
							for ($j = 0; $j <= 99; $j++) {
								//今回修正箇所(2022.10.16)
								//if ($cc == $codeC[$j]) {
								if (strcmp($cc, $codeC[$j]) == 0) {
									break;
								}
							}
							if ($j >= 100) {
								$swABC = getABC(substr($code, $i));
								if($swABC == 1)
									$charSum += 100 * ++$cnt;
								else
									$charSum += 101 * ++$cnt;
							}
						}
					}
				} catch (Exception $ex) {
					$swABC = getABC(substr($code, $i));
					if($swABC == 1)
						$charSum += 100 * ++$cnt;
					else
						$charSum += 101 * ++$cnt;
				}

			}

		}

		$c;
		$idx;
		if(strlen($code) >= $i+6 && substr($code, $i,6)=="{FNC1}")
		{
			$idx = 102;
			$i += 5;
		}
		else if (strlen($code) >= $i+4 && substr($code, $i,4)=="{AI}")
		{
			$idx = -1;
			$i += 3;
		}
		else if ($CodeABC == CodeSet128::CODE_A)
		{
			$c = substr($code, $i, 1);
			$idx = strpos($codeA, $c);
		}
		else if ($CodeABC == CodeSet128::CODE_B)
		{
			$c = substr($code, $i, 1);
			$idx = strpos($codeB, $c);
		}
		else if ($CodeABC == CodeSet128::AUTO && $swABC != 2)
		{
			$c = substr($code, $i, 1);
			if($swABC == 0)
				$idx = strpos($codeA, $c);
			else
				$idx = strpos($codeB, $c);

		} else {
			$c = substr($code, $i, 2);
			if(strlen($c) == 1) $c = $c."0";

			for ($idx = 0; $idx <= 99; $idx++) {
				//今回修正箇所(2020.11.24)
				if (strcmp($c, $codeC[$idx]) == 0) {
					break;
				}
			}
			$i++;
		}

		if($idx != -1) $charSum += $idx * ++$cnt;

	}
	return (int) ($charSum % 103);
}
	
/**p
* モモジュラス47　計算　CODE97
* @param 対象値
* @param 重み
* @return チェックデジット
* @throws illegal character in string !
*/
function getModulus47($value, $weight) {
	
	$modulus47StrList = "0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ-. $/+%<>?!";
	
	if (!preg_match('/^[A-Z|0-9|\-|.| |$|\/|+|%|<|>|!|?]+$/', $value))
    {
		throw new Exception('illegal character in string !');
    }

    $x = 0;
    $ii = 0;
    for ($i = strlen($value) - 1; $i >= 0; $i--)
    {
        $ii++;
        if ($ii > $weight) $ii = 1;
		$x += strpos($modulus47StrList, substr($value, $i, 1));
    }
		
	return substr($modulus47StrList, $x % 47, 1);

	
}

function getABC($code) {

	$escString = "\x00\x01\x02\x03\x04\x05\x06\x07\x08\x09\x0A\x0B\x0C\x0D\x0E\x0F\x10\x12\x13\x14\x15\x16\x17\x18\x19\x1A\x1B\x1C\x1D\x1E\x1F";
	$only_codeB = "`abcdefghijklmnopqrstuvwxyz{|}~\x7F";  

	$code2 = str_replace("{FNC1}", "", $code);
	$code2 = str_replace("{AI}", "", $code2);

	$swABC = 1;

	for ($i = 0; $i < strlen($code2); $i++)
	{
		if (strpos($only_codeB, substr($code2, $i, 1)) !== false)
		{
			$swABC = 1;
			break;
		}

		if (strpos($escString, substr($code2, $i, 1)) !== false)
		{
			$swABC = 0;
			break;
		}
	}

	return $swABC;

}

