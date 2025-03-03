$(document).ready(function() {
    if (typeof $.fn.autoKana !== "function") {
        console.error("autoKana is not loaded!");
    } else {
        $.fn.autoKana('#name_kanji', '#name_kana', {
            katakana: true
        });
    }

    $('#postal').on('input', function () {
        if (event.which != 13) { // if not enter key
            let postal = $(this).val().replace(/-/g, '');
            fetchAddressFromPostal(postal, 'address1', 'address2', 'address3', 'address4', 'address5');
        }
    });
});

function fetchAddressFromPostal(postal, address1, address2, address3, address4, address5) {
    $.ajax({
        url: '/gfh/order/api/zipcode/info/' + postal,
        method: 'GET',
        success: function(response) {
            if(typeof response === "object") {
                document.getElementById(address1).value = response.postal_prefecture;
                document.getElementById(address2).value = response.postal_city + response.postal_town;
                document.getElementById(address3).value = null;
                document.getElementById(address4).value = null;
                document.getElementById(address5).value = response.postal_city_kana + response.postal_town_kana;
                /* Check text byte body */
                checkByteBody($(`#${address1}`));
                checkByteBody($(`#${address2}`));
                checkByteBody($(`#${address3}`));
                checkByteBody($(`#${address4}`));
                checkByteBody($(`#${address5}`));
                /* Check text byte body */
            }
        }
    });
}
