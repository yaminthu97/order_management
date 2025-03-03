{{-- NECSM0110:顧客検索 --}}
@php
$ScreenCd='NECSM0110';
$test=true;
@endphp

{{-- layout設定 --}}
@extends('common.layouts.default')

{{-- タイトル設定 --}}
@section('title', '顧客検索')

{{-- ぱんくず設定 --}}
@section('breadcrumb')
<li>顧客検索</li>
@endsection

@section('content')
@include('common.elements.datetime_picker_script')
@if( !empty($viewMessage) )
	<div class="c-box--1700 c-tbl-border-all u-p--sm sy_notice u-mb--ss">
		@foreach($viewMessage as $message)
			<p class="icon_sy_notice_03">{{$message}}</p>
		@endforeach
	</div>
@endif
<div id="messageContainer"></div>
<form enctype="multipart/form-data" method="POST" action="" name="Form1" id="Form1">
{{ csrf_field() }}

<div class="c-box--full">

	<div class="row c-box--full">
		<div class="col-md-12">
			<table class="table c-tbl c-tbl--full">
				
				<tr>
					<th style="width: 20%;">使用区分</th>
					<td>
						<label class="checkbox-inline"><input type="checkbox" name="delete_flg[]" value="0" {{ in_array('0', old('delete_flg', isset($searchRow['delete_flg']) ? $searchRow['delete_flg'] : []), true) ? 'checked="checked"' : '' }}>使用中</label>
						<label class="checkbox-inline"><input type="checkbox" name="delete_flg[]" value="1" {{ in_array('1', old('delete_flg', isset($searchRow['delete_flg']) ? $searchRow['delete_flg'] : []), true) ? 'checked="checked"' : '' }}>使用停止</label>
					</td>
					<th style="width: 20%;">メールアドレス</th>
					<td>
						<input class="form-control" type="text" name="email"  value="{{old('email', isset($searchRow['email'])? $searchRow['email']:'')}}">
						
					</td>
				</tr>
				
				<tr>
					<th style="width: 20%;">顧客ランク</th>
					<td>
						@if(!empty($viewExtendData))
						@foreach($viewExtendData['contactWayTypes'] as $custRunkName => $custRunkId)
						
						<label class="checkbox-inline">
						    <input type="checkbox" name="m_cust_runk_id[]" value="{{ $custRunkId }}" 
						        {{ in_array($custRunkId, old('m_cust_runk_id', isset($searchRow['m_cust_runk_id']) ? $searchRow['m_cust_runk_id'] : [])) ? 'checked="checked"' : '' }}>
						    {{ $custRunkName }}
						</label>

						@endforeach
						@endif
					</td>
					<th style="width: 20%;">郵便番号</th>
					<td>
						<input class="form-control" type="text" name="postal" value="{{old('postal', isset($searchRow['postal'])? $searchRow['postal']:'')}}">
						
					</td>
				</tr>

				<tr>
					<th style="width: 20%;">顧客ID</th>
					<td>
						<input class="form-control" type="text" name="m_cust_id" value="{{old('m_cust_id', isset($searchRow['m_cust_id'])? $searchRow['m_cust_id']:'')}}">
						
					</td>
					<th style="width: 20%;">都道府県</th>
					<td>
						<select name="address1" class="form-control c-box--200">
						<option value=""></option>
						@if(!empty($viewExtendData))
						@foreach($viewExtendData['pref'] as $row)
						@php($prefValue = $row['prefectual_name'])
						@php($prefName = $row['prefectual_name'])
						<option value="{{$prefValue}}" {{ old('address1', isset($searchRow['address1']) ? $searchRow['address1'] : '') == $prefValue ? 'selected' : '' }}>{{$prefName}}</option>
						@endforeach
						@endif
						</select>
						
					</td>
				</tr>

				<tr>
					<th style="width: 20%;">顧客コード</th>
					<td>
						<input class="form-control" type="text" name="cust_cd" value="{{old('cust_cd', isset($searchRow['cust_cd'])? $searchRow['cust_cd']:'')}}">
						
					</td>
					<th style="width: 20%;">購入回数</th>
					<td>
						<input class="form-control u-input--mid" type="text" name="total_order_count_from" value="{{old('total_order_count_from', isset($searchRow['total_order_count_from'])? $searchRow['total_order_count_from']:'')}}">

						～
						<input class="form-control u-input--mid" type="text" name="total_order_count_to" value="{{old('total_order_count_to', isset($searchRow['total_order_count_to'])? $searchRow['total_order_count_to']:'')}}">
						
					</td>
				</tr>

				<tr>
					<th style="width: 20%;">顧客区分</th>
					<td>
						@if(!empty($viewExtendData))
						@foreach($viewExtendData['customerType'] as $custTypeName => $custTypeId)
						
						<label class="checkbox-inline">
						    <input type="checkbox" name="customer_type[]" value="{{ $custTypeId }}" 
						        {{ in_array($custTypeId, old('customer_type', isset($searchRow['customer_type']) ? $searchRow['customer_type'] : [])) ? 'checked="checked"' : '' }}>
						    {{ $custTypeName }}
						</label>

						@endforeach
						@endif
					</td>
					
					<th style="width: 20%;">累計購入金額</th>
					<td>
						<input class="form-control u-input--mid" type="text" name="total_order_money_from" value="{{old('total_order_money_from', isset($searchRow['total_order_money_from'])? $searchRow['total_order_money_from']:'')}}" >
						
						～
						<input class="form-control u-input--mid" type="text" name="total_order_money_to" value="{{old('total_order_money_to', isset($searchRow['total_order_money_to'])? $searchRow['total_order_money_to']:'')}}" >
						
					</td>
				</tr>

				<tr>
					<th style="width: 20%;">法人名・団体名</th>
					<td>
						<input class="form-control" type="text" name="corporate_kanji" value="{{old('corporate_kanji', isset($searchRow['corporate_kanji'])? $searchRow['corporate_kanji']:'')}}">
						

					</td>
					<th style="width: 20%;">最新受注日</th>
					<td>
						<div style="display: flex;align-items: center;">
							<div class="u-input--mid">
								<div class="input-group date date-picker">
									<input class="form-control u-input--mid" style="width: 161px" name="newest_order_date_from" value="{{ old('newest_order_date_from', $searchRow['newest_order_date_from'] ?? '') }}">
									<span class="input-group-addon">
										<span class="glyphicon glyphicon-calendar"></span>
									</span>
								</div>
							</div>
						<span style="margin: 0 5px;font-size: 14px; ">～</span>
						<div class="u-input--mid">
							<div class="input-group date date-picker">
								<input class="form-control u-input--mid" style="width: 161px"  name="newest_order_date_to" value="{{ old('newest_order_date_to', $searchRow['newest_order_date_to'] ?? '') }}">
								<span class="input-group-addon">
									<span class="glyphicon glyphicon-calendar"></span>
								</span>
							</div>
						</div>
					</td>
				</tr>

				<tr>
					<th style="width: 20%;">法人名・団体名（フリガナ）</th>
					<td>
						<input class="form-control" type="text" name="corporate_kana" value="{{old('corporate_kana', isset($searchRow['corporate_kana'])? $searchRow['corporate_kana']:'')}}">
						
					</td>
					<th style="width: 20%;">備考の有無</th>
					<td>

						<label class="checkbox-inline"><input type="checkbox" name="note_existence[]" value="2" {{ in_array('2', old('note_existence', isset($searchRow['note_existence']) ? $searchRow['note_existence'] : []), true) ? 'checked="checked"' : '' }}>無</label>
						<label class="checkbox-inline"><input type="checkbox" name="note_existence[]" value="1"  {{ in_array('1', old('note_existence', isset($searchRow['note_existence']) ? $searchRow['note_existence'] : []), true) ? 'checked="checked"' : '' }}>有</label>
					</td>
				</tr>
				
				<tr>
					<th style="width: 20%;">電話番号（勤務先）</th>
					<td>
						<input class="form-control" type="text" name="corporate_tel" value="{{old('corporate_tel', isset($searchRow['corporate_tel'])? $searchRow['corporate_tel']:'')}}">
						
					</td>
					<th style="width: 20%;">備考</th>
					<td>
						<input class="form-control" type="text" name="note" value="{{old('note', isset($searchRow['note'])? $searchRow['note']:'')}}">
						
					</td>
				</tr>

				<tr>
					<th style="width: 20%;">名前</th>
					<td>
						<input class="form-control" type="text" name="name_kanji" value="{{old('name_kanji', isset($searchRow['name_kanji'])? $searchRow['name_kanji']:'')}}">
						<label class="checkbox-inline"><input type="checkbox" name="name_kanji_fuzzy" value="1" {{ isset($searchRow['name_kanji_fuzzy']) && ($searchRow['name_kanji_fuzzy']=='1')? 'checked="checked"' : ''  }}>あいまい検索</label>
						
					</td>
					<th style="width: 20%;">要注意区分</th>
					<td>
						<label class="checkbox-inline"><input type="checkbox" name="alert_cust_type[]" value="0"  {{ in_array('0', old('alert_cust_type', isset($searchRow['alert_cust_type']) ? $searchRow['alert_cust_type'] : []), true) ? 'checked="checked"' : '' }}>通常</label>
						<label class="checkbox-inline"><input type="checkbox" name="alert_cust_type[]" value="1" {{ in_array('1', old('alert_cust_type', isset($searchRow['alert_cust_type']) ? $searchRow['alert_cust_type'] : []), true) ? 'checked="checked"' : '' }}>要確認</label>
						<label class="checkbox-inline"><input type="checkbox" name="alert_cust_type[]" value="2" {{ in_array('2', old('alert_cust_type', isset($searchRow['alert_cust_type']) ? $searchRow['alert_cust_type'] : []), true) ? 'checked="checked"' : '' }}>受注不可</label>
					</td>
				</tr>

				<tr>
					<th style="width: 20%;">フリガナ</th>
					<td>
						<input class="form-control" type="text" name="name_kana" value="{{old('name_kana', isset($searchRow['name_kana'])? $searchRow['name_kana']:'')}}">
						<label class="checkbox-inline"><input type="checkbox" name="name_kana_fuzzy" value="1" {{ isset($searchRow['name_kana_fuzzy']) && ($searchRow['name_kana_fuzzy']=='1')? 'checked="checked"' : ''  }}>あいまい検索</label>
						
					</td>
					<th style="width: 20%;">要注意コメント</th>
					<td>
						<input class="form-control" type="text" name="alert_cust_comment" value="{{old('alert_cust_comment', isset($searchRow['alert_cust_comment'])? $searchRow['alert_cust_comment']:'')}}">
						
					</td>
				</tr>

				<tr>
					<th style="width: 20%;">性別</th>
					<td>
						<label class="checkbox-inline"><input type="checkbox" name="sex_type[]" value="0" {{ in_array('0', old('sex_type', isset($searchRow['sex_type']) ? $searchRow['sex_type'] : []), true) ? 'checked="checked"' : '' }}>不明</label>
						<label class="checkbox-inline"><input type="checkbox" name="sex_type[]" value="1" {{ in_array('1', old('sex_type', isset($searchRow['sex_type']) ? $searchRow['sex_type'] : []), true) ? 'checked="checked"' : '' }}>男性</label>
						<label class="checkbox-inline"><input type="checkbox" name="sex_type[]" value="2" {{ in_array('2', old('sex_type', isset($searchRow['sex_type']) ? $searchRow['sex_type'] : []), true) ? 'checked="checked"' : '' }}>女性</label>
					</td>
					<th style="width: 20%;">削除顧客を含む</th>
					<td>
						<label class="checkbox-inline"><input type="checkbox" name="delete_include" value="1" {{ old('delete_include', isset($searchRow['delete_include']) ? $searchRow['delete_include'] : '') == '1' ? 'checked' : '' }}>含む</label>
					</td>
				</tr>

				<tr>
					<th style="width: 20%;">電話番号</th>
					<td>
						<input class="form-control" type="text" name="tel" value="{{old('tel', isset($searchRow['tel'])? $searchRow['tel']:'')}}">
						<label class="checkbox-inline"><input type="checkbox" name="tel_forward_match" value="1" {{ isset($searchRow['tel_forward_match']) && ($searchRow['tel_forward_match']=='1')? 'checked="checked"' : ''  }}>前方一致</label>
						
					</td>
					<th style="width: 20%;">FAX番号</th>
					<td>
						<input class="form-control" type="text" name="fax" value="{{old('fax', isset($searchRow['fax'])? $searchRow['fax']:'')}}">
						<label class="checkbox-inline"><input type="checkbox" name="fax_forward_match" value="1" {{ isset($searchRow['fax_forward_match']) && ($searchRow['fax_forward_match']=='1')? 'checked="checked"' : ''  }}>前方一致</label>
						
					</td>
				</tr>
			</table>
		</div>

		
	</div>

	<div>
		<div class="d-flex c-ttl--02" style="border: 1px solid #337ab7; align-items: center; align-content: center;font-size: 15px !important;">
            <a class="u-bold u-mr--xs d-flex collapsed" data-toggle="collapse" data-target="#advanceSearch" aria-expanded="false"
				style="background-color: #fff; width: 25px; height: 20px; border-radius: 3px; align-content: center; justify-content: center;
					padding-right: 5px">
			</a>

			<p class="u-bold" style="margin-bottom: -2px">
				詳細検索
			</p>
        </div>

		<div id="advanceSearch" class="collapse">
			<table class="table c-tbl c-tbl--full c-tbl-border-all">
			<tbody>
				<tr>
				<th style="width: 20%;">受注日時</th>
				<td>
					<div style="display: flex;align-items: center;">
					<div class="u-input--mid">
						<div class="input-group date datetime-picker" id="order_datetime_from">
							<input class="form-control u-input--mid" type="text" name="order_datetime_from" value="{{$searchRow['order_datetime_from'] ?? ''}}" style="width: 161px;">
							<span class="input-group-addon">
								<span class="glyphicon glyphicon-calendar"></span>
							</span>
							
							
						</div>
					</div>
                    <span style="margin: 0 10px;font-size: 14px; ">～</span>
					<div class="u-input--mid">
						<div class="input-group date datetime-picker" id="order_datetime_to">
							<input class="form-control u-input--mid" type="text" name="order_datetime_to" value="{{$searchRow['order_datetime_to'] ?? ''}}" style="width: 161px;">
							<span class="input-group-addon">
								<span class="glyphicon glyphicon-calendar"></span>
							</span>
						</div>
					</div>
					</div>
				</td>
				</tr>
				<tr>
				<th style="width: 20%;">DM発送方法 郵便</th>
				<td>
					<label class="checkbox-inline">
					<input class="form-check-input" type="checkbox" name="dm_send_letter_flg[]" value="0" {{ isset($searchRow['dm_send_letter_flg']) && in_array('0', ($searchRow['dm_send_letter_flg']), true)? 'checked="checked"' : ''  }}>希望する
					</label>
					<label class="checkbox-inline">
					<input class="form-check-input" type="checkbox" name="dm_send_letter_flg[]" value="1" {{ isset($searchRow['dm_send_letter_flg']) && in_array('1', ($searchRow['dm_send_letter_flg']), true)? 'checked="checked"' : ''  }}>希望しない
					</label>
				</td>
				</tr>
				<tr>
				<th style="width: 20%;">DM発送方法 メール</th>
				<td>
					<label class="checkbox-inline">
					<input class="form-check-input" type="checkbox" name="dm_send_mail_flg[]" value="0" {{ isset($searchRow['dm_send_mail_flg']) && in_array('0', ($searchRow['dm_send_mail_flg']), true)? 'checked="checked"' : ''  }}>希望する
					</label>
					<label class="checkbox-inline">
					<input class="form-check-input" type="checkbox" name="dm_send_mail_flg[]" value="1" {{ isset($searchRow['dm_send_mail_flg']) && in_array('1', ($searchRow['dm_send_mail_flg']), true)? 'checked="checked"' : ''  }}>希望しない
					</label>
				</td>
				</tr>
				<tr>
				<th style="width: 20%;">キャンセル・含める</th>
				<td>
					<label class="checkbox-inline">
					<input class="form-check-input" type="checkbox" name="with_cancel" value="0" {{ isset($searchRow['with_cancel'])? 'checked="checked"' : ''  }}>キャンセルを含める
					</label>
					<label class="checkbox-inline">
					<input class="form-check-input" type="checkbox" name="with_return" value="1" {{ isset($searchRow['with_return'])? 'checked="checked"' : ''  }}>返品を含める
					</label>
				</td>
				</tr>
			</tbody>
			</table>
		</div>
	</div>

	<button class="btn btn-success btn-lg u-mt--sm" type="submit" name="submit" id="submit_search" value="search">検索</button>&nbsp;<button type="button" class="btn btn-default btn-lg u-mt--sm" onClick="location.href='./new'">新規顧客登録</button>
	<input type="hidden" name="{{config('define.cc.session_key_id')}}" value="{{$searchRow[config('define.cc.session_key_id')] ?? ''}}">
	<div class="u-mt--sl c-box c-tbl-border-all c-box--full">
	<table class="table table-bordered c-tbl c-tbl--full nowrap">
	<tr>
	<th>
	    {{-- {{$csvName or ''}} --}}
	    顧客取込
	</th>
	</tr>

	</table>

	<div  class="u-p--ss">
	    <div class="u-mt--sm">
	    	<input type="hidden" name="submit" value="" id="csv_input">
	        <input type="file" class="u-ib" name="csv_input_file" id="csv_input_file" form="Form1">
	        <button class="btn btn-default" type="submit" name="submit" id="submit_csv_input" value="csv_input" onClick="csvImport(event)">CSV 取込</button>
	        <div class="error u-mt--xs" id="importError"></div>
	        
	    </div>
	</div>

</div>
<br>

@if($test)
<div style="overflow-x: auto; white-space: nowrap;">
@if(isset($paginator) && !empty($paginator) && count($paginator) > 0)
@include('common.elements.paginator_header')
@include('common.elements.page_list_count')
@include('common.elements.sorting_script')
@endif
<br>

<table class="table table-bordered c-tbl c-tbl-full table-link nowrap" style="width: 100% !important;">
	<tr>
		<th><label><input type="checkbox" id="check_all" value="" onclick="checkAll()"></label>@include('common.elements.sorting_column_name', ['columnName' => 'm_cust_id', 'columnViewName' => '顧客ID'])</th>
		<th>@include('common.elements.sorting_column_name', ['columnName' => 'cust_cd', 'columnViewName' => '顧客コード'])</th>
		<th>@include('common.elements.sorting_column_name', ['columnName' => 'm_cust_runk_id', 'columnViewName' => '顧客ランク'])</th>
		<th>法人名・団体名</th>
		<th>@include('common.elements.sorting_column_name', ['columnName' => 'corporate_tel', 'columnViewName' => '電話番号（勤務先）'])</th>
		<th>@include('common.elements.sorting_column_name', ['columnName' => 'name_kanji', 'columnViewName' => '名前'])</th>
		<th>@include('common.elements.sorting_column_name', ['columnName' => 'newest_order_date', 'columnViewName' => '最新受注日'])</th>
		<th>@include('common.elements.sorting_column_name', ['columnName' => 'email1', 'columnViewName' => 'メールアドレス'])</th>
		<th>@include('common.elements.sorting_column_name', ['columnName' => 'reserve10', 'columnViewName' => 'Web顧客番号'])</th>
		<th>電話番号</th>
		<th>FAX番号</th>
		<th>@include('common.elements.sorting_column_name', ['columnName' => 'postal', 'columnViewName' => '郵便番号'])</th>
		<th>@include('common.elements.sorting_column_name', ['columnName' => 'address1', 'columnViewName' => '都道府県'])</th>
		<th>備考</th>
	</tr>
	

	@if(isset($paginator) && count($paginator) > 0)
		@foreach($paginator as $customer)
		<tr>
			<td>@include('common.elements.output_checkbox', ['checkKeyId' => $customer['m_cust_id']]) &nbsp;  <a href='./edit/{{$customer['m_cust_id']}}'>{{$customer['m_cust_id'] ?? null}}</a></td>
			<td>{{$customer['cust_cd'] ?? null}}</td>
			<td>{{$customer['m_itemname_type_name'] ?? null}}</td>
			<td>{{$customer['corporate_kanji'] ?? null}}</td>
			<td>{{$customer['corporate_tel'] ?? null}}</td>
			<td>{{$customer['name_kanji'] ?? null}}</td>
			<td>{{$customer['newest_order_date'] ?? null}}</td>
			<td>{{$customer['email1'] ?? null}}</td>
			<td>{{$customer['reserve10'] ?? null}}</td>
			<td>{{$customer['tel1'] ?? null}}</td>
			<td>{{$customer['fax'] ?? null}}</td>
			<td>{{$customer['postal'] ?? null}}</td>
			<td>{{$customer['address1'] ?? null}}</td>
			<td>@include('customer.gfh_1207.tooltip_script', ['text' => $customer['note'], 'limit' => '10'])</td> 
		</tr>
		@endforeach
	@else
		<tr>
			<td colspan="14">該当顧客が見つかりません。</td>
		</tr>
	@endif
</table>

@if(isset($paginator) && count($paginator) > 0)
	@include('common.elements.all_check_script', ['column_name' => $viewExtendData['output_check_key_name']])

	@include('common.elements.paginator_footer')
	{{-- @include('common.elements.csv_output_button') --}}
	<div>
		チェックした行を
		<button class="btn btn-default" type="submit" name="submit" id="submit_csv_output" value="csv_output" onClick="csvExport(event)">CSV出力</button>
		<button class="btn btn-default" type="submit" name="submit" id="submit_csv_bulk_output" value="csv_bulk_output" onClick="csvBulkExport(event)">一覧をすべてCSV出力</button>
		<div class="error u-mt--xs" id="exportError"></div>
		<input type="hidden" name="submit" value="" id="csv_output">
	</div>
@endif

<br><br>
</div>
@endif
</form>

@push('js')
    <script src="{{ esm_internal_asset('js/customer/gfh_1207/NECSM0110.js') }}"></script>
@endpush

@endsection
