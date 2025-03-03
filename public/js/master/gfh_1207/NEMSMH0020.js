/* tabここから */
$('#tabs').tabs();
$('.edit_form').css('visibility', 'visible');

$(document).ready(function () {
    var ql = window.location.search.slice(1).split('&');
    var len = ql.length;
    var tid = 0;

    for (var i = 0; i < len; i++) {
        var v = ql[i].split('=');
        if (v[0] === 't') {
            tid = v[1];
            break;
        }
    }

    $('#tabs').tabs({
        active: tid
    });
    $("[name=warehouse_postal]").keyup(function () {
        AjaxZip3.zip2addr(
            this,
            '',
            'warehouse_prefectural',
            'warehouse_address',
            'dummy',
            'warehouse_house_number'
        );
    });
});

/* tabここまで */

