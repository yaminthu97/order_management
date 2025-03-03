{{-- GFMSMA0010:帳票テンプレートマスタ登録・修正 --}}
@php
$ScreenCd='GFMSMA0020';
@endphp

{{-- layout設定 --}}
@extends('common.layouts.default')

{{-- タイトル設定 --}}
@section('title', '帳票テンプレートマスタ登録・修正')

{{-- ぱんくず設定 --}}
@section('breadcrumb')
<li>帳票テンプレートマスタ登録・修正</li>
@endsection

@section('content')
<style>
	.header{
		background: #4472c4 !important;
		color: white !important;
	}

</style>
	<form method="POST" action="{{ route('master.templatemaster.edit.update', [ 'id' => $id]) }}" name="Form1" id="Form1" enctype="multipart/form-data">
		{{ csrf_field() }}
		<div class="u-mt--xs">
			<form id="search_form" method="post" >
				{{ csrf_field() }}
				<table class="table table-bordered c-tbl c-tbl--600">
					<tr>
						<th class="c-box--250">帳票名</th>
						<td>
							<lable>{{count($searchData)>0?$searchData[0]['report_name']: ""}}</label>
						</td>
					</tr>
					<tr>
						<th class="c-box--250">テンプレート名</th>
						<td><input type='text' class="form-control c-box--300" name="template_name" id="template_name" placeholder="" value="{{count($searchData)>0?($searchData[0]['template_name'] == null ? (count($req) > 0? $req['template_name'] : "") : $searchData[0]['template_name']) :"" }}" /></td>
					</tr>
					<tr>
						<th class="c-box--250 must">テンプレートファイル名</th>
						<td>
							<table class="u-mt--sm">
								<td><input id="ref_file_path" name="ref_file_path" type="file" class="u-ib"></td>
								<td>　　　　　　　　</td>
							</table>
						</td>
					</tr>
				</table>
				<div class="u-mt--sm">
					<a href="{{ route('master.templatemaster.list') }}" class="btn btn-default btn-lg">キャンセル</a>
					<input type="submit" value="登録" class="btn btn-success btn-lg  js_disabled_button" style="margin-left: 20px;">
				</div>
			</form>
		</div>
		<input id="hidden_report_file_name" name="hidden_report_file_name" type="hidden">
	</form>
	<script>
		document.addEventListener("DOMContentLoaded", function () {
			const fileInput = document.querySelector('input[type="file"]');
			const hiddenReportName = document.getElementById("hidden_report_file_name");
			const initialFileName = "{{$searchData[0]['template_file_name'] ?? '未登録'}}";
			const myFile = new File([''], initialFileName, {
				type: 'text/plain',
				lastModified: new Date(),
			});
			const dataTransfer = new DataTransfer();
			dataTransfer.items.add(myFile);
			fileInput.files = dataTransfer.files;
			hiddenReportName.value = initialFileName;

			// Update label when a file is selected
			fileInput.addEventListener("change", function () {
				if (fileInput.files.length > 0) {
					hiddenReportName.value = fileInput.files[0].name;
				} else {
					
					const initialFileName = hiddenReportName.value;
					const myFile = new File([''], initialFileName, {
						type: 'text/plain',
						lastModified: new Date(),
					});
					const dataTransfer = new DataTransfer();
					dataTransfer.items.add(myFile);
					fileInput.files = dataTransfer.files;
				}
			});
		});
	</script>
@endsection
