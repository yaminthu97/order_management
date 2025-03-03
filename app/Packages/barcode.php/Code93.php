<?php

	require_once("CheckDigit.php");

	/**
	 * Code93作成クラス
	 */
	class Code93 {

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
	
	/**
		 * バーコードの描画を行います。バーコード全体の幅を指定するのではなく、バーを描画する最小単位のドット数を指定します。
		 * @param $code 描画を行うバーコードのコード(テキスト)
		 * @param $minWidthDot 横方向の最少描画ドット数
		 * @param $height バーコードのバーの高さ(単位：ドット)
		 * @return バーコードのイメージを返します。
		 */
		function draw($code, $minWidthDot, $height) {
			
			global $TextWrite, $FontName, $FontSize;

			//$code = "*".strtoupper($code)."*";

			$x0 = $minWidthDot;

			$dot = array($x0);
			$asc = "0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ-. $/+%<>?!";
			
			$ptn =  array("131112", "111213", "111312", "111411", "121113", "121212", "121311", "111114", "131211", "141111"
                                , "211113", "211212", "211311", "221112", "221211", "231111", "112113", "112212", "112311", "122112"
                                , "132111", "111123", "111222", "111321", "121122", "131121", "212112", "212211", "211122", "211221"
                                , "221121", "222111", "112122", "112221", "122121", "123111", "121131", "311112", "311211", "321111"
                                , "112131", "113121", "211131"
                                , "121221", "312111", "311121", "122211");


			$ptnStart = "111141";
			$ptnStop = "1111411";

			$chkNum = "0123456789";

			$gazouHeight = $height;
			if($this->TextWrite == true)
			{
				$gazouHeight = $height + $this->FontSize + 3;
			}




			$xPos = 0;

			//スタートコード
			$xPos += $dot[0] * 9;

			//データ
			for($i = 0; $i < strlen($code); $i++) {

				$c = substr($code, $i, 1);
				$p = strpos($asc, $c);

				$xPos += $dot[0] * 9;
			}

            // チェックディジット(CK)
			$xPos += $dot[0] * 9;
			$xPos += $dot[0] * 9;


            // ストップコード
			$xPos += $dot[0] * 10;



			$img = ImageCreate($xPos, $gazouHeight);
			$white = ImageColorAllocate($img, 0xFF, 0xFF, 0xFF);
			$black = ImageColorAllocate($img, 0x00, 0x00, 0x00);

			imagesetthickness($img, $this->BarThick);

			$xPos = 0;

			//スタートコード
			for ($j = 0; $j < 6; $j++) {
				$l = (int)substr($ptnStart, $j, 1);
				if ($j % 2 == 0) { //黒バー
					imagefilledrectangle($img, $xPos, 0, $xPos+($dot[0] * $l)-1 + $this->KuroBarCousei,  $height, $black);
				}
				$xPos += $dot[0] * $l;
			}
		
			// データ
			for($i = 0; $i < strlen($code); $i++) {

				$c = substr($code, $i, 1);
				$idx = strpos($asc, $c);

				for($j = 0; $j < 6; $j++)
				{
					$l = (int)substr($ptn[$idx], $j, 1);
					if ($j % 2 == 0) {
						imagefilledrectangle($img, $xPos, 0, $xPos+($dot[0] * $l)-1 + $this->KuroBarCousei,  $height, $black);
					}
					$xPos += $dot[0] * $l;
				}
			}


            // チェックディジット(C)
            $cdc = getModulus47($code, 15);
			$p = strpos($asc, $cdc);

			for($j = 0; $j < 6; $j++)
			{
				$l = (int)substr($ptn[$p], $j, 1);
				if ($j % 2 == 0) {
					imagefilledrectangle($img, $xPos, 0, $xPos+($dot[0] * $l)-1 + $this->KuroBarCousei,  $height, $black);
				}
				$xPos += $dot[0] * $l;
			}
			
            // チェックディジット(K)
            $cdc = getModulus47($code.$cdc, 20);
			$p = strpos($asc, $cdc);

			for($j = 0; $j < 6; $j++)
			{
				$l = (int)substr($ptn[$p], $j, 1);
				if ($j % 2 == 0) {
					imagefilledrectangle($img, $xPos, 0, $xPos+($dot[0] * $l)-1 + $this->KuroBarCousei,  $height, $black);
				}
				$xPos += $dot[0] * $l;
			}

			//ストップコード
			for ($j = 0; $j < 7; $j++) {
				$l = (int)substr($ptnStop, $j, 1);
				if ($j % 2 == 0) { //黒バー
					imagefilledrectangle($img, $xPos, 0, $xPos+($dot[0] * $l)-1 + $this->KuroBarCousei,  $height, $black);
				}
				$xPos += $dot[0] * $l;
			}

			// 添え字
			if($this->TextWrite) {
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
