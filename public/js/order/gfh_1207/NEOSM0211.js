///////////////////////////// ダイアログ関連 ///////////////////////////////////
var DIALOG_TYPE = {
	'stock': '在庫状態'
};
var trigger = '';
var dlgTitle = '';
var dlgSrc = '';
var initDisp = true;
var rowId = '';
$( function() {
	$("body").on("keydown",function(e){
		if(e.keyCode != 13){return true;}
		if($(document.activeElement).prop('tagName') == "TEXTAREA"){
			return true;
		}
		return false;
	});
});
$( function() {
	//受注日時カレンダー表示
	$('#datetimepicker_datetime').datetimepicker({
		format: 'YYYY/MM/DD HH:mm'
	});
	//画面スクロール位置
	$('form').submit(function(){
		var scroll_top = $(window).scrollTop();
		$('input.st',this).prop('value', scroll_top);
	});
});
$( function() {
	// 熨斗編集モーダルの設定
	$('#dialogNoshiWindow').dialog({
		autoOpen: false,
		resizable: false,
		width: 1200,
		height: 720,
		modal: true,
		show: {
			effect : 'fade',
		},
		buttons: [{
			text: '閉じる',
			class: 'btn',
			click: function() {
				{
					$(this).dialog('close');
				};
			}
		}],
		open : function(event, ui){
			$('.ui-dialog-titlebar').hide();
		},
		close: function() {
		}
	});
	// 付属品編集モーダルの設定
	$('#dialogAttachmentItemWindow').dialog({
		autoOpen: false,
		resizable: false,
		width: 1200,
		height: 720,
		modal: true,
		show: {
			effect : 'fade',
		},
		buttons: [{
			text: '閉じる',
			class: 'btn',
			click: function() {
				{
					$(this).dialog('close');
				};
			}
		}],
		open : function(event, ui){
			$('.ui-dialog-titlebar').hide();
		},
		close: function() {
		}
	});	
	// モーダル共通の設定
	$('#dialogWindow').dialog({
		autoOpen: false,
		resizable: false,
		width: 1200,
		height: 720,
		modal: true,
		show: {
			effect : 'fade',
		},
		buttons: [{
			text: '閉じる',
			class: 'btn',
			click: function() {
				{
					$(this).dialog('close');
				};
			}
		}],
		open : function(event, ui){
			$('.ui-dialog-titlebar').hide();
			$('#dialog_body').attr({
				width : '1162px',
				height : '582px',
			 });
		},
		close: function() {
		}
	});	

	$('[id^="drawing_status"]').click(function() {
		rowId = $(this).data('rowid');
		dlgTitle = DIALOG_TYPE.stock;
		var indexs = rowId.split('-');
		var destIndex =indexs[0];
		var dtlIndex =indexs[1];
		var sellVol = $('#register_destination_' + destIndex + '_register_detail_' + dtlIndex + '_order_sell_vol' ).val()
		dlgSrc = $(this).data('href') + '/vol/' + sellVol;
		initDisp = true;
		$('#frameWindow').dialog('open');
	});

	//子画面表示
	$('#frameWindow').dialog({
		autoOpen: false,
		resizable: false,
		width: 1200,
		height: 720,
		modal: true,
		tltle: dlgTitle,
		show: {
			effect : 'fade',
		},
		buttons: [{
			text: '閉じる',
			class: 'btn',
			click: function() {
				{
					trigger = 'cancel';
					$(this).dialog('close');
				};
			}
		}],
		open : function(event, ui){
			$('.ui-dialog-titlebar').hide();
			$('#iframeDiv').attr({
				src : dlgSrc,
				width : '1162px',
				height : '582px',
				frameborder: '0',
				marginwidth: '0',
				marginheight: '0'
			 });
		},
		close: function() {
			initDisp = true;
			trigger = '';
			rowId = '';
			dlgTitle = '';
			$('#sell_find_index').val('');
			$('#iframeDiv').attr({src : ''});
		}
	});
	//子画面初期表示設定
	$('#iframeDiv').load(function(){
	});
});
