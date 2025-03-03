// バイト取得
function getByte(value) {
	var count = 0;
	for ( var i = 0; i < value.length; i++ ) {
		var s = value.substring(i, i + 1);
		var c = s.charCodeAt(0);
		if (c < 256 || (c >= 0xff61 && c <= 0xff9f)) {
			// 半角の場合
			count += 1;
		} else {
			// 全角の場合
			count += 2;
		}
	}
	return count;
}
// 文字列を最大バイト数で切り捨てる
function cutStr(value,max_byte) {
	var count = 0;
	var rv = "";
	for ( var i = 0; i < value.length; i++ ) {
		var s = value.substring(i, i + 1);
		var c = s.charCodeAt(0);
		if (c < 256 || (c >= 0xff61 && c <= 0xff9f)) {
			// 半角の場合
			count += 1;
		} else {
			// 全角の場合
			count += 2;
		}
		if(count > max_byte){
			return rv;
		} else {
			rv = rv + s;
		}
	}
	return rv;
}
function setInputBackGroundColor(target, flg) {
    target.removeClass("sizeover-error");
    if( flg == 'on'){
        target.addClass("sizeover-error");
    }
}
function checkByteBody(target){
	let item_name = target.data("item_name");
	let max_byte = target.data("max_byte");
	if(item_name && max_byte){
		let moji = target.val();
		var bt = getByte(moji);
		if(bt > max_byte){
	        alert(item_name	 + "の文字数が多すぎます。\n" + 
        	      "全角" + max_byte / 2 + "文字、半角" + max_byte + "文字以内で入力してください。\n" + 
            	  "現在全角換算" + bt / 2 + "文字、半角換算"+ bt +"です。");
			let cut = cutStr(moji,max_byte);
			target.val(cut);
	       	return false;
		}
	}
	setInputBackGroundColor(target,'off');
}

function checkByteBodyNoAlert(target){
	let item_name = target.data("item_name");
	let max_byte = target.data("max_byte");
	if(item_name && max_byte){
		let moji = target.val();
		var bt = getByte(moji);
		if(bt > max_byte){
            setInputBackGroundColor(target,'on')
	       	return false;
		}
	}
	setInputBackGroundColor(target,'off');
	return true;
}

function checkTextByteError(){
	let rv = true;
	$(".check_textbyte").each(function(){
		if(checkByteBodyNoAlert($(this)) == false){
			rv = false;
		}
	});
	return rv;
}
$(document).on('change', '.check_textbyte', function () {
	return checkByteBody($(this));
});

