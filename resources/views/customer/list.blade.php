{{-- NECSM0110:顧客検索 --}}
@php
$ScreenCd='NECSM0110';
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
<form enctype="multipart/form-data" method="POST" action="" name="Form1" id="Form1">
{{ csrf_field() }}

<div>

<table class="table c-tbl">
	<tr>
		<th>使用区分</th>
		<td>
			<label class="checkbox-inline"><input type="checkbox" name="delete_flg[]" value="0" {{ isset($searchRow['delete_flg']) && in_array('0', ($searchRow['delete_flg']), true)? 'checked="checked"' : ''  }}>使用中</label>
			<label class="checkbox-inline"><input type="checkbox" name="delete_flg[]" value="1" {{ isset($searchRow['delete_flg']) && in_array('1', ($searchRow['delete_flg']), true)? 'checked="checked"' : ''  }}>使用停止</label>
		</td>
		<th>顧客ID</th>
		<td>
			<input class="form-control" type="text" name="m_cust_id" value="{{$searchRow['m_cust_id'] ?? ''}}">
		</td>
		<th>顧客コード</th>
		<td>
			<input class="form-control" type="text" name="cust_cd" value="{{$searchRow['cust_cd'] ?? ''}}">
		</td>
	</tr>
	<tr>
		<th>顧客ランク</th>
		<td>
			@foreach($viewExtendData['contactWayTypes'] as $tableIdName1 => $tableIdValue1)
			<label class="checkbox-inline"><input type="checkbox" name="m_cust_runk_id[]" value="{{$tableIdValue1}}" {{ isset($searchRow['m_cust_runk_id']) && in_array("$tableIdValue1", ($searchRow['m_cust_runk_id']), true)? 'checked="checked"' : ''  }}>{{$tableIdName1}}</label>
			@endforeach
		</td>
		<th>法人名・団体名</th>
		<td>
			<input class="form-control" type="text" name="corporate_kanji" value="{{$searchRow['corporate_kanji'] ?? ''}}">
		</td>
		<th>法人名・団体名（フリガナ）</th>
		<td>
			<input class="form-control" type="text" name="corporate_kana" value="{{$searchRow['corporate_kana'] ?? ''}}">
		</td>
	</tr>
	<tr>
		<th>電話番号（勤務先）</th>
		<td>
			<input class="form-control" type="text" name="corporate_tel" value="{{$searchRow['corporate_tel'] ?? ''}}">
		</td>
		<th>名前</th>
		<td>
			<input class="form-control" type="text" name="name_kanji" value="{{$searchRow['name_kanji'] ?? ''}}">
			<label class="checkbox-inline"><input type="checkbox" name="name_kanji_fuzzy" value="1" {{ isset($searchRow['name_kanji_fuzzy']) && ($searchRow['name_kanji_fuzzy']=='1')? 'checked="checked"' : ''  }}>あいまい検索</label>
		</td>
		<th>フリガナ</th>
		<td>
			<input class="form-control" type="text" name="name_kana" value="{{$searchRow['name_kana'] ?? ''}}">
			<label class="checkbox-inline"><input type="checkbox" name="name_kana_fuzzy" value="1" {{ isset($searchRow['name_kana_fuzzy']) && ($searchRow['name_kana_fuzzy']=='1')? 'checked="checked"' : ''  }}>あいまい検索</label>
		</td>
	</tr>
	<tr>
		<th>性別</th>
		<td>
			<label class="checkbox-inline"><input type="checkbox" name="sex_type[]" value="0" {{ isset($searchRow['sex_type']) && in_array('0', ($searchRow['sex_type']), true)? 'checked="checked"' : ''  }}>不明</label>
			<label class="checkbox-inline"><input type="checkbox" name="sex_type[]" value="1" {{ isset($searchRow['sex_type']) && in_array('1', ($searchRow['sex_type']), true)? 'checked="checked"' : ''  }}>男性</label>
			<label class="checkbox-inline"><input type="checkbox" name="sex_type[]" value="2" {{ isset($searchRow['sex_type']) && in_array('2', ($searchRow['sex_type']), true)? 'checked="checked"' : ''  }}>女性</label>
		</td>
		<th>メールアドレス</th>
		<td>
			<input class="form-control" type="text" name="email" value="{{$searchRow['email'] ?? ''}}">
		</td>
		<th>電話番号</th>
		<td>
			<input class="form-control" type="text" name="tel" value="{{$searchRow['tel'] ?? ''}}">
			<label class="checkbox-inline"><input type="checkbox" name="tel_forward_match" value="1" {{ isset($searchRow['tel_forward_match']) && ($searchRow['tel_forward_match']=='1')? 'checked="checked"' : ''  }}>前方一致</label>
		</td>
	</tr>
	<tr>
		<th>FAX番号</th>
		<td>
			<input class="form-control" type="text" name="fax" value="{{$searchRow['fax'] ?? ''}}">
			<label class="checkbox-inline"><input type="checkbox" name="fax_forward_match" value="1" {{ isset($searchRow['fax_forward_match']) && ($searchRow['fax_forward_match']=='1')? 'checked="checked"' : ''  }}>前方一致</label>
		</td>
		<th>郵便番号</th>
		<td>
			<input class="form-control" type="text" name="postal" value="{{$searchRow['postal'] ?? ''}}">
		</td>
		<th>都道府県</th>
		<td>
			<select name="address1" class="form-control c-box--200">
			<option value=""></option>
			@foreach($viewExtendData['pref'] as $row)
			@php($prefValue = $row['prefectual_name'])
			@php($prefName = $row['prefectual_name'])
			<option value="{{$row->prefectual_name}}" {{isset($searchRow['address1'])&&($searchRow['address1']==$row->prefectual_name)?'selected':''}} >{{$row->prefectual_name}}</option>
			@endforeach
			</select>
		</td>
	</tr>
	<tr>
		<th>備考の有無</th>
		<td>
			<label class="checkbox-inline"><input type="checkbox" name="note_existence[]" value="2" {{ isset($searchRow['note_existence']) && in_array('2', ($searchRow['note_existence']), true)? 'checked="checked"' : ''  }}>無</label>
			<label class="checkbox-inline"><input type="checkbox" name="note_existence[]" value="1" {{ isset($searchRow['note_existence']) && in_array('1', ($searchRow['note_existence']), true)? 'checked="checked"' : ''  }}>有</label>
		</td>
		<th>備考</th>
		<td>
			<input class="form-control" type="text" name="note" value="{{$searchRow['note'] ?? ''}}">
		</td>
		<td></td>
		<td></td>
	</tr>
	<tr>
		<th>累計購入金額</th>
		<td>
			<input class="form-control u-input--mid" type="text" name="total_order_money_from" value="{{$searchRow['total_order_money_from'] ?? ''}}">
			～
			<input class="form-control u-input--mid" type="text" name="total_order_money_to" value="{{$searchRow['total_order_money_to'] ?? ''}}">
		</td>
		<th>購入回数</th>
		<td>
			<input class="form-control u-input--mid" type="text" name="total_order_count_from" value="{{$searchRow['total_order_count_from'] ?? ''}}">
			～
			<input class="form-control u-input--mid" type="text" name="total_order_count_to" value="{{$searchRow['total_order_count_to'] ?? ''}}">
		</td>
		<td></td>
		<td></td>
	</tr>
	<tr>
		<th>要注意区分</th>
		<td>
			<label class="checkbox-inline"><input type="checkbox" name="alert_cust_type[]" value="0" {{ isset($searchRow['alert_cust_type']) && in_array('0', ($searchRow['alert_cust_type']), true)? 'checked="checked"' : ''  }}>通常</label>
			<label class="checkbox-inline"><input type="checkbox" name="alert_cust_type[]" value="1" {{ isset($searchRow['alert_cust_type']) && in_array('1', ($searchRow['alert_cust_type']), true)? 'checked="checked"' : ''  }}>要確認</label>
			<label class="checkbox-inline"><input type="checkbox" name="alert_cust_type[]" value="2" {{ isset($searchRow['alert_cust_type']) && in_array('2', ($searchRow['alert_cust_type']), true)? 'checked="checked"' : ''  }}>受注不可</label>
		</td>
		<th>要注意コメント</th>
		<td>
			<input class="form-control" type="text" name="alert_cust_comment" value="{{$searchRow['alert_cust_comment'] ?? ''}}">
		</td>
		<th>削除顧客を含む</th>
		<td>
			<input type="checkbox" name="delete_include" value="1" {{ isset($searchRow['delete_include']) && ($searchRow['delete_include']=='1')? 'checked="checked"' : ''  }}>
		</td>
	</tr>
</table>

<input class="btn btn-success btn-lg" type="submit" name="submit_search" value="検索">
 &nbsp; <input type="button" class="btn btn-default btn-lg" name="new" value="新規顧客登録" onClick="location.href='./new'">
<input type="hidden" name="{{config('define.session_key_id')}}" value="{{$searchRow[config('define.session_key_id')] ?? ''}}">

@include('common.elements.csv_input_button', ['csvName' => '顧客'])

</div>
<br>
@if($paginator)
<div>
@include('common.elements.paginator_header')
@include('common.elements.page_list_count')
@include('common.elements.sorting_script')
<br>
<table class="table table-bordered c-tbl table-link nowrap">
	<tr>
		<th>@include('common.elements.sorting_column_name', ['columnName' => 'm_cust_id', 'columnViewName' => '顧客ID']) </th>
		<th>@include('common.elements.sorting_column_name', ['columnName' => 'cust_cd', 'columnViewName' => '顧客コード']) </th>
		<th>@include('common.elements.sorting_column_name', ['columnName' => 'm_cust_runk_id', 'columnViewName' => '顧客ランク']) </th>
		<th>法人名・団体名</th>
		<th>@include('common.elements.sorting_column_name', ['columnName' => 'name_kanji', 'columnViewName' => '名前']) </th>
		<th>@include('common.elements.sorting_column_name', ['columnName' => 'email', 'columnViewName' => 'メールアドレス']) </th>
		<th>電話</th>
		<th>FAX</th>
		<th>@include('common.elements.sorting_column_name', ['columnName' => 'postal', 'columnViewName' => '郵便番号']) </th>
		<th>@include('common.elements.sorting_column_name', ['columnName' => 'address1', 'columnViewName' => '都道府県']) </th>
		<th>備考</th>
	</tr>
	@if(!empty($paginator->count()) > 0)
		@foreach($paginator as $cust)

		<tr>
			<td>@include('common.elements.output_checkbox', ['checkKeyId' => $cust['m_cust_id']]) &nbsp;  <a href='@createUrl(cc , customer/edit/{{$cust['m_cust_id']}})'>{{$cust['m_cust_id']}}</a></td>
			<td>{{$cust['cust_cd']}}</td>
			<td>{{$cust['m_cust_runk_id']}}</td>
			<td>{{$cust['corporate_kanji']}}</td>
			<td>{{$cust['name_kanji']}}</td>
			<td>{{$cust['email']}}</td>
			<td>{{$cust['tel1']}}</td>
			<td>{{$cust['fax']}}</td>
			<td>{{$cust['postal']}}</td>
			<td>{{$cust['address1']}}</td>
			<td title="{{$cust['note_min']}}">{{$cust['note']}}</td>
		</tr>
		@endforeach
	@else
		@if ($searchResult->count() == 0)
			<tr>
				<td colspan="11">該当顧客が見つかりません。</td>
			</tr>
		@endif
	@endif
</table>
@include('common.elements.paginator_footer')
@include('common.elements.csv_output_button')
</div>
@endif
</form>
@endsection
