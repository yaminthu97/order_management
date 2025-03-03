<?php

	/**
	 * NEC2of5作成クラス
	 */
	class NEC2of5 {

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
		 * バーコードの描画を行います。バーコード全体の幅を指定するのではなく、バーを描画する横方向の最小単位のドット数を指定します。(1～)
		 * @param $code 描画を行うバーコードのコード(テキスト)
		 * @param $minWidthDot 横方向の最少描画ドット数
		 * @param $height バーコードのバーの高さ(単位：ドット)
		 * @return バーコードのイメージを返します。
		 */
		function draw($code, $minWidthDot, $height) {

			global $TextWrite, $FontName, $FontSize;

			$x0 = $minWidthDot;
			$x1 = $minWidthDot * 2.5;
			if($minWidthDot % 2 != 0) {
				$x1 = $minWidthDot * 3;
			}

			$dot = array($x0, $x1);
			$asc = "0123456789";
			$ptn = array(0x18, 0x03, 0x05, 0x06, 0x09, 0x0a, 0x0c, 0x11, 0x12, 0x14);
			$ptnStart = 0x5;
			$ptnStop = 0x3;

			$gazouHeight = $height;
			if($this->TextWrite == true)
			{
				$gazouHeight = $height + $this->FontSize + 3;
			}

			$xPos = 0;
			$chk = 0x4;
			for ($j = 0; $j < 3; $j++) {
				$x0or1 = 0;
				if (($ptnStart & ($chk >> $j)) != 0) {
					$x0or1 = 1; //太バー
				}
				$xPos += $dot[$x0or1];
			}
			$xPos += $x0; //キャラクタ間ギャップ
	
			//データ部
			for ($i = 0; $i < strlen($code); $i++)	{
				$c = substr($code, $i, 1);
				$p = strpos($asc, $c);
				$chk = 0x10;
				for ($j = 0; $j < 5; $j++) {
					$x0or1 = 0;
					if (($ptn[$p] & ($chk >> $j)) != 0) {
						$x0or1 = 1; //太バー
					}
					$xPos += $dot[$x0or1];
				}
				$xPos += $x0; //キャラクタ間ギャップ
			}
	
			//ストップコード
			$chk = 0x4;
			for ($j = 0; $j < 3; $j++) {
				$x0or1 = 0;
				if (($ptnStop & ($chk >> $j)) != 0) {
					$x0or1 = 1; //太バー
				}
				$xPos += $dot[$x0or1];
			}
			$img = ImageCreate($xPos, $gazouHeight);
			$white = ImageColorAllocate($img, 0xFF, 0xFF, 0xFF);
			$black = ImageColorAllocate($img, 0x00, 0x00, 0x00);
			
			imagesetthickness($img, $this->BarThick);

			$xPos = 0;
			//スタートコード
			$chk = 0x4;
			for ($j = 0; $j < 3; $j++)	{
				$x0or1 = 0;
				if (($ptnStart & ($chk >> $j)) != 0) {
					$x0or1 = 1; //太バー
				}
	
				if ($j % 2 == 0) { //黒バー
				imagefilledrectangle($img, $xPos, 0, $xPos+$dot[$x0or1]-1 + $this->KuroBarCousei,  $height, $black);
				}
				$xPos += $dot[$x0or1];
			}
			$xPos += $x0; //キャラクタ間ギャップ
						
			//データ部
			for ($i = 0; $i < strlen($code); $i++)	{
				//添字の描画
				if($this->TextWrite) {
				ImageTTFText($img, $this->FontSize, 0, $xPos, (int)($gazouHeight + cmTo(0.01))
					    	,$black, $this->FontName, substr($code, $i, 1));
				}
				$c = substr($code, $i, 1);
				$p = strpos($asc, $c);
				if ($p === false) {
					trigger_error("NEC2of5 : not number in code string.",E_USER_ERROR);
					exit;
				}
	
				$chk = 0x10;
				for ($j = 0; $j < 5; $j++) {
					$x0or1 = 0;
					if (($ptn[$p] & ($chk >> $j)) != 0) {
						$x0or1 = 1; //太バー
					}
	
					if ($j % 2 == 0) { //黒バー
					imagefilledrectangle($img, $xPos, 0, $xPos+$dot[$x0or1]-1 + $this->KuroBarCousei,  $height, $black);
					}
					$xPos += $dot[$x0or1];
				}
				$xPos += $x0; //キャラクタ間ギャップ
			}
	
			//ストップコード
			$chk = 0x4;
			for ($j = 0; $j < 3; $j++) {
				$x0or1 = 0;
				if (($ptnStop & ($chk >> $j)) != 0) {
					$x0or1 = 1; //太バー
				}
				
				if ($j % 2 == 0) { //黒バー
				imagefilledrectangle($img, $xPos, 0, $xPos+$dot[$x0or1]-1 + $this->KuroBarCousei,  $height, $black);
				}
				$xPos += $dot[$x0or1];
			}
	
			if($this->TextWrite) {
				$interval = ($xPos - pointTo($this->FontSize) - cmTo(0.1)) / (strlen($code) - 1);
				for ($i = 0; $i < strlen($code); $i++) {
				//						ImageTTFText($img, $this->FontSize, 0, (int)($i * $interval) + cmTo(0.05), $gazouHeight + cmTo(0.01)
//					    	,$black, $this->FontName, substr($code, $i, 1));
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

