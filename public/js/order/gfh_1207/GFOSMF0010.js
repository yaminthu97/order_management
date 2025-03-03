/* css/order/scroll/app.js */
$(function(){
    // 表示時処理
    $(window).on('load', function () {
        changeImportType();
        changeExportType();
    });

    // 変更チェック
    $('input[name="import_type"]').change(function() {
        changeImportType();
    });
    $('input[name="export_type"]').change(function() {
        changeExportType();
    });

    // #import_type の値チェック
    function changeImportType(){
        var import_type = $('input[name="import_type"]:checked').val();
        console.log(import_type);
        if (import_type == '3') {
            $('#order_input_file').css('visibility', 'visible');
            $('#customer_input_file').css('visibility', 'visible');
        } else {
            $('#order_input_file').css('visibility', 'hidden');
            $('#customer_input_file').css('visibility', 'hidden');
        }
    }

    // export_type の値チェック
    function changeExportType(){
        var export_type = $('input[name="export_type"]:checked').val();
        console.log(export_type);
        if (export_type == '3') {
            $('#ship_output_file').css('visibility', 'visible');
            $('#nyukin_output_file').css('visibility', 'visible');
        } else {
            $('#ship_output_file').css('visibility', 'hidden');
            $('#nyukin_output_file').css('visibility', 'hidden');
        }
    }
})
