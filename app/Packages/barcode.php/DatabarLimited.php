<?php

require_once('BigInteger.php');
	
	/**
	 * GS1データバー 限定型 (RSS Limited) 作成クラス
	 */
	class DatabarLimited {

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
		
		/*! 白バーの太さ調整ドット数 */
		var $ShiroBarCousei = 0;
	
		/*! 結果の添え字(バーコードの下の文字)(出力：呼び出し元へ返す) */
		var $outputCode = "";

	
	/**
		 * バーコードの描画を行います。バーコード全体の幅を指定するのではなく、バーを描画する最小単位のドット数を指定します。
		 * @param $code 描画を行うバーコードのコード(テキスト)
		 * @param $minWidthDot 横方向の最少描画ドット数
		 * @param $height バーコードのバーの高さ(単位：ドット)
		 * @return バーコードのイメージを返します。
		 */
	function draw($code, $minWidthDot, $height) {
			
		global $TextWrite, $FontName, $FontSize;

		if (!$this->encode($code))
		{
			throw new IllegalArgumentException($this->error_msg);
		}

		$xPos = 0;
		$yPos = 0;
		$h = array();
		for ($i = 0; $i < count($this->pattern); $i++) 
		{
			$xPos = 0;

			$h[0] = $height;

			for ($j = 0; $j < strlen($this->pattern[$i]); $j++) 
			{
				$w = intval(substr($this->pattern[$i], $j, 1));
				if ((($j % 2) == 0))
				{
					$xPos += $w * $minWidthDot + $this->KuroBarCousei;
				}
				else
				{
					$xPos += $w * $minWidthDot + $this->ShiroBarCousei;
				}
			}
			$yPos += $h[$i];
		}

		$gazouHeight = $yPos;
		if($this->TextWrite == true)
		{
			$gazouHeight = $yPos + $this->FontSize + 3;
		}


		$img = ImageCreate($xPos, $gazouHeight);
		$white = ImageColorAllocate($img, 0xFF, 0xFF, 0xFF);
		$black = ImageColorAllocate($img, 0x00, 0x00, 0x00);

		imagesetthickness($img, $this->BarThick);

		$xPos = 0;
		$yPos = 0;
		$h = array();
		for ($i = 0; $i < count($this->pattern); $i++) 
		{
			$xPos = 0;

			$h[0] = $height;

			for ($j = 0; $j < strlen($this->pattern[$i]); $j++) 
			{
				$w = intval (substr($this->pattern[$i], $j, 1));
				if ((($j % 2) == 0))
				{
					if($xPos > 0 || $w > 0)
					{
						imagefilledrectangle($img, $xPos, 0, $xPos + $w * $minWidthDot + $this->KuroBarCousei,  $h[$i], $black);
					}
					$xPos += $w * $minWidthDot + $this->KuroBarCousei;
				}
				else
				{
					$xPos += $w * $minWidthDot + $this->ShiroBarCousei;
				}
			}
			$yPos += $h[$i];
		}


		// 添え字
		$strText = "(01)" . $this->hrt;
		if($this->TextWrite) {
			$interval = ($xPos - $this->FontSize) / (strlen($strText) - 1);
			for($i = 0; $i < strlen($strText); $i++) {
				ImageTTFText($img, $this->FontSize, 0, (int)($i * $interval), $gazouHeight
					,$black, $this->FontName, substr($strText, $i, 1));
			}
		}
		$this->outputCode = $strText; 

		if(!isOK()) {
			//SAMPLE 描画
			$red = ImageColorAllocate($img, 0xFF, 0x00, 0x00);
			ImageTTFText($img, 12, 0, 2, 14	,$red, $this->FontName, "SAMPLE");
		}
		
		return $img;
	}
	

	protected $error_msg;	// String
	protected $pattern;	// String[]
	protected $row_height;	// int[]
	protected $hrt;	// String
	protected $t_even_ltd;	// int[]
	protected $modules_odd_ltd;	// int[]
	protected $modules_even_ltd;	// int[]
	protected $widest_odd_ltd;	// int[]
	protected $widest_even_ltd;	// int[]
	protected $checksum_weight_ltd;	// int[]
	protected $finder_pattern_ltd;	// int[]
	protected $linkageFlag;	// boolean
	protected $widths;	// int[]
	protected function setLinkageFlag () 
	{
		$this->linkageFlag =  TRUE ;
	}
	protected function unsetLinkageFlag () 
	{
		$this->linkageFlag =  FALSE ;
	}
	public function encode ($content) // [String content]
	{
		$this->error_msg = "";
		$this->t_even_ltd = array(28, 728, 6454, 203, 2408, 1, 16632);
		$this->modules_odd_ltd = array(17, 13, 9, 15, 11, 19, 7);
		$this->modules_even_ltd = array(9, 13, 17, 11, 15, 7, 19);
		$this->widest_odd_ltd = array(6, 5, 3, 5, 4, 8, 1);
		$this->widest_even_ltd = array(3, 4, 6, 4, 5, 1, 8);
		$this->checksum_weight_ltd = array(1, 3, 9, 27, 81, 65, 17, 51, 64, 14, 42, 37, 22, 66, 20, 60, 2, 6, 18, 54, 73, 41, 34, 13, 39, 28, 84, 74);
		$this->finder_pattern_ltd = array(1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 3, 3, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 2, 3, 2, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 3, 3, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 2, 1, 1, 3, 2, 1, 1, 1, 1, 1, 1, 1, 1, 1, 2, 1, 2, 3, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 3, 1, 1, 3, 1, 1, 1, 1, 1, 1, 1, 1, 2, 1, 1, 1, 1, 3, 2, 1, 1, 1, 1, 1, 1, 1, 2, 1, 1, 1, 2, 3, 1, 1, 1, 1, 1, 1, 1, 1, 2, 1, 2, 1, 1, 3, 1, 1, 1, 1, 1, 1, 1, 1, 3, 1, 1, 1, 1, 3, 1, 1, 1, 1, 1, 1, 2, 1, 1, 1, 1, 1, 1, 3, 2, 1, 1, 1, 1, 1, 2, 1, 1, 1, 1, 1, 2, 3, 1, 1, 1, 1, 1, 1, 2, 1, 1, 1, 2, 1, 1, 3, 1, 1, 1, 1, 1, 1, 2, 1, 2, 1, 1, 1, 1, 3, 1, 1, 1, 1, 1, 1, 3, 1, 1, 1, 1, 1, 1, 3, 1, 1, 1, 1, 2, 1, 1, 1, 1, 1, 1, 1, 1, 3, 2, 1, 1, 1, 2, 1, 1, 1, 1, 1, 1, 1, 2, 3, 1, 1, 1, 1, 2, 1, 1, 1, 1, 1, 2, 1, 1, 3, 1, 1, 1, 1, 2, 1, 1, 1, 2, 1, 1, 1, 1, 3, 1, 1, 1, 1, 2, 1, 2, 1, 1, 1, 1, 1, 1, 3, 1, 1, 1, 1, 3, 1, 1, 1, 1, 1, 1, 1, 1, 3, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 2, 1, 2, 3, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 2, 2, 2, 2, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 2, 3, 2, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 2, 2, 1, 2, 2, 1, 1, 1, 1, 1, 1, 1, 1, 1, 2, 2, 2, 2, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 3, 2, 1, 2, 1, 1, 1, 1, 1, 1, 1, 1, 2, 1, 1, 2, 1, 2, 2, 1, 1, 1, 1, 1, 1, 1, 2, 1, 1, 2, 2, 2, 1, 1, 1, 1, 1, 1, 1, 1, 2, 1, 2, 2, 1, 2, 1, 1, 1, 1, 1, 1, 1, 1, 3, 1, 1, 2, 1, 2, 1, 1, 1, 1, 1, 1, 2, 1, 1, 1, 1, 2, 1, 2, 2, 1, 1, 1, 1, 1, 2, 1, 1, 1, 1, 2, 2, 2, 1, 1, 1, 1, 1, 1, 2, 1, 1, 1, 2, 2, 1, 2, 1, 1, 1, 1, 1, 1, 2, 1, 2, 1, 1, 2, 1, 2, 1, 1, 1, 1, 1, 1, 3, 1, 1, 1, 1, 2, 1, 2, 1, 1, 1, 1, 2, 1, 1, 1, 1, 1, 1, 2, 1, 2, 2, 1, 1, 1, 2, 1, 1, 1, 1, 1, 1, 2, 2, 2, 1, 1, 1, 1, 2, 1, 1, 1, 1, 1, 2, 2, 1, 2, 1, 1, 1, 1, 2, 1, 1, 1, 2, 1, 1, 2, 1, 2, 1, 1, 1, 1, 2, 1, 2, 1, 1, 1, 1, 2, 1, 2, 1, 1, 1, 1, 3, 1, 1, 1, 1, 1, 1, 2, 1, 2, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 3, 1, 1, 3, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 3, 2, 1, 2, 1, 1, 1, 1, 1, 1, 1, 1, 1, 2, 3, 1, 1, 2, 1, 1, 1, 1, 1, 2, 1, 1, 1, 1, 3, 1, 1, 2, 1, 1, 1, 2, 1, 1, 1, 1, 1, 1, 3, 1, 1, 2, 1, 1, 1, 1, 1, 1, 1, 1, 2, 1, 1, 1, 2, 3, 1, 1, 1, 1, 1, 1, 1, 1, 2, 1, 1, 2, 2, 2, 1, 1, 1, 1, 1, 1, 1, 1, 2, 1, 1, 3, 2, 1, 1, 1, 1, 1, 1, 1, 1, 1, 2, 2, 1, 1, 2, 2, 1, 1, 1, 1, 1, 2, 1, 1, 2, 1, 1, 1, 2, 2, 1, 1, 1, 1, 1, 2, 1, 1, 2, 1, 1, 2, 2, 1, 1, 1, 1, 1, 1, 2, 1, 1, 2, 2, 1, 1, 2, 1, 1, 1, 1, 1, 1, 2, 1, 2, 2, 1, 1, 1, 2, 1, 1, 1, 1, 1, 1, 3, 1, 1, 2, 1, 1, 1, 2, 1, 1, 1, 1, 2, 1, 1, 1, 1, 2, 1, 1, 1, 2, 2, 1, 1, 1, 2, 1, 1, 1, 1, 2, 1, 1, 2, 2, 1, 1, 1, 1, 2, 1, 2, 1, 1, 2, 1, 1, 1, 2, 1, 1, 1, 1, 1, 1, 1, 2, 1, 1, 1, 1, 1, 2, 3, 1, 1, 1, 1, 1, 1, 2, 1, 1, 1, 1, 2, 2, 2, 1, 1, 1, 1, 1, 1, 2, 1, 1, 1, 1, 3, 2, 1, 1, 1, 1, 1, 1, 1, 2, 1, 1, 2, 1, 1, 2, 2, 1, 1, 1, 1, 1, 1, 2, 1, 1, 2, 1, 2, 2, 1, 1, 1, 1, 1, 1, 1, 2, 2, 1, 1, 1, 1, 2, 2, 1, 1, 1, 2, 1, 1, 2, 1, 1, 1, 1, 1, 2, 2, 1, 1, 1, 2, 1, 1, 2, 1, 1, 1, 1, 2, 2, 1, 1, 1, 1, 2, 1, 1, 2, 1, 1, 2, 1, 1, 2, 1, 1, 1, 1, 2, 1, 1, 2, 2, 1, 1, 1, 1, 2, 1, 1, 1, 1, 2, 1, 2, 2, 1, 1, 1, 1, 1, 2, 1, 1, 1, 1, 3, 1, 1, 2, 1, 1, 1, 1, 1, 2, 1, 1, 1, 1, 1, 2, 1, 1, 1, 1, 1, 1, 1, 2, 3, 1, 1, 1, 1, 2, 1, 1, 1, 1, 1, 1, 2, 2, 2, 1, 1, 1, 1, 2, 1, 1, 1, 1, 1, 1, 3, 2, 1, 1, 1, 1, 1, 2, 1, 1, 1, 1, 2, 1, 1, 2, 2, 1, 1, 1, 1, 2, 1, 1, 1, 1, 2, 1, 2, 2, 1, 1, 1, 1, 1, 2, 1, 1, 1, 1, 3, 1, 1, 2, 1, 1, 1, 1, 1, 2, 1, 1, 2, 1, 1, 1, 1, 2, 2, 1, 1, 1, 1, 2, 1, 1, 2, 1, 1, 1, 2, 2, 1, 1, 1, 1, 1, 2, 2, 1, 1, 1, 1, 1, 1, 2, 2, 1, 1, 2, 1, 1, 1, 1, 1, 1, 1, 1, 2, 2, 2, 1, 1, 2, 1, 1, 1, 1, 1, 1, 1, 1, 3, 2, 1, 1, 1, 2, 1, 1, 1, 1, 1, 1, 2, 1, 1, 2, 2, 1, 1, 2, 1, 1, 1, 1, 1, 1, 2, 1, 2, 2, 1, 1, 1, 2, 1, 1, 1, 1, 1, 1, 3, 1, 1, 2, 1, 1, 1, 2, 1, 1, 1, 1, 2, 1, 1, 1, 2, 2, 1, 1, 1, 2, 1, 1, 1, 1, 2, 1, 2, 1, 1, 2, 1, 1, 1, 2, 1, 1, 2, 1, 1, 1, 1, 1, 2, 2, 1, 1, 1, 2, 1, 1, 1, 1, 1, 1, 1, 2, 2, 1, 2, 1, 1);
		$this->widths = array();


		$accum = null;
		$left_reg = null;
		$right_reg = null;
		$left_group = 0;
		$right_group = 0;
		$i = 0;
		$j = 0;
		$left_character = 0;
		$right_character = 0;
		$left_odd = 0;
		$right_odd = 0;
		$left_even = 0;
		$right_even = 0;
		$left_widths = array();
		$right_widths = array();
		$checksum = 0;
		$check_elements = array();
		$total_widths = array();
		$bin = "";
		$notbin = "";
		$bar_latch = false;
		$writer = 0;
		$check_digit = 0;
		$count = 0;
		$compositeOffset = 0;
		
			
				
					if (strlen($content) > 13)
		{
			$this->error_msg = "Input too long";
			return  FALSE ;
		}
		if (($t=strpbrk($content, "[0-9]+?")) == false) {
			$this->error_msg = "Invalid characters in input";
			return  false ;
		}
		if ((strlen($content) == 13))
		{
			if (((($this->charAt2($content, 0) != '0')) && (($this->charAt2($content, 0) != '1'))))
			{
				$this->error_msg = "Input out of range";
				return  FALSE ;
			}
		}
		$accum = new Math_BigInteger($content);
		if ($this->linkageFlag)
		{
			$accum = $accum->add(new Math_BigInteger("2015133531096"));
		}
		list($left_reg, $right_reg) = $accum->divide(new Math_BigInteger("2013571"));
		$left_group = 0;
		if (($left_reg->compare(new Math_BigInteger("183063")) > 0))
		{
			$left_group = 1;
		}
		if (($left_reg->compare(new Math_BigInteger("820063")) > 0))
		{
			$left_group = 2;
		}
		if (($left_reg->compare(new Math_BigInteger("1000775")) > 0))
		{
			$left_group = 3;
		}
		if (($left_reg->compare(new Math_BigInteger("1491020")) > 0))
		{
			$left_group = 4;
		}
		if (($left_reg->compare(new Math_BigInteger("1979844")) > 0))
		{
			$left_group = 5;
		}
		if (($left_reg->compare(new Math_BigInteger("1996938")) > 0))
		{
			$left_group = 6;
		}
		$right_group = 0;
		if (($right_reg->compare(new Math_BigInteger("183063")) > 0))
		{
			$right_group = 1;
		}
		if (($right_reg->compare(new Math_BigInteger("820063")) > 0))
		{
			$right_group = 2;
		}
		if (($right_reg->compare(new Math_BigInteger("1000775")) > 0))
		{
			$right_group = 3;
		}
		if (($right_reg->compare(new Math_BigInteger("1491020")) > 0))
		{
			$right_group = 4;
		}
		if (($right_reg->compare(new Math_BigInteger("1979844")) > 0))
		{
			$right_group = 5;
		}
		if (($right_reg->compare(new Math_BigInteger("1996938")) > 0))
		{
			$right_group = 6;
		}
		switch ($left_group) {
			case 1:
				$left_reg = $left_reg->subtract(new Math_BigInteger("183064"));
				break;
			case 2:
				$left_reg = $left_reg->subtract(new Math_BigInteger("820064"));
				break;
			case 3:
				$left_reg = $left_reg->subtract(new Math_BigInteger("1000776"));
				break;
			case 4:
				$left_reg = $left_reg->subtract(new Math_BigInteger("1491021"));
				break;
			case 5:
				$left_reg = $left_reg->subtract(new Math_BigInteger("1979845"));
				break;
			case 6:
				$left_reg = $left_reg->subtract(new Math_BigInteger("1996939"));
				break;
		}
		switch ($right_group) {
			case 1:
				$right_reg = $right_reg->subtract(new Math_BigInteger("183064"));
				break;
			case 2:
				$right_reg = $right_reg->subtract(new Math_BigInteger("820064"));
				break;
			case 3:
				$right_reg = $right_reg->subtract(new Math_BigInteger("1000776"));
				break;
			case 4:
				$right_reg = $right_reg->subtract(new Math_BigInteger("1491021"));
				break;
			case 5:
				$right_reg = $right_reg->subtract(new Math_BigInteger("1979845"));
				break;
			case 6:
				$right_reg = $right_reg->subtract(new Math_BigInteger("1996939"));
				break;
		}
		$left_character = intval($left_reg->toString());
		$right_character = intval($right_reg->toString());
		$left_odd = (int)($left_character / $this->t_even_ltd[$left_group]);
		$left_even = (int)($left_character % $this->t_even_ltd[$left_group]);
		$right_odd = (int)($right_character / $this->t_even_ltd[$right_group]);
		$right_even = (int)($right_character % $this->t_even_ltd[$right_group]);
		$this->getWidths($left_odd, $this->modules_odd_ltd[$left_group], 7, $this->widest_odd_ltd[$left_group], 1);
		$left_widths[0] = $this->widths[0];
		$left_widths[2] = $this->widths[1];
		$left_widths[4] = $this->widths[2];
		$left_widths[6] = $this->widths[3];
		$left_widths[8] = $this->widths[4];
		$left_widths[10] = $this->widths[5];
		$left_widths[12] = $this->widths[6];
		$this->getWidths($left_even, $this->modules_even_ltd[$left_group], 7, $this->widest_even_ltd[$left_group], 0);
		$left_widths[1] = $this->widths[0];
		$left_widths[3] = $this->widths[1];
		$left_widths[5] = $this->widths[2];
		$left_widths[7] = $this->widths[3];
		$left_widths[9] = $this->widths[4];
		$left_widths[11] = $this->widths[5];
		$left_widths[13] = $this->widths[6];
		$this->getWidths($right_odd, $this->modules_odd_ltd[$right_group], 7, $this->widest_odd_ltd[$right_group], 1);
		$right_widths[0] = $this->widths[0];
		$right_widths[2] = $this->widths[1];
		$right_widths[4] = $this->widths[2];
		$right_widths[6] = $this->widths[3];
		$right_widths[8] = $this->widths[4];
		$right_widths[10] = $this->widths[5];
		$right_widths[12] = $this->widths[6];
		$this->getWidths($right_even, $this->modules_even_ltd[$right_group], 7, $this->widest_even_ltd[$right_group], 0);
		$right_widths[1] = $this->widths[0];
		$right_widths[3] = $this->widths[1];
		$right_widths[5] = $this->widths[2];
		$right_widths[7] = $this->widths[3];
		$right_widths[9] = $this->widths[4];
		$right_widths[11] = $this->widths[5];
		$right_widths[13] = $this->widths[6];
		$checksum = 0;
		$symbol_width = 0;
		for ($i = 0; ($i < 14); ++$i) 
		{
			$checksum += ($this->checksum_weight_ltd[$i] * $left_widths[$i]);
			$checksum += ($this->checksum_weight_ltd[($i + 14)] * $right_widths[$i]);
		}
		$checksum %= 89;
		for ($i = 0; ($i < 14); ++$i) 
		{
			$check_elements[$i] = $this->finder_pattern_ltd[($i + (($checksum * 14)))];
		}
		$total_widths[0] = 1;
		$total_widths[1] = 1;
		$total_widths[44] = 1;
		$total_widths[45] = 1;
		for ($i = 0; ($i < 14); ++$i) 
		{
			$total_widths[($i + 2)] = $left_widths[$i];
			$total_widths[($i + 16)] = $check_elements[$i];
			$total_widths[($i + 30)] = $right_widths[$i];
		}
		$bin = "";
		$notbin = "";
		$writer = 0;
		$bar_latch =  FALSE ;
		for ($i = 0; ($i < 46); ++$i) 
		{
			for ($j = 0; ($j < $total_widths[$i]); ++$j) 
			{
				if ($bar_latch)
				{
					$bin .= "1";
					$notbin .= "0";
				}
				else
				{
					$bin .= "0";
					$notbin .= "1";
				}
				++$writer;
			}
			if ($bar_latch)
			{
				$bar_latch =  FALSE ;
			}
			else
			{
				$bar_latch =  TRUE ;
			}
		}
		if (($symbol_width < (($writer + 20))))
		{
			$symbol_width = ($writer + 20);
		}
		
		$this->hrt = "";
		for ($i = strlen($content); $i < 13; $i++) 
		{
			$this->hrt .= "0";
		}
		$this->hrt .= $content;
		
		
		for ($i = 0; ($i < 13); $i++) 
		{
			$count += ord($this->charAt2($this->hrt, $i)) - 0x30;
			if (((($i & 1)) == 0))
			{
				$count += (2 * (ord($this->charAt2($this->hrt, $i)) - 0x30));
			}
		}
		$check_digit = (10 - (($count % 10)));
		if (($check_digit == 10))
		{
			$check_digit = 0;
		}
		$this->hrt .= chr(($check_digit + 0x30));
		
		if ($this->linkageFlag)
		{
			$compositeOffset = 1;
		}
		$this->row_height = array();
		$this->row_height[(0 + $compositeOffset)] = -1;
		$this->pattern = array();
		$this->pattern[(0 + $compositeOffset)] = $this->bin2pat2($bin);
		if ($this->linkageFlag)
		{
			$notbin = substr($notbin, 4, 70 - 4);
			$this->row_height[0] = 1;
			$this->pattern[0] = ("0:04" . $this->bin2pat2($notbin));
		}
		return  TRUE ;
	}
	protected function getCombinations ($n, $r) // [int n, int r]
	{
		$i = 0;
		$j = 0;
		$maxDenom = 0;
		$minDenom = 0;
		$val = 0;
		if ((($n - $r) > $r))
		{
			$minDenom = $r;
			$maxDenom = ($n - $r);
		}
		else
		{
			$minDenom = ($n - $r);
			$maxDenom = $r;
		}
		$val = 1;
		$j = 1;
		for ($i = $n; ($i > $maxDenom); --$i) 
		{
			$val *= $i;
			if (($j <= $minDenom))
			{
				$val /= $j;
				++$j;
			}
		}
		for (; ($j <= $minDenom); ++$j) 
		{
			$val /= $j;
		}
		return ($val);
	}
	protected function getWidths ($val, $n, $elements, $maxWidth, $noNarrow) // [int val, int n, int elements, int maxWidth, int noNarrow]
	{
		$bar = null;
		$elmWidth = null;
		$mxwElement = null;
		$subVal = null;
		$lessVal = null;
		$narrowMask = 0;
		for ($bar = 0; ($bar < ($elements - 1)); ++$bar) 
		{
			for ($elmWidth = 1, $narrowMask |= ((1 << $bar)); ; ++$elmWidth, $narrowMask &= ~((1 << $bar))) 
			{
				$subVal = $this->getCombinations((($n - $elmWidth) - 1), (($elements - $bar) - 2));
				if ((((($noNarrow == 0)) && (($narrowMask == 0))) && (((($n - $elmWidth) - ((($elements - $bar) - 1))) >= (($elements - $bar) - 1)))))
				{
					$subVal -= $this->getCombinations((($n - $elmWidth) - (($elements - $bar))), (($elements - $bar) - 2));
				}
				if (((($elements - $bar) - 1) > 1))
				{
					$lessVal = 0;
					for ($mxwElement = (($n - $elmWidth) - ((($elements - $bar) - 2))); ($mxwElement > $maxWidth); --$mxwElement) 
					{
						$lessVal += $this->getCombinations(((($n - $elmWidth) - $mxwElement) - 1), (($elements - $bar) - 3));
					}
					$subVal -= ($lessVal * ((($elements - 1) - $bar)));
				}
				else
					if ((($n - $elmWidth) > $maxWidth))
					{
						--$subVal;
					}
				$val -= $subVal;
				if (($val < 0))
					break;
			}
			$val += $subVal;
			$n -= $elmWidth;
			$this->widths[$bar] = $elmWidth;
		}
		$this->widths[$bar] = $n;
	}
	
		/**
		*
	* @param string $string String to "search" from
	* @param int $index Index of the letter we want.
	* @return string The letter found on $index.
	*/
	private function charAt2($string, $index){
		if($index < strlen($string)){
			return substr($string, $index, 1);
		}
		else{
			return -1;
		}
	}
	
	private function bin2pat2 ($bin) // [String bin]
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
				if ($this->charAt2($bin, $i) == '1')
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
				if ($this->charAt2($bin, $i) == '0')
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
}
?>
