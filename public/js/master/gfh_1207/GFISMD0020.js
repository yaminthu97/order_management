/* js/master/gfh_1207/GFISMD0020.js */
$(function(){

    // テンプレートを取得
    const rowtemplate = $('#detail_list_template').html();

    // 検索ボタンの処理
    $('.action_search').click(function(){
        const rowtemplate = $('#detail_list_template').html();

        url = $('#list-class').data('url');
        $('#detail_list').empty();// detail_listをクリアする
        
        $.ajax({
            url: url,
            dataType:'json',
            data:{
                'm_noshi_id':$('#m_noshi_id').val(),
                'm_noshi_format_id':$('#m_noshi_format_id').val()
            },

            success: function(data) {
                // エラーメッセージのクリア
                $('#search_error').html('');
                $('#search_error_format').html('');
                $('#search_error_format_index').html('');
            },
    
            error: function(data) {
                // エラーメッセージのクリア
                $('#search_error').html('');
                $('#search_error_format').html('');
                $('#search_error_format_index').html('');

                // グローバルメッセージを表示
                const errors = data.responseJSON.errors;

                if(data.status = 422){
                    if (errors.hasOwnProperty('m_noshi_format_id')) {
                        // $('#search_error_format').html(data.responseJSON.message);
                        $('#search_error_format').html(errors.m_noshi_format_id[0]);
                        $('#search_error_format_index').html(errors.m_noshi_format_id[0]);
                    } else if (errors.hasOwnProperty('m_noshi_id')) {
                        // $('#search_error').html(data.responseJSON.message);
                        $('#search_error').html(errors.m_noshi_id[0]);
                    } else {
                        $('#search_error').html(data.responseJSON.message);
                    }
                }
            }

        })
        .done(function(data){
            $('#m_noshi_format_selected').val($('#m_noshi_format_id').val());

            for(let idx=0;idx<data.length;idx++){
                let html = rowtemplate;
                html = html.replaceAll("##index##",idx);
                $('#detail_list').append(html);

                // 値の設定
                $('#detail_'+ idx + '_m_noshi_detail_id').val(data[idx]['m_noshi_detail_id']);
                $('#detail_'+ idx + '_m_noshi_naming_pattern_id').val(data[idx]['m_noshi_naming_pattern_id']);
                $('#detail_'+ idx + ' .company_name_count').text(data[idx]['company_name_count']);
                $('#detail_'+ idx + ' .section_name_count').text(data[idx]['section_name_count']);
                $('#detail_'+ idx + ' .title_count').text(data[idx]['title_count']);
                $('#detail_'+ idx + ' .f_name_count').text(data[idx]['f_name_count']);
                $('#detail_'+ idx + ' .name_count').text(data[idx]['name_count']);
                $('#detail_'+ idx + ' .ruby_count').text(data[idx]['ruby_count']);
                $('#detail_'+ idx + ' .template_file_name').text(data[idx]['template_file_name']);
                $('#detail_'+ idx + '_delete_flg_'+  data[idx]['delete_flg']).prop('checked',true);

                // template_file_nameの設定
                if (data[idx]['template_file_name'] === null || data[idx]['template_file_name'].trim() === "") {
                    $('#detail_' + idx + ' .template_file_name').text(''); // 空にする
                } else {
                    $('#detail_' + idx + ' .template_file_name').text(data[idx]['template_file_name']);
                }

                // template_file_nameがない場合、ダウンロードボタン非活性
                if (!data[idx]['template_file_name']) {
                    $('#detail_'+ idx + ' .submit_download').prop('disabled', true);
                }else{
                    $('#detail_'+ idx + ' .submit_download').prop('disabled', false);
                }
            }
        })
        .fail(function(data){ // 通信が失敗したとき
        })
        .always(function(data){ //通信の成否にかかわらず実行する処理 
        });
    });

    // 行を追加する処理
    $('.action_append').click(function(){
        let html = rowtemplate;
        let index = $('#detail_list tr').length; // 追加される行のインデックスを取得
        html = html.replaceAll("##index##", index);
        $('#detail_list').append(html);

        //追加された行の delete_flg のデフォルトを0に設定
        $('#detail_' + index + '_delete_flg_0').prop('checked', true);

        // 追加された行のダウンロードボタンを非活性に設定
        $('#detail_' + index + ' .submit_download').prop('disabled', true);
    });

});


// Naming Patternの選択変更時にカウント情報を更新する処理
$(document).on('change', '.m_noshi_naming_pattern_id', function() {
    let namingPatternId = $(this).val();
    let rowIndex = $(this).closest('tr').attr('id').replace('detail_', '');

    if (namingPatternId) {
        $.ajax({
            // url: '/gfh/order/api/noshi-naming-pattern/' + namingPatternId,
            url: '/gfh/order/api/noshi-naming-pattern/info/' + namingPatternId,
            type: 'GET',
            dataType: 'json',
            success: function(data) {
                // カウント情報を取得して該当行に表示
                let row = $('#detail_list tr').eq(rowIndex);
                row.find('.m_noshi_detail_id').text(data.m_noshi_detail_id);
                row.find('.company_name_count').text(data.company_name_count);
                row.find('.section_name_count').text(data.section_name_count);
                row.find('.title_count').text(data.title_count);
                row.find('.f_name_count').text(data.f_name_count);
                row.find('.name_count').text(data.name_count);
                row.find('.ruby_count').text(data.ruby_count);
                row.find('.template_file_name').text(data.template_file_name);
                row.find('.delete_flg').text(data.delete_flg).prop('checked',true);
            },
            error: function(a,b,c) {
                alert('データ取得に失敗しました');
            }
        });
    } else {
        // 初期化（選択が解除された場合）
        $('#detail_' + rowIndex + ' .company_name_count').text('');
        $('#detail_' + rowIndex + ' .section_name_count').text('');
        $('#detail_' + rowIndex + ' .title_count').text('');
        $('#detail_' + rowIndex + ' .f_name_count').text('');
        $('#detail_' + rowIndex + ' .name_count').text('');
        $('#detail_' + rowIndex + ' .ruby_count').text('');
        $('#detail_' + rowIndex + ' .template_file_name').text('');
    }
});


$(document).on('click', '.submit_register', function () {

    const rowIndex = $(this).closest('tr').attr('id').replace('detail_', '');
    formData = new FormData($('#Form2').get(0));
    const fileInput = $('#detail_' + rowIndex + ' input[type="file"]')[0]; // ファイル入力要素を取得
    

    // フォームデータに他の情報を追加
    formData.append('m_noshi_detail_id', $('#detail_' + rowIndex + ' .m_noshi_detail_id').val());
    formData.append('m_noshi_id', $('#m_noshi_id').val());
    formData.append('m_noshi_format_id', $('#m_noshi_format_selected').val());
    formData.append('m_noshi_naming_pattern_id', $('#detail_' + rowIndex + '_m_noshi_naming_pattern_id').val());
    formData.append('delete_flg', $("input[name='delete_flg_" + rowIndex + "']:checked").val());
    formData.append('_token', $('input[name="_token"]').val());

    // ファイルが選択されている場合、FormDataに追加
    if (fileInput && fileInput.files.length > 0) { 
        formData.append('file', fileInput.files[0]); // 修正: fileInput.file -> fileInput.files[0]
    } else {
    }

    url = $('#update-class').data('url');

    $.ajax({
        method: 'POST',
        url: url,
        data: formData,
        contentType: false, // FormDataを使う場合は、contentTypeをfalseにする
        processData: false, // FormDataを使う場合は、processDataをfalseにする
        success: function(json) {

            // エラーメッセージと成功メッセージのクリア
            $('#global_error').html('');
            $('#global_success').html('');

            $(`#detail_${rowIndex}_m_noshi_naming_pattern_id_error`).html('');
            $(`#detail_${rowIndex}_file_error`).html('');
            $(`#detail_${rowIndex}_delete_flg_error`).html('');

            // グローバルメッセージ 成功時のメッセージ取得
            $('#global_success').html(json.message);

            // ファイル名をtemplate_file_nameに表示
            if (fileInput && fileInput.files.length > 0) {
                const fileName = fileInput.files[0].name;
                $('#detail_' + rowIndex + ' .template_file_name').text(fileName);
                
                // ファイル入力フィールドをクリア
                fileInput.value = '';
            }

            // template_file_nameが空の場合ダウンロードボタンを非活性にする
            const templateFileName = $('#detail_' + rowIndex + ' .template_file_name').text();
            const downloadButton = $('#detail_' + rowIndex + ' .submit_download');
            if (templateFileName.trim() === "") {
                downloadButton.prop('disabled', true);
            } else {
                downloadButton.prop('disabled', false);
            }
        },

        error: function(json) {
            const errors = json.responseJSON.errors;

            // 成功メッセージとエラーメッセージのクリア
            $('#global_success').html('');
            $('#global_error').html('');

            if(json.status = 422){        
                for (let key in errors) {
                    const errorMessage = errors[key][0];
                    const errorElement = `<span class="text-danger">${errorMessage}</span>`;
        
                    // 各エラー項目にメッセージを表示
                    // `key`を使って、行のエラーメッセージ用の<div>に追加
                    $(`#detail_${rowIndex}_${key}_error`).html(errorElement);
                }
        
                // グローバルメッセージを表示
                $('#global_error').html(json.responseJSON.message);

            }

        }
    });
});


$(document).on('click', '.submit_download', function () {
    const detailId = $(this).data('id');
    const mAccountId = $(this).data('m_account_id');
    
    const m_noshi_detail_id = $('#' + detailId).val();
    const m_account_id = $('#' + mAccountId).val();
    const url = $('#download_class').data('url');

    const timestamp = new Date().getTime();
    const downloadUrl = `${url}?m_noshi_detail_id=${m_noshi_detail_id}&m_account_id=${mAccountId}&t=${timestamp}`;

    $.ajax({
        url: downloadUrl,
        method: 'GET',
        success: function (response, status, xhr) {
            const link = document.createElement('a');
            link.href = downloadUrl;
            document.body.appendChild(link);
            link.click();
            document.body.removeChild(link);
        },
        error: function (xhr) {
            const errorData = xhr.responseJSON;
            if (errorData && errorData.error) {
                $('#download_class').text(errorData.error); // エラーメッセージを表示
                $('#download_class_global').text(errorData.error); // エラーメッセージを表示
            } else {
                $('#download_class').text('ファイルのダウンロード中にエラーが発生しました。');
            }
        }
    });
});
