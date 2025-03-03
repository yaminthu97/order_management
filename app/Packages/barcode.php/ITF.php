<?php

	require_once("Common.php");

	/**
	 * ITF作成クラス
	 */
	class ITF {

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

		if(preg_match ("/^[0-9]+$/",$code) == false)
			{
				trigger_error("ITF : not number in code string.",E_USER_ERROR);
				exit;
			}


			if (strlen($code) % 2 != 0) {
				$code = "0".$code;
			}

			//x0:細バー x1:太バー
			$x0 = $minWidthDot;
			$x1 = $minWidthDot * 2.5;
			if($minWidthDot % 2 != 0) {
				$x1 = $minWidthDot * 3;
			}
				
			$dot = array($x0, $x1);
				
			$xPos = 0;
			$xPos += $dot[0]*(4+strlen($code)*10+3);
	
			$gazouHeight = $height;
			if($this->TextWrite == true)
			{
				$gazouHeight = $height + $this->FontSize + 3;
			}
		
			$img = ImageCreate($xPos, $gazouHeight);
			$white = ImageColorAllocate($img, 0xFF, 0xFF, 0xFF);
			$black = ImageColorAllocate($img, 0x00, 0x00, 0x00);

			imagesetthickness($img, $this->BarThick);
		
			$asc = "0123456789";
			$ptn = array(0x06, 0x11, 0x09, 0x18, 0x05, 0x14, 0x0c, 0x03, 0x12, 0x0a);

		
			$xPos = 0;
			for ($j = 0; $j < 4; $j++) {
				$x0or1 = 0;
				if ($j % 2 == 0) { //黒バー
				imagefilledrectangle($img, $xPos, 0, $xPos+$dot[$x0or1]-1 + $this->KuroBarCousei,  $height, $black);
				}
				$xPos += $dot[$x0or1];
			}
	
	
			$svXPos = 0;
			for ($i = 0; $i < strlen($code); $i += 2) {
				//添字の描画
				if($this->TextWrite) {
					if ($i > 0) {
					ImageTTFText($img, $this->FontSize, 0, $svXPos + (($xPos - $svXPos) / 2), (int)($gazouHeight + cmTo(0.01))
				    			,$black, $this->FontName, substr($code, $i - 1, 1));
					}
				ImageTTFText($img, $this->FontSize, 0, $xPos, (int)($gazouHeight + cmTo(0.01))
				    		,$black, $this->FontName, substr($code, $i, 1));
					$svXPos = $xPos;
				}
	
				$c1 = substr($code, $i, 1);
				$c2 = substr($code, $i + 1, 1);
				$p1 = strpos($asc, $c1);
				$p2 = strpos($asc, $c2);
	
				$chk = 0x10;
				$ptnAll = 0x1;
				for ($j = 0; $j < 5; $j++) {
					$x0or1 = 0;
					if (($ptn[$p1] & ($chk >> $j)) != 0) {
						$x0or1 = 1; //太バー
					}
	
					$ptnAll = $ptnAll << 1;
					$ptnAll += $x0or1;
	
					$x0or1 = 0;
					if (($ptn[$p2] & ($chk >> $j)) != 0) {
						$x0or1 = 1; //太バー
					}
					$ptnAll = $ptnAll << 1;
					$ptnAll += $x0or1;
				}
				$ptnAll = $ptnAll & 0x03FF;
	
				$chk = 0x200;
				for ($j = 0; $j < 10; $j++) {
					$x0or1 = 0;
					if (($ptnAll & ($chk >> $j)) != 0) {
						$x0or1 = 1; //太バー
					}
	
					if ($j % 2 == 0) { //黒バー
					imagefilledrectangle($img, $xPos, 0, $xPos+$dot[$x0or1]-1 + $this->KuroBarCousei,  $height, $black);
					}
					$xPos += $dot[$x0or1];
				}
			}
	
			//添字の描画
			if($this->TextWrite) {
			ImageTTFText($img, $this->FontSize, 0, $svXPos + (($xPos - $svXPos) / 2), (int)($gazouHeight + cmTo(0.01))
				    	,$black, $this->FontName, substr($code, strlen($code)-1, 1));
			}
	
			for ($j = 0; $j < 3; $j++) {
				$x0or1 = 0;
				if ($j == 0) {
					$x0or1 = 1; //太バー
				}
				if ($j % 2 == 0) { //黒バー
				imagefilledrectangle($img, $xPos, 0, $xPos+$dot[$x0or1]-1 + $this->KuroBarCousei,  $height, $black);
				}
				$xPos += $dot[$x0or1];
			}
	
			//添字の描画
			if($this->TextWrite) {
				$interval = ($xPos - pointTo($this->FontSize - cmTo(0.01))) / (strlen($code) - 1);
				for ($i = 0; $i < strlen($code); $i++) {
				//					ImageTTFText($img, $this->FontSize, 0, (int)($i * $interval) + cmTo(0.05), $gazouHeight + cmTo(0.01)
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