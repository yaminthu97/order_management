<?php
define ("PATH_RESOURCE","resource");
require_once("CheckDigit.php");
/**
 * QRコード作成クラス
 */
class QR{

	var $pari="";
	var $data_org="";
	var $mm1;
	var $shiftDat;
	var $toban=0;
	var $tobam=0;

	var $error_level="M";
	var $version=5;

	/*! 全角文字コード 例："utf-8" / "shift-jis" / "932" ...等... */
	var $StringEncoding = "utf-8";

	function __construct(){
		$this->error_level="M";
		$this->version=5;

		$this->toban=0;
		$this->tobam=0;
		$this->pari="";
		$this->data_org="";
		$this->StringEncoding = "utf-8";
	}

	/**
	* 指定された幅に伸縮したQRコードを描画します。(読み取り精度は低下します。)
	* @param $code 描画を行うバーコードのコード(テキスト)
	* @param $width QRコードの幅を指定(単位：ドット)
	* @return QRコードのイメージを返します。
	*/
	function Draw_by_width($code, $width)
	{
		try {
			$wk = (int)$code;
			if($wk < 0)
			{
				trigger_error("QR : width is not plus.",E_USER_ERROR);
				exit;
			}
		} catch (Exception $ex) {
			trigger_error("QR : width is not number.",E_USER_ERROR);
			exit;
		}

		$img_output =ImageCreate($width, $width);
		$img_base = $this->draw_base($code);
		$w_base=$this->mm1+8;
		ImageCopyResized($img_output,$img_base,0,0,0,0,$width,$width,$w_base,$w_base);


		return $img_output;

	}


	/**
	* サイズ(1,2,4,8,16)を指定して読み取り精度の高いQRコードを描画します。
	* @param $code 描画を行うQRコードのコード(テキスト)
	* @param $size 1,2,4,8,16 のいずれかを指定
	* @return QRコードのイメージを返します。
	*/
	function draw_by_size($code, $size)
	{
		/*
		if($size == 1
			|| $size == 2
			|| $size == 4
			|| $size == 8
			|| $size == 16)
		{
		}
		else
		{
			trigger_error("QR : please set size -> 1,2,4,8,16",E_USER_ERROR);
			exit;
		}
		*/

		$img_base = $this->draw_base($code);
		$w_base=$this->mm1+8;
		$img_output =ImageCreate($w_base*$size, $w_base*$size);
		ImageCopyResized($img_output,$img_base,0,0,0,0,$w_base*$size,$w_base*$size,$w_base,$w_base);

		return $img_output;

	}

	/**
	* ベースとなる一番小さなQRコードを描画します。
	* @param $code 描画を行うバーコードのコード(テキスト)
	* @return QRコードのイメージを返します。
	*/
	function draw_base($code)
	{

		if($code == "")
		{
			trigger_error("QR : code empty.",E_USER_ERROR);
			exit;
		}


		$code0 = mb_convert_encoding ($code, $this->StringEncoding, "UTF-8, EUC-JP, JIS, SJIS, eucjp-win, sjis-win");
		$mtxContent = $this->SetQr($code0);
		
		//$qrcode_module_size=4;
		$w_base=$this->mm1+8;
		//$qrcode_image_size=$w_base*$qrcode_module_size;
		//if ($qrcode_image_size>1480){
		//	trigger_error("QRcode : Too large image size",E_USER_ERROR);
		//}

		$image_path="resource/_".$this->version.".qri";
		$base_image=ImageCreateFromPNG($image_path);

		$col[1]=ImageColorAllocate($base_image,0,0,0);
		$col[0]=ImageColorAllocate($base_image,255,255,255);

		$i=4;
		$mxe=4+$this->mm1;
		$ii=0;
		while ($i<$mxe){
			$j=4;
			$jj=0;
			while ($j<$mxe){
				if ($mtxContent[$ii][$jj] & $this->shiftDat){
					ImageSetPixel($base_image,$i,$j,$col[1]); 
				}
				$j++;
				$jj++;
			}
			$i++;
			$ii++;
		}

		if(!isOK()) {
			//SAMPLE 描画
			$red = ImageColorAllocate($base_image, 0xFF, 0x00, 0x00);
			ImageTTFText($base_image, 5, 0, 2, 5	,$red, "./font/mplus-1p-black.ttf", "SAMPLE");
		}

		return $base_image;

	}


	function SetQr($code){

		$codeLen=strlen($code);
		if ($codeLen<=0) {
			trigger_error("Data do not exist.",E_USER_ERROR);
			exit;
		}
		$dCntr=0;
		if ($this->toban>1){

			$dValue[0]=3;
			$dBits[0]=4;

			$dValue[1]=$this->tobam-1;
			$dBits[1]=4;

			$dValue[2]=$this->toban-1;
			$dBits[2]=4;

			$dValue[3]=$this->pari;
			$dBits[3]=8;

			$dCntr=4;
		}

		$dBits[$dCntr]=4;

		if (preg_match("/[^0-9]/",$code)!=0){
			if (preg_match("/[^0-9A-Z \$\*\%\+\.\/\:\-]/",$code)!=0) {

				$CwNumPlus=array(0,0,0,0,0,0,0,0,0,0,
					8,8,8,8,8,8,8,8,8,8,8,8,8,8,8,8,8,
					8,8,8,8,8,8,8,8,8,8,8,8,8,8);

				$dValue[$dCntr]=4;
				$dCntr++;
				$dValue[$dCntr]=$codeLen;
				$dBits[$dCntr]=8;
				$CwNumCntrVal=$dCntr;

				$dCntr++;
				$i=0;
				while ($i<$codeLen){
					$dValue[$dCntr]=ord(substr($code,$i,1));
					$dBits[$dCntr]=8;
					$dCntr++;
					$i++;
				}
			} else {

				$CwNumPlus=array(0,0,0,0,0,0,0,0,0,0,
					2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,
					4,4,4,4,4,4,4,4,4,4,4,4,4,4);

				$dValue[$dCntr]=2;
				$dCntr++;
				$dValue[$dCntr]=$codeLen;
				$dBits[$dCntr]=9; 
				$CwNumCntrVal=$dCntr;

				$alphanumericCharHash=array("0"=>0,"1"=>1,"2"=>2,"3"=>3,
					"4"=>4,"5"=>5,"6"=>6,"7"=>7,"8"=>8,"9"=>9,"A"=>10,"B"=>11,"C"=>12,"D"=>13,
					"E"=>14,"F"=>15,"G"=>16,"H"=>17,"I"=>18,"J"=>19,"K"=>20,"L"=>21,"M"=>22,
					"N"=>23,"O"=>24,"P"=>25,"Q"=>26,"R"=>27,"S"=>28,"T"=>29,"U"=>30,"V"=>31,
					"W"=>32,"X"=>33,"Y"=>34,"Z"=>35," "=>36,"$"=>37,"%"=>38,"*"=>39,
					"+"=>40,"-"=>41,"."=>42,"/"=>43,":"=>44);

				$i=0;
				$dCntr++;
				while ($i<$codeLen){
					if (($i %2)==0){
						$dValue[$dCntr]=$alphanumericCharHash[substr($code,$i,1)];
						$dBits[$dCntr]=6;
					} else {
						$dValue[$dCntr]=$dValue[$dCntr]*45+$alphanumericCharHash[substr($code,$i,1)];
						$dBits[$dCntr]=11;
						$dCntr++;
					}
					$i++;
				}
			}
		} else {

			$CwNumPlus=array(0,0,0,0,0,0,0,0,0,0,
				2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,
				4,4,4,4,4,4,4,4,4,4,4,4,4,4);

			$dValue[$dCntr]=1;
			$dCntr++;
			$dValue[$dCntr]=$codeLen;
			$dBits[$dCntr]=10; 
			$CwNumCntrVal=$dCntr;

			$i=0;
			$dCntr++;
			while ($i<$codeLen){
				if (($i % 3)==0){
					$dValue[$dCntr]=substr($code,$i,1);
					$dBits[$dCntr]=4;
				} else {
					$dValue[$dCntr]=$dValue[$dCntr]*10+substr($code,$i,1);
					if (($i % 3)==1){
						$dBits[$dCntr]=7;
					} else {
						$dBits[$dCntr]=10;
						$dCntr++;
					}
				}
				$i++;
			}
		}

		if (@$dBits[$dCntr]>0) {
			$dCntr++;
		}
		$i=0;
		$total_dBits=0;
		while($i<$dCntr){
			$total_dBits+=$dBits[$i];
			$i++;
		}


		$eccCharHash=array("L"=>"1",
			"l"=>"1",
			"M"=>"0",
			"m"=>"0",
			"Q"=>"3",
			"q"=>"3",
			"H"=>"2",
			"h"=>"2");

		$ec=@$eccCharHash[$this->error_level]; 

		if (!$ec){$ec=0;}

		$MaxdBitsArr=array(
			0,128,224,352,512,688,864,992,1232,1456,1728,
			2032,2320,2672,2920,3320,3624,4056,4504,5016,5352,
			5712,6256,6880,7312,8000,8496,9024,9544,10136,10984,
			11640,12328,13048,13800,14496,15312,15936,16816,17728,18672,

			152,272,440,640,864,1088,1248,1552,1856,2192,
			2592,2960,3424,3688,4184,4712,5176,5768,6360,6888,
			7456,8048,8752,9392,10208,10960,11744,12248,13048,13880,
			14744,15640,16568,17528,18448,19472,20528,21616,22496,23648,

			72,128,208,288,368,480,528,688,800,976,
			1120,1264,1440,1576,1784,2024,2264,2504,2728,3080,
			3248,3536,3712,4112,4304,4768,5024,5288,5608,5960,
			6344,6760,7208,7688,7888,8432,8768,9136,9776,10208,

			104,176,272,384,496,608,704,880,1056,1232,
			1440,1648,1952,2088,2360,2600,2936,3176,3560,3880,
			4096,4544,4912,5312,5744,6032,6464,6968,7288,7880,
			8264,8920,9368,9848,10288,10832,11408,12016,12656,13328
			);

		if (!$this->version){
			$i=1+40*$ec;
			$j=$i+39;
			$this->version=1; 
			while ($i<=$j){
				if (($MaxdBitsArr[$i])>=$total_dBits+$CwNumPlus[$this->version]     ){
					$MaxdBits=$MaxdBitsArr[$i];
					break;
				}
				$i++;
				$this->version++;
			}
		} else {
			$MaxdBits=$MaxdBitsArr[$this->version+40*$ec];
		}

		$total_dBits+=$CwNumPlus[$this->version];
		$dBits[$CwNumCntrVal]+=$CwNumPlus[$this->version];

		$MaxCwArr=array(0,26,44,70,100,134,172,196,242,
			292,346,404,466,532,581,655,733,815,901,991,1085,1156,
			1258,1364,1474,1588,1706,1828,1921,2051,2185,2323,2465,
			2611,2761,2876,3034,3196,3362,3532,3706);

		$MaxCw=$MaxCwArr[$this->version];
		$this->mm1=17+($this->version <<2);

		$matrixRemainBit=array(0,0,7,7,7,7,7,0,0,0,0,0,0,0,3,3,3,3,3,3,3,
			4,4,4,4,4,4,4,3,3,3,3,3,3,3,0,0,0,0,0,0);

		$byteNum=$matrixRemainBit[$this->version]+($MaxCw << 3);
		$filename=PATH_RESOURCE."/Ver.".$this->version.".".$ec.".qrd";
		$fp1 = fopen ($filename, "rb");
		$matx=fread($fp1,$byteNum);
		$maty=fread($fp1,$byteNum);
		$masks=fread($fp1,$byteNum);
		$fiX=fread($fp1,15);
		$fiY=fread($fp1,15);
		$rsEccCw=ord(fread($fp1,1));
		$rso=fread($fp1,128);
		fclose($fp1);

		$matrixXArr=unpack("C*",$matx);
		$matrixYArr=unpack("C*",$maty);
		$maskArr=unpack("C*",$masks);

		$rsOrder=unpack("C*",$rso);

		$formatInfoX2=unpack("C*",$fiX);
		$formatInfoY2=unpack("C*",$fiY);

		$formatInfoX1=array(0,1,2,3,4,5,7,8,8,8,8,8,8,8,8);
		$formatInfoY1=array(8,8,8,8,8,8,8,8,7,5,4,3,2,1,0);

		$MaxdataCW=($MaxdBits >>3);

		$filename = PATH_RESOURCE."/_".$rsEccCw.".qrd";
			
		$fp0 = fopen ($filename, "rb");
		$i=0;
		while ($i<256) {
			$rsCalArr[$i]=fread ($fp0,$rsEccCw);
			$i++;
		}
		fclose ($fp0);

		$filename = PATH_RESOURCE."/Ver.f".$this->version.".qrd";
		
		$fp0 = fopen ($filename, "rb");
		$frameDat = fread ($fp0, filesize ($filename));
		fclose ($fp0);

		if ($total_dBits<=$MaxdBits-4){
			$dValue[$dCntr]=0;
			$dBits[$dCntr]=4;
		} else {
			if ($total_dBits<$MaxdBits){
				$dValue[$dCntr]=0;
				$dBits[$dCntr]=$MaxdBits-$total_dBits;
			} else {
				if ($total_dBits>$MaxdBits){
					trigger_error("Overflow error",E_USER_ERROR);
					exit;
				}
			}
		}

		$i=0;
		$CwCntr=0;
		$Cw[0]=0;
		$remainingBit=8;

		while ($i<=$dCntr) {
			$buffer=@$dValue[$i];
			$bufferBit=@$dBits[$i];

			$flag=1;
			while ($flag) {
				if ($remainingBit>$bufferBit){  
					$Cw[$CwCntr]=((@$Cw[$CwCntr]<<$bufferBit) | $buffer);
					$remainingBit-=$bufferBit;
					$flag=0;
				} else {
					$bufferBit-=$remainingBit;
					$Cw[$CwCntr]=(($Cw[$CwCntr] << $remainingBit) | ($buffer >> $bufferBit));

					if ($bufferBit==0) {
						$flag=0;
					} else {
						$buffer= ($buffer & ((1 << $bufferBit)-1) );
						$flag=1;   
					}

					$CwCntr++;
					if ($CwCntr<$MaxdataCW-1){
						$Cw[$CwCntr]=0;
					}
					$remainingBit=8;
				}
			}
			$i++;
		}
		if ($remainingBit!=8) {
			$Cw[$CwCntr]=$Cw[$CwCntr] << $remainingBit;
		} else {
			$CwCntr--;
		}

		if ($CwCntr<$MaxdataCW-1){
			$flag=1;
			while ($CwCntr<$MaxdataCW-1){
				$CwCntr++;
				if ($flag==1) {
					$Cw[$CwCntr]=236;
				} else {
					$Cw[$CwCntr]=17;
				}
				$flag=$flag*(-1);
			}
		}

		$i=0;
		$j=0;
		$rsBlockNum=0;
		$rsTmp[0]="";

		while($i<$MaxdataCW){

			$rsTmp[$rsBlockNum].=chr($Cw[$i]);
			$j++;

			if ($j>=$rsOrder[$rsBlockNum+1]-$rsEccCw){
				$j=0;
				$rsBlockNum++;
				$rsTmp[$rsBlockNum]="";
			}
			$i++;
		}


		$rsBlockNum=0;
		$rsOrderNum=count($rsOrder);

		while ($rsBlockNum<$rsOrderNum){

			$rsCW=$rsOrder[$rsBlockNum+1];
			$rsDatCW=$rsCW-$rsEccCw;

			$rstemp=$rsTmp[$rsBlockNum].str_repeat(chr(0),$rsEccCw);
			$paddingDat=str_repeat(chr(0),$rsDatCW);

			$j=$rsDatCW;
			while($j>0){
				$first=ord(substr($rstemp,0,1));

				if ($first){
					$leftChar=substr($rstemp,1);
					$cal=$rsCalArr[$first].$paddingDat;
					$rstemp=$leftChar ^ $cal;
				} else {
					$rstemp=substr($rstemp,1);
				}

				$j--;
			}

			$Cw=array_merge($Cw,unpack("C*",$rstemp));

			$rsBlockNum++;
		}

		$i=0;
		while ($i<$this->mm1){
			$j=0;
			while ($j<$this->mm1){
				$mtxContent[$j][$i]=0;
				$j++;
			}
			$i++;
		}

		$i=0;
		while ($i<$MaxCw){
			$Cw_i=$Cw[$i];
			$j=8;
			while ($j>=1){
				$CwBitNumber=($i << 3) +  $j;
				$mtxContent[ $matrixXArr[$CwBitNumber] ][ $matrixYArr[$CwBitNumber] ]=((255*($Cw_i & 1)) ^ $maskArr[$CwBitNumber] ); 
				$Cw_i= $Cw_i >> 1;
				$j--;
			}
			$i++;
		}

		$mtxRemain=$matrixRemainBit[$this->version];
		while ($mtxRemain){
			$BitTmp = $mtxRemain + ( $MaxCw <<3);
			$mtxContent[ $matrixXArr[$BitTmp] ][ $matrixYArr[$BitTmp] ]  =  ( 255 ^ $maskArr[$BitTmp] );
			$mtxRemain--;
		}

		$minDSore=0;
		$horM="";
		$verM="";
		$k=0;
		while($k<$this->mm1){
			$l=0;
			while($l<$this->mm1){
				$horM=$horM.chr($mtxContent[$l][$k]);
				$verM=$verM.chr($mtxContent[$k][$l]);
				$l++;
			}
			$k++;
		}
		$i=0;
		$all_matrix=$this->mm1*$this->mm1;

		while ($i<8){
			$demN1=0;
			$tmpPttern=array();
			$bit= 1<< $i;
			$bitR=(~$bit)&255;
			$bitMask=str_repeat(chr($bit),$all_matrix);
			$hor = $horM & $bitMask;
			$ver = $verM & $bitMask;

			$Vshift1=$ver.str_repeat(chr(170),$this->mm1);
			$Vshift2=str_repeat(chr(170),$this->mm1).$ver;
			$Vor=chunk_split(~($Vshift1 | $Vshift2),$this->mm1,chr(170));
			$Vand=chunk_split(~($Vshift1 & $Vshift2),$this->mm1,chr(170));

			$hor=chunk_split(~$hor,$this->mm1,chr(170));
			$ver=chunk_split(~$ver,$this->mm1,chr(170));
			$hor=$hor.chr(170).$ver;

			$n1_search="/".str_repeat(chr(255),5)."+|".str_repeat(chr($bitR),5)."+/";
			$n3_search=chr($bitR).chr(255).chr($bitR).chr($bitR).chr($bitR).chr(255).chr($bitR);

			$demN3=substr_count($hor,$n3_search)*40;
			$demN4=floor(abs(( (100* (substr_count($ver,chr($bitR))/($byteNum)) )-50)/5))*10;


			$n2_search1="/".chr($bitR).chr($bitR)."+/";
			$n2_search2="/".chr(255).chr(255)."+/";
			$demN2=0;
			preg_match_all($n2_search1,$Vand,$tmpPttern);
			foreach($tmpPttern[0] as $tmpText){
				$demN2+=(strlen($tmpText)-1);
			}
			$tmpPttern=array();
			preg_match_all($n2_search2,$Vor,$tmpPttern);
			foreach($tmpPttern[0] as $tmpText){
				$demN2+=(strlen($tmpText)-1);
			}
			$demN2*=3;
			
			$tmpPttern=array();

			preg_match_all($n1_search,$hor,$tmpPttern);
			foreach($tmpPttern[0] as $tmpText){
				$demN1+=(strlen($tmpText)-2);
			}

			$demeritScore=$demN1+$demN2+$demN3+$demN4;

			if ($demeritScore<=$minDSore || $i==0){
				$maskNumber=$i;
				$minDSore=$demeritScore;
			}

			$i++;
		}

		$this->shiftDat=1 << $maskNumber;

		$formatInfoVal=(($ec << 3) | $maskNumber);
		$formatInfoArr=array("101010000010010","101000100100101",
			"101111001111100","101101101001011","100010111111001","100000011001110",
			"100111110010111","100101010100000","111011111000100","111001011110011",
			"111110110101010","111100010011101","110011000101111","110001100011000",
			"110110001000001","110100101110110","001011010001001","001001110111110",
			"001110011100111","001100111010000","000011101100010","000001001010101",
			"000110100001100","000100000111011","011010101011111","011000001101000",
			"011111100110001","011101000000110","010010010110100","010000110000011",
			"010111011011010","010101111101101");
		$i=0;
		while ($i<15){
			$content=substr($formatInfoArr[$formatInfoVal],$i,1);

			$mtxContent[$formatInfoX1[$i]][$formatInfoY1[$i]]=$content * 255;
			$mtxContent[$formatInfoX2[$i+1]][$formatInfoY2[$i+1]]=$content * 255;
			$i++;
		}

		return $mtxContent;
	}



}


