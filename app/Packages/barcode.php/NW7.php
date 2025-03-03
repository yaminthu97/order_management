<?php

	/**
	 * NW-7作成クラス
	 */
	class NW7 {

		/*! 添字(バーコードの下の文字)を描画する・しない */
		var $TextWrite = true;

		/*! 添字(バーコードの下の文字)のフォントファイル名 */
		var $FontName = "./font/mplus-1p-black.ttf";

		/*! 添字のフォントサイズ */
		var $FontSize = 10;

		/*! スタート／ストップコード表示する・しない */
		var $dispStartStopCode = false;

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

			$code;
			$startCode;

			$s = strtoupper($code);
			$sS = substr($s, 0, 1);
			$eS = substr($s, strlen($s) - 1, 1);

			if (($sS == "A" || $sS == "B" || $sS == "C" || $sS == "D")
			 && ($eS == "A" || $eS == "B" || $eS == "C" || $eS == "D")) {
				$code = $s;
				$startCode = $sS;
			 } else if (($sS == "A" || $sS == "B" || $sS == "C" || $sS == "D")
			         || ($eS == "A" || $eS == "B" || $eS == "C" || $eS == "D")) {
				trigger_error("NW7 : bat character in code string. safe character is [ABCD.+:/$-0123456789]",E_USER_ERROR);
				exit;
			} else {
				$code = "A" . $s . "A";
				$startCode = "A";
			}
			
			$x0 = $minWidthDot;
			$x1 = $minWidthDot * 2.5;
			if($minWidthDot % 2 != 0) {
				$x1 = $minWidthDot * 3;
			}

			$dot = array($x0, $x1);

			$asc = "ABCD.+:/$-0123456789";
			$ptn = array(0x1a, 0x29, 0x0b, 0x0e, 0x54, 0x15, 0x45, 0x51, 0x18, 0x0c, 0x03, 0x06, 0x09, 0x60, 0x12, 0x42, 0x21, 0x24, 0x30, 0x48);

			$posStart = 0;
			$posStop = strlen($code);
			if (!$this->dispStartStopCode) {
				$posStart = 1;
				$posStop = strlen($code) - 1;
			}

			$gazouHeight = $height;
			if($this->TextWrite == true)
			{
				$gazouHeight = $height + $this->FontSize + 3;
			}

			$xPos = 0;
			for ($i = 0; $i < strlen($code); $i++) {
				$c = substr($code, $i, 1);
				$p = strpos($asc, $c);
				if ($p === false) {
					trigger_error("NW7 : bat character in code string. ('".$c."') safe character is [ABCD.+:/$-0123456789]",E_USER_ERROR);
					exit;
				}
				$chk = 0x40;
				for ($j = 0; $j < 7; $j++) {
					$x0or1 = 0;
					if (($ptn[$p] & ($chk >> $j)) != 0) {
						$x0or1 = 1; //太バー
					}
					$xPos += $dot[$x0or1];
				}
				$xPos += $x0 * 2; //キャラクタ間ギャップ
			}
			$img = ImageCreate($xPos, $gazouHeight);
			$white = ImageColorAllocate($img, 0xFF, 0xFF, 0xFF);
			$black = ImageColorAllocate($img, 0x00, 0x00, 0x00);

			imagesetthickness($img, $this->BarThick);

			$xPos = 0;
			for ($i = 0; $i < strlen($code); $i++) {
				//添字の描画
				if($this->TextWrite && ($i >= $posStart && $i < $posStop)) {
					ImageTTFText($img, $this->FontSize, 0, $xPos, (int)($gazouHeight + cmTo(0.01))
					    	,$black, $this->FontName, substr($code, $i, 1));
				}
	
				$c = substr($code, $i, 1);
				$p = strpos($asc, $c);
				if ($p === false) {
					trigger_error("NW7 : bat character in code string. ('".$c."') safe character is [ABCD.+:/$-0123456789]",E_USER_ERROR);
					exit;
				}
	
				$chk = 0x40;
				for ($j = 0; $j < 7; $j++) {
					$x0or1 = 0;
					if (($ptn[$p] & ($chk >> $j)) != 0) {
						$x0or1 = 1; //太バー
					}
	
					if ($j % 2 == 0) { //黒バー
						imagefilledrectangle($img, $xPos, 0, $xPos+$dot[$x0or1]-1 + $this->KuroBarCousei,  $height, $black);
					}
					$xPos += $dot[$x0or1];
				}
				$xPos += $x0 * 2; //キャラクタ間ギャップ
			}

			if($this->TextWrite) {
				if ($dispStartStopCode) {
					$interval = ($xPos - pointTo($this->FontSize) - cmTo(0.1)) / (strlen($code) - 1);
					for ($i = 0; $i < strlen($code); $i++) {
						//						ImageTTFText($img, $this->FontSize, 0, (int)($i * $interval) + cmTo(0.05), $gazouHeight + cmTo(0.01)
//					    	,$black, $this->FontName, substr($code, $i, 1));
					}
				} else {
					$interval = ($xPos - pointTo($this->FontSize) - cmTo(0.1)) / (strlen($code) - 2 - 1);
					for ($i = 0; $i < strlen($code) - 1; $i++) {
						//						ImageTTFText($img, $this->FontSize, 0, (int)(($i - 1) * $interval) + cmTo(0.05), $gazouHeight + cmTo(0.01)
//					    	,$black, $this->FontName, substr($code, $i, 1));
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

