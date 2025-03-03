
document.querySelectorAll(".length_flg").forEach(chk => {
    chk.addEventListener("change", function() {
    	chkedName = $(this).attr('name');
	    if (chk.checked) {
	        $("#" + chkedName).val('1');
	    } else {
	        $("#" + chkedName).val('0');
	    }
    });
});

// Trigger on page reload
$(".tableID").each(function() {
    let selectedValue = $(this).val();
    let selectedName = $(this).prop("name");
    let columnID = selectedName.replace("table", "column");

    if (selectedValue) {
        $.ajax({
            url: '/gfh/getOrderTagColDict/' + selectedValue,
            method: 'GET',
            success: function(response) {
                $("." + columnID).empty().append('<option value=""></option>');
                $.each(response, function (key, value) {
                	var selected = '';
          			if(value == $("#db_" + columnID).val()) {
          				selected = "selected";
          			}
                    $("." + columnID).append('<option value="' + value + '"' + selected + '>' + key + '</option>');
                });	
            }
        });
    }
});

$(document).on("change", ".tableID", function () {
	let selectedValue = $(this).val();
	let selectedName = $(this).prop("name");
	let columnID = selectedName.replace("table", "column");;

	if (selectedValue) {
	    $.ajax({
	        url: '/gfh/getOrderTagColDict/' + selectedValue,
	        method: 'GET',
	        success: function(response) {
	        	$("." + columnID).empty().append('<option value=""></option>');
	        	$.each(response, function (key, value) {
	                $("." + columnID).append('<option value="' + value + '">' +key + '</option>');
	            });
	        }
	    });
	}
});