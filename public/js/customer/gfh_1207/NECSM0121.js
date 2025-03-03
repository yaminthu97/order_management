$(function () {
    let phoneNumber = document.getElementById('tel').value;
    let submitButton = document.getElementById('submit_CTI_call');

    if (phoneNumber) {
        submitButton.classList.remove('disabled-link');
    } else {
        submitButton.classList.add('disabled-link');
    }
});

function csvExport(event) {
    event.preventDefault();

    const outputUrl = event.target.dataset.outputAction;

    const form = document.getElementById("Form1");
    form.action = outputUrl;
    HTMLFormElement.prototype.submit.call(form);
}

function openModal(event) {
    event.preventDefault();
}

function deleteCustCommDtl(event) {

    event.preventDefault();

    const deleteUrl = event.target.dataset.deleteAction;

    const form = document.getElementById("Form1");
    form.action = deleteUrl;
    HTMLFormElement.prototype.submit.call(form);
}

function validatePhoneNumber() {
    let phoneNumber = document.getElementById('tel').value;
    let submitButton = document.getElementById('submit_CTI_call');

    if (phoneNumber) {
        submitButton.classList.remove('disabled-link');
    } else {
        submitButton.classList.add('disabled-link');
    }
}

function makeCall(event) {
    event.preventDefault();

    let phoneNumber = document.getElementById('tel').value;
    let submitButton = document.getElementById('submit_CTI_call');

    if (!submitButton.classList.contains('disabled-link') && phoneNumber) {
        submitButton.href = `callto:${phoneNumber}`;
    }
}

function updateTelField(telValue) {
    let submitButton = document.getElementById('submit_CTI_call');
    var $telField = $('#tel');

    if (telValue) {
        $telField.val(telValue);
        submitButton.classList.remove('disabled-link');
    } else {
        $telField.val('');
        submitButton.classList.add('disabled-link');
    }
}

// モーダル共通の設定
$('#dialogWindow').dialog({
    autoOpen: false,
    resizable: false,
    width: 1200,
    height: 720,
    modal: true,
    show: {
        effect: 'fade',
    },
    buttons: [{
        text: '閉じる',
        class: 'btn',
        click: function () {
            {
                $(this).dialog('close');
            };
        }
    }],
    open: function (event, ui) {
        $('.ui-dialog-titlebar').hide();
        $('#dialog_body').attr({
            width: '1162px',
            height: '582px',
        });
    },
    close: function () {
    }
});

function searchCustomer() {
    let formData = $('#search_customer_modal');
    $.ajax({
        url: '/gfh/order/api/customer/list',
        method: 'POST',
        data: formData.serialize(),
        success: function (response) {
            $('#dialogWindow .dialog_body').html(response.html);
        },
        error: function (xhr, status, error) {
            alert("顧客検索モーダルAPIの呼び出しに失敗しました。");
        }
    });
}

$(document).on('click', '#search_customer_modal .action_search_modal_button', function () {
    $('#search_customer_modal [name="hidden_next_page_no"]').val(1);
    $('#search_customer_modal [name="sorting_column"]').val("");
    $('#search_customer_modal [name="sorting_shift"]').val("");
    searchCustomer();
    return false;
});
$(document).on('click', '#search_customer_modal .next_page_link', function () {
    let hidden_next_page_no = $(this).attr('page_no');
    $('#search_customer_modal [name="hidden_next_page_no"]').val(hidden_next_page_no);
    searchCustomer();
    return false;
});
$(document).on('change', '#search_customer_modal [name="page_list_count"]', function () {
    $('#search_customer_modal [name="hidden_next_page_no"]').val(1);
    searchCustomer();
    return false;
});
$(document).on('click', '#search_customer_modal .next_sort_link', function () {
    $('#search_customer_modal [name="sorting_column"]').val($(this).attr('sort_column'));
    $('#search_customer_modal [name="sorting_shift"]').val($(this).attr('sort_shift'));
    $('#search_customer_modal [name="hidden_next_page_no"]').val(1);
    searchCustomer();
    return false;
});

$(document).on('click', '.billing_customer_selected_action', function () {
    // 顧客検索ダイアログ選択ボタン押下時イベント
    $.ajax({
        url: '/gfh/order/api/customer/' + $(this).attr('data-customer-id'),
        method: 'GET',
        headers: {
            'Authorization': $('input[name="_token"]').val()
        },
        dataType: 'json',
        success: function (json) {
            $('#m_cust_id').val(json.m_cust_id);
            $('#name_kanji').val(json.name_kanji);
            $('#name_kana').val(json.name_kana);
            updateTelField(json.tel1);
            $('#email').val(json.email1);
            $('#postal').val(json.postal);
            $('#address1').val(json.address1);
            $('#address2').val(json.address2);
            $('#dialogWindow').dialog('close');
        },
        error: function (xhr, status, error) {
            alert("顧客情報取得APIの呼び出しに失敗しました。");
        }
    });
});

// 顧客を検索する押下処理
$(".action_billing_search").click(function () {
    $.ajax({
        url: '/gfh/order/api/customer/list',
        method: 'POST',
        data: {
            'page_list_count': 30,
            'hidden_next_page_no': 1,
            '_token': $('input[name="_token"]').val()
        },
        success: function (response) {
            $('#dialogWindow .dialog_body').html(response.html);
            $('#dialogWindow').dialog('open');
        },
        error: function (xhr, status, error) {
            alert("顧客検索モーダルAPIの呼び出しに失敗しました。");
        }
    });
});
