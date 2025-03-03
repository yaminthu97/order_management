<?php 
{
	/// <summary>
	/// エラートラップ用Exception：Code39不正文字使用
	/// </summary>
	class Code39_BadChar extends Exception
	{
		public function __construct($s, $code = 0, Throwable $previous = null) {
			// なんらかのコード
		
			$message = "Barcode.php Code39 : 利用できない文字 = '" . $s . "' が使用されました。\n" 
			. "使用できる文字は\"1234567890ABCDEFGHIJKLMNOPQRSTUVWXYZ-. *$/+%\"です。";

			// 全てを正しく確実に代入する
			parent::__construct($message, $code, $previous);
		}
	}

	/// <summary>
	/// エラートラップ用Exception：Code128不正文字使用
	/// </summary>
	class Code128_BadChar extends Exception
	{
		public function __construct($s, $code = 0, Throwable $previous = null) {
			// なんらかのコード
		
			$message = "Barcode.php Code128 : 利用できない文字 = '" . $s 
			. "' ASCIIコード(0x" + dechex(ord(s)) + ") が使用されました。"; 

			// 全てを正しく確実に代入する
			parent::__construct($message, $code, $previous);
		}
	}

	/*


	/// <summary>
	/// エラートラップ用Exception：NW7不正文字使用
	/// </summary>
	class errNW7BadChar : ApplicationException
	{
		public errNW7BadChar(string s)
			:	base("Pao.BarCode.NW7 : 利用できない文字 = '" + s + "' が使用されました。\n" 
			+ "使用できる文字は\"ABCD.+:/$-0123456789\"です。"){}
		public errNW7BadChar(Exception innerException, string s)
			:	base("Pao.BarCode.NW7 : 利用できない文字 = '" + s + "' が使用されました。\n" 
			+ "使用できる文字は\"ABCD.+:/$-0123456789\"です。",innerException){}
	}

	/// <summary>
	/// エラートラップ用Exception：ITF不正文字使用
	/// </summary>
	public class errITFBadChar : ApplicationException
	{
		public errITFBadChar()
			:	base("Pao.BarCode.ITF : 数字以外の文字が使用されました。\n" 
			+ "使用できる文字は数字のみです。"){}
		public errITFBadChar(Exception innerException)
			:	base("Pao.BarCode.ITF : 数字以外の文字が使用されました。\n" 
			+ "使用できる文字は数字のみです。",innerException){}
	}

	/// <summary>
	/// エラートラップ用Exception：Matrix2of5不正文字使用
	/// </summary>
	public class errMatrix2of5BadChar : ApplicationException
	{
		public errMatrix2of5BadChar()
			:	base("Pao.BarCode.Matrix2of5 : 数字以外の文字が使用されました。\n" 
			+ "使用できる文字は数字のみです。"){}
		public errMatrix2of5BadChar(Exception innerException)
			:	base("Pao.BarCode.Matrix2of5 : 数字以外の文字が使用されました。\n" 
			+ "使用できる文字は数字のみです。",innerException){}
	}

	/// <summary>
	/// エラートラップ用Exception：NEC2of5不正文字使用
	/// </summary>
	public class errNEC2of5BadChar : ApplicationException
	{
		public errNEC2of5BadChar()
			:	base("Pao.BarCode.NEC2of5 : 数字以外の文字が使用されました。\n" 
			+ "使用できる文字は数字のみです。"){}
		public errNEC2of5BadChar(Exception innerException)
			:	base("Pao.BarCode.NEC2of5 : 数字以外の文字が使用されました。\n" 
			+ "使用できる文字は数字のみです。",innerException){}
	}

	/// <summary>
	/// エラートラップ用Exception：ITF奇数桁数
	/// </summary>
	public class errITFOddLen : ApplicationException
	{
		public errITFOddLen()
			:	base("Pao.BarCode.ITF : コードの桁数が奇数です。\n" 
			+ "ITFでは偶数の桁数しか表現できません。"){}
		public errITFOddLen(Exception innerException)
			:	base("Pao.BarCode.ITF : コードの桁数が奇数です。\n" 
			+ "ITFでは偶数の桁数しか表現できません。",innerException){}
	}

	/// <summary>
	/// エラートラップ用Exception：JAN13不正文字使用
	/// </summary>
	public class errJAN13BadChar : ApplicationException
	{
		public errJAN13BadChar()
			:	base("Pao.BarCode.JAN13 : 数字以外の文字が使用されました。\n" 
			+ "使用できる文字は数字のみです。"){}
		public errJAN13BadChar(Exception innerException)
			:	base("Pao.BarCode.JAN13 : 数字以外の文字が使用されました。\n" 
			+ "使用できる文字は数字のみです。",innerException){}
	}

	/// <summary>
	/// エラートラップ用Exception：JAN13桁数不正
	/// </summary>
	public class errJAN13BadLen : ApplicationException
	{
		public errJAN13BadLen()
			:	base("Pao.BarCode.JAN13 : コードの桁数は、13桁か、12桁を指定してください。\n" 
			+ "12桁の場合チェックキャラクタを自動付与します。"){}
		public errJAN13BadLen(Exception innerException)
			:	base("Pao.BarCode.JAN13 : コードの桁数は、13桁か、12桁を指定してください。\n" 
			+ "12桁の場合チェックキャラクタを自動付与します。",innerException){}
	}

	/// <summary>
	/// エラートラップ用Exception：JAN13チェックディジット不正
	/// </summary>
	public class errJAN13CheckDigit : ApplicationException
	{
		public errJAN13CheckDigit()
			:	base("Pao.BarCode.JAN13 : コード末尾のチェックデジットが誤っています。"){}
		public errJAN13CheckDigit(Exception innerException)
			:	base("Pao.BarCode.JAN13 : コード末尾のチェックデジットが誤っています。",innerException){}
	}

	/// <summary>
	/// エラートラップ用Exception：JAN8不正文字使用
	/// </summary>
	public class errJAN8BadChar : ApplicationException
	{
		public errJAN8BadChar()
			:	base("Pao.BarCode.JAN8 : 数字以外の文字が使用されました。\n" 
			+ "使用できる文字は数字のみです。"){}
		public errJAN8BadChar(Exception innerException)
			:	base("Pao.BarCode.JAN8 : 数字以外の文字が使用されました。\n" 
			+ "使用できる文字は数字のみです。",innerException){}
	}

	/// <summary>
	/// エラートラップ用Exception：JAN8桁数不正
	/// </summary>
	public class errJAN8BadLen : ApplicationException
	{
		public errJAN8BadLen()
			:	base("Pao.BarCode.JAN8 : コードの桁数は、8桁か、7桁を指定してください。\n" 
			+ "7桁の場合チェックキャラクタを自動付与します。"){}
		public errJAN8BadLen(Exception innerException)
			:	base("Pao.BarCode.JAN8 : コードの桁数は、8桁か、7桁を指定してください。\n" 
			+ "7桁の場合チェックキャラクタを自動付与します。",innerException){}
	}

	/// <summary>
	/// エラートラップ用Exception：JAN8チェックディジット不正
	/// </summary>
	public class errJAN8CheckDigit : ApplicationException
	{
		public errJAN8CheckDigit()
			:	base("Pao.BarCode.JAN8 : コード末尾のチェックデジットが誤っています。"){}
		public errJAN8CheckDigit(Exception innerException)
			:	base("Pao.BarCode.JAN8 : コード末尾のチェックデジットが誤っています。",innerException){}
	}

	/// <summary>
	/// エラートラップ用Exception：郵便カスタマーコード不正文字使用
	/// </summary>
	public class errYubinBadChar : ApplicationException
	{
		public errYubinBadChar(string s)
			:	base("Pao.BarCode.YubinCustomer : 利用できない文字 = '" + s + "' が使用されました。\n" 
			+ "使用できる文字は\"0123456789-ABCDEFGHIJKLMNOPQRSTUVWXYZ\"です。"){}
		public errYubinBadChar(Exception innerException, string s)
			:	base("Pao.BarCode.YubinCustomer : 利用できない文字 = '" + s + "' が使用されました。\n" 
			+ "使用できる文字は\"0123456789-ABCDEFGHIJKLMNOPQRSTUVWXYZ\"です。",innerException){}
	}

	/// <summary>
	/// エラートラップ用Exception：チェックデジットに数字以外の文字使用
	/// </summary>
	public class errCheckDigitBadChar extends Exception
	{
		public errCheckDigitBadChar()
			:	base("Pao.BarCode.CheckDigit : 数字以外の文字が使用されました。\n" 
			+ "使用できる文字は数字のみです。"){}
		public errCheckDigitBadChar(Exception innerException)
			:	base("Pao.BarCode.CheckDigit : 数字以外の文字が使用されました。\n" 
			+ "使用できる文字は数字のみです。",innerException){}
	}

	/// <summary>
	/// エラートラップ用Exception：QRコード桁数オーバー
	/// </summary>
	public class errQRCodeOverLength : ApplicationException
	{
		public errQRCodeOverLength()
			:	base("このバージョンのQRコードに格納できる文字数をオーバーしました。"){}
		public errQRCodeOverLength(Exception innerException)
			:	base("このバージョンのQRコードに格納できる文字数をオーバーしました。",innerException){}
	}

	*/
}
?>