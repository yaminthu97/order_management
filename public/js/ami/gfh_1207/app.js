// for 商品画像	item
const imgNameDisplay = document.getElementById('imgNameDisplay');
const imagePreview = document.getElementById('imagePreview');
const testInput = document.getElementById('test_input');
const imgDeleteBtn = document.getElementById("imgDeleteBtn");
const isDeleteInput = document.getElementById('is_delete_ami_page_img');
isDeleteInput.value = "0";

// for save id when 付属品の削除 is click
const deleted_id_arr = [];
document.getElementById("deleted_attachment_ids").value = JSON.stringify(deleted_id_arr);

// ファイルを選択ボタン を押下処理
function selectAmiPageImg(input) {
    const imgNameDisplay = document.getElementById("imgNameDisplay");

    // If a file is selected, update the image name
    if (input.files && input.files[0]) {
        const imgName = input.files[0].name; // get image name
        lastSelectedImageName = imgName; // store it as the last selected name
        imgNameDisplay.textContent = imgName; // set image name
    } else {
        // the file selected is empty (click cancel)
        imgNameDisplay.textContent = lastSelectedImageName; // restore last selected name
    }

    if (input.files && input.files[0]) {
        const reader = new FileReader();

        reader.onload = function (e) {
            const imagePreview = document.getElementById("imagePreview");
            imagePreview.src = e.target.result; // set image src to the file's data
            imagePreview.style.display = "block"; // image show
        };

        reader.readAsDataURL(input.files[0]); // convert file to data URL
        imgDeleteBtn.style.display = "inline-block"; // show delete button
    }
}

// 削除を押下処理
function deleteAmiPageImg(event) {
    event.preventDefault();
    const fileInput = document.getElementById('fileInput');

    fileInput.value = "";  // clear the file input
    imgNameDisplay.textContent = "未登録";  // clear the file name display
    imagePreview.style.display = 'none';  // hide the image preview
    imagePreview.src = "";  // clear the image preview src
    imgDeleteBtn.style.display = "none";
    isDeleteInput.value = "1";
}

// 付属品の追加ボタンを押下処理
$(document).on("click", ".action_attachment_search", function () {
    // URL from the data-url attribute
    const apiUrl = $(this).data('url');

    $.ajax({
        url: apiUrl,
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': $('input[name="_token"]').val()
        },
        data: {
            'page_list_count': 30,
            'hidden_next_page_no': 1
        },
        success: function (response) {
            console.log(response.html);
            $('#dialogWindow .dialog_body').html(response.html);
            $('#dialogWindow').dialog('open');
        },
        error: function (xhr, status, error) {
            console.error("Error details:", xhr.responseText, status, error);
            alert("付属品モーダルAPIの呼び出しに失敗しました。");
        }
    });
});

// 付属品の検索 in modal box
function searchAttachmentItem(){
    let formData = $('#search_attachment_item_modal');
    // URL from the data-url attribute
    let searchUrl = formData.data('url');

    $.ajax({
        url: searchUrl,
        method: 'POST',
        data:  formData.serialize(),
        success: function(response) {
            $('#dialogWindow .dialog_body').html(response.html);
        },
        error: function(xhr, status, error) {
            alert("付属品検索モーダルAPIの呼び出しに失敗しました。");
        }
    });
}

// pagination of 付属品の検索
$(document).on('click', '#search_attachment_item_modal .action_search_modal_button', function () {
    $('#search_attachment_item_modal [name="hidden_next_page_no"]').val(1);
    $('#search_attachment_item_modal [name="sorting_column"]').val("");
    $('#search_attachment_item_modal [name="sorting_shift"]').val("");
    searchAttachmentItem();
    return false;
});

$(document).on('click', '#search_attachment_item_modal .next_page_link', function () {
    let hidden_next_page_no = $(this).attr('page_no');
    $('#search_attachment_item_modal [name="hidden_next_page_no"]').val(hidden_next_page_no);
    searchAttachmentItem();
    return false;
});

$(document).on('change', '#search_attachment_item_modal [name="page_list_count"]', function () {
    $('#search_attachment_item_modal [name="sorting_column"]').val($(this).attr('sort_column'));
    $('#search_attachment_item_modal [name="sorting_shift"]').val($(this).attr('sort_shift'));
    $('#search_attachment_item_modal [name="hidden_next_page_no"]').val(1);
    searchAttachmentItem();
    return false;
});

// 付属品の削除 is click, remove this current <tr> row
document.addEventListener("click", function (event) {
    if (event.target.tagName === "BUTTON" && event.target.id.startsWith("deleteBtn_")) {
        const buttonId = event.target.id;
        const deleteButton = document.getElementById(`${buttonId}`);
        const buttonParent = deleteButton.parentElement.parentElement;
        const deleteId = event.target.getAttribute("del-data-id");

        // in edit mode, save deleted id
        if (deleteId) {
            deleted_id_arr.push(deleteId);
            document.getElementById("deleted_attachment_ids").value = JSON.stringify(deleted_id_arr);
        }

        // remove current <tr> row
        if (buttonParent && buttonParent.tagName === "TR") {
            buttonParent.remove();
        }
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

// 説明文 use summernote
$(document).ready(function() {
    $('#amiPageDesc').summernote({
        lang: 'ja-JP',
        tabsize: 2,
        height: 150,
        toolbar: [
            ['style', ['style']],
            ['font', ['bold', 'underline', 'clear']],
            ['fontname', ['fontname']],
            ['color', ['color']],
            ['para', ['ul', 'ol', 'paragraph']],
        ],
        fontNames: [
            'Meiryo', 
            'Meiryo UI', 
            'MS Gothic', 
            'MS Mincho', 
            'MS PGothic', 
            'MS PMincho', 
            'Arial', 
            'Arial Black', 
            'Comic Sans MS', 
            'Courier New', 
            'Helvetica', 
            'Impact', 
            'Tahoma', 
            'Times New Roman', 
            'Verdana'
        ],
        fontNamesIgnoreCheck: [
           'Meiryo', 
            'Meiryo UI', 
            'MS Gothic', 
            'MS Mincho', 
            'MS PGothic', 
            'MS PMincho', 
            'Arial', 
            'Arial Black', 
            'Comic Sans MS', 
            'Courier New', 
            'Helvetica', 
            'Impact', 
            'Tahoma', 
            'Times New Roman', 
            'Verdana'
        ]
    });
});