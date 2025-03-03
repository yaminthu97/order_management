{{-- GFMSMA0010:帳票テンプレートマスタ一覧 --}}
@php
$ScreenCd='GFMSMA0010';
@endphp

{{-- layout設定 --}}
@extends('common.layouts.default')

{{-- タイトル設定 --}}
@section('title', '帳票テンプレートマスタ一覧')

{{-- ぱんくず設定 --}}
@section('breadcrumb')
<li>帳票テンプレートマスタ一覧</li>
@endsection

@section('content')
<style>
	.header{
		background: #4472c4 !important;
		color: white !important;
	}

</style>
	<form method="POST" action="{{ route('master.templatemaster.download') }}" name="Form1" id="Form1" enctype="multipart/form-data">
		{{ csrf_field() }}
		<div class="u-mt--xs">
			<table class="table table-bordered c-tbl c-tbl--950">
				@if(isset($dataList) && is_array($dataList))
					@foreach($dataList as $key  =>$dataList)
						<tr>
							<th colspan="3" class="header">{{$key}}</th>
						</tr>
						<tr>
							<th class="c-box--250">帳票名</th>
							<th class="c-box--250">テンプレート名</th>
							<th class="c-box--450" >テンプレートファイル</th>
						</tr>
						@if(isset($dataList) && is_array($dataList))
							@foreach($dataList as $key1  =>$val)
								<tr>
									<td class="c-box--300"><a href="{{ route('master.templatemaster.edit', [ 'id' => $val['m_report_template_id']]) }}">{{$val['report_name']}}</a></td>
									<td class="c-box--300">{{$val['template_name']}}</td>
									<td class="c-box--300">
										<div style="display: flex; justify-content: space-between; align-items: center;">
											@if($val['template_file_name'] !="")
												<label>{{$val['template_file_name']}}</label>
												<button class="btn btn-default" style="margin-left: auto;" type="submit" name="submit" value="{{ $val['m_report_template_id'] }}" >ダウンロード</button>
											@else
												<label>未登録</label>
											@endif
										</div>
									</td>
								</tr>
							@endforeach
						@endif
					@endforeach
				@endif
			</table>
		</div>
	</form>
@endsection
