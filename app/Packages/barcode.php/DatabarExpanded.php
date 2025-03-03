<?php
	/**
	 * GS1データバー 拡張型 (RSS Expanded) 作成クラス
	 */
	class DatabarExpanded {

		/* GS1 Databar RSS Expanded のタイプ */
		/*! 一層型 (RSS Expanded) */
		const UNSTACKED = 0;
		/*! 二層型 (RSS Expanded Stacked) */
		const STACKED = 1;


		/*! GS1 Databa RSS 14 のタイプ */
		var $symbolType = self::UNSTACKED;

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
		
		$dansu = 0;
        if ($this->symbolType == self::STACKED)
        {
			for ($i = 0; $i < count($this->row_height); $i++)
            {
                if ($this->row_height[$i] == -1)
                {
                    $dansu++;
                }
            }
        }


		$xPos = 0;
		$yPos = 0;
		$h = array();
		for ($i = 0; $i < count($this->pattern); $i++) 
		{
			$xPos = 0;

			if ($this->symbolType == self::UNSTACKED)
			{
				$h[0] = $height;
			}
			else if ($this->symbolType == self::STACKED)
			{
				$h[$i] = $minWidthDot * $this->row_height[$i];
				if (($h[$i] < 0))
				{
					$h[$i] = (int)(($height - ($minWidthDot * 3 * ($dansu - 1))) / $dansu);
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

			if ($i == 0) $xPosMax = $xPos;

			$yPos += $h[$i];
		}

		$gazouHeight = $yPos;
		if($this->TextWrite == true)
		{
			$gazouHeight = $yPos + $this->FontSize + 5;
		}


		$img = ImageCreate($xPosMax, $gazouHeight);
		$white = ImageColorAllocate($img, 0xFF, 0xFF, 0xFF);
		$black = ImageColorAllocate($img, 0x00, 0x00, 0x00);

		imagesetthickness($img, $this->BarThick);

		$xPos = 0;
		$yPos = 0;
		$h = array();
		for ($i = 0; $i < count($this->pattern); $i++) 
		{
			$xPos = 0;
			if ($this->symbolType == self::UNSTACKED)
			{
				$h[0] = $height;
			}
			else if ($this->symbolType == self::STACKED)
			{
				$h[$i] = $minWidthDot * $this->row_height[$i];
				if (($h[$i] < 0))
				{
					$h[$i] = (int)(($height - ($minWidthDot * 3 * ($dansu - 1))) / $dansu);
				}
			}

			for ($j = 0; $j < strlen($this->pattern[$i]); $j++) 
			{
				$w = intval (substr($this->pattern[$i], $j, 1));
				if ((($j % 2) == 0))
				{
					if($xPos > 0 || $w > 0)
					{
						imagefilledrectangle($img, $xPos, $yPos, $xPos + $w * $minWidthDot + $this->KuroBarCousei,  $yPos + $h[$i], $black);
					}
					$xPos += $w * $minWidthDot + $this->KuroBarCousei;
				}
				else
				{
					$xPos += $w * $minWidthDot + $this->ShiroBarCousei;
				}
			}
			if ($i == 0) $xPosMax = $xPos;
			$yPos += $h[$i];
		}


		// 添え字
		if($this->TextWrite) {
			if(strlen($this->strText) > 1)
			{
				$interval = ($xPosMax - $this->FontSize) / (strlen($this->strText) - 1);
			}
			else
			{
				$interval = $xPosMax / 2;
			}
			for($i = 0; $i < strlen($this->strText); $i++) {
				ImageTTFText($img, $this->FontSize, 0, (int)($i * $interval), $gazouHeight
					,$black, $this->FontName, substr($this->strText, $i, 1));
			}
		}
		$this->outputCode = $this->strText; 

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
		
		$dansu = 0;
		if ($this->symbolType == self::STACKED)
		{
			for ($i = 0; $i < count($this->row_height); $i++)
			{
				if ($this->row_height[$i] == -1)
				{
					$dansu++;
				}
			}
		}


		$xPos = 0;
		$yPos = 0;
		$h = array();
		for ($i = 0; $i < count($this->pattern); $i++) 
		{
			$xPos = 0;

			if ($this->symbolType == self::UNSTACKED)
			{
				$h[0] = $height;
			}
			else if ($this->symbolType == self::STACKED)
			{
				$h[$i] = $minWidthDot * $this->row_height[$i];
				if (($h[$i] < 0))
				{
					$h[$i] = (int)(($height - ($minWidthDot * 3 * ($dansu - 1))) / $dansu);
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

			if ($i == 0) $xPosMax = $xPos;

			$yPos += $h[$i];
		}

		$gazouHeight = $yPos;
		if($this->TextWrite == true)
		{
			$gazouHeight = $yPos + $this->FontSize + 5;
		}


		$img = ImageCreate($xPosMax, $gazouHeight);
		$white = ImageColorAllocate($img, 0xFF, 0xFF, 0xFF);
		$black = ImageColorAllocate($img, 0x00, 0x00, 0x00);

		imagesetthickness($img, $this->BarThick);

		$xPos = 0;
		$yPos = 0;
		$h = array();
		for ($i = 0; $i < count($this->pattern); $i++) 
		{
			$xPos = 0;
			if ($this->symbolType == self::UNSTACKED)
			{
				$h[0] = $height;
			}
			else if ($this->symbolType == self::STACKED)
			{
				$h[$i] = $minWidthDot * $this->row_height[$i];
				if (($h[$i] < 0))
				{
					$h[$i] = (int)(($height - ($minWidthDot * 3 * ($dansu - 1))) / $dansu);
				}
			}

			for ($j = 0; $j < strlen($this->pattern[$i]); $j++) 
			{
				$w = intval (substr($this->pattern[$i], $j, 1));
				if ((($j % 2) == 0))
				{
					if($xPos > 0 || $w > 0)
					{
						imagefilledrectangle($img, $xPos, $yPos, $xPos + $w * $minWidthDot + $this->KuroBarCousei,  $yPos + $h[$i], $black);
					}
					$xPos += $w * $minWidthDot + $this->KuroBarCousei;
				}
				else
				{
					$xPos += $w * $minWidthDot + $this->ShiroBarCousei;
				}
			}
			if ($i == 0) $xPosMax = $xPos;
			$yPos += $h[$i];
		}


		// 添え字
		if($this->TextWrite) {
			if(strlen($this->strText) > 1)
			{
				$interval = ($xPosMax - $this->FontSize) / (strlen($this->strText) - 1);
			}
			else
			{
				$interval = $xPosMax / 2;
			}
			for($i = 0; $i < strlen($this->strText); $i++) {
				ImageTTFText($img, $this->FontSize, 0, (int)($i * $interval), $gazouHeight
					,$black, $this->FontName, substr($this->strText, $i, 1));
			}
		}
		$this->outputCode = $this->strText; 

		if(!isOK()) {
			//SAMPLE 描画
			$red = ImageColorAllocate($img, 0xFF, 0x00, 0x00);
			ImageTTFText($img, 12, 0, 2, 14	,$red, $this->FontName, "SAMPLE");
		}
		
		return $img;
	}
	
	/* enum: DatabarType translated to consts */
	protected $error_msg;	// String
	protected $pattern;	// String[]
	protected $row_height;	// int[]
	protected $g_sum_exp;	// int[]
	protected $t_even_exp;	// int[]
	protected $modules_odd_exp;	// int[]
	protected $modules_even_exp;	// int[]
	protected $widest_odd_exp;	// int[]
	protected $widest_even_exp;	// int[]
	protected $checksum_weight_exp;	// int[]
	protected $finder_pattern_exp;	// int[]
	protected $finder_sequence;	// int[]
	protected $weight_rows;	// int[]
	protected $source;	// String
	protected $binary_string;	// String
	protected $general_field;	// String
	protected $general_field_type;	// encodeMode[]
	protected $widths;	// int[]
	protected $linkageFlag;	// boolean
	protected $preferredNoOfColumns;	// int
	/* enum: encodeMode translated to consts */
	const encodeMode_NUMERIC = 0;
	const encodeMode_ALPHA = 1;
	const encodeMode_ISOIEC = 2;
	const encodeMode_INVALID_CHAR = 3;
	const encodeMode_ANY_ENC = 4;
	const encodeMode_ALPHA_OR_ISO = 5;
	protected $gCode;	// String
	protected $strText;	// String
	public function setSymbolType_DatabarType ($val) // [DatabarType val]
	{
		$this->symbolType = $val;
	}
	public function setSymbolType () 
	{
		return $this->symbolType;
	}
	public function setNoOfColumns ($columns) // [int columns]
	{
		$this->preferredNoOfColumns = $columns;
	}
	protected function setLinkageFlag () 
	{
		$this->linkageFlag =  TRUE ;
	}
	protected function unsetLinkageFlag () 
	{
		$this->linkageFlag =  FALSE ;
	}
	public function encode ($code) // [String code]
	{
		$this->error_msg = "";
		$this->g_sum_exp = array(0, 348, 1388, 2948, 3988);
		$this->t_even_exp = array(4, 20, 52, 104, 204);
		$this->modules_odd_exp = array(12, 10, 8, 6, 4);
		$this->modules_even_exp = array(5, 7, 9, 11, 13);
		$this->widest_odd_exp = array(7, 5, 4, 3, 1);
		$this->widest_even_exp = array(2, 4, 5, 6, 8);
		$this->checksum_weight_exp = array(1, 3, 9, 27, 81, 32, 96, 77, 20, 60, 180, 118, 143, 7, 21, 63, 189, 145, 13, 39, 117, 140, 209, 205, 193, 157, 49, 147, 19, 57, 171, 91, 62, 186, 136, 197, 169, 85, 44, 132, 185, 133, 188, 142, 4, 12, 36, 108, 113, 128, 173, 97, 80, 29, 87, 50, 150, 28, 84, 41, 123, 158, 52, 156, 46, 138, 203, 187, 139, 206, 196, 166, 76, 17, 51, 153, 37, 111, 122, 155, 43, 129, 176, 106, 107, 110, 119, 146, 16, 48, 144, 10, 30, 90, 59, 177, 109, 116, 137, 200, 178, 112, 125, 164, 70, 210, 208, 202, 184, 130, 179, 115, 134, 191, 151, 31, 93, 68, 204, 190, 148, 22, 66, 198, 172, 94, 71, 2, 6, 18, 54, 162, 64, 192, 154, 40, 120, 149, 25, 75, 14, 42, 126, 167, 79, 26, 78, 23, 69, 207, 199, 175, 103, 98, 83, 38, 114, 131, 182, 124, 161, 61, 183, 127, 170, 88, 53, 159, 55, 165, 73, 8, 24, 72, 5, 15, 45, 135, 194, 160, 58, 174, 100, 89);
		$this->finder_pattern_exp = array(1, 8, 4, 1, 1, 1, 1, 4, 8, 1, 3, 6, 4, 1, 1, 1, 1, 4, 6, 3, 3, 4, 6, 1, 1, 1, 1, 6, 4, 3, 3, 2, 8, 1, 1, 1, 1, 8, 2, 3, 2, 6, 5, 1, 1, 1, 1, 5, 6, 2, 2, 2, 9, 1, 1, 1, 1, 9, 2, 2);
		$this->finder_sequence = array(1, 2, 0, 0, 0, 0, 0, 0, 0, 0, 0, 1, 4, 3, 0, 0, 0, 0, 0, 0, 0, 0, 1, 6, 3, 8, 0, 0, 0, 0, 0, 0, 0, 1, 10, 3, 8, 5, 0, 0, 0, 0, 0, 0, 1, 10, 3, 8, 7, 12, 0, 0, 0, 0, 0, 1, 10, 3, 8, 9, 12, 11, 0, 0, 0, 0, 1, 2, 3, 4, 5, 6, 7, 8, 0, 0, 0, 1, 2, 3, 4, 5, 6, 7, 10, 9, 0, 0, 1, 2, 3, 4, 5, 6, 7, 10, 11, 12, 0, 1, 2, 3, 4, 5, 8, 7, 10, 9, 12, 11);
		$this->weight_rows = array(0, 1, 2, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 5, 6, 3, 4, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 9, 10, 3, 4, 13, 14, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 17, 18, 3, 4, 13, 14, 7, 8, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 17, 18, 3, 4, 13, 14, 11, 12, 21, 22, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 17, 18, 3, 4, 13, 14, 15, 16, 21, 22, 19, 20, 0, 0, 0, 0, 0, 0, 0, 0, 0, 1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13, 14, 0, 0, 0, 0, 0, 0, 0, 1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 17, 18, 15, 16, 0, 0, 0, 0, 0, 1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 17, 18, 19, 20, 21, 22, 0, 0, 0, 1, 2, 3, 4, 5, 6, 7, 8, 13, 14, 11, 12, 17, 18, 15, 16, 21, 22, 19, 20);
		$this->widths = array();
		$this->preferredNoOfColumns = 0;


		$i = null;
		$j = null;
		$k = null;
		$data_chars = null;
		$vs = array();
		$group = array();
		$v_odd = array();
		$v_even = array();
		$char_widths = array();
		$checksum = null;
		$row = null;
		$check_char = null;
		$c_group = null;
		$c_odd = null;
		$c_even = null;
		$check_widths = array();
		$pattern_width = null;
		$elements = array();
		$codeblocks = null;
		$stack_rows = null;
		$blocksPerRow = null;
		$current_block = null;
		$current_row = null;
		$special_case_row = null;
		$elements_in_sub = null;
		$reader = null;
		$writer = null;
		$sub_elements = array();
		$l = null;
		$symbol_row = null;
		$seperator_binary = null;
		$seperator_pattern = null;
		$black = null;
		$left_to_right = null;
		$compositeOffset = null;
		$this->gCode = $code;
		$content = str_replace("{AI}", "", $code);
		$this->source = $content;
		$this->strText = $this->source;
		if ($this->linkageFlag)
		{
			$this->binary_string = "1";
			$compositeOffset = 1;
		}
		else
		{
			$this->binary_string = "0";
			$compositeOffset = 0;
		}
		if (($this->calculateBinaryString() ==  FALSE ))
		{
			return  FALSE ;
		}
		$data_chars = (int)(strlen($this->binary_string) / 12);
		for ($i = 0; ($i < $data_chars); $i++) 
		{
			$vs[$i] = 0;
			for ($j = 0; ($j < 12); $j++) 
			{
				if (($this->charAt2($this->binary_string, ((($i * 12)) + $j)) == '1'))
				{
					$vs[$i] += (2048 >> $j);
				}
			}
		}
		for ($i = 0; ($i < $data_chars); $i++) 
		{
			if (($vs[$i] <= 347))
			{
				$group[$i] = 1;
			}
			if (((($vs[$i] >= 348)) && (($vs[$i] <= 1387))))
			{
				$group[$i] = 2;
			}
			if (((($vs[$i] >= 1388)) && (($vs[$i] <= 2947))))
			{
				$group[$i] = 3;
			}
			if (((($vs[$i] >= 2948)) && (($vs[$i] <= 3987))))
			{
				$group[$i] = 4;
			}
			if (($vs[$i] >= 3988))
			{
				$group[$i] = 5;
			}
			$v_odd[$i] = (int)((($vs[$i] - $this->g_sum_exp[($group[$i] - 1)])) / $this->t_even_exp[($group[$i] - 1)]);
			$v_even[$i] = (int)((($vs[$i] - $this->g_sum_exp[($group[$i] - 1)])) % $this->t_even_exp[($group[$i] - 1)]);
			$this->getWidths($v_odd[$i], $this->modules_odd_exp[($group[$i] - 1)], 4, $this->widest_odd_exp[($group[$i] - 1)], 0);
			$char_widths[$i][0] = $this->widths[0];
			$char_widths[$i][2] = $this->widths[1];
			$char_widths[$i][4] = $this->widths[2];
			$char_widths[$i][6] = $this->widths[3];
			$this->getWidths($v_even[$i], $this->modules_even_exp[($group[$i] - 1)], 4, $this->widest_even_exp[($group[$i] - 1)], 1);
			$char_widths[$i][1] = $this->widths[0];
			$char_widths[$i][3] = $this->widths[1];
			$char_widths[$i][5] = $this->widths[2];
			$char_widths[$i][7] = $this->widths[3];
		}
		$checksum = 0;
		for ($i = 0; ($i < $data_chars); $i++) 
		{
			$row = $this->weight_rows[(((((int)((($data_chars - 2)) / 2)) * 21)) + $i)];
			for ($j = 0; ($j < 8); $j++) 
			{
				$checksum += (($char_widths[$i][$j] * $this->checksum_weight_exp[((($row * 8)) + $j)]));
			}
		}
		$check_char = (((211 * (((($data_chars + 1)) - 4)))) + (($checksum % 211)));
		$c_group = 1;
		if (((($check_char >= 348)) && (($check_char <= 1387))))
		{
			$c_group = 2;
		}
		if (((($check_char >= 1388)) && (($check_char <= 2947))))
		{
			$c_group = 3;
		}
		if (((($check_char >= 2948)) && (($check_char <= 3987))))
		{
			$c_group = 4;
		}
		if (($check_char >= 3988))
		{
			$c_group = 5;
		}
		$c_odd = (int)((($check_char - $this->g_sum_exp[($c_group - 1)])) / $this->t_even_exp[($c_group - 1)]);
		$c_even = (int)((($check_char - $this->g_sum_exp[($c_group - 1)])) % $this->t_even_exp[($c_group - 1)]);
		$this->getWidths($c_odd, $this->modules_odd_exp[($c_group - 1)], 4, $this->widest_odd_exp[($c_group - 1)], 0);
		$check_widths[0] = $this->widths[0];
		$check_widths[2] = $this->widths[1];
		$check_widths[4] = $this->widths[2];
		$check_widths[6] = $this->widths[3];
		$this->getWidths($c_even, $this->modules_even_exp[($c_group - 1)], 4, $this->widest_even_exp[($c_group - 1)], 1);
		$check_widths[1] = $this->widths[0];
		$check_widths[3] = $this->widths[1];
		$check_widths[5] = $this->widths[2];
		$check_widths[7] = $this->widths[3];
		$pattern_width = ((((((((int)((($data_chars + 1)) / 2)) + (((($data_chars + 1)) & 1)))) * 5)) + (((($data_chars + 1)) * 8))) + 4);
		for ($i = 0; ($i < $pattern_width); $i++) 
		{
			$elements[$i] = 0;
		}
		$elements[0] = 1;
		$elements[1] = 1;
		$elements[($pattern_width - 2)] = 1;
		$elements[($pattern_width - 1)] = 1;
		for ($i = 0; ($i < ((((int)((($data_chars + 1)) / 2)) + (((($data_chars + 1)) & 1))))); $i++) 
		{
			$k = (((((((((int)((((($data_chars + 1)) - 2)) / 2)) + (((($data_chars + 1)) & 1)))) - 1)) * 11)) + $i);
			for ($j = 0; ($j < 5); $j++) 
			{
				$elements[((((21 * $i)) + $j) + 10)] = $this->finder_pattern_exp[((((($this->finder_sequence[$k] - 1)) * 5)) + $j)];
			}
		}
		for ($i = 0; ($i < 8); $i++) 
		{
			$elements[($i + 2)] = $check_widths[$i];
		}
		for ($i = 1; ($i < $data_chars); $i += 2) 
		{
			for ($j = 0; ($j < 8); $j++) 
			{
				$elements[((((((int)((($i - 1)) / 2)) * 21)) + 23) + $j)] = $char_widths[$i][$j];
			}
		}
		for ($i = 0; ($i < $data_chars); $i += 2) 
		{
			for ($j = 0; ($j < 8); $j++) 
			{
				$elements[((((int)((($i / 2)) * 21)) + 15) + $j)] = $char_widths[$i][(7 - $j)];
			}
		}
		$row_count = 0;
		if (($this->symbolType == self::UNSTACKED /* could not resolve enum */))
		{
			$row_count = (1 + $compositeOffset);
			$this->row_height = array();
			$this->row_height[(0 + $compositeOffset)] = -1;
			$this->pattern = array();
			$this->pattern[(0 + $compositeOffset)] = "0";
			$writer = 0;
			$black =  FALSE ;
			$seperator_binary = "";
			for ($i = 0; ($i < $pattern_width); $i++) 
			{
				$this->pattern[(0 + $compositeOffset)] .= chr($elements[$i] + 0x30);
				for ($j = 0; ($j < $elements[$i]); $j++) 
				{
					if ($black)
					{
						$seperator_binary .= "0";
					}
					else
					{
						$seperator_binary .= "1";
					}
				}
				$black = !($black);
				$writer += $elements[$i];
			}
			$seperator_binary = ("0000" . substr($seperator_binary, 4, ($writer - 4 - 4)));
			for ($j = 0; ($j < ((int)($writer / 49))); $j++) 
			{
				$k = (((49 * $j)) + 18);
				for ($i = 0; ($i < 15); $i++) 
				{
					if (((($this->charAt2($seperator_binary, (($i + $k) - 1)) == '1')) && (($this->charAt2($seperator_binary, ($i + $k)) == '1'))))
					{
						$seperator_binary = substr($seperator_binary, 0, $i + $k) . "0" . substr($seperator_binary, $i + $k + 1);
					}
				}
			}
			if ($this->linkageFlag)
			{
				$this->pattern[0] = $this->bin2pat2($seperator_binary);
				$this->row_height[0] = 1;
			}
		}
		else
		{
			$codeblocks = ((int)((($data_chars + 1)) / 2) + (((($data_chars + 1)) % 2)));
			$blocksPerRow = $this->preferredNoOfColumns;
			if (((($blocksPerRow < 1)) || (($blocksPerRow > 10))))
			{
				$blocksPerRow = 2;
			}
			if (($this->linkageFlag && (($blocksPerRow == 1))))
			{
				$blocksPerRow = 2;
			}
			$stack_rows = (int)($codeblocks / $blocksPerRow);
			if ((($codeblocks % $blocksPerRow) > 0))
			{
				$stack_rows++;
			}
			$row_count = ((($stack_rows * 4)) - 3);
			$this->row_height = array();
			$this->pattern = array();
			$symbol_row = 0;
			$current_block = 0;
			for ($current_row = 1; ($current_row <= $stack_rows); $current_row++) 
			{
				for ($i = 0; ($i < 235); $i++) 
				{
					$sub_elements[$i] = 0;
				}
				$special_case_row =  FALSE ;
				$sub_elements[0] = 1;
				$sub_elements[1] = 1;
				$elements_in_sub = 2;
				$reader = 0;
				do 
				{
					if (((((((($blocksPerRow & 1)) != 0)) || (((($current_row & 1)) != 0)))) || ((((($current_row == $stack_rows)) && (($codeblocks != (($current_row * $blocksPerRow))))) && (((((((($current_row * $blocksPerRow)) - $codeblocks)) & 1))) != 0)))))
					{
						$left_to_right =  TRUE ;
						$i = (2 + (($current_block * 21)));
						for ($j = 0; ($j < 21); $j++) 
						{
							if (((($i + $j)) < $pattern_width))
							{
								$sub_elements[(($j + (($reader * 21))) + 2)] = $elements[($i + $j)];
								$elements_in_sub++;
							}
						}
					}
					else
					{
						$left_to_right =  FALSE ;
						if (((($current_row * $blocksPerRow)) < $codeblocks))
						{
							$i = (2 + ((((((($current_row * $blocksPerRow)) - $reader) - 1)) * 21)));
							for ($j = 0; ($j < 21); $j++) 
							{
								if (((($i + $j)) < $pattern_width))
								{
									$sub_elements[((((20 - $j)) + (($reader * 21))) + 2)] = $elements[($i + $j)];
									$elements_in_sub++;
								}
							}
						}
						else
						{
							$k = (((($current_row * $blocksPerRow)) - $codeblocks));
							$l = (((($current_row * $blocksPerRow)) - $reader) - 1);
							$i = (2 + (((($l - $k)) * 21)));
							for ($j = 0; ($j < 21); $j++) 
							{
								if (((($i + $j)) < $pattern_width))
								{
									$sub_elements[((((20 - $j)) + (($reader * 21))) + 2)] = $elements[($i + $j)];
									$elements_in_sub++;
								}
							}
						}
					}
					$reader++;
					$current_block++;
				}
				while (((($reader < $blocksPerRow)) && (($current_block < $codeblocks))));
				$sub_elements[$elements_in_sub] = 1;
				$sub_elements[($elements_in_sub + 1)] = 1;
				$elements_in_sub += 2;
				$this->pattern[($symbol_row + $compositeOffset)] = "";
				$black =  TRUE ;
				$this->row_height[($symbol_row + $compositeOffset)] = -1;
				if (((($current_row & 1)) != 0))
				{
					$this->pattern[($symbol_row + $compositeOffset)] = "0";
					$black =  FALSE ;
				}
				else
				{
					if ((((($current_row == $stack_rows)) && (($codeblocks != (($current_row * $blocksPerRow))))) && (((((((($current_row * $blocksPerRow)) - $codeblocks)) & 1)) != 0))))
					{
						$special_case_row =  TRUE ;
						$sub_elements[0] = 2;
						$this->pattern[($symbol_row + $compositeOffset)] = "0";
						$black =  FALSE ;
					}
				}
				$writer = 0;
				$seperator_binary = "";
				for ($i = 0; ($i < $elements_in_sub); $i++) 
				{
					$this->pattern[($symbol_row + $compositeOffset)] .= chr($sub_elements[$i] + ord('0'));
					for ($j = 0; ($j < $sub_elements[$i]); $j++) 
					{
						if ($black)
						{
							$seperator_binary .= "0";
						}
						else
						{
							$seperator_binary .= "1";
						}
					}
					$black = !($black);
					$writer += $sub_elements[$i];
				}
				$seperator_binary = ("0000" . substr($seperator_binary, 4, ($writer - 4 - 4)));
				for ($j = 0; ($j < $reader); $j++) 
				{
					$k = (((49 * $j)) + (( ($special_case_row) ? 19 : 18 )));
					if ($left_to_right)
					{
						for ($i = 0; ($i < 15); $i++) 
						{
							if (((($this->charAt2($seperator_binary, (($i + $k) - 1)) == '1')) && (($this->charAt2($seperator_binary, ($i + $k)) == '1'))))
							{
								$seperator_binary = substr($seperator_binary, 0, $i + $k) . "0" . substr($seperator_binary, $i + $k + 1);
							}
						}
					}
					else
					{
						for ($i = 14; ($i >= 0); --$i) 
						{
							if (((($this->charAt2($seperator_binary, (($i + $k) + 1)) == '1')) && (($this->charAt2($seperator_binary, ($i + $k)) == '1'))))
							{
								$seperator_binary = substr($seperator_binary, 0, $i + $k) . "0" . substr($seperator_binary, $i + $k + 1);
							}
						}
					}
				}
				$seperator_pattern = $this->bin2pat2($seperator_binary);
				if (((($current_row == 1)) && $this->linkageFlag))
				{
					$this->row_height[0] = 1;
					$this->pattern[0] = $seperator_pattern;
				}
				if (($current_row != 1))
				{
					$this->pattern[(($symbol_row - 2) + $compositeOffset)] = "05";
					for ($j = 5; ($j < ((49 * $blocksPerRow))); $j += 2) 
					{
						$this->pattern[(($symbol_row - 2) + $compositeOffset)] .= "11";
					}
					$this->row_height[(($symbol_row - 2) + $compositeOffset)] = 1;
					$this->row_height[(($symbol_row - 1) + $compositeOffset)] = 1;
					$this->pattern[(($symbol_row - 1) + $compositeOffset)] = $seperator_pattern;
				}
				if (($current_row != $stack_rows))
				{
					$this->row_height[(($symbol_row + 1) + $compositeOffset)] = 1;
					$this->pattern[(($symbol_row + 1) + $compositeOffset)] = $seperator_pattern;
				}
				$symbol_row += 4;
			}
			$row_count += $compositeOffset;
		}
		return  TRUE ;
	}
	protected function calculateBinaryString () 
	{
		$last_mode = self::encodeMode_NUMERIC /* could not resolve enum */;
		$encoding_method = 0;
		$i = 0;
		$j = 0;
		$read_posn = 0;
		$latch = false;
		$remainder = 0;
		$d1 = 0;
		$d2 = 0;
		$value = 0;
		$padstring = "";
		$weight = 0.00;
		$group_val = 0;
		$current_length = 0;
		$patch = "";
		$read_posn = 0;
		$AI = "{AI}";
		

		if (strlen($this->source) >= 16 && $this->charAt2($this->source, 0) == '0' && $this->charAt2($this->source, 1) == '1')
		{
			$encoding_method = 1;
			$this->strText = "(01)" . substr($this->gCode, 2);
			for (; ; ) 
			{
				$pAI = strpos($this->strText, $AI);
				if (($pAI == false))
					break;
				$this->strText = substr($this->strText, 0, $pAI) . "(" . substr($this->strText, $pAI + strlen($AI), 2) . ")" . substr($this->strText, $pAI + strlen($AI) + 2);
			}
		}
		else
		{
			$encoding_method = 2;
			$this->strText = $this->gCode;
			for (; ; ) 
			{
				$pAI = strpos($this->strText, $AI);
				if (($pAI == false))
					break;
				$this->strText = substr($this->strText, 0, $pAI) . "(" . substr($this->strText, $pAI + strlen($AI), 2) . ")" . substr($this->strText, $pAI + strlen($AI) + 2);
			}
		}
		if (strlen($this->source) >= 20 && $encoding_method == 1 && $this->charAt2($this->source, 2) == '9' && $this->charAt2($this->source, 16) == '3')
		{
			if (strlen($this->source) >= 26 && $this->charAt2($this->source, 17) == '1')
			{
				if ($this->charAt2($this->source, 18) == '0')
				{
					$weight = doubleval(0.0);
					for ($i = 0; $i < 6; $i++) 
					{
						$weight *= 10;
						$weight += (ord($this->charAt2($this->source, 20 + $i)) - 0x30);
					}
					if ($weight < doubleval(99999.0))
					{
						if ($this->charAt2($this->source, 19) == '3' && strlen($this->source) == 26)
						{
							$weight /= doubleval(1000.0);
							if ($weight <= doubleval(32.767))
							{
								$encoding_method = 3;
								$this->strText = "(01)" . substr($this->source, 2, 16 - 2) . "(3103)" . substr($this->source, 20);
							}
						}
						if (strlen($this->source) == 34)
						{
							if ($this->charAt2($this->source, 26) == '1' && $this->charAt2($this->source, 27) == '1')
							{
								$encoding_method = 7;
								$this->strText = "(01)" . substr($this->source, 2, 16 - 2) . "(310" . substr($this->source, 19, 1) . ")" . substr($this->source, 20, 26 - 20) . "(11)" . substr($this->source, 28);
							}
							if ($this->charAt2($this->source, 26) == '1' && $this->charAt2($this->source, 27) == '3')
							{
								$encoding_method = 9;
								$this->strText = "(01)" . substr($this->source, 2, 16 - 2) . "(310" . substr($this->source, 19, 1) . ")" . substr($this->source, 20, 26 - 20) . "(13)" . substr($this->source, 28);
							}
							if ($this->charAt2($this->source, 26) == '1' && $this->charAt2($this->source, 27) == '5')
							{
								$encoding_method = 11;
								$this->strText = "(01)" . substr($this->source, 2, 16 - 2) . "(310" . substr($this->source, 19, 1) . ")" . substr($this->source, 20, 26 - 20) . "(15)" . substr($this->source, 28);
							}
							if ($this->charAt2($this->source, 26) == '1' && $this->charAt2($this->source, 27) == '7')
							{
								$encoding_method = 13;
								$this->strText = "(01)" . substr($this->source, 2, 16 - 2) . "(310" . substr($this->source, 19, 1) . ")" . substr($this->source, 20, 26 - 20) . "(17)" . substr($this->source, 28);
							}
						}
					}
				}
				if (strlen($this->source) >= 26 && $this->charAt2($this->source, 17) == '2')
				{
					if (($this->charAt2($this->source, 18) == '0'))
					{
						$weight = doubleval(0.0);
						for ($i = 0; ($i < 6); $i++) 
						{
							$weight *= 10;
							$weight += (ord($this->charAt2($this->source, 20 + $i)) - 0x30);
						}
						if (($weight < doubleval(99999.0)))
						{
							if ($this->charAt2($this->source, 19) == '2' && $this->charAt2($this->source, 19) == '3' && strlen($this->source) == 26)
							{
								if (($this->charAt2($this->source, 19) == '3'))
								{
									$weight /= doubleval(1000.0);
									if (($weight <= doubleval(22.767)))
									{
										$encoding_method = 4;
										$this->strText = "(01)" . substr($this->source, 2, 16 - 2) . "(3203)" . substr($this->source, 20);
									}
								}
								else
								{
									$weight /= doubleval(100.0);
									if (($weight <= doubleval(99.99)))
									{
										$encoding_method = 4;
										$this->strText = "(01)" . substr($this->source, 2, 16 - 2) . "(3202)" . substr($this->source, 20);
									}
								}
							}
							if (strlen($this->source) == 34)
							{
								if ($this->charAt2($this->source, 26) == '1' && $this->charAt2($this->source, 27) == '1')
								{
									$encoding_method = 8;
									$this->strText = "(01)" . substr($this->source, 2, 16 - 2) . "(320" . substr($this->source, 19, 1) . ")" . substr($this->source, 20, 26 - 20) . "(11)" . substr($this->source, 28);
								}
								if ($this->charAt2($this->source, 26) == '1' && $this->charAt2($this->source, 27) == '3')
								{
									$encoding_method = 10;
									$this->strText = "(01)" . substr($this->source, 2, 16 - 2) . "(320" . substr($this->source, 19, 1) . ")" . substr($this->source, 20, 26 - 20) . "(13)" . substr($this->source, 28);
								}
								if ($this->charAt2($this->source, 26) == '1' && $this->charAt2($this->source, 27) == '5')
								{
									$encoding_method = 12;
									$this->strText = "(01)" . substr($this->source, 2, 16 - 2) . "(320" . substr($this->source, 19, 1) . ")" . substr($this->source, 20, 26 - 20) . "(15)" . substr($this->source, 28);
								}
								if ($this->charAt2($this->source, 26) == '1' && $this->charAt2($this->source, 27) == '7')
								{
									$encoding_method = 14;
									$this->strText = "(01)" . substr($this->source, 2, 16 - 2) . "(320" . substr($this->source, 19, 1) . ")" . substr($this->source, 20, 26 - 20) . "(17)" . substr($this->source, 28);
								}

							}
						}
					}
				}
				if ($this->charAt2($this->source, 17) == '9')
				{
					if ($this->charAt2($this->source, 18) == '2' && ord($this->charAt2($this->source, 19)) >= 0x30 && ord($this->charAt2($this->source, 19)) <= 0x33)
					{
						$encoding_method = 5;
						$this->strText = "(01)" . substr($this->source, 2, 16 - 2) . "(392" . substr($this->source, 19, 1) . ")" . substr($this->source, 20);
					}
					if ($this->charAt2($this->source, 18) == '3' && ord($this->charAt2($this->source, 19)) >= 0x30 && ord($this->charAt2($this->source, 19)) <= 0x33)
					{
						$encoding_method = 6;
						$this->strText = "(01)" . substr($this->source, 2, 16 - 2) . "(393" . substr($this->source, 19, 1) . ")" . substr($this->source, 20);
					}
				}
			}
		}

		
			switch ($encoding_method) {
				case 1:
					$this->binary_string .= "1XX";
					$read_posn = 16;
					break;
				case 2:
					$this->binary_string .= "00XX";
					$read_posn = 0;
					break;
				case 3:
					$this->binary_string .= "0100";
					$read_posn = strlen($this->source);
					break;
				case 4:
					$this->binary_string .= "0101";
					$read_posn = strlen($this->source);
					break;
				case 5:
					$this->binary_string .= "01100XX";
					$read_posn = 20;
					break;
				case 6:
					$this->binary_string .= "01101XX";
					$read_posn = 23;
					break;
				case 7:
					$this->binary_string .= "0111000";
					$read_posn = strlen($this->source);
					break;
				case 8:
					$this->binary_string .= "0111001";
					$read_posn = strlen($this->source);
					break;
				case 9:
					$this->binary_string .= "0111010";
					$read_posn = strlen($this->source);
					break;
				case 10:
					$this->binary_string .= "0111011";
					$read_posn = strlen($this->source);
					break;
				case 11:
					$this->binary_string .= "0111100";
					$read_posn = strlen($this->source);
					break;
				case 12:
					$this->binary_string .= "0111101";
					$read_posn = strlen($this->source);
					break;
				case 13:
					$this->binary_string .= "0111110";
					$read_posn = strlen($this->source);
					break;
				case 14:
					$this->binary_string .= "0111111";
					$read_posn = strlen($this->source);
					break;
			}
			for ($i = 0; ($i < $read_posn); $i++) 
			{
				if ((((ord($this->charAt2($this->source, $i)) < 0x30)) || ((ord($this->charAt2($this->source, $i)) > 0x39))))
				{
					if (((($this->charAt2($this->source, $i) != '[')) && (($this->charAt2($this->source, $i) != ']'))))
					{
						$this->error_msg = "Invalid characters in input data";
						return  FALSE ;
					}
				}
			}
			if (($encoding_method == 1))
			{
				$group_val = ord($this->charAt2($this->source, 2)) - 0x30;
				for ($j = 0; ($j < 4); $j++) 
				{
					if (((($group_val & ((0x08 >> $j)))) == 0))
					{
						$this->binary_string .= "0";
					}
					else
					{
						$this->binary_string .= "1";
					}
				}
				for ($i = 1; ($i < 5); $i++) 
				{
					$group_val = (100 * (ord($this->charAt2($this->source, $i * 3)) - 0x30));
					$group_val += (10 * (ord($this->charAt2($this->source, ($i * 3) + 1)) - 0x30));
					$group_val += (ord($this->charAt2($this->source, ($i * 3) + 2)) - 0x30);
					for ($j = 0; ($j < 10); $j++) 
					{
						if (((($group_val & ((0x200 >> $j)))) == 0))
						{
							$this->binary_string .= "0";
						}
						else
						{
							$this->binary_string .= "1";
						}
					}
				}
			}
			if (($encoding_method == 3))
			{
				for ($i = 1; ($i < 5); $i++) 
				{
					$group_val = (100 * (ord($this->charAt2($this->source, $i * 3)) - 0x30));
					$group_val += (10 * (ord($this->charAt2($this->source, (i * 3) + 1))  - 0x30));
					$group_val += (ord($this->charAt2($this->source, ($i * 3) + 2))  - 0x30);
					for ($j = 0; ($j < 10); $j++) 
					{
						if (((($group_val & ((0x200 >> $j)))) == 0))
						{
							$this->binary_string .= "0";
						}
						else
						{
							$this->binary_string .= "1";
						}
					}
				}
				$group_val = 0;
				for ($i = 0; ($i < 6); $i++) 
				{
					$group_val *= 10;
					$group_val += (ord($this->charAt2($this->source, 20 + $i)) + 0x30);
				}
				for ($j = 0; ($j < 15); $j++) 
				{
					if (((($group_val & ((0x4000 >> $j)))) == 0))
					{
						$this->binary_string .= "0";
					}
					else
					{
						$this->binary_string .= "1";
					}
				}
			}
			if (($encoding_method == 4))
			{
				for ($i = 1; ($i < 5); $i++) 
				{
					$group_val = (100 * (ord($this->charAt2($this->source, $i * 3)) - 0x30));
					$group_val += (10 * (ord($this->charAt2($this->source, (i * 3) + 1))  - 0x30));
					$group_val += (ord($this->charAt2($this->source, ($i * 3) + 2))  - 0x30);
					for ($j = 0; ($j < 10); $j++) 
					{
						if (((($group_val & ((0x200 >> $j)))) == 0))
						{
							$this->binary_string .= "0";
						}
						else
						{
							$this->binary_string .= "1";
						}
					}
				}
				$group_val = 0;
				for ($i = 0; ($i < 6); $i++) 
				{
					$group_val *= 10;
					$group_val += (ord($this->charAt2($this->source, 20 + $i)) - 0x30);
				}
				if (($this->charAt2($this->source, 19) == '3'))
				{
					$group_val = ($group_val + 10000);
				}
				for ($j = 0; ($j < 15); $j++) 
				{
					if (((($group_val & ((0x4000 >> $j)))) == 0))
					{
						$this->binary_string .= "0";
					}
					else
					{
						$this->binary_string .= "1";
					}
				}
			}
			if (((($encoding_method >= 7)) && (($encoding_method <= 14))))
			{
				for ($i = 1; ($i < 5); $i++) 
				{
					$group_val = (100 * (ord($this->charAt2($this->source, $i * 3)) - 0x30));
					$group_val += (10 * (ord($this->charAt2($this->source, (i * 3) + 1))  - 0x30));
					$group_val += (ord($this->charAt2($this->source, ($i * 3) + 2))  - 0x30);

					for ($j = 0; ($j < 10); $j++) 
					{
						if (((($group_val & ((0x200 >> $j)))) == 0))
						{
							$this->binary_string .= "0";
						}
						else
						{
							$this->binary_string .= "1";
						}
					}
				}
				$group_val = ord($this->charAt2($this->source, 19)) - 0x30;
				for ($i = 0; ($i < 5); $i++) 
				{
					$group_val *= 10;
					$group_val += (ord($this->charAt2($this->source, 21 + $i)) - 0x30);
				}
				for ($j = 0; ($j < 20); $j++) 
				{
					if (((($group_val & ((0x80000 >> $j)))) == 0))
					{
						$this->binary_string .= "0";
					}
					else
					{
						$this->binary_string .= "1";
					}
				}
				if ((strlen($this->source) == 34))
				{
					//$group_val = (((((10 . (($this->charAt2($this->source, 28) . '0')))) . (($this->charAt2($this->source, 29) . '0')))) * 384);
					//$group_val += (((((((10 . (($this->charAt2($this->source, 30) . '0')))) . (($this->charAt2($this->source, 31) . '0')))) - 1)) * 32);
					//$group_val += (((10 . (($this->charAt2($this->source, 32) . '0')))) . (($this->charAt2($this->source, 33) . '0')));

					$group_val = ((10 * (ord($this->charAt2($this->source, 28)) - 0x30)) + (ord($this->charAt2($this->source, 29)) - 0x30)) * 384;
					$group_val += (((10 * (ord($this->charAt2($this->source, 30)) - 0x30)) + (ord($this->charAt2($this->source, 31)) - 0x30)) - 1) * 32;
					$group_val += (10 * (ord($this->charAt2($this->source, 32)) - 0x30)) + (ord($this->charAt2($this->source, 33)) - 0x30);
				}
				else
				{
					$group_val = 38400;
				}
				for ($j = 0; ($j < 16); $j++) 
				{
					if (((($group_val & ((0x8000 >> $j)))) == 0))
					{
						$this->binary_string .= "0";
					}
					else
					{
						$this->binary_string .= "1";
					}
				}
			}
			if (($encoding_method == 5))
			{
				for ($i = 1; ($i < 5); $i++) 
				{
					$group_val = (100 * (ord($this->charAt2($this->source, $i * 3)) - 0x30));
					$group_val += (10 * (ord($this->charAt2($this->source, (i * 3) + 1))  - 0x30));
					$group_val += (ord($this->charAt2($this->source, ($i * 3) + 2))  - 0x30);

					for ($j = 0; ($j < 10); $j++) 
					{
						if (((($group_val & ((0x200 >> $j)))) == 0))
						{
							$this->binary_string .= "0";
						}
						else
						{
							$this->binary_string .= "1";
						}
					}
				}
				switch ($this->charAt2($this->source, 19)) {
					case '0':
						$this->binary_string .= "00";
						break;
					case '1':
						$this->binary_string .= "01";
						break;
					case '2':
						$this->binary_string .= "10";
						break;
					case '3':
						$this->binary_string .= "11";
						break;
				}
			}
			if (($encoding_method == 6))
			{
				for ($i = 1; ($i < 5); $i++) 
				{
					$group_val = (100 * (ord($this->charAt2($this->source, $i * 3)) - 0x30));
					$group_val += (10 * (ord($this->charAt2($this->source, (i * 3) + 1))  - 0x30));
					$group_val += (ord($this->charAt2($this->source, ($i * 3) + 2))  - 0x30);
				
					for ($j = 0; ($j < 10); $j++) 
					{
						if (((($group_val & ((0x200 >> $j)))) == 0))
						{
							$this->binary_string .= "0";
						}
						else
						{
							$this->binary_string .= "1";
						}
					}
				}
				switch ($this->charAt2($this->source, 19)) {
					case '0':
						$this->binary_string .= "00";
						break;
					case '1':
						$this->binary_string .= "01";
						break;
					case '2':
						$this->binary_string .= "10";
						break;
					case '3':
						$this->binary_string .= "11";
						break;
				}
				$group_val = 0;
				for ($i = 0; ($i < 3); $i++) 
				{
					$group_val *= 10;
					$group_val += (ord($this->charAt2($this->source, 20 + $i)) - 0x30);
				}
				for ($j = 0; ($j < 10); $j++) 
				{
					if (((($group_val & ((0x200 >> $j)))) == 0))
					{
						$this->binary_string .= "0";
					}
					else
					{
						$this->binary_string .= "1";
					}
				}
			}
			
			$this->general_field = substr($this->source, $read_posn);
			$this->general_field_type = array();
			if (strlen($this->general_field) != 0)
			{
				$latch =  FALSE ;
				for ($i = 0; ($i < strlen($this->general_field)); $i++) 
				{
					if ((ord($this->charAt2($this->general_field, $i)) < ord(' ')) || (ord($this->charAt2($this->general_field, $i)) > ord('z')))
					{
						$this->general_field_type[$i] = self::encodeMode_INVALID_CHAR /* could not resolve enum */;
						$latch =  TRUE ;
					}
					else
					{
						$this->general_field_type[$i] = self::encodeMode_ISOIEC /* could not resolve enum */;
					}
					if (ord($this->charAt2($this->general_field, $i)) == ord('#'))
					{
						$this->general_field_type[$i] = self::encodeMode_INVALID_CHAR /* could not resolve enum */;
						$latch =  TRUE ;
					}
					if (ord($this->charAt2($this->general_field, $i)) == ord('$'))
					{
						$this->general_field_type[$i] = self::encodeMode_INVALID_CHAR /* could not resolve enum */;
						$latch =  TRUE ;
					}
					if (ord($this->charAt2($this->general_field, $i)) == ord('@'))
					{
						$this->general_field_type[$i] = self::encodeMode_INVALID_CHAR /* could not resolve enum */;
						$latch =  TRUE ;
					}
					if (ord($this->charAt2($this->general_field, $i)) == 92)
					{
						$this->general_field_type[$i] = self::encodeMode_INVALID_CHAR /* could not resolve enum */;
						$latch =  TRUE ;
					}
					if (ord($this->charAt2($this->general_field, $i)) == ord('^'))
					{
						$this->general_field_type[$i] = self::encodeMode_INVALID_CHAR /* could not resolve enum */;
						$latch =  TRUE ;
					}
					if (ord($this->charAt2($this->general_field, $i)) == 96)
					{
						$this->general_field_type[$i] = self::encodeMode_INVALID_CHAR /* could not resolve enum */;
						$latch =  TRUE ;
					}
					if (ord($this->charAt2($this->general_field, $i)) >= ord('A') && ord($this->charAt2($this->general_field, $i)) <= ord('Z'))
					{
						$this->general_field_type[$i] = self::encodeMode_ALPHA_OR_ISO /* could not resolve enum */;
					}
					if (ord($this->charAt2($this->general_field, $i)) == ord('*'))
					{
						$this->general_field_type[$i] = self::encodeMode_ALPHA_OR_ISO /* could not resolve enum */;
					}
					if (ord($this->charAt2($this->general_field, $i)) == ord(','))
					{
						$this->general_field_type[$i] = self::encodeMode_ALPHA_OR_ISO /* could not resolve enum */;
					}
					if (ord($this->charAt2($this->general_field, $i)) == ord('-'))
					{
						$this->general_field_type[$i] = self::encodeMode_ALPHA_OR_ISO /* could not resolve enum */;
					}
					if (ord($this->charAt2($this->general_field, $i)) == ord('.'))
					{
						$this->general_field_type[$i] = self::encodeMode_ALPHA_OR_ISO /* could not resolve enum */;
					}
					if (ord($this->charAt2($this->general_field, $i)) == ord('/'))
					{
						$this->general_field_type[$i] = self::encodeMode_ALPHA_OR_ISO /* could not resolve enum */;
					}
					if (ord($this->charAt2($this->general_field, $i)) >= ord('0') && ord($this->charAt2($this->general_field, $i)) <= ord('9'))
					{
						$this->general_field_type[$i] = self::encodeMode_ANY_ENC /* could not resolve enum */;
					}
					if (ord($this->charAt2($this->general_field, $i)) == ord('['))
					{
						$this->general_field_type[$i] = self::encodeMode_ANY_ENC /* could not resolve enum */;
					}
				}
				if ($latch)
				{
					$this->error_msg = "Invalid characters in input data";
					return  FALSE ;
				}
				for ($i = 0; ($i < (strlen($this->general_field) - 1)); $i++) 
				{
					if ($this->general_field_type[$i] == self::encodeMode_ISOIEC && $this->general_field[$i + 1] == ord('['))
					{
						$this->general_field_type[($i + 1)] = self::encodeMode_ISOIEC /* could not resolve enum */;
					}
				}
				for ($i = 0; ($i < (strlen($this->general_field) - 1)); $i++) 
				{
					if ($this->general_field_type[$i] == self::encodeMode_ALPHA_OR_ISO && $this->general_field[$i+1] == ord('['))
					{
						$this->general_field_type[($i + 1)] = self::encodeMode_ALPHA_OR_ISO /* could not resolve enum */;
					}
				}
				$latch = $this->applyGeneralFieldRules();
				if (($this->general_field_type[0] == self::encodeMode_ALPHA /* could not resolve enum */))
				{
					$this->binary_string .= "0000";
					$last_mode = self::encodeMode_ALPHA /* could not resolve enum */;
				}
				if (($this->general_field_type[0] == self::encodeMode_ISOIEC /* could not resolve enum */))
				{
					$this->binary_string .= "0000";
					$this->binary_string .= "00100";
					$last_mode = self::encodeMode_ISOIEC /* could not resolve enum */;
				}
				$i = 0;
				do 
				{
					switch ($this->general_field_type[$i])  {
						case self::encodeMode_NUMERIC:
							if (($last_mode != self::encodeMode_NUMERIC /* could not resolve enum */))
							{
								$this->binary_string .= "000";
							}
							if ($this->charAt2($this->general_field, $i) != '[')
							{
								$d1 = ord($this->charAt2($this->general_field, $i)) - 0x30;
							}
							else
							{
								$d1 = 10;
							}
							if ($this->charAt2($this->general_field, $i + 1) != '[')
							{
								$d2 = ord($this->charAt2($this->general_field, ($i + 1))) - 0x30;
							}
							else
							{
								$d2 = 10;
							}
							$value = ((((11 * $d1)) + $d2) + 8);
							for ($j = 0; ($j < 7); $j++) 
							{
								if (((($value & ((0x40 >> $j)))) != 0))
								{
									$this->binary_string .= "1";
								}
								else
								{
									$this->binary_string .= "0";
								}
							}
							$i += 2;
							$last_mode = self::encodeMode_NUMERIC /* could not resolve enum */;
							break;
						case self::encodeMode_ALPHA:
							if (($i != 0))
							{
								if (($last_mode == self::encodeMode_NUMERIC /* could not resolve enum */))
								{
									$this->binary_string .= "0000";
								}
								if (($last_mode == self::encodeMode_ISOIEC /* could not resolve enum */))
								{
									$this->binary_string .= "00100";
								}
							}
							if (ord($this->charAt2($this->general_field, $i)) >= ord('0') && ord($this->charAt2($this->general_field, $i)) <= ord('9'))
							{
								$value = ord($this->charAt2($this->general_field, $i)) - 43;
								for ($j = 0; ($j < 5); $j++) 
								{
									if (((($value & ((0x10 >> $j)))) != 0))
									{
										$this->binary_string .= "1";
									}
									else
									{
										$this->binary_string .= "0";
									}
								}
							}
							if (ord($this->charAt2($this->general_field, $i)) >= ord('A') && ord($this->charAt2($this->general_field, $i)) <= ord('Z'))
							{
								$value = ord($this->charAt2($this->general_field, $i)) - 33;
								for ($j = 0; ($j < 6); $j++) 
								{
									if (((($value & ((0x20 >> $j)))) != 0))
									{
										$this->binary_string .= "1";
									}
									else
									{
										$this->binary_string .= "0";
									}
								}
							}
							$last_mode = self::encodeMode_ALPHA /* could not resolve enum */;
							if (ord($this->charAt2($this->general_field, $i)) == ord('['))
							{
								$this->binary_string .= "01111";
								$last_mode = self::encodeMode_NUMERIC;
							}
							if (ord($this->charAt2($this->general_field, $i)) == ord('*'))
								$this->binary_string .= "111010";
							if (ord($this->charAt2($this->general_field, $i)) == ord(','))
								$this->binary_string .= "111011";
							if (ord($this->charAt2($this->general_field, $i)) == ord('-'))
								$this->binary_string .= "111100";
							if (ord($this->charAt2($this->general_field, $i)) == ord('.'))
								$this->binary_string .= "111101";
							if (ord($this->charAt2($this->general_field, $i)) == ord('/'))
								$this->binary_string .= "111110";
							$i++;
							break;
							case self::encodeMode_ISOIEC:
							if (($i != 0))
							{
								if (($last_mode == self::encodeMode_NUMERIC /* could not resolve enum */))
								{
									$this->binary_string .= "0000";
									$this->binary_string .= "00100";
								}
								if (($last_mode == self::encodeMode_ALPHA /* could not resolve enum */))
								{
									$this->binary_string .= "00100";
								}
							}
							if (ord($this->charAt2($this->general_field, $i)) >= ord('0') && ord($this->charAt2($this->general_field, $i)) <= ord('9'))
							{
								$value = ord($this->charAt2($this->general_field, $i)) - 43;
								for ($j = 0; ($j < 5); $j++) 
								{
									if (((($value & ((0x10 >> $j)))) != 0))
									{
										$this->binary_string .= "1";
									}
									else
									{
										$this->binary_string .= "0";
									}
								}
							}
							if (ord($this->charAt2($this->general_field, $i)) >= ord('A') && ord($this->charAt2($this->general_field, $i)) <= ord('Z'))
							{
								$value = ord($this->charAt2($this->general_field, $i)) - 1;
								for ($j = 0; ($j < 7); $j++) 
								{
									if (((($value & ((0x40 >> $j)))) != 0))
									{
										$this->binary_string .= "1";
									}
									else
									{
										$this->binary_string .= "0";
									}
								}
							}
							if (ord($this->charAt2($this->general_field, $i)) >= ord('a') && ord($this->charAt2($this->general_field, $i)) <= ord('z'))
							{
								$value = ord($this->charAt2($this->general_field, $i)) - 7;
								for ($j = 0; ($j < 7); $j++) 
								{
									if (((($value & ((0x40 >> $j)))) != 0))
									{
										$this->binary_string .= "1";
									}
									else
									{
										$this->binary_string .= "0";
									}
								}
							}
							$last_mode = self::encodeMode_ISOIEC /* could not resolve enum */;
							if (ord($this->charAt2($this->general_field, $i)) == ord('['))
							{
								$this->binary_string .= "01111";
								$last_mode = self::encodeMode_NUMERIC /* could not resolve enum */;
							}
							if (ord($this->charAt2($this->general_field, $i)) == ord('!'))
								$this->binary_string .= "11101000";
							if (ord($this->charAt2($this->general_field, $i)) == 34)
							$this->binary_string .= "11101001";
							if (ord($this->charAt2($this->general_field, $i)) == 37)
								$this->binary_string .= "11101010";
							if (ord($this->charAt2($this->general_field, $i)) == ord('&'))
								$this->binary_string .= "11101011";
							if (ord($this->charAt2($this->general_field, $i)) == 39)
								$this->binary_string .= "11101100";
							if (ord($this->charAt2($this->general_field, $i)) == ord('('))
								$this->binary_string .= "11101101";
							if (ord($this->charAt2($this->general_field, $i)) == ord(')'))
								$this->binary_string .= "11101110";
							if (ord($this->charAt2($this->general_field, $i)) == ord('*'))
								$this->binary_string .= "11101111";
							if (ord($this->charAt2($this->general_field, $i)) == ord('+'))
								$this->binary_string .= "11110000";
							if (ord($this->charAt2($this->general_field, $i)) == ord(','))
								$this->binary_string .= "11110001";
							if (ord($this->charAt2($this->general_field, $i)) == ord('-'))
								$this->binary_string .= "11110010";
							if (ord($this->charAt2($this->general_field, $i)) == ord('.'))
								$this->binary_string .= "11110011";
							if (ord($this->charAt2($this->general_field, $i)) == ord('/'))
								$this->binary_string .= "11110100";
							if (ord($this->charAt2($this->general_field, $i)) == ord(':'))
								$this->binary_string .= "11110101";
							if (ord($this->charAt2($this->general_field, $i)) == ord(';'))
								$this->binary_string .= "11110110";
							if (ord($this->charAt2($this->general_field, $i)) == ord('<'))
								$this->binary_string .= "11110111";
							if (ord($this->charAt2($this->general_field, $i)) == ord('='))
								$this->binary_string .= "11111000";
							if (ord($this->charAt2($this->general_field, $i)) == ord('>'))
								$this->binary_string .= "11111001";
							if (ord($this->charAt2($this->general_field, $i)) == ord('?'))
								$this->binary_string .= "11111010";
							if (ord($this->charAt2($this->general_field, $i)) == ord('_'))
								$this->binary_string .= "11111011";
							if (ord($this->charAt2($this->general_field, $i)) == ord(' '))
								$this->binary_string .= "11111100";
							$i++;
							break;
						default:
							break;
					}
					$current_length = $i;
					if ($latch)
					{
						$current_length++;
					}
				}
				while (($current_length < strlen($this->general_field)));
				$remainder = DatabarExpanded::calculateRemainder(strlen($this->binary_string));
				if ($latch)
				{
					if (($last_mode == self::encodeMode_NUMERIC /* could not resolve enum */))
					{
						if (((($remainder >= 4)) && (($remainder <= 6))))
						{
							$value = ord($this->charAt2($this->general_field, $i)) - 0x30;
							$value++;
							for ($j = 0; ($j < 4); $j++) 
							{
								if (((($value & ((0x08 >> $j)))) != 0))
								{
									$this->binary_string .= "1";
								}
								else
								{
									$this->binary_string .= "0";
								}
							}
						}
						else
						{
							$d1 = ord($this->charAt2($this->general_field, $i)) - 0x30;
							$d2 = 10;
							$value = ((((11 * $d1)) + $d2) + 8);
							for ($j = 0; ($j < 7); $j++) 
							{
								if (((($value & ((0x40 >> $j)))) != 0))
								{
									$this->binary_string .= "1";
								}
								else
								{
									$this->binary_string .= "0";
								}
							}
						}
					}
					else
					{
						$value = ord($this->charAt2($this->general_field, $i)) - 43;
						for ($j = 0; ($j < 5); $j++) 
						{
							if (((($value & ((0x10 >> $j)))) != 0))
							{
								$this->binary_string .= "1";
							}
							else
							{
								$this->binary_string .= "0";
							}
						}
					
					}
				}
			}
		if ((strlen($this->binary_string) > 252))
		{
			$this->error_msg = "Input too long";
			return  FALSE ;
		}
		$remainder = DatabarExpanded::calculateRemainder(strlen($this->binary_string));
		$i = $remainder;
		if ((((strlen($this->general_field) != 0)) && (($last_mode == self::encodeMode_NUMERIC /* could not resolve enum */))))
		{
			$padstring = "0000";
			$i -= 4;
		}
		else
		{
			$padstring = "";
		}
		for (; ($i > 0); $i -= 5) 
		{
			$padstring .= "00100";
		}
		$this->binary_string .= substr($padstring, 0, $remainder);
		$patch = "";
		if (((((((int)(strlen($this->binary_string) / 12)) + 1)) & 1)) == 0)
		{
			$patch .= "0";
		}
		else
		{
			$patch .= "1";
		}
		if ((strlen($this->binary_string) <= 156))
		{
			$patch .= "0";
		}
		else
		{
			$patch .= "1";
		}
		if (($encoding_method == 1))
		{
			$this->binary_string = ((substr($this->binary_string, 0, 2) . $patch) . substr($this->binary_string, 4));
		}
		if (($encoding_method == 2))
		{
			$this->binary_string = ((substr($this->binary_string, 0, 3) . $patch) . substr($this->binary_string, 5));
		}
		if (((($encoding_method == 5)) || (($encoding_method == 6))))
		{
			$this->binary_string = ((substr($this->binary_string, 0, 6) . $patch) . substr($this->binary_string, 8));
		}
		$this->displayBinaryString();
		return  TRUE ;
	}
	protected static function calculateRemainder ($binaryStringLength) // [int binaryStringLength]
	{
		$remainder = (12 - (($binaryStringLength % 12)));
		if (($remainder == 12))
		{
			$remainder = 0;
		}
		if (($binaryStringLength < 36))
		{
			$remainder = (36 - $binaryStringLength);
		}
		return $remainder;
	}
	protected function displayBinaryString () 
	{
		$i = null;
		for ($i = 0; $i < strlen($this->binary_string); $i++) 
		{
			switch (($i % 4)) {
				case 0:
					if (($this->charAt2($this->binary_string, $i) == '1'))
					break;
				case 1:
					if (($this->charAt2($this->binary_string, $i) == '1'))
					break;
				case 2:
					if (($this->charAt2($this->binary_string, $i) == '1'))
					break;
				case 3:
					if (($this->charAt2($this->binary_string, $i) == '1'))
					break;
			}
		}
	}
	protected function applyGeneralFieldRules () 
	{
		$block_count = null;
		$i = null;
		$j = null;
		$k = null;
		$current = null;
		$next = null;
		$last = null;
		$blockLength = array();
		$blockType = array();
		$block_count = 0;
		$blockLength[$block_count] = 1;
		$blockType[$block_count] = $this->general_field_type[0];
		for ($i = 1; $i < strlen($this->general_field); $i++) 
		{
			$current = $this->general_field_type[$i];
			$last = $this->general_field_type[($i - 1)];
			if (($current == $last))
			{
				$blockLength[$block_count] = ($blockLength[$block_count] + 1);
			}
			else
			{
				$block_count++;
				$blockLength[$block_count] = 1;
				$blockType[$block_count] = $this->general_field_type[$i];
			}
		}
		$block_count++;
		for ($i = 0; ($i < $block_count); $i++) 
		{
			$current = $blockType[$i];
			if(!array_key_exists($i + 1, $blockType))
			 $next = NULL;
			else
			 $next = $blockType[($i + 1)];
			if (((($current == self::encodeMode_ISOIEC /* could not resolve enum */)) && (($i != (($block_count - 1))))))
			{
				if (((($next == self::encodeMode_ANY_ENC /* could not resolve enum */)) && (($blockLength[($i + 1)] >= 4))))
				{
					$blockType[($i + 1)] = self::encodeMode_NUMERIC /* could not resolve enum */;
				}
				if (((($next == self::encodeMode_ANY_ENC /* could not resolve enum */)) && (($blockLength[($i + 1)] < 4))))
				{
					$blockType[($i + 1)] = self::encodeMode_ISOIEC /* could not resolve enum */;
				}
				if (((($next == self::encodeMode_ALPHA_OR_ISO /* could not resolve enum */)) && (($blockLength[($i + 1)] >= 5))))
				{
					$blockType[($i + 1)] = self::encodeMode_ALPHA /* could not resolve enum */;
				}
				if (((($next == self::encodeMode_ALPHA_OR_ISO /* could not resolve enum */)) && (($blockLength[($i + 1)] < 5))))
				{
					$blockType[($i + 1)] = self::encodeMode_ISOIEC /* could not resolve enum */;
				}
			}
			if (($current == self::encodeMode_ALPHA_OR_ISO /* could not resolve enum */))
			{
				$blockType[$i] = self::encodeMode_ALPHA /* could not resolve enum */;
			}
			if (((($current == self::encodeMode_ALPHA /* could not resolve enum */)) && (($i != (($block_count - 1))))))
			{
				if (((($next == self::encodeMode_ANY_ENC /* could not resolve enum */)) && (($blockLength[($i + 1)] >= 6))))
				{
					$blockType[($i + 1)] = self::encodeMode_NUMERIC /* could not resolve enum */;
				}
				if (((($next == self::encodeMode_ANY_ENC /* could not resolve enum */)) && (($blockLength[($i + 1)] < 6))))
				{
					if (((($i == ($block_count - 2))) && (($blockLength[($i + 1)] >= 4))))
					{
						$blockType[($i + 1)] = self::encodeMode_NUMERIC /* could not resolve enum */;
					}
					else
					{
						$blockType[($i + 1)] = self::encodeMode_ALPHA /* could not resolve enum */;
					}
				}
			}
			if (($current == self::encodeMode_ANY_ENC /* could not resolve enum */))
			{
				$blockType[$i] = self::encodeMode_NUMERIC /* could not resolve enum */;
			}
		}
		if (($block_count > 1))
		{
			$i = 1;
			while (($i < $block_count)) 
			{
				if (($blockType[($i - 1)] == $blockType[$i]))
				{
					$blockLength[($i - 1)] = ($blockLength[($i - 1)] + $blockLength[$i]);
					$j = ($i + 1);
					while (($j < $block_count)) 
					{
						$blockLength[($j - 1)] = $blockLength[$j];
						$blockType[($j - 1)] = $blockType[$j];
						$j++;
					}
					--$block_count;
					--$i;
				}
				$i++;
			}
		}
		for ($i = 0; ($i < ($block_count - 1)); $i++) 
		{
			if (((($blockType[$i] == self::encodeMode_NUMERIC /* could not resolve enum */)) && (((($blockLength[$i] & 1)) != 0))))
			{
				$blockLength[$i] = ($blockLength[$i] - 1);
				$blockLength[($i + 1)] = ($blockLength[($i + 1)] + 1);
			}
		}
		$j = 0;
		for ($i = 0; ($i < $block_count); $i++) 
		{
			for ($k = 0; ($k < $blockLength[$i]); $k++) 
			{
				$this->general_field_type[$j] = $blockType[$i];
				$j++;
			}
		}
		if (((($blockType[($block_count - 1)] == self::encodeMode_NUMERIC /* could not resolve enum */)) && (((($blockLength[($block_count - 1)] & 1)) != 0))))
		{
			return  TRUE ;
		}
		else
		{
			return  FALSE ;
		}
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
				$val = (int)($val / $j);
				$j++;
			}
		}
		for (; ($j <= $minDenom); $j++) 
		{
			$val = (int)($val / $j);
		}
		return ($val);
	}
	protected function getWidths ($val, $n, $elements, $maxWidth, $noNarrow) // [int val, int n, int elements, int maxWidth, int noNarrow]
	{
		$bar = 0;
		$elmWidth = 0;
		$mxwElement = 0;
		$subVal = 0;
		$lessVal = 0;
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
