/* css/order/base/image-modal.js */

$(function(){
	// 画像モーダルの初期化
	$('#itemImageDialog').dialog({
		autoOpen: false,
		resizable: false,
		width: 400,
		height: 400,
		modal: true,
		show: {
			effect : 'fade',
		},
		open : function(event, ui){
			$('.ui-dialog-titlebar').hide();
		},
		close: function() {
		}
	});

	/**
	 * 画像拡大表示
	 */
	$(document).on('click','.action_item_zoom',function(){
		// 同階層のimgを取得
	 	var imgs = $(this).siblings('img');
		if( imgs.length == 0 ){
			return;
		}
		$('#itemImageDialog img').attr('src', $( imgs[0] ).attr('src'));
		$('#itemImageDialog').dialog('open');
	});

	/**
	 * モーダル閉じる
	 */
	$(document).on('click','#closeItemImageDialog',function(){
		$('#itemImageDialog').dialog('close');
	});
});