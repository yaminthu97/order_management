<?php
class DxConstants {
	public static $DxAlmostZero;	// double
	public static $DxModuleOff;	// int
	public static $DxModuleOnRed;	// int
	public static $DxModuleOnGreen;	// int
	public static $DxModuleOnBlue;	// int
	public static $DxModuleOnRGB;	// int
	public static $DxModuleOn;	// int
	public static $DxModuleUnsure;	// int
	public static $DxModuleAssigned;	// int
	public static $DxModuleVisited;	// int
	public static $DxModuleData;	// int
	public static $DxCharAsciiPad;	// byte
	public static $DxCharAsciiUpperShift;	// byte
	public static $DxCharTripletShift1;	// byte
	public static $DxCharTripletShift2;	// byte
	public static $DxCharTripletShift3;	// byte
	public static $DxCharFNC1;	// byte
	public static $DxCharStructuredAppend;	// byte
	public static $DxChar05Macro;	// byte
	public static $DxChar06Macro;	// byte
	public static $DxC40TextBasicSet;	// int
	public static $DxC40TextShift1;	// int
	public static $DxC40TextShift2;	// int
	public static $DxC40TextShift3;	// int
	public static $DxCharTripletUnlatch;	// int
	public static $DxCharEdifactUnlatch;	// int
	public static $DxCharC40Latch;	// byte
	public static $DxCharTextLatch;	// byte
	public static $DxCharX12Latch;	// byte
	public static $DxCharEdifactLatch;	// byte
	public static $DxCharBase256Latch;	// byte
	public static $SymbolRows;	// int[]
	public static $SymbolCols;	// int[]
	public static $DataRegionRows;	// int[]
	public static $DataRegionCols;	// int[]
	public static $HorizDataRegions;	// int[]
	public static $InterleavedBlocks;	// int[]
	public static $SymbolDataWords;	// int[]
	public static $BlockErrorWords;	// int[]
	public static $BlockMaxCorrectable;	// int[]
	public static $DxSzSquareCount;	// int
	public static $DxSzRectCount;	// int
	public static $DxUndefined;	// int
	public static $DxPatternX;	// int[]
	public static $DxPatternY;	// int[]
	public static $DxBlankEdge;	// DxPointFlow
	public static $DxHoughRes;	// int
	public static $DxNeighborNone;	// int
	public static $rHvX;	// int[]
	public static $rHvY;	// int[]
	public static $aLogVal;	// int[]
	public static $logVal;	// int[]
	public static function __staticinit() { // static class members
		self::$DxAlmostZero = doubleval(0.000001);
		self::$DxModuleOff = 0x00;
		self::$DxModuleOnRed = 0x01;
		self::$DxModuleOnGreen = 0x02;
		self::$DxModuleOnBlue = 0x04;
		self::$DxModuleOnRGB = 0x07;
		self::$DxModuleOn = 0x07;
		self::$DxModuleUnsure = 0x08;
		self::$DxModuleAssigned = 0x10;
		self::$DxModuleVisited = 0x20;
		self::$DxModuleData = 0x40;
		self::$DxCharAsciiPad = 129;
		self::$DxCharAsciiUpperShift = 235;
		self::$DxCharTripletShift1 = 0;
		self::$DxCharTripletShift2 = 1;
		self::$DxCharTripletShift3 = 2;
		self::$DxCharFNC1 = 232;
		self::$DxCharStructuredAppend = 233;
		self::$DxChar05Macro = 236;
		self::$DxChar06Macro = 237;
		self::$DxC40TextBasicSet = 0;
		self::$DxC40TextShift1 = 1;
		self::$DxC40TextShift2 = 2;
		self::$DxC40TextShift3 = 3;
		self::$DxCharTripletUnlatch = 254;
		self::$DxCharEdifactUnlatch = 31;
		self::$DxCharC40Latch = 230;
		self::$DxCharTextLatch = 239;
		self::$DxCharX12Latch = 238;
		self::$DxCharEdifactLatch = 240;
		self::$DxCharBase256Latch = 231;
		self::$SymbolRows = array( 10, 12, 14, 16, 18, 20, 22, 24, 26, 32, 36, 40, 44, 48, 52, 64, 72, 80, 88, 96, 104, 120, 132, 144, 8, 8, 12, 12, 16, 16 );
		self::$SymbolCols = array( 10, 12, 14, 16, 18, 20, 22, 24, 26, 32, 36, 40, 44, 48, 52, 64, 72, 80, 88, 96, 104, 120, 132, 144, 18, 32, 26, 36, 36, 48 );
		self::$DataRegionRows = array( 8, 10, 12, 14, 16, 18, 20, 22, 24, 14, 16, 18, 20, 22, 24, 14, 16, 18, 20, 22, 24, 18, 20, 22, 6, 6, 10, 10, 14, 14 );
		self::$DataRegionCols = array( 8, 10, 12, 14, 16, 18, 20, 22, 24, 14, 16, 18, 20, 22, 24, 14, 16, 18, 20, 22, 24, 18, 20, 22, 16, 14, 24, 16, 16, 22 );
		self::$HorizDataRegions = array( 1, 1, 1, 1, 1, 1, 1, 1, 1, 2, 2, 2, 2, 2, 2, 4, 4, 4, 4, 4, 4, 6, 6, 6, 1, 2, 1, 2, 2, 2 );
		self::$InterleavedBlocks = array( 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 2, 2, 4, 4, 4, 4, 6, 6, 8, 10, 1, 1, 1, 1, 1, 1 );
		self::$SymbolDataWords = array( 3, 5, 8, 12, 18, 22, 30, 36, 44, 62, 86, 114, 144, 174, 204, 280, 368, 456, 576, 696, 816, 1050, 1304, 1558, 5, 10, 16, 22, 32, 49 );
		self::$BlockErrorWords = array( 5, 7, 10, 12, 14, 18, 20, 24, 28, 36, 42, 48, 56, 68, 42, 56, 36, 48, 56, 68, 56, 68, 62, 62, 7, 11, 14, 18, 24, 28 );
		self::$BlockMaxCorrectable = array( 2, 3, 5, 6, 7, 9, 10, 12, 14, 18, 21, 24, 28, 34, 21, 28, 18, 24, 28, 34, 28, 34, 31, 31, 3, 5, 7, 9, 12, 14 );
		self::$DxSzSquareCount = 24;
		self::$DxSzRectCount = 6;
		self::$DxUndefined = -1;
		self::$DxPatternX = array( -1, 0, 1, 1, 1, 0, -1, -1 );
		self::$DxPatternY = array( -1, -1, -1, 0, 1, 1, 1, 0 );
		self::$DxHoughRes = 180;
		self::$DxNeighborNone = 8;
		self::$rHvX = array(256, 256, 256, 256, 255, 255, 255, 254, 254, 253, 252, 251, 250, 249, 248, 247, 246, 245, 243, 242, 241, 239, 237, 236, 234, 232, 230, 228, 226, 224, 222, 219, 217, 215, 212, 210, 207, 204, 202, 199, 196, 193, 190, 187, 184, 181, 178, 175, 171, 168, 165, 161, 158, 154, 150, 147, 143, 139, 136, 132, 128, 124, 120, 116, 112, 108, 104, 100, 96, 92, 88, 83, 79, 75, 71, 66, 62, 58, 53, 49, 44, 40, 36, 31, 27, 22, 18, 13, 9, 4, 0, -4, -9, -13, -18, -22, -27, -31, -36, -40, -44, -49, -53, -58, -62, -66, -71, -75, -79, -83, -88, -92, -96, -100, -104, -108, -112, -116, -120, -124, -128, -132, -136, -139, -143, -147, -150, -154, -158, -161, -165, -168, -171, -175, -178, -181, -184, -187, -190, -193, -196, -199, -202, -204, -207, -210, -212, -215, -217, -219, -222, -224, -226, -228, -230, -232, -234, -236, -237, -239, -241, -242, -243, -245, -246, -247, -248, -249, -250, -251, -252, -253, -254, -254, -255, -255, -255, -256, -256, -256);
		self::$rHvY = array(0, 4, 9, 13, 18, 22, 27, 31, 36, 40, 44, 49, 53, 58, 62, 66, 71, 75, 79, 83, 88, 92, 96, 100, 104, 108, 112, 116, 120, 124, 128, 132, 136, 139, 143, 147, 150, 154, 158, 161, 165, 168, 171, 175, 178, 181, 184, 187, 190, 193, 196, 199, 202, 204, 207, 210, 212, 215, 217, 219, 222, 224, 226, 228, 230, 232, 234, 236, 237, 239, 241, 242, 243, 245, 246, 247, 248, 249, 250, 251, 252, 253, 254, 254, 255, 255, 255, 256, 256, 256, 256, 256, 256, 256, 255, 255, 255, 254, 254, 253, 252, 251, 250, 249, 248, 247, 246, 245, 243, 242, 241, 239, 237, 236, 234, 232, 230, 228, 226, 224, 222, 219, 217, 215, 212, 210, 207, 204, 202, 199, 196, 193, 190, 187, 184, 181, 178, 175, 171, 168, 165, 161, 158, 154, 150, 147, 143, 139, 136, 132, 128, 124, 120, 116, 112, 108, 104, 100, 96, 92, 88, 83, 79, 75, 71, 66, 62, 58, 53, 49, 44, 40, 36, 31, 27, 22, 18, 13, 9, 4);
		self::$aLogVal = array(1, 2, 4, 8, 16, 32, 64, 128, 45, 90, 180, 69, 138, 57, 114, 228, 229, 231, 227, 235, 251, 219, 155, 27, 54, 108, 216, 157, 23, 46, 92, 184, 93, 186, 89, 178, 73, 146, 9, 18, 36, 72, 144, 13, 26, 52, 104, 208, 141, 55, 110, 220, 149, 7, 14, 28, 56, 112, 224, 237, 247, 195, 171, 123, 246, 193, 175, 115, 230, 225, 239, 243, 203, 187, 91, 182, 65, 130, 41, 82, 164, 101, 202, 185, 95, 190, 81, 162, 105, 210, 137, 63, 126, 252, 213, 135, 35, 70, 140, 53, 106, 212, 133, 39, 78, 156, 21, 42, 84, 168, 125, 250, 217, 159, 19, 38, 76, 152, 29, 58, 116, 232, 253, 215, 131, 43, 86, 172, 117, 234, 249, 223, 147, 11, 22, 44, 88, 176, 77, 154, 25, 50, 100, 200, 189, 87, 174, 113, 226, 233, 255, 211, 139, 59, 118, 236, 245, 199, 163, 107, 214, 129, 47, 94, 188, 85, 170, 121, 242, 201, 191, 83, 166, 97, 194, 169, 127, 254, 209, 143, 51, 102, 204, 181, 71, 142, 49, 98, 196, 165, 103, 206, 177, 79, 158, 17, 34, 68, 136, 61, 122, 244, 197, 167, 99, 198, 161, 111, 222, 145, 15, 30, 60, 120, 240, 205, 183, 67, 134, 33, 66, 132, 37, 74, 148, 5, 10, 20, 40, 80, 160, 109, 218, 153, 31, 62, 124, 248, 221, 151, 3, 6, 12, 24, 48, 96, 192, 173, 119, 238, 241, 207, 179, 75, 150, 1);
		self::$logVal = array(-255, 255, 1, 240, 2, 225, 241, 53, 3, 38, 226, 133, 242, 43, 54, 210, 4, 195, 39, 114, 227, 106, 134, 28, 243, 140, 44, 23, 55, 118, 211, 234, 5, 219, 196, 96, 40, 222, 115, 103, 228, 78, 107, 125, 135, 8, 29, 162, 244, 186, 141, 180, 45, 99, 24, 49, 56, 13, 119, 153, 212, 199, 235, 91, 6, 76, 220, 217, 197, 11, 97, 184, 41, 36, 223, 253, 116, 138, 104, 193, 229, 86, 79, 171, 108, 165, 126, 145, 136, 34, 9, 74, 30, 32, 163, 84, 245, 173, 187, 204, 142, 81, 181, 190, 46, 88, 100, 159, 25, 231, 50, 207, 57, 147, 14, 67, 120, 128, 154, 248, 213, 167, 200, 63, 236, 110, 92, 176, 7, 161, 77, 124, 221, 102, 218, 95, 198, 90, 12, 152, 98, 48, 185, 179, 42, 209, 37, 132, 224, 52, 254, 239, 117, 233, 139, 22, 105, 27, 194, 113, 230, 206, 87, 158, 80, 189, 172, 203, 109, 175, 166, 62, 127, 247, 146, 66, 137, 192, 35, 252, 10, 183, 75, 216, 31, 83, 33, 73, 164, 144, 85, 170, 246, 65, 174, 61, 188, 202, 205, 157, 143, 169, 82, 72, 182, 215, 191, 251, 47, 178, 89, 151, 101, 94, 160, 123, 26, 112, 232, 21, 51, 238, 208, 131, 58, 69, 148, 18, 15, 16, 68, 17, 121, 149, 129, 19, 155, 59, 249, 70, 214, 250, 168, 71, 201, 156, 64, 60, 237, 130, 111, 20, 93, 122, 177, 150);
	}
	public static function constructor__ () 
	{
		$me = new self();
		try 
		{
			self::$DxBlankEdge = DxPointFlow::constructor__I(0);
		}
		catch (Exception $ex)
		{ /* empty */ }
		return $me;
	}
}
DxConstants::__staticinit(); // initialize static vars for this class on load
?>
