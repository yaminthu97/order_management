$( function() {
	//カレンダー表示
	$('.datetime-picker').datetimepicker({
		format: 'YYYY/MM/DD'
	});
	//画面スクロール位置
	$('form').submit(function(){
		var scroll_top = $(window).scrollTop();
		$('input.st',this).prop('value', scroll_top);
	});
	$('.shared_flg_all').click(function(){
		$('.shared_flg').prop('checked',$(this).prop('checked'));

	});
	$('.copy_to_all').click(function(){
		$('.copy_to').prop('checked',$(this).prop('checked'));
	});
	$('#submit_search').click(function(){
		document.getElementById("hidden_next_page_no").value = 1;
		return true;
	});
	$('.action-check-linkage').click(function(){
		let shared_flg = $(".shared_flg:checked");
		if(shared_flg.length == 0){
			alert("まとめて確認する項目をチェックしてください");
			return;
		}
		let ids=[];
		shared_flg.each(function(){
			ids.push($(this).val());
		});
		let data = JSON.stringify({
			shared_flg:ids,
			_token: $('input[name="_token"]').val()
		});
        // AJAXリクエストを送信
        $.ajax({
            url: $('#create_noshi_check_linkage_url').val(),
            method: 'POST',
            data: data,
			contentType: 'application/json',
			dataType: 'json',
			cache: false
        }).done(function(response) {
			if(response.error){
				alert(response.error);
				return;
			}
			$.each(response.list, function(index, value) {
				$('#noshiDtl-'+value['t_order_dtl_noshi_id']+' td.shared_flg_text').html("済");
				$('#noshiDtl-'+value['t_order_dtl_noshi_id']+' td.shared_flg_text').removeClass('shared_flg0');
				$('#noshiDtl-'+value['t_order_dtl_noshi_id']+' td.shared_flg_text').removeClass('shared_flg1');
				$('#noshiDtl-'+value['t_order_dtl_noshi_id']+' td.shared_flg_text').addClass('shared_flg1');
			});
		}).fail(function(jqXHR, textStatus, errorThrown) {
			console.log(jqXHR);
			if(jqXHR.responseJSON){
				alert(jqXHR.responseJSON.error);
			} else if(jqXHR.responseText){
				alert(jqXHR.responseText);
			} else {
				alert("まとめて確認に失敗しました");
			}
		});
	});
	$('.action-check-create').click(function(){
		let copy_from = $(".copy_from:checked");
		if(copy_from.length == 0){
			alert("コピー元を選択してください");
			return;
		}
		let copy_to = $(".copy_to:checked");
		if(copy_to.length == 0){
			alert("まとめて作成する項目をチェックしてください");
			return;
		}
		let ids=[];
		copy_to.each(function(){
			ids.push($(this).val());
		});
		let data = JSON.stringify({
			copy_from:copy_from.val(),
			copy_to:ids,
			_token: $('input[name="_token"]').val()
		});
        // AJAXリクエストを送信
        $.ajax({
            url: $('#create_noshi_check_create_url').val(),
            method: 'POST',
            data: data,
			contentType: 'application/json',
			dataType: 'json',
			cache: false
        }).done(function(response) {
			if(response.error){
				alert(response.error);
				return;
			}
			var link = document.createElement("a");
			document.body.appendChild(link);
			link.setAttribute("type", "hidden");
			link.href = "data:text/plain;base64," + response.data;
			link.download = response.name;
			link.click();
			document.body.removeChild(link);
			$.each(response.list, function(index, value) {
				$('#noshiDtl-'+value['t_order_dtl_noshi_id']+' td.noshi_file_name').html(value['noshi_file_name']);
			});
		}).fail(function(jqXHR, textStatus, errorThrown) {
			console.log(jqXHR);
			if(jqXHR.responseJSON){
				alert(jqXHR.responseJSON.error);
			} else if(jqXHR.responseText){
				alert(jqXHR.responseText);
			} else {
				alert("まとめて作成に失敗しました");
			}
		});
	});	
	$('.action-check-shared').click(function(){
		let copy_from = $(".copy_from:checked");
		if(copy_from.length == 0){
			alert("コピー元を選択してください");
			return;
		}
		let copy_to = $(".copy_to:checked");
		if(copy_to.length == 0){
			alert("まとめて共有する項目をチェックしてください");
			return;
		}
		let ids=[];
		copy_to.each(function(){
			ids.push($(this).val());
		});
		let data = JSON.stringify({
			copy_from:copy_from.val(),
			copy_to:ids,
			_token: $('input[name="_token"]').val()
		});
        // AJAXリクエストを送信
        $.ajax({
            url: $('#create_noshi_check_shared_url').val(),
            method: 'POST',
            data: data,
			contentType: 'application/json',
			dataType: 'json',
			cache: false
        }).done(function(response) {
			if(response.error){
				alert(response.error);
				return;
			}
			$.each(response.list, function(index, value) {
				$('#noshiDtl-'+value['t_order_dtl_noshi_id']+' td.noshi_file_name').html(value['noshi_file_name']);
			});
		}).fail(function(jqXHR, textStatus, errorThrown) {
			console.log(jqXHR);
			if(jqXHR.responseJSON){
				alert(jqXHR.responseJSON.error);
			} else if(jqXHR.responseText){
				alert(jqXHR.responseText);
			} else {
				alert("まとめて共有に失敗しました");
			}
		});
	});
	$('.action-create').click(function(){
		let data = JSON.stringify({
			t_order_dtl_noshi_id:$(this).data("id"),
			_token: $('input[name="_token"]').val()
		});
        // AJAXリクエストを送信
        $.ajax({
            url: $('#create_noshi_create_url').val(),
            method: 'POST',
            data: data,
			contentType: 'application/json',
			dataType: 'json',
			cache: false
        }).done(function(response) {
			if(response.error){
				alert(response.error);
				return;
			}
			var link = document.createElement("a");
			document.body.appendChild(link);
			link.setAttribute("type", "hidden");
			link.href = "data:text/plain;base64," + response.data;
			link.download = response.name;
			link.click();
			document.body.removeChild(link);
			$.each(response.list, function(index, value) {
				$('#noshiDtl-'+value['t_order_dtl_noshi_id']+' td.noshi_file_name').html(value['noshi_file_name']);
			});
		}).fail(function(jqXHR, textStatus, errorThrown) {
			console.log(jqXHR);
			if(jqXHR.responseJSON){
				alert(jqXHR.responseJSON.error);
			} else if(jqXHR.responseText){
				alert(jqXHR.responseText);
			} else {
				alert("熨斗作成に失敗しました");
			}
		});
	});
	$('.action-clear').click(function(){
		let data = JSON.stringify({
			t_order_dtl_noshi_id:$(this).data("id"),
			_token: $('input[name="_token"]').val()
		});
        // AJAXリクエストを送信
        $.ajax({
            url: $('#create_noshi_clear_url').val(),
            method: 'POST',
            data: data,
			contentType: 'application/json',
			dataType: 'json',
			cache: false
        }).done(function(response) {
			if(response.error){
				alert(response.error);
				return;
			}
			$.each(response.list, function(index, value) {
				$('#noshiDtl-'+value['t_order_dtl_noshi_id']+' td.noshi_file_name').html(value['noshi_file_name']);
			});
		}).fail(function(jqXHR, textStatus, errorThrown) {
			console.log(jqXHR);
			if(jqXHR.responseJSON){
				alert(jqXHR.responseJSON.error);
			} else if(jqXHR.responseText){
				alert(jqXHR.responseText);
			} else {
				alert("クリアに失敗しました");
			}
		});
	});
});