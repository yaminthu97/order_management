<?php 

//	require("Common.php");
	require_once("CheckDigit.php");

	/**
	 * UPC-E作成クラス
	 */
	class UpcE {

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
	
		/*! 結果の添え字(バーコードの下の文字)(出力：呼び出し元へ返す) */
		var $outputCode = "";
	
	/**
		 * バーコードの描画を行います。バーコード全体の幅を指定するのではなく、バーを描画する横方向の最小単位のドット数を指定します。(1～)
		 * @param $code 描画を行うバーコードのコード(テキスト)
		 * @param $minWidthDot 横方向の最少描画ドット数
		 * @param $height バーコードのバーの高さ(単位：ドット)
		 * @return バーコードのイメージを返します。
		 */
		function draw($pCode, $minWidthDot, $height) {

		global $TextWrite, $FontName, $FontSize, $outputCode;

		$soejiCode	= "";
		
		if(preg_match ("/^[0-9]+$/",$pCode) == false)
		{
			trigger_error("UPC-E : not number in code string.",E_USER_ERROR);
			exit;
		}
		
		$code = "";
		$cd_code_in = "";
		if (strlen($pCode) == 6) {
			$code = $pCode;
		} else if (strlen($pCode) == 7) {
			$code = substr($pCode(1, 6));
		} else if (strlen($pCode) == 8) {
            $cd_code_in = substr($pCode, 7, 1);
            $code = substr($pCode, 1, 6);
		} else {
			trigger_error("UPC-E : length error. (length != 8 or lenght != 7)",E_USER_ERROR);
			exit;
		}
		
        $soejiCode = "0" . $code;

		$cd_code = modulus10W3_UPC_E(substr($soejiCode, 0, 7));
        if ($cd_code == "")
        {
			trigger_error("UPC-E : wrong code because can not make check digit. ",E_USER_ERROR);
			exit;
        }

        if ($cd_code_in == "")
        { }
        else if ($cd_code_in == $cd_code)
        { }
        else
        {
			trigger_error("UPC-E : check digit is wrong. ",E_USER_ERROR);
			exit;
        }

        $soejiCode = "0" . $code . $cd_code;

		$pre = (int)$cd_code;
		$oe = array(0x00, 0x0b, 0x0d, 0x0e, 0x13, 0x19, 0x1c, 0x15, 0x16, 0x1a);
		$left_1 = array(0x27, 0x33, 0x1b, 0x21, 0x1d, 0x39, 0x05, 0x11, 0x09, 0x17);
		$left_2 = array(0x0d, 0x19, 0x13, 0x3d, 0x23, 0x31, 0x2f, 0x3b, 0x37, 0x0b);
		$left = array($left_1, $left_2);


		$x0 = $minWidthDot; //x0:細バー x1:太バー
		$x1 = $minWidthDot * 2.5;
		if($minWidthDot % 2 != 0) {
			$x1 = $minWidthDot * 3;
		}

		$dot = array($x0, $x1);

		$xPos = 0;
		$xPos += $dot[0]*(3+28+5+28+3);

		$gazouHeight = $height;
		if($this->TextWrite == true)
		{
			$gazouHeight = $height + $this->FontSize + 3;
		}
		$img = ImageCreate($xPos, $gazouHeight);
		$white = ImageColorAllocate($img, 0xFF, 0xFF, 0xFF);
		$black = ImageColorAllocate($img, 0x00, 0x00, 0x00);

		imagesetthickness($img, $this->BarThick);

		$xPos = 0;
		//スタートコード
		for ($j = 0; $j < 3; $j++) {
			if ($j % 2 == 0) { //黒バー
				imagefilledrectangle($img, $xPos, 0, $xPos+$dot[0]-1 + $this->KuroBarCousei,  $height, $black);
			}
			$xPos += $dot[0];
		}

		//DATA 6キャラクタ
		$chkOE = 0x20;
		for ($i = 0; $i < strlen($code); $i++) {
			$flgOE = 0;
			if (($oe[$pre] & ($chkOE >> $i)) != 0) {
				$flgOE = 1;
			}
			$c = (int)substr($code, $i, 1);
			$chk = 0x40;
			$l = 0;
			for ($j = 0; $j < 7; $j++) {
				if (($left[$flgOE][$c] & ($chk >> $j)) != 0) {
					$l++;
				}elseif($l != 0){
					imagefilledrectangle($img, $xPos, 0, $xPos+($dot[0]*$l)-1 + $this->KuroBarCousei,  $height, $black);
					$xPos += $dot[0]*$l + $dot[0];
					$l = 0;
				}else{
					$xPos += $dot[0];
				}
			}
			if($l != 0){
				imagefilledrectangle($img, $xPos, 0, $xPos+($dot[0]*$l)-1 + $this->KuroBarCousei,  $height, $black);
				$xPos += $dot[0]*$l;
			}
		}

		//エンドコード
		for ($j = 0; $j < 6; $j++) {
			$k = 0;
			if ($j % 2 != 0) { //黒バー
				imagefilledrectangle($img, $xPos, 0, $xPos+$dot[0]-1 + $this->KuroBarCousei,  $height, $black);
			}
			$xPos += $dot[0];
		}

		if($this->TextWrite) {
			$interval = ($xPos - pointTo($this->FontSize - cmTo(0.01))) / (strlen($soejiCode) - 1)-1;
			for ($i = 0; $i < strlen($soejiCode); $i++) {
				ImageTTFText($img, $this->FontSize, 0, (int)(($i * $interval) + cmTo(0.05)), (int)($gazouHeight + cmTo(0.01))
					,$black, $this->FontName, substr($soejiCode, $i, 1));
			}
		}
		
		$this->outputCode = $soejiCode;
		
		if(!isOK()) {
			//SAMPLE 描画
			$red = ImageColorAllocate($img, 0xFF, 0x00, 0x00);
			ImageTTFText($img, 12, 0, 2, 14	,$red, $this->FontName, "SAMPLE");
		}

		return $img;
		
		}
	}

	