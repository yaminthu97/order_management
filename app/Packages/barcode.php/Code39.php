<?php

	require_once("CheckDigit.php");

	/**
	 * Code39作成クラス
	 */
	class Code39 {


		/*! 添字(バーコードの下の文字)を描画する・しない */
		var $TextWrite = true;

		/*! 添字(バーコードの下の文字)のフォントファイル名 */
		var $FontName = "./font/mplus-1p-black.ttf";

		/*! 添字のフォントサイズ */
		var $FontSize = 10;

		/*! スタート／ストップコード表示する・しない */
		var $dispStartStopCode = true;

		/*! バー厚み */
		var $BarThick = 1;
	
		/*! 黒バーの太さ調整ドット数 */
		var $KuroBarCousei = 0;
	
		// スタート・ストップコードを含めたコード(テキスト)
		var $outputCode = "";


		/**
		 * バーコードの描画を行います。バーコード全体の幅を指定するのではなく、バーを描画する横方向の最小単位のドット数を指定します。(1～)
		 * @param $code 描画を行うバーコードのコード(テキスト)
		 * @param $minWidthDot 横方向の最少描画ドット数
		 * @param $height バーコードのバーの高さ(単位：ドット)
		 * @return バーコードのイメージを返します。
		 */
		function draw($code, $minWidthDot, $height) {

			global $TextWrite, $FontName, $FontSize, $dispStartStopCode, $outputCode;

			$code = "*".strtoupper($code)."*";

			$x0 = $minWidthDot;
			$x1 = $minWidthDot * 2.5;
			if($minWidthDot % 2 != 0) {
				$x1 = $minWidthDot * 3;
			}

			$dot = array($x0, $x1);
			$asc = "1234567890ABCDEFGHIJKLMNOPQRSTUVWXYZ-. *$/+%";
			$ptn = array(0x121, 0x061, 0x160, 0x031, 0x130, 0x070, 0x025,
					0x124, 0x064, 0x034, 0x109, 0x049, 0x148, 0x019, 0x118,
					0x058, 0x00d, 0x10c, 0x04c, 0x01c, 0x103, 0x043, 0x142,
					0x013, 0x112, 0x052, 0x007, 0x106, 0x046, 0x016, 0x181,
					0x0c1, 0x1c0, 0x091, 0x190, 0x0d0, 0x085, 0x184, 0x0c4,
					0x094, 0x0a8, 0x0a2, 0x08a, 0x02a);

			$xPos = 0;
			
			$gazouHeight = $height;
			if($this->TextWrite)
			{
				$gazouHeight = $height + $this->FontSize + 3;
			}
			
		for($i = 0; $i < strlen($code); $i++) {

				$c = substr($code, $i, 1);
				$p = strpos($asc, $c);

				if($p === false)
					trigger_error("Code39 : bat character in code string. ('".$c."') safe character is [1234567890ABCDEFGHIJKLMNOPQRSTUVWXYZ-. *$/+%\]",E_USER_ERROR);
					//throw new Code39_BadChar($c);

				$chk = 0x100;
				for($j = 0; $j < 9; $j++)
				{
					$x0or1 = 0;
					if(($ptn[$p] & ($chk >> $j)) != 0) {
						$x0or1 = 1;
					}
					$xPos += $dot[$x0or1];
				}
				$xPos += $x0 * 2;
			}

		$img = ImageCreate($xPos, $gazouHeight);
			$white = ImageColorAllocate($img, 0xFF, 0xFF, 0xFF);
			$black = ImageColorAllocate($img, 0x00, 0x00, 0x00);

			imagesetthickness($img, $this->BarThick);


			$xPos = 0;
		for($i = 0; $i < strlen($code); $i++) {

				$c = substr($code, $i, 1);
				$p = strpos($asc, $c);

				$chk = 0x100;
				for($j = 0; $j < 9; $j++)
				{
					$x0or1 = 0;
					if(($ptn[$p] & ($chk >> $j)) != 0) {
						$x0or1 = 1;
					}

					if ($j % 2 == 0) {
					imagefilledrectangle($img, $xPos, 0, $xPos+$dot[$x0or1]-1 + $this->KuroBarCousei,  $height, $black);
					}
					$xPos += $dot[$x0or1];
				}
				$xPos += $x0 * 2;
			}

			//添字の描画
			if($this->TextWrite) {
				if($this->dispStartStopCode) {
				$interval = ($xPos - $this->FontSize) / (strlen($code) - 1);
					for($i = 0; $i < strlen($code); $i++) {
						ImageTTFText($img, $this->FontSize, 0, (int)($i * $interval), $gazouHeight
								,$black, $this->FontName, substr($code, $i, 1));
					}
				} else {
				$interval = ($xPos - $this->FontSize) / (strlen($code) - 2 - 1);
					for($i = 0; $i < strlen($code) ; $i++) {
					ImageTTFText($img, $this->FontSize, 0, (int)($i * $interval), $gazouHeight
					    	,$black, $this->FontName, substr($code, ($i+1), 1));
					}
				}
			}

			$this->outputCode = $code;
			

			if(!isOK()) {
				//SAMPLE 描画
				$red = ImageColorAllocate($img, 0xFF, 0x00, 0x00);
				ImageTTFText($img, 12, 0, 2, 14	,$red, $this->FontName, "SAMPLE");
			}

			return $img;
		}

	}

