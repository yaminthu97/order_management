/* css/order/base/app.js */
$(function(){
    // 表示時処理
    $(window).on('load', function () {
        //他の要素との重なり調整のため、要素の位置を移動する。
        $('#float_search_wrapper').insertAfter('#pagetop');
        $('#float_clear_wrapper').insertAfter('#pagetop');
    });

    // 受注取込：形式選択
	$('#input_order_csv_type').change(function() {
		if ($('#input_order_csv_type').val() == '7') {
			$('#input_order_csv_shop').show();
		} else {
			$('#input_order_csv_shop').hide();
		}
	});

    // フッタ検索
    $('#float_search').click(function() {
        $('#button_search').trigger('click');
    });

    // フッタ条件クリア
    $('#float_clear').click(function() {
        $('#button_search_clear').trigger('click');
    });

    // 検索ボタンクリック
    $(document).on('click', '#button_search', function(event) {
        event.preventDefault();
        getOrderList("search_list");
    });

    $(document).on('click', '.button_search_progress', function(event) {
        event.preventDefault();
        // 押下されたボタンに値が設定されていたらprogress_typeパラメータに設定する
        var params = [];
        var progress_type = $(this).val();
        if( progress_type != undefined && progress_type != null && progress_type != "" ){
            params.push({ name : "progress_type", value : progress_type });
        }
        $(this).closest('tr').find('input[name="progress_type[]"]').prop("checked", false);
        $(this).closest('td').find('input[name="progress_type[]"]').prop("checked", true);
        getOrderList("search_list", params);
    });

    // 一覧ページ番号クリック
    $(document).on('click', '.next_page_link', function(event) {
        event.preventDefault();
        changePage( this );
    });

    // 一覧表示件数変更
    $(document).on('change', '#page_list_count', function(event) {
        event.preventDefault();
        changePage( this );
    });

    // ソート
    $(document).on('click', '.next_sort_link', function(event) {
        event.preventDefault();
        $('#sorting_column').val( $(this).attr('sort_column') );
        $('#sorting_shift').val( $(this).attr('sort_shift') );
        $('#hidden_next_page_no').val( 1 );
        getOrderList('change_page');
    });

    // 一覧検索
    function getOrderList( submitName, formData = null ){
        var formUrl = $('#Form1').attr('action');
        // フォームのパラメータが指定されていない場合は現在のフォームから取得する
        if( formData == null ){
            formData = $('#Form1').serializeArray();
        }
        // submitパラメータを設定
        formData.push({ name : "submit", value : submitName });
        // AJAXリクエストを送信
        $.ajax({
            url: formUrl,
            method: 'POST',
            data: formData,
            success: function(response) {
                $('#search_results').html(response.html);
            },
            error: function(xhr, status, error) {
                $('#search_results').html('<p>エラーが発生しました。</p>');
            }
        });
    }

    // ページ遷移共通
    function changePage( element ){
        $('#hidden_next_page_no').val( $(element).attr('page_no') );
        getOrderList('change_page');
    }
})
