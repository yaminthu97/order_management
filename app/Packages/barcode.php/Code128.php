<?php

require_once("Common.php");
require_once("CheckDigit.php");
require_once("Error.php");

	/**
	 * Code128作成クラス
	 */
	class Code128 {

		/*! 添字(バーコードの下の文字)を描画する・しない */
		var $TextWrite = true;

		/*! 添字(バーコードの下の文字)のフォントファイル名 */
		var $FontName = "./font/mplus-1p-black.ttf";

		/*! 添字のフォントサイズ */
		var $FontSize = 10;

		/*! バー厚み */
		var $BarThick = 1;

		/*! 黒バーの太さ調整ドット数 */
		var $KuroBarCousei = 0;
	
		/*! CODE128のコードセット(AUTO, A, B, C) */
		var $CodeABC = CodeSet128::AUTO;
	
	/**
		 * バーコードの描画を行います。バーコード全体の幅を指定するのではなく、バーを描画する最小単位のドット数を指定します。
		 * @param $code 描画を行うバーコードのコード(テキスト)
		 * @param $minWidthDot 横方向の最少描画ドット数
		 * @param $height バーコードのバーの高さ(単位：ドット)
		 * @return バーコードのイメージを返します。
		 */
		function draw($code, $minWidthDot, $height) {
			
			global $TextWrite, $FontName, $FontSize, $CodeABC;

			//$code = "*".strtoupper($code)."*";

			$x0 = $minWidthDot;
			$x1 = $minWidthDot * 2.5;
			if($minWidthDot % 2 != 0) {
				$x1 = $minWidthDot * 3;
			}

			$dot = array($x0, $x1);
			$asc = "1234567890ABCDEFGHIJKLMNOPQRSTUVWXYZ-. *$/+%";

			// CODE A
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

			$ptn =  array("212222", "222122", "222221", "121223", "121322", "131222", "122213", "122312", "132212", "221213"
					   	, "221312", "231212", "112232", "122132", "122231", "113222", "123122", "123221", "223211", "221132"
					    , "221231", "213212", "223112", "312131", "311222", "321122", "321221", "312212", "322112", "322211"
					   , "212123", "212321", "232121", "111323", "131123", "131321", "112313", "132113", "132311", "211313"
					   , "231113", "231311", "112133", "112331", "132131", "113123", "113321", "133121", "313121", "211331"
					   , "231131", "213113", "213311", "213131", "311123", "311321", "331121", "312113", "312311", "332111"
					   , "314111", "221411", "431111", "111224", "111422", "121124", "121421", "141122", "141221", "112214"
					   , "112412", "122114", "122411", "142112", "142211", "241211", "221114", "413111", "241112", "134111"
					   , "111242", "121142", "121241", "114212", "124112", "124211", "411212", "421112", "421211", "212141"
					   , "214121", "412121", "111143", "111341", "131141", "114113", "114311", "411113", "411311", "113141"
					   , "114131", "311141", "411131");



			$ptnBorC = array("311141", "114131", "113141");
			$ptnStart = array("211412", "211214", "211232");

				
				$ptnStop = "2331112";

				$swABC;

				$chkNum = "0123456789";

				$gazouHeight = $height;
				if($this->TextWrite == true)
				{
					$gazouHeight = $height + $this->FontSize + 3;
				}


			$cntNum = 0;


			

			if ($this->CodeABC == CodeSet128::AUTO)
			{
				for ($cntNum = 0; $cntNum < strlen($code) - 1; $cntNum++) {
					if (strpos($chkNum, substr($code, $cntNum, 1)) === false) {
						break;
					}
				}

				if ($cntNum < 3) {
					$swABC = getABC($code);
				} else {
					$swABC = 2;
				}
			}
		else if ($this->CodeABC == CodeSet128::CODE_A)
			{
				$swABC = 0;
			}
			else if ($this->CodeABC == CodeSet128::CODE_B)
			{
				$swABC = 1;
			}
			else //if ($this->CodeABC == CodeSet128::CODE_C)
			{
				$swABC = 2;
			}



			$xPos = 0;
			//スタートコード
			for ($j = 0; $j < 6; $j++) {
				for ($l = 0; $l < (int)substr($ptnStart[$swABC], $j, 1); $l++) {
					$xPos += $dot[0];
				}
			}

			//コード
			$svXPos = 0;
			for ($i = 0; $i < strlen($code); $i++) {
				$chgFlg = false;
				if ($this->CodeABC == CodeSet128::AUTO)
				{
					if ($swABC == 0) {
						if (isParseAbleInteger($code, $i, $i + 4)) {
							$swABC = 2;
							$chgFlg = true;
						} elseif (strpos($only_codeB, substr($code, $i, 1)) !== false) {
							$swABC = 1;
							$chgFlg = true;
						}
					} elseif ($swABC == 1) {
						if (isParseAbleInteger($code, $i, $i + 4)) {
							$swABC = 2;
							$chgFlg = true;
						} elseif (strpos($escString, substr($code, $i, 1)) !== false) {
							$swABC = 0;
							$chgFlg = true;
						}
					} else {
						try {
							$cc = substr($code, $i, 2);
							if(strlen($cc) == 1)
							{
								$swABC = getABC(substr($code, $i));
								$chgFlg = true;
							}
							else
							{
								$j = 0;
								for (; $j <= 99; $j++) {
									//今回修正箇所(2020.11.24)
									if (strcmp($cc, $codeC[$j]) == 0) {
										break;
									}
								}
								if ($j >= 100) {
									$swABC = getABC(substr($code, $i));
									$chgFlg = true;
								}
							}
						} catch (Exception $ex) {
							$swABC = getABC(substr($code, $i));
							$chgFlg = true;
						}
					}

					if ($chgFlg) {
						//Codeチェンジ
						for ($j = 0; $j < 6; $j++) {
							for ($l = 0; $l < (int)substr($ptnBorC[$swABC], $j, 1); $l++) {
								$xPos += $dot[0];
							}
						}
					}
				}
				
				//添字の描画
				if($this->TextWrite) {
					//					ImageTTFText($img, $this->FontSize, 0, $xPos, $gazouHeight + cmTo(0.01)
					//						    	,$black, $this->FontName, substr($code, $i, 1));
					$svXPos = $xPos;
				}

				$c;
				$idx;
				if ($this->CodeABC == CodeSet128::CODE_A)
				{
					$c = substr($code, $i, 1);
					$idx = strpos($codeA, $c);
				}
				else if ($this->CodeABC == CodeSet128::CODE_B)
				{
					$c = substr($code, $i, 1);
					$idx = strpos($codeB, $c);
				}
				else if ($this->CodeABC == CodeSet128::AUTO && $swABC != 2)
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
						//if (strcmp($c, $idx) == 0) {
						// 新たに修正 2024/03/19
						if (strcmp($c, sprintf("%02d", $idx)) == 0) {
							break;
						}
					}
					$i++;
				}

				if($idx === false)
					trigger_error("Code128 : bat character in code string. ('".$c."') ASCII-CODE(0x".dechex(ord($c)).") ",E_USER_ERROR);

					//throw new Code128_BadChar($c);

				for ($j = 0; $j < 6; $j++) {
					for ($l = 0; $l < (int)substr($ptn[$idx], $j, 1); $l++) {
						$xPos += $dot[0];
					}
				}

			}

			//チェックディジット
			$bIdx = modulus103W1($code, $this->CodeABC);
			for ($j = 0; $j < 6; $j++) {
				for ($l = 0; $l < (int)substr($ptn[$bIdx], $j, 1); $l++) {
					$xPos += $dot[0];
				}
			}

			//ストップコード
			for ($j = 0; $j < 7; $j++) {
				for ($l = 0; $l < (int)substr($ptnStop, $j, 1); $l++) {
					$xPos += $dot[0];
				}
			}





			$img = ImageCreate($xPos, $gazouHeight);
			$white = ImageColorAllocate($img, 0xFF, 0xFF, 0xFF);
			$black = ImageColorAllocate($img, 0x00, 0x00, 0x00);

			imagesetthickness($img, $this->BarThick);

			$cntNum = 0;
			for ($cntNum = 0; $cntNum < strlen($code) - 1; $cntNum++) {
				if (strpos($chkNum, substr($code, $cntNum, 1)) === false) {
					break;
				}
			}


			if ($this->CodeABC == CodeSet128::AUTO)
			{

				if ($cntNum < 3) {
					if ($cntNum < 3) {
						$swABC = getABC($code);
					} else {
						$swABC = 2;
					}
					} else {
					$swABC = 2;
				}

			}
			
			$xPos = 0;
			//スタートコード
			for ($j = 0; $j < 6; $j++) {
				$l = (int)substr($ptnStart[$swABC], $j, 1);
				if ($j % 2 == 0) { //黒バー
				imagefilledrectangle($img, $xPos, 0, $xPos+($dot[0] * $l)-1 + $this->KuroBarCousei,  $height, $black);
				}
				$xPos += $dot[0] * $l;
			}

			//コード
			$svXPos = 0;
			for ($i = 0; $i < strlen($code); $i++) {
				$chgFlg = false;
				if ($this->CodeABC == CodeSet128::AUTO)
				{
					if ($swABC == 0) {
						if (isParseAbleInteger($code, $i, $i + 4)) {
							$swABC = 2;
							$chgFlg = true;
						} elseif (strpos($only_codeB, substr($code, $i, 1)) !== false) {
							$swABC = 1;
							$chgFlg = true;
						}
					} elseif ($swABC == 1) {
						if (isParseAbleInteger($code, $i, $i + 4)) {
							$swABC = 2;
							$chgFlg = true;
						} elseif (strpos($escString, substr($code, $i, 1)) !== false) {
							$swABC = 0;
							$chgFlg = true;
						}
					} else {
						try {
							$cc = substr($code, $i, 2);
							if(strlen($cc) == 1)
							{
								$swABC = getABC(substr($code, $i));
								$chgFlg = true;
							}
							else
							{
								for ($j = 0; $j <= 99; $j++) {
									//今回修正箇所(2020.11.24)
									if (strcmp($cc, $codeC[$j]) == 0) {
										break;
									}
								}
								if ($j >= 100) {
									$swABC = getABC(substr($code, $i));
									$chgFlg = true;
								}
							}
						} catch (Exception $ex) {
							$swABC = getABC(substr($code, $i));
							$chgFlg = true;
						}
					}

					if ($chgFlg) {
						//Codeチェンジ
						for ($j = 0; $j < 6; $j++) {
							$l = (int)substr($ptnBorC[$swABC], $j, 1);
							if ($j % 2 == 0) { //黒バー
								imagefilledrectangle($img, $xPos, 0, $xPos+($dot[0] * $l)-1 + $this->KuroBarCousei,  $height, $black);
							}
							$xPos += $dot[0]  * $l;
						}
					}
				}

				//添字の描画
				if($this->TextWrite) {
				//					ImageTTFText($img, $this->FontSize, 0, $xPos, $gazouHeight + cmTo(0.01)
	//						    	,$black, $this->FontName, substr($code, $i, 1));
					$svXPos = $xPos;
				}
					
				$c;	
				$idx;
				if ($this->CodeABC == CodeSet128::CODE_A)
				{
					$c = substr($code, $i, 1);
					$idx = strpos($codeA, $c);
				}
				else if ($this->CodeABC == CodeSet128::CODE_B)
				{
					$c = substr($code, $i, 1);
					$idx = strpos($codeB, $c);
				}
				else if ($this->CodeABC == CodeSet128::AUTO && $swABC != 2)
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
						//if (strcmp($c, $idx) == 0) {
						// 新たに修正 2024/03/19
						if (strcmp($c, sprintf("%02d", $idx)) == 0) {
							break;
						}
					}
					$i++;
				}


				for ($j = 0; $j < 6; $j++) {
					$l = (int)substr($ptn[$idx], $j, 1);
					if ($j % 2 == 0) { //黒バー
					imagefilledrectangle($img, $xPos, 0, $xPos+($dot[0] * $l)-1 + $this->KuroBarCousei,  $height, $black);
					}
					$xPos += $dot[0] * $l;
				}

			}

			//チェックディジット
			for ($j = 0; $j < 6; $j++) {
				$l = (int)substr($ptn[$bIdx], $j, 1);
				if ($j % 2 == 0) { //黒バー
				imagefilledrectangle($img, $xPos, 0, $xPos+($dot[0] * $l)-1 + $this->KuroBarCousei,  $height, $black);
				}
				$xPos += $dot[0]  * $l;
			}

			//ストップコード
			for ($j = 0; $j < 7; $j++) {
				$l = (int)substr($ptnStop, $j, 1);
				if ($j % 2 == 0) { //黒バー
					imagefilledrectangle($img, $xPos, 0, $xPos+($dot[0] * $l)-1 + $this->KuroBarCousei,  $height, $black);
				}
				$xPos += $dot[0]  * $l;
			}

			if($this->TextWrite) {
				if(strlen($code) == 1)
					$interval = ($xPos - $this->FontSize);
				else
					$interval = ($xPos - $this->FontSize) / (strlen($code) - 1);
				for($i = 0; $i < strlen($code); $i++) {
				ImageTTFText($img, $this->FontSize, 0, (int)($i * $interval), $gazouHeight
								,$black, $this->FontName, substr($code, $i, 1));
				}
			}

			if(!isOK()) {
				//SAMPLE 描画
				$red = ImageColorAllocate($img, 0xFF, 0x00, 0x00);
				ImageTTFText($img, 12, 0, 2, 14	,$red, $this->FontName, "SAMPLE");
			}
			return $img;
		}

	}
?>
