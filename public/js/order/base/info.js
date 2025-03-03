/* css/order/gfh_1207/info.js */

var DIALOG_TYPE = {
    'sell': '販売検索',
    'cust': '顧客検索',
    'stock': '在庫状態',
    'warehouse': '引当倉庫変更'
};
var trigger = '';
var dlgTitle = '';
var dlgSrc = '';
var initDisp = true;
var rowId = '';

$(function(){
    // 送付先と各種操作部分のタブを有効にする
    $( "#tabs" ).tabs();
    $( "#tabs2" ).tabs();

    /**
     * 送付先タブのループ
     */
    $(document).ready(function() {
        $('.destTabs').each(function(idx, elm){
            var zipcode = $(elm).find('.zipcode').text().trim().replaceAll('-', '');
            var address1 = $(elm).find('.address1_kana');
            var address2 = $(elm).find('.address2_kana');
            setAddressKana(zipcode, address1, address2);
        });
    });    

    /**
     * 全額設定
     */
    $(document).on('click','#setPaymentPrice',function(){
        $("input[name='payment_price']").val( $("input[name='bill_balance']").val() );
    });

	//在庫状態
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

	//引当倉庫変更
	$(document).on('click', '[name=btn_warehouse_change]', function() {
		rowId = $(this).data('skuid');
		dlgTitle = DIALOG_TYPE.warehouse;
		dlgSrc = $(this).data('href');
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
             console.log( "取得" );
		},
		close: function() {
			initDisp = true;
			if(trigger != 'cancel')
			{
				if (dlgTitle == DIALOG_TYPE.cust)
				{
					$('[name=submit_search_cust]').trigger('click');
				}
				if (dlgTitle == DIALOG_TYPE.sell)
				{
					$('#sell_find_index').val(rowId);
					$('[name=submit_search_sell]').trigger('click');
				}
			}
			trigger = '';
			rowId = '';
			dlgTitle = '';
			$('#sell_find_index').val('');
			$('#iframeDiv').attr({src : ''});
		}
	});
	//子画面初期表示設定
	$('#iframeDiv').load(function(){

		if (initDisp)
		{
			if (dlgTitle == DIALOG_TYPE.cust)
			{
				$(this).contents().find("[name=m_cust_id]").val($('#m_cust_id').val());
				$(this).contents().find("[name=tel]").val($('#order_tel1').val());
				$(this).contents().find("[name=name_kanji]").val($('#order_name').val());
				$(this).contents().find("[name=name_kana]").val($('#order_name_kana').val());
				$(this).contents().find("[name=email]").val($('#order_email1').val());
				$(this).contents().find("[name=postal]").val($('#order_postal').val());
				$(this).contents().find("[name=address1]").val($('#order_address1').val());
				$(this).contents().find("[name=address2]").val($('#order_address2').val() + $('#order_address3').val() + $('#order_address4').val());
			}
			if (dlgTitle == DIALOG_TYPE.sell)
			{
				var indexs = rowId.split('-');
				var destIndex =indexs[0];
				var dtlIndex =indexs[1];
				var baseName = 'register_destination_' + destIndex + '_register_detail_' + dtlIndex + '_';
				$(this).contents().find("#returnControllName1").val(baseName + 'order_sell_vol');
				$(this).contents().find("#returnControllName2").val(baseName + 'sell_cd');
				$(this).contents().find("#returnControllName3").val(baseName + 'variation_values');
				$(this).contents().find("#ec_page_cd").val($('#' + baseName + 'sell_cd').val());
				$(this).contents().find("#m_ecs_id").val($('#m_ecs_id').val());
				$(this).contents().find('#sales_condition tr').eq(0).children('td').eq(0).text($('#m_ecs_id option:selected').text());
			}
			initDisp = false;
		}
	});


    /**
     * 郵便番号から取得した住所フリガナをセット
     * zipcode : 郵便番号
     * address1 : 都道府県フリガナをセットする要素
     * address2 : 市区町村フリガナをセットする要素
     */
    function setAddressKana(zipcode, address1, address2){
        $(address1).val("");
        $(address2).val("");
        if( zipcode == '' ){
            return
        }
        $.ajax({
            url: '/gfh/order/api/zipcode/info/' + zipcode,
            method: 'GET',
            headers: {
                'Authorization': $('input[name="_token"]').val()
            },
            dataType: 'json',
            async: true,
            success: function(response) {
                $(address1).text(response.postal_prefecture_kana);
                $(address2).text(response.postal_city_kana + response.postal_town_kana);
            },
            error: function(xhr, status, error) {
                // ないので未処理
            }
        });
    }
})
