///////////////////////////// ダイアログ関連 ///////////////////////////////////
var DIALOG_TYPE = {
	'stock': '在庫状態'
};
var trigger = '';
var dlgTitle = '';
var dlgSrc = '';
var initDisp = true;
var rowId = '';
$(function(){
    function getTagList(){
        if($('#t_order_hdr_id').val() != ""){
            let html = '';
            $.ajax({
                url: '/gfh/order/api/order-tags/order/'+$('#t_order_hdr_id').val(),
                method: 'GET',
                headers: {
                    'Authorization': $('input[name="_token"]').val()
                },
                dataType: 'json',
                success: function(json) {
                    for (var key in json) {
                        html += '<label>';
                        html += '<p data-toggle="tooltip" data-placement="top">';
                        html += '<a class="btn ns-orderTag-style" style="background:#' + json[key].tag_color + ';color:#' + json[key].font_color + ';" type="button">';
                        if(json[key].deli_stop_flg < 0){
                            html += '' + json[key].tag_display_name + '';
                        } else {
                            html += '<u>' + json[key].tag_display_name + '</u>';
                        }
                        html += '</a>';
                        html += '</p>';
                        html += '</label>';
                    }
                    $('.tag-box').html(html);
                },
                error: function(xhr, status, error) {
                    alert("タグ一覧取得APIの呼び出しに失敗しました。");
                    $('.tag-box').html(html);
                }
            });
        }
    }

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
    // 在庫
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
    getTagList();
    $("#destination_area .nav-tabs .destination_tab:last").addClass("active");
    $("#destination_area .destination_tab_body div.tab-pane:last").addClass("active");
});