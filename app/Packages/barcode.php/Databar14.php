<?php

require_once('Common.php');
require_once('BigInteger.php');

	/**
	 * GS1データバー 標準型 (RSS-14) 作成クラス
	 */
	class Databar14 {

		/* GS1 Databar RSS 14 のタイプ */
		/*! 標準型 (RSS-14 Omni-directional) */
		const OMNIDIRECTIONAL = 0;
		/*! 二層型 (RSS-14 Stacked) */
		const STACKED = 1;
		/*! 標準二層型 (RSS-14 Stacked Omni-directional) */
		const STACKED_OMNIDIRECTIONAL = 2;


		/*! GS1 Databa RSS 14 のタイプ */
		var $symbolType = self::OMNIDIRECTIONAL;

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

			if ($this->symbolType == self::OMNIDIRECTIONAL)
			{
				$h[0] = $height;
			}
			else if ($this->symbolType == self::STACKED)
			{
				$h[$i] = $minWidthDot * $this->row_height[$i];
			}
			else if ($this->symbolType == self::STACKED_OMNIDIRECTIONAL)
			{
				$h[$i] = $minWidthDot * $this->row_height[$i];
				if (($h[$i] < 0))
				{
					$h[$i] = ($height - $minWidthDot * 3) / 2;
				}
			}

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
			$gazouHeight = $yPos + $this->FontSize + 5;
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

			if ($this->symbolType == self::OMNIDIRECTIONAL)
			{
				$h[0] = $height;
			}
			else if ($this->symbolType == self::STACKED)
			{
				$h[$i] = $minWidthDot * $this->row_height[$i];
			}
			else if ($this->symbolType == self::STACKED_OMNIDIRECTIONAL)
			{
				$h[$i] = $minWidthDot * $this->row_height[$i];
				if (($h[$i] < 0))
				{
					$h[$i] = ($height - $minWidthDot * 3) / 2;
				}
			}

			for ($j = 0; $j < strlen($this->pattern[$i]); $j++) 
			{
				$w = intval(substr($this->pattern[$i], $j, 1));
				if ($j % 2 == 0)
				{
					if($xPos > 0 || $w > 0)
					{
						imagefilledrectangle($img, $xPos, $yPos, $xPos + $w * $minWidthDot + $this->KuroBarCousei,  $yPos + $h[$i], $black);
					}
					$xPos += ($w * $minWidthDot + $this->KuroBarCousei);
				}
				else
				{
					$xPos += ($w * $minWidthDot + $this->ShiroBarCousei);
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
	
	/**
		 * バーコードの描画を行います。バーコード全体の幅を指定するのではなく、バーを描画する最小単位のドット数を指定します。
		 * @param $code 描画を行うバーコードのコード(テキスト)
		 * @param $minWidthDot 横方向の最少描画ドット数
		 * @param $height バーコードのバーの高さ(単位：ドット)
		 * @return バーコードのイメージを返します。
		 */
	function draw_by_width($code, $width, $height, $minWidthDot) {
		
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

			if ($this->symbolType == self::OMNIDIRECTIONAL)
			{
				$h[0] = $height;
			}
			else if ($this->symbolType == self::STACKED)
			{
				$h[$i] = $minWidthDot * $this->row_height[$i];
			}
			else if ($this->symbolType == self::STACKED_OMNIDIRECTIONAL)
			{
				$h[$i] = $minWidthDot * $this->row_height[$i];
				if (($h[$i] < 0))
				{
					$h[$i] = ($height - $minWidthDot * 3) / 2;
				}
			}

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
		
		//$minWidthDot = $width / $xPos;
		
		$gazouHeight = $yPos;
		if($this->TextWrite == true)
		{
			$gazouHeight = $yPos + $this->FontSize + 5;
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

			if ($this->symbolType == self::OMNIDIRECTIONAL)
			{
				$h[0] = $height;
			}
			else if ($this->symbolType == self::STACKED)
			{
				$h[$i] = $minWidthDot * $this->row_height[$i];
			}
			else if ($this->symbolType == self::STACKED_OMNIDIRECTIONAL)
			{
				$h[$i] = $minWidthDot * $this->row_height[$i];
				if (($h[$i] < 0))
				{
					$h[$i] = ($height - $minWidthDot * 3) / 2;
				}
			}

			for ($j = 0; $j < strlen($this->pattern[$i]); $j++) 
			{
				$w = intval(substr($this->pattern[$i], $j, 1));
				if ($j % 2 == 0)
				{
					if($xPos > 0 || $w > 0)
					{
						imagefilledrectangle($img, $xPos, $yPos, $xPos + $w * $minWidthDot + $this->KuroBarCousei,  $yPos + $h[$i], $black);
					}
					$xPos += ($w * $minWidthDot + $this->KuroBarCousei);
				}
				else
				{
					$xPos += ($w * $minWidthDot + $this->ShiroBarCousei);
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

		return $img;
	}
	protected $g_sum_table;	// int[]
	protected $t_table;	// int[]
	protected $widths;	// int[]
	protected $modules_odd;	// int[]
	protected $modules_even;	// int[]
	protected $widest_odd;	// int[]
	protected $widest_even;	// int[]
	protected $checksum_weight;	// int[]
	protected $finder_pattern;	// int[]
	protected $linkageFlag;	// boolean
	protected $grid;	// boolean[][]
	protected $seperator;	// boolean[]
	protected $error_msg;	// String
	protected $pattern;	// String[]
	protected $row_height;	// int[]
	protected $hrt;	// String

	public function encode ($content) // [String content]
	{

		$this->g_sum_table = array(0, 161, 961, 2015, 2715, 0, 336, 1036, 1516);
		$this->t_table = array(1, 10, 34, 70, 126, 4, 20, 48, 81);
		$this->widths = array();
		$this->modules_odd = array(12, 10, 8, 6, 4, 5, 7, 9, 11);
		$this->modules_even = array(4, 6, 8, 10, 12, 10, 8, 6, 4);
		$this->widest_odd = array(8, 6, 4, 3, 1, 2, 4, 6, 8);
		$this->widest_even = array(1, 3, 5, 6, 8, 7, 5, 3, 1);
		$this->checksum_weight = array(1, 3, 9, 27, 2, 6, 18, 54, 4, 12, 36, 29, 8, 24, 72, 58, 16, 48, 65, 37, 32, 17, 51, 74, 64, 34, 23, 69, 49, 68, 46, 59);
		$this->finder_pattern = array(3, 8, 2, 1, 1, 3, 5, 5, 1, 1, 3, 3, 7, 1, 1, 3, 1, 9, 1, 1, 2, 7, 4, 1, 1, 2, 5, 6, 1, 1, 2, 3, 8, 1, 1, 1, 5, 7, 1, 1, 1, 3, 9, 1, 1);
		$this->grid = array();
		$this->seperator = array();
		$this->error_msg = "";

		$accum = null;
		$left_reg = null;
		$right_reg = null;
		$data_character = array();
		$data_group = array();
		$v_odd = array();
		$v_even = array();
		$i = null;
		$data_widths = array();
		$checksum = null;
		$c_left = null;
		$c_right = null;
		$total_widths = array();
		$writer = null;
		$latch = null;
		$j = null;
		$count = null;
		$check_digit = null;
		$bin = null;
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

		for($i = 0; $i < 5; $i++)
		{
			for($j = 0; $j < 100; $j++)
			{
				$this->grid[$i][$j] = false;
			}
		}

		$accum = new Math_BigInteger($content, 10);

		if ($this->linkageFlag)
		{
			$accum = $accum->add(new Math_BigInteger("10000000000000"));
			$compositeOffset = 1;
		}
		list($quotient, $remainder) = $accum->divide(new Math_BigInteger("4537077"));
		$left_reg = $quotient;
		$right_reg = $remainder;

		list($quotient, $remainder) = $left_reg->divide(new Math_BigInteger("1597"));
		$data_character[0] = intval($quotient->toString());
		$data_character[1] = intval($remainder->toString());

		list($quotient, $remainder) = $right_reg->divide(new Math_BigInteger("1597"));
		$data_character[2] = intval($quotient->toString());
		$data_character[3] = intval($remainder->toString());

		if (((($data_character[0] >= 0)) && (($data_character[0] <= 160))))
		{
			$data_group[0] = 0;
		}
		if (((($data_character[0] >= 161)) && (($data_character[0] <= 960))))
		{
			$data_group[0] = 1;
		}
		if (((($data_character[0] >= 961)) && (($data_character[0] <= 2014))))
		{
			$data_group[0] = 2;
		}
		if (((($data_character[0] >= 2015)) && (($data_character[0] <= 2714))))
		{
			$data_group[0] = 3;
		}
		if (((($data_character[0] >= 2715)) && (($data_character[0] <= 2840))))
		{
			$data_group[0] = 4;
		}
		if (((($data_character[1] >= 0)) && (($data_character[1] <= 335))))
		{
			$data_group[1] = 5;
		}
		if (((($data_character[1] >= 336)) && (($data_character[1] <= 1035))))
		{
			$data_group[1] = 6;
		}
		if (((($data_character[1] >= 1036)) && (($data_character[1] <= 1515))))
		{
			$data_group[1] = 7;
		}
		if (((($data_character[1] >= 1516)) && (($data_character[1] <= 1596))))
		{
			$data_group[1] = 8;
		}
		if (((($data_character[3] >= 0)) && (($data_character[3] <= 335))))
		{
			$data_group[3] = 5;
		}
		if (((($data_character[3] >= 336)) && (($data_character[3] <= 1035))))
		{
			$data_group[3] = 6;
		}
		if (((($data_character[3] >= 1036)) && (($data_character[3] <= 1515))))
		{
			$data_group[3] = 7;
		}
		if (((($data_character[3] >= 1516)) && (($data_character[3] <= 1596))))
		{
			$data_group[3] = 8;
		}
		if (((($data_character[2] >= 0)) && (($data_character[2] <= 160))))
		{
			$data_group[2] = 0;
		}
		if (((($data_character[2] >= 161)) && (($data_character[2] <= 960))))
		{
			$data_group[2] = 1;
		}
		if (((($data_character[2] >= 961)) && (($data_character[2] <= 2014))))
		{
			$data_group[2] = 2;
		}
		if (((($data_character[2] >= 2015)) && (($data_character[2] <= 2714))))
		{
			$data_group[2] = 3;
		}
		if (((($data_character[2] >= 2715)) && (($data_character[2] <= 2840))))
		{
			$data_group[2] = 4;
		}
		$v_odd[0] = ((($data_character[0] - $this->g_sum_table[$data_group[0]])) / $this->t_table[$data_group[0]]);
		$v_even[0] = ((($data_character[0] - $this->g_sum_table[$data_group[0]])) % $this->t_table[$data_group[0]]);
		$v_odd[1] = ((($data_character[1] - $this->g_sum_table[$data_group[1]])) % $this->t_table[$data_group[1]]);
		$v_even[1] = ((($data_character[1] - $this->g_sum_table[$data_group[1]])) / $this->t_table[$data_group[1]]);
		$v_odd[3] = ((($data_character[3] - $this->g_sum_table[$data_group[3]])) % $this->t_table[$data_group[3]]);
		$v_even[3] = ((($data_character[3] - $this->g_sum_table[$data_group[3]])) / $this->t_table[$data_group[3]]);
		$v_odd[2] = ((($data_character[2] - $this->g_sum_table[$data_group[2]])) / $this->t_table[$data_group[2]]);
		$v_even[2] = ((($data_character[2] - $this->g_sum_table[$data_group[2]])) % $this->t_table[$data_group[2]]);
		for ($i = 0; ($i < 4); $i++) 
		{
			if (((($i == 0)) || (($i == 2))))
			{
				$this->getWidths($v_odd[$i], $this->modules_odd[$data_group[$i]], 4, $this->widest_odd[$data_group[$i]], 1);
				$data_widths[0][$i] = $this->widths[0];
				$data_widths[2][$i] = $this->widths[1];
				$data_widths[4][$i] = $this->widths[2];
				$data_widths[6][$i] = $this->widths[3];
				$this->getWidths($v_even[$i], $this->modules_even[$data_group[$i]], 4, $this->widest_even[$data_group[$i]], 0);
				$data_widths[1][$i] = $this->widths[0];
				$data_widths[3][$i] = $this->widths[1];
				$data_widths[5][$i] = $this->widths[2];
				$data_widths[7][$i] = $this->widths[3];
			}
			else
			{
				$this->getWidths($v_odd[$i], $this->modules_odd[$data_group[$i]], 4, $this->widest_odd[$data_group[$i]], 0);
				$data_widths[0][$i] = $this->widths[0];
				$data_widths[2][$i] = $this->widths[1];
				$data_widths[4][$i] = $this->widths[2];
				$data_widths[6][$i] = $this->widths[3];
				$this->getWidths($v_even[$i], $this->modules_even[$data_group[$i]], 4, $this->widest_even[$data_group[$i]], 1);
				$data_widths[1][$i] = $this->widths[0];
				$data_widths[3][$i] = $this->widths[1];
				$data_widths[5][$i] = $this->widths[2];
				$data_widths[7][$i] = $this->widths[3];
			}
		}
		$checksum = 0;
		for ($i = 0; ($i < 8); $i++) 
		{
			$checksum += ($this->checksum_weight[$i] * $data_widths[$i][0]);
			$checksum += ($this->checksum_weight[($i + 8)] * $data_widths[$i][1]);
			$checksum += ($this->checksum_weight[($i + 16)] * $data_widths[$i][2]);
			$checksum += ($this->checksum_weight[($i + 24)] * $data_widths[$i][3]);
		}
		$checksum %= 79;
		if (($checksum >= 8))
		{
			$checksum++;
		}
		if (($checksum >= 72))
		{
			$checksum++;
		}
		$c_left = ($checksum / 9);
		$c_right = ($checksum % 9);
		$total_widths[0] = 1;
		$total_widths[1] = 1;
		$total_widths[44] = 1;
		$total_widths[45] = 1;
		for ($i = 0; ($i < 8); $i++) 
		{
			$total_widths[($i + 2)] = $data_widths[$i][0];
			$total_widths[($i + 15)] = $data_widths[(7 - $i)][1];
			$total_widths[($i + 23)] = $data_widths[$i][3];
			$total_widths[($i + 36)] = $data_widths[(7 - $i)][2];
		}
		for ($i = 0; ($i < 5); $i++) 
		{
			$total_widths[($i + 10)] = $this->finder_pattern[($i + ((5 * (int)$c_left)))];
			$total_widths[($i + 31)] = $this->finder_pattern[(((4 - $i)) + ((5 * (int)$c_right)))];
		}
		$row_count = 0;
		$symbol_width = 0;
		for ($i = 0; ($i < 100); $i++) 
		{
			$this->seperator[$i] =  FALSE ;
		}
		if (($this->symbolType == self::OMNIDIRECTIONAL /* could not resolve enum */))
		{
			$writer = 0;
			$latch = '0';
			for ($i = 0; ($i < 46); $i++) 
			{
				for ($j = 0; ($j < $total_widths[$i]); $j++) 
				{
					if (($latch == '1'))
					{
						$this->setGridModule($row_count, $writer);
					}
					$writer++;
				}
				if (($latch == '1'))
				{
					$latch = '0';
				}
				else
				{
					$latch = '1';
				}
			}
			if (($symbol_width < $writer))
			{
				$symbol_width = $writer;
			}
			if ($this->linkageFlag)
			{
				for ($i = 4; ($i < 92); $i++) 
				{
					$this->seperator[$i] = (!($this->grid[0][$i]));
				}
				$latch = '1';
				for ($i = 16; ($i < 32); $i++) 
				{
					if (!($this->grid[0][$i]))
					{
						if (($latch == '1'))
						{
							$this->seperator[$i] =  TRUE ;
							$latch = '0';
						}
						else
						{
							$this->seperator[$i] =  FALSE ;
							$latch = '1';
						}
					}
					else
					{
						$this->seperator[$i] =  FALSE ;
						$latch = '1';
					}
				}
				$latch = '1';
				for ($i = 63; ($i < 78); $i++) 
				{
					if (!($this->grid[0][$i]))
					{
						if (($latch == '1'))
						{
							$this->seperator[$i] =  TRUE ;
							$latch = '0';
						}
						else
						{
							$this->seperator[$i] =  FALSE ;
							$latch = '1';
						}
					}
					else
					{
						$this->seperator[$i] =  FALSE ;
						$latch = '1';
					}
				}
			}
			$row_count = ($row_count + 1);
			
			$count = 0;
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

		}
		$count = 0;
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
		if (($this->symbolType == self::STACKED /* could not resolve enum */))
		{
			$writer = 0;
			$latch = '0';
			for ($i = 0; ($i < 23); $i++) 
			{
				for ($j = 0; ($j < $total_widths[$i]); $j++) 
				{
					if (($latch == '1'))
					{
						$this->setGridModule($row_count, $writer);
					}
					else
					{
						$this->unsetGridModule($row_count, $writer);
					}
					$writer++;
				}
				if (($latch == '1'))
				{
					$latch = '0';
				}
				else
				{
					$latch = '1';
				}
			}
			$this->setGridModule($row_count, $writer);
			$this->unsetGridModule($row_count, ($writer + 1));
			$row_count = ($row_count + 2);
			$this->setGridModule($row_count, 0);
			$this->unsetGridModule($row_count, 1);
			$writer = 0;
			$latch = '1';
			for ($i = 23; ($i < 46); $i++) 
			{
				for ($j = 0; ($j < $total_widths[$i]); $j++) 
				{
					if (($latch == '1'))
					{
						$this->setGridModule($row_count, ($writer + 2));
					}
					else
					{
						$this->unsetGridModule($row_count, ($writer + 2));
					}
					$writer++;
				}
				if (($latch == '1'))
				{
					$latch = '0';
				}
				else
				{
					$latch = '1';
				}
			}
			for ($i = 4; ($i < 46); $i++) 
			{
				if (($this->gridModuleIsSet(($row_count - 2), $i) == $this->gridModuleIsSet($row_count, $i)))
				{
					if (!($this->gridModuleIsSet(($row_count - 2), $i)))
					{
						$this->setGridModule(($row_count - 1), $i);
					}
				}
				else
				{
					if (!($this->gridModuleIsSet(($row_count - 1), ($i - 1))))
					{
						$this->setGridModule(($row_count - 1), $i);
					}
				}
			}
			if ($this->linkageFlag)
			{
				for ($i = 4; ($i < 46); $i++) 
				{
					$this->seperator[$i] = (!($this->grid[0][$i]));
				}
				$latch = '1';
				for ($i = 16; ($i < 32); $i++) 
				{
					if (!($this->grid[0][$i]))
					{
						if (($latch == '1'))
						{
							$this->seperator[$i] =  TRUE ;
							$latch = '0';
						}
						else
						{
							$this->seperator[$i] =  FALSE ;
							$latch = '1';
						}
					}
					else
					{
						$this->seperator[$i] =  FALSE ;
						$latch = '1';
					}
				}
			}
			$row_count = ($row_count + 1);
			if (($symbol_width < 50))
			{
				$symbol_width = 50;
			}
		}
		if (($this->symbolType == self::STACKED_OMNIDIRECTIONAL /* could not resolve enum */))
		{
			$writer = 0;
			$latch = '0';
			for ($i = 0; ($i < 23); $i++) 
			{
				for ($j = 0; ($j < $total_widths[$i]); $j++) 
				{
					if (($latch == '1'))
					{
						$this->setGridModule($row_count, $writer);
					}
					else
					{
						$this->unsetGridModule($row_count, $writer);
					}
					$writer++;
				}
				$latch = (( (($latch == '1')) ? '0' : '1' ));
			}
			$this->setGridModule($row_count, $writer);
			$this->unsetGridModule($row_count, ($writer + 1));
			$row_count = ($row_count + 4);
			$this->setGridModule($row_count, 0);
			$this->unsetGridModule($row_count, 1);
			$writer = 0;
			$latch = '1';
			for ($i = 23; ($i < 46); $i++) 
			{
				for ($j = 0; ($j < $total_widths[$i]); $j++) 
				{
					if (($latch == '1'))
					{
						$this->setGridModule($row_count, ($writer + 2));
					}
					else
					{
						$this->unsetGridModule($row_count, ($writer + 2));
					}
					$writer++;
				}
				if (($latch == '1'))
				{
					$latch = '0';
				}
				else
				{
					$latch = '1';
				}
			}
			for ($i = 5; ($i < 46); $i += 2) 
			{
				$this->setGridModule(($row_count - 2), $i);
			}
			for ($i = 4; ($i < 46); $i++) 
			{
				if (!($this->gridModuleIsSet(($row_count - 4), $i)))
				{
					$this->setGridModule(($row_count - 3), $i);
				}
			}
			$latch = '1';
			for ($i = 17; ($i < 33); $i++) 
			{
				if (!($this->gridModuleIsSet(($row_count - 4), $i)))
				{
					if (($latch == '1'))
					{
						$this->setGridModule(($row_count - 3), $i);
						$latch = '0';
					}
					else
					{
						$this->unsetGridModule(($row_count - 3), $i);
						$latch = '1';
					}
				}
				else
				{
					$this->unsetGridModule(($row_count - 3), $i);
					$latch = '1';
				}
			}
			for ($i = 4; ($i < 46); $i++) 
			{
				if (!($this->gridModuleIsSet($row_count, $i)))
				{
					$this->setGridModule(($row_count - 1), $i);
				}
			}
			$latch = '1';
			for ($i = 16; ($i < 32); $i++) 
			{
				if (!($this->gridModuleIsSet($row_count, $i)))
				{
					if (($latch == '1'))
					{
						$this->setGridModule(($row_count - 1), $i);
						$latch = '0';
					}
					else
					{
						$this->unsetGridModule(($row_count - 1), $i);
						$latch = '1';
					}
				}
				else
				{
					$this->unsetGridModule(($row_count - 1), $i);
					$latch = '1';
				}
			}
			if (($symbol_width < 50))
			{
				$symbol_width = 50;
			}
			if ($this->linkageFlag)
			{
				for ($i = 4; ($i < 46); $i++) 
				{
					$this->seperator[$i] = (!($this->grid[0][$i]));
				}
				$latch = '1';
				for ($i = 16; ($i < 32); $i++) 
				{
					if (!($this->grid[0][$i]))
					{
						if (($latch == '1'))
						{
							$this->seperator[$i] =  TRUE ;
							$latch = '0';
						}
						else
						{
							$this->seperator[$i] =  FALSE ;
							$latch = '1';
						}
					}
					else
					{
						$this->seperator[$i] =  FALSE ;
						$latch = '1';
					}
				}
			}
			$row_count = ($row_count + 1);
		}
		$this->pattern = array();
		$this->row_height = array();
		if ($this->linkageFlag)
		{
			$bin = "";
			for ($j = 0; ($j < $symbol_width); $j++) 
			{
				if ($this->seperator[$j])
				{
					$bin = $bin . "1";
				}
				else
				{
					$bin = $bin . "0";
				}
			}
			$this->pattern[0] = $this->bin2pat2($bin);
			$this->row_height[0] = 1;
		}
		for ($i = 0; ($i < $row_count); $i++) 
		{
			$bin = "";
			for ($j = 0; ($j < $symbol_width); $j++) 
			{
				if ($this->grid[$i][$j])
				{
					$bin = $bin . "1";
				}
				else
				{
					$bin = $bin . "0";
				}
			}
			$this->pattern[($i + $compositeOffset)] = $this->bin2pat2($bin);
		}
		if (($this->symbolType == self::OMNIDIRECTIONAL /* could not resolve enum */))
		{
			$this->row_height[(0 + $compositeOffset)] = -1;
		}
		if (($this->symbolType == self::STACKED /* could not resolve enum */))
		{
			$this->row_height[(0 + $compositeOffset)] = 5;
			$this->row_height[(1 + $compositeOffset)] = 1;
			$this->row_height[(2 + $compositeOffset)] = 7;
		}
		if (($this->symbolType == self::STACKED_OMNIDIRECTIONAL /* could not resolve enum */))
		{
			$this->row_height[(0 + $compositeOffset)] = -1;
			$this->row_height[(1 + $compositeOffset)] = 1;
			$this->row_height[(2 + $compositeOffset)] = 1;
			$this->row_height[(3 + $compositeOffset)] = 1;
			$this->row_height[(4 + $compositeOffset)] = -1;
		}
		if ($this->linkageFlag)
		{
			$row_count++;
		}
		return  TRUE ;
	}
	protected function getCombinations ($n, $r) // [int n, int r]
	{
		$i = null;
		$j = null;
		$maxDenom = null;
		$minDenom = null;
		$val = null;
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
				$j++;
			}
		}
		for (; ($j <= $minDenom); $j++) 
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
		for ($bar = 0; ($bar < ($elements - 1)); $bar++) 
		{
			for ($elmWidth = 1, $narrowMask |= ((1 << $bar)); ; $elmWidth++, $narrowMask &= ~((1 << $bar))) 
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
	protected function setGridModule ($row, $column) // [int row, int column]
	{
		$this->grid[$row][$column] =  TRUE ;
	}
	protected function unsetGridModule ($row, $column) // [int row, int column]
	{
		$this->grid[$row][$column] =  FALSE ;
	}
	protected function gridModuleIsSet ($row, $column) // [int row, int column]
	{
		return $this->grid[$row][$column];
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
