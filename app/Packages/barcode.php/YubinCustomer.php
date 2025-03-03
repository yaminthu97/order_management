<?php

require_once("Common.php");

/**
 * 郵便カスタマバーコード作成クラス
 */
class YubinCustomer {

	private $img;
	private $black;
	private $xBar;
	private	$yLong;
	private	$yShort;
	private	$yMiddle;
	private	$lineWidth;
	private $minWidthDot;
		
	/*! 黒バーの太さ調整ドット数 */
	var $KuroBarCousei = 0;


	/**
		* サイズを指定して、郵便カスタマバーコードの描画を行います。
		* @param $code 描画を行うバーコードのコード(テキスト)
		* @param $size バーコードの大きさ(100ぐらいが妥当、後は調整してください)
		* @return バーコードのイメージを返します。
		*/
	function draw($code, $size) {

		$ptnN = array("144", "114", "132", "312", "123", "141", "321", "213", "231", "411");
		$ptnC = array("324", "342", "234", "432", "243", "423", "441", "111");
		$ptnStart = "13";
		$ptnStop = "31";
		$ptnHi = "414";
		$asc = "0123456789-ABCDEFGHIJKLMNOPQRSTUVWXYZ";
		$ptn = array($ptnN[0], $ptnN[1], $ptnN[2], $ptnN[3], $ptnN[4], $ptnN[5], $ptnN[6], $ptnN[7], $ptnN[8], $ptnN[9], 
			$ptnHi, 
			$ptnC[0] . $ptnN[0], $ptnC[0] . $ptnN[1], $ptnC[0] . $ptnN[2], $ptnC[0] . $ptnN[3], $ptnC[0] . $ptnN[4], $ptnC[0] . $ptnN[5], $ptnC[0] . $ptnN[6], $ptnC[0] . $ptnN[7], $ptnC[0] . $ptnN[8], $ptnC[0] . $ptnN[9], 
			$ptnC[1] . $ptnN[0], $ptnC[1] . $ptnN[1], $ptnC[1] . $ptnN[2], $ptnC[1] . $ptnN[3], $ptnC[1] . $ptnN[4], $ptnC[1] . $ptnN[5], $ptnC[1] . $ptnN[6], $ptnC[1] . $ptnN[7], $ptnC[1] . $ptnN[8], $ptnC[1] . $ptnN[9], 
			$ptnC[2] . $ptnN[0], $ptnC[2] . $ptnN[1], $ptnC[2] . $ptnN[2], $ptnC[2] . $ptnN[3], $ptnC[2] . $ptnN[4], $ptnC[2] . $ptnN[5]);

		$ptnChk = array($ptnN[0], $ptnN[1], $ptnN[2], $ptnN[3], $ptnN[4], $ptnN[5], $ptnN[6], $ptnN[7], $ptnN[8], $ptnN[9], 
						$ptnHi, 
						$ptnC[0], $ptnC[1], $ptnC[2], $ptnC[3], $ptnC[4], $ptnC[5], $ptnC[6], $ptnC[7]);
				

		$code = strtoupper($code);
		$this->xBar = CmTo(0.3 * $size /10 / 10);
		$this->yLong = CmTo(3.6 * $size / 10 /10);
		$this->yShort = CmTo(1.2 * $size / 10 / 10);
		$this->yMiddle = $this->yLong / 2 + $this->yShort / 2;
		$this->lineWidth = CmTo(0.01/10);
			

		$xPos = 0;
		$chkStr = "";

		//スタートコード
		$xPos += $this->xBar * 2 * strlen($ptnStart);

		//データ部(count)
		$codeLen = 0;
		for($i=0; $i<strlen($code); $i++){
			$c = substr($code, $i,1);
			$p = strpos($asc, $c);
			if($p === false) {
				trigger_error("YubinCustomer : bat character in code string. ('".$c."') safe character is [0123456789-ABCDEFGHIJKLMNOPQRSTUVWXYZ]",E_USER_ERROR);
				exit;
			}

			if($codeLen == 19 && strlen($ptn[$p]) > 3) {
				for($j=0; $j < strlen($ptnC[0]); $j++) {
					$xPos += $this->xBar * 2;
					$chkStr .= substr($ptnC[0], $j,1);
				}
				$codeLen++;
				break;
			} else {
				for($j=0; $j < strlen($ptn[$p]); $j++) {
					$xPos += $this->xBar * 2;
					$chkStr .= substr($ptn[$p], $j,1);
				}
			}

			if(strlen($ptn[$p]) <= 3) {
				$codeLen++;
			} else {
				$codeLen+=2;
			}

			if($codeLen >= 20) {
				break;
			}
		}

		if($codeLen < 20) {
			for($i = $codeLen; $i<20; $i++)	{
				for($j=0; $j<strlen($ptnC[3]); $j++) {
					$xPos += $this->xBar * 2;
				}
				$chkStr .= $ptnC[3];
				$codeLen++;
			}
		}
		//チェックディジット
		$chkSum = 0;
		for($i=0; $i<$codeLen*3; $i+=3) {
			$j=0;
			for($j=0; $j<10; $j++) {
				if(substr($chkStr, $i,3) == $ptnN[$j]) {
					$chkSum += $j;
					break;
				}
			}

			if($j<10)
			{
				continue;
			}

			if(substr($chkStr, $i,3) == $ptnHi) {
				$chkSum += 10;
//					continue;
			}
			for($j=11; $j<19; $j++) {
				if(substr($chkStr, $i,3) == $ptnC[$j-11]) {
					$chkSum += $j;
					break;
				}
			}
		}			
		$chkD = 19 - ($chkSum % 19);
		if($chkD == 19) $chkD = 0;
		for($j=0; $j < strlen($ptnChk[$chkD]); $j++) {
			$xPos += $this->xBar * 2;
		}

		//ストップコード
			$xPos += $this->xBar * 2 * strlen($ptnStop);

		$xPos =402;
		if(isOK()) {
			$this->img = ImageCreate($xPos * ($size / 100), $this->yLong);
		} else {
			$this->img = ImageCreate($xPos * ($size / 100), $this->yLong + 99);
		}
		$this->white = ImageColorAllocate($this->img, 0xFF, 0xFF, 0xFF);
		$this->black = ImageColorAllocate($this->img, 0x00, 0x00, 0x00);
			
		if(!isOK()) {
			$this->w = $xPos * ($size / 100);
			$this->h = $this->yLong;
			ImageFilledRectangle($this->img, $this->w,$this->h, $this->w, $this->h + 99, $this->white);
		}

		$xPos = 0;
		$chkStr = "";
		//スタートコード
		for($j=0; $j<strlen($ptnStart); $j++) {
			$this->WriteBar(substr($ptnStart, $j, 1), $xPos);
			$xPos += $this->xBar * 2;
		}

		//データ部
		$codeLen = 0;
		for($i=0; $i<strlen($code); $i++){
		$c = substr($code, $i,1);
			$p = strpos($asc, $c);
			//ImageTTFText($this->img, $this->FontSize, 0, $xPos, 50	,$this->black, $this->FontName, $c);
			if($p === false){
			trigger_error("YubinCustomer : bat character in code string. ('".$c."') safe character is [0123456789-ABCDEFGHIJKLMNOPQRSTUVWXYZ]",E_USER_ERROR);
				exit;
			}

			if($codeLen == 19 && strlen($ptn[$p]) > 3) {
				for($j=0; $j < strlen($ptnC[0]); $j++) {
					$this->WriteBar(substr($ptnC[0], $j, 1), $xPos);
					$xPos += $this->xBar * 2;
					$chkStr .= substr($ptnC[0], $j,1);
				}
				$codeLen++;
				break;
			} else {
				for($j=0; $j < strlen($ptn[$p]); $j++) {
					$this->WriteBar(substr($ptn[$p], $j, 1), $xPos);
					$xPos += $this->xBar * 2;
					$chkStr .= substr($ptn[$p], $j,1);
				}
			}

			if(strlen($ptn[$p]) <= 3){
				$codeLen++;
			}else{
				$codeLen+=2;
			}

			if($codeLen >= 20){
				break;
			}
		}

		if($codeLen < 20){
			for($i = $codeLen; $i<20; $i++){
				for($j=0; $j<strlen($ptnC[3]); $j++){
					$this->WriteBar(substr($ptnC[3], $j,1), $xPos);
					$xPos += $this->xBar * 2;
				}
				$chkStr .= $ptnC[3];
				$codeLen++;
			}
		}

		//チェックディジット
		$chkSum = 0;
		for($i=0; $i<$codeLen*3; $i+=3)	{
			$j=0;
			for($j=0; $j<10; $j++){
				if(substr($chkStr, $i,3) == $ptnN[$j]){
					$chkSum += $j;
					break;
				}
			}

			if($j<10)
			{
				continue;
			}

			if(substr($chkStr, $i,3) == $ptnHi)	{
				$chkSum += 10;
				continue;
			}
			for($j=11; $j<19; $j++) {
				if(substr($chkStr, $i,3) == $ptnC[$j-11])
				{
					$chkSum += $j;
					break;
				}
			}
			if($j<19)
			{
				continue;
			}
		}			
		$chkD = 19 - ($chkSum % 19);
		if($chkD == 19) $chkD = 0;
		for($j=0; $j < strlen($ptnChk[$chkD]); $j++) {
			$this->WriteBar(substr($ptnChk[$chkD], $j, 1), $xPos);
			$xPos += $this->xBar * 2;
		}

		//ストップコード
		for($j=0; $j<strlen($ptnStop); $j++) {
			$this->WriteBar(substr($ptnStop, $j, 1), $xPos);
			$xPos += $this->xBar * 2;
		}

		if(!isOK()) {
			//SAMPLE 描画
			$this->red = ImageColorAllocate($this->img, 0xFF, 0x00, 0x00);
			ImageTTFText($this->img, 99, 0, 2, $this->h+99 ,$this->red, "./font/mplus-1p-black.ttf", "SAMPLE");
		}

		return $this->img;
	}
	
	/**
	 * Intermediate method to draw barcode's bar
	 * @param val Height type
	 * @param xPos X position
	 */
	function WriteBar($val , $xPos) {
			$yPos = 0;
			$height = 0;

			switch($val) {
				case "1":
					$yPos = 0;
					$height = $this->yLong;
					break;
				case "2":
					$yPos = 0;
					$height = $this->yMiddle;
					break;
				case "3":
					$yPos = $this->yLong - $this->yMiddle;
					$height = $this->yMiddle;
					break;
				case "4":
					$yPos = $this->yLong - $this->yMiddle;
					$height = $this->yShort;
					break;
			}

		imagefilledrectangle($this->img, $xPos, $yPos, $xPos+$this->xBar - 1 + $this->KuroBarCousei, $height + $yPos, $this->black);

			return;
		}
	}