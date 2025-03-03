{{-- NECSM0111:顧客受付 --}}
@php
$ScreenCd='NECSM0111';
@endphp

{{-- layout設定 --}}
@extends('common.layouts.default')

{{-- タイトル設定 --}}
@section('title', '顧客受付')

{{-- ぱんくず設定 --}}
@section('breadcrumb')
<li>顧客受付</li>
@endsection
@section('content')
<form method="POST" action="" name="Form1" id="Form1">
{{ csrf_field() }}
<div>
    <div class="c-box--1200" style='text-align: right;'>
        <a href='{{\Config::get('const.action_url')}}/gfh/order/order/list' style='color: #337ab7;'><i class="fas fa-arrows-alt-h"></i><u style='text-decoration: underline;'>受注検索へ</u></a>
    </div>
    <table class="table c-tbl">
        <tbody>
            <tr>
                <th class="c-box--200">電話番号</th>
                <td class="c-box--380">
                    <input type="text" class="form-control c-box--250" name="tel" placeholder="" value="{{ old('tel', $searchRow['tel'] ?? '') }}">
                    <label><input type="checkbox" name="tel_forward" value="1" {{ isset($searchRow['tel_forward']) && ($searchRow['tel_forward']=='1')? 'checked="checked"' : ''  }}>&nbsp;前方一致</label>
                </td>
                <th class="c-box--200">顧客コード</th>
                <td class="c-box--380">
                    <input type="text" class="form-control c-box--250" name="cust_cd" placeholder="" value="{{ old('cust_cd', $searchRow['cust_cd'] ?? '') }}">
                </td>
            </tr>
            <tr>
                <th class="c-box--200">顧客ID</th>
                <td class="c-box--380">
                    <input type="text" class="form-control c-box--250" name="m_cust_id" placeholder="" value="{{ old('m_cust_id', $searchRow['m_cust_id'] ?? '') }}">
                </td>
                <th class="c-box--200">Web会員番号</th>
                <td class="c-box--380">
                    <input type="text" class="form-control c-box--250" name="reserve10" placeholder="" value="{{ old('reserve10', $searchRow['reserve10'] ?? '') }}">
                	@include('common.elements.error_tag', ['name' => 'reserve10'])
                </td>
            </tr>
            <tr>
                <th class="c-box--200">名前</th>
                <td class="c-box--380">
                    <input type="text" class="form-control c-box--250" name="name_kanji" placeholder="" value="{{ old('name_kanji', $searchRow['name_kanji'] ?? '') }}">
                    <label><input type="checkbox" name="name_kanji_fuzzy" value="1" {{ isset($searchRow['name_kanji_fuzzy']) && ($searchRow['name_kanji_fuzzy']=='1')? 'checked="checked"' : ''  }}>&nbsp;あいまい検索</label>
                </td>
                <th class="c-box--200">郵便番号</th>
                <td class="c-box--380">
                    <input type="text" class="form-control c-box--250" name="postal" placeholder="" value="{{ old('postal', $searchRow['postal'] ?? '') }}">
                </td>
            </tr>
            <tr>
                <th class="c-box--200">フリガナ</th>
                <td class="c-box--380">
                    <input type="text" class="form-control c-box--250" name="name_kana" placeholder="" value="{{ old('name_kana', $searchRow['name_kana'] ?? '') }}">
                    <label><input type="checkbox" name="name_kana_fuzzy" value="1" {{ isset($searchRow['name_kana_fuzzy']) && ($searchRow['name_kana_fuzzy']=='1')? 'checked="checked"' : ''  }}>&nbsp;あいまい検索</label>
                </td>
                <th class="c-box--200">都道府県</th>
                <td class="c-box--380">
                <select name="address1" class="form-control c-box--250">
                    <option value=""></option>
                    @foreach($viewExtendData['pref'] as $row)
                    @php($prefValue = $row['prefectual_name'])
                    @php($prefName = $row['prefectual_name'])	<option value="{{$prefValue}}" {{isset($searchRow['address1'])&&($searchRow['address1']==$prefValue)?'selected':''}} >{{$prefName}}</option>
                    @endforeach
                </select>
                </td>
            </tr>
            <tr>
                <th class="c-box--200">メールアドレス</th>
                <td class="c-box--380">
                    <input type="text" class="form-control c-box--250" name="email" placeholder="" value="{{ old('email', $searchRow['email'] ?? '') }}">
                </td>
                <th class="c-box--200">住所</th>
                <td class="c-box--380">
                    <input type="text" class="form-control c-box--250" name="address2" placeholder="" value="{{ old('address2', $searchRow['address2'] ?? '') }}">
                    <label><input type="checkbox" name="address2_forward" value="1" {{ isset($searchRow['address2_forward']) && ($searchRow['address2_forward']=='1')? 'checked="checked"' : ''  }}>&nbsp;あいまい検索</label>
                </td>
            </tr>
        </tbody>
    </table>
</div>

@if( isset($viewExtendData['area_expanded_delivery']) && $viewExtendData['area_expanded_delivery'] == 'true' )
<div class="c-btn--03"><a data-toggle="collapse" href="#collapse-menu" aria-expanded="true">詳細検索</a></div>
<div class="collapse in" id="collapse-menu" aria-expanded="true" style="">
@else
<div class="c-btn--03"><a class="collapsed" data-toggle="collapse" href="#collapse-menu" aria-expanded="false">詳細検索</a></div>
<div class="collapse" id="collapse-menu" aria-expanded="false" style="height: 0px;">
@endif
	<div class="c-box--850Half">
		<table class="table c-tbl c-tbl--1200">
            <tbody>
                <tr>
                    <th class="c-box--200">使用区分</th>
                    <td class="c-box--350">
                        @if(isset($searchRow['delete_flg']))
                        <label class="checkbox-inline"><input type="checkbox" name="delete_flg[]" value="0" {{ isset($searchRow['delete_flg']) && in_array('0', ($searchRow['delete_flg']), true)? 'checked="checked"' : ''  }}>使用中</label>
                        <label class="checkbox-inline"><input type="checkbox" name="delete_flg[]" value="1" {{ isset($searchRow['delete_flg']) && in_array('1', ($searchRow['delete_flg']), true)? 'checked="checked"' : ''  }}>使用停止</label>
                        @else
                        <label class="checkbox-inline"><input type="checkbox" name="delete_flg[]" value="0" checked>使用中</label>
                        <label class="checkbox-inline"><input type="checkbox" name="delete_flg[]" value="1" >使用停止</label>
                        @endif
                    </td>
                    <th class="c-box--200">要注意区分</th>
                    <td class="c-box--380">
                        <label class="checkbox-inline"><input type="checkbox" name="alert_cust_type[]" value="0" {{ isset($searchRow['alert_cust_type']) && in_array('0', ($searchRow['alert_cust_type']), true)? 'checked="checked"' : ''  }}>通常</label>
                        <label class="checkbox-inline"><input type="checkbox" name="alert_cust_type[]" value="1" {{ isset($searchRow['alert_cust_type']) && in_array('1', ($searchRow['alert_cust_type']), true)? 'checked="checked"' : ''  }}>要確認</label>
                        <label class="checkbox-inline"><input type="checkbox" name="alert_cust_type[]" value="2" {{ isset($searchRow['alert_cust_type']) && in_array('2', ($searchRow['alert_cust_type']), true)? 'checked="checked"' : ''  }}>受注不可</label>
                    </td>
                </tr>
                <tr>
                    <th class="c-box--200">顧客ランク</th>
                    <td class="c-box--350">
                         <select name="m_cust_runk_id" class="form-control c-box--250">
                        <option value=""></option>
                        @foreach($viewExtendData['custrunk'] as $key => $value)
                        <option value="{{$value}}" {{isset($searchRow['m_cust_runk_id'])&&($searchRow['m_cust_runk_id']==$value)?'selected':''}} >{{$key}}</option>
                        @endforeach
                        </select>
                    </td>
                    <th class="c-box--200">FAX番号</th>
                    <td class="c-box--380">
                        <input type="text" class="form-control c-box--250" name="fax" placeholder="" value="{{ old('fax', $searchRow['fax'] ?? '') }}">
                        <label><input type="checkbox" name="fax_forward" value="1" {{ isset($searchRow['fax_forward']) && ($searchRow['fax_forward']=='1')? 'checked="checked"' : ''  }}>&nbsp;前方一致</label>
                    	@include('common.elements.error_tag', ['name' => 'fax'])
                    </td>
                </tr>
                <tr class="c-tbl-border-bottom">
                    <th class="c-box--200">法人名・団体名</th>
                    <td class="c-box--350">
                        <input type="text" class="form-control c-box--250" name="corporate_kanji" placeholder="" value="{{ old('corporate_kanji', $searchRow['corporate_kanji'] ?? '') }}">
                        <label><input type="checkbox" name="corporate_kanji_fuzzy" value="1" {{ isset($searchRow['corporate_kanji_fuzzy']) && ($searchRow['corporate_kanji_fuzzy']=='1')? 'checked="checked"' : ''  }}>&nbsp;あいまい検索</label>
                    </td>
                    <th class="c-box--200">備考</th>
                    <td class="c-box--380">
                        <input type="text" class="form-control c-box--250" name="note" placeholder="" value="{{ old('note', $searchRow['note'] ?? '') }}">
                    </td>
                </tr>
                <tr class="c-tbl-border-bottom">
                    <th class="c-box--200">法人名・団体名（フリガナ）</th>
                    <td class="c-box--350">
                        <input type="text" class="form-control c-box--250" name="corporate_kana" placeholder="" value="{{ old('corporate_kana', $searchRow['corporate_kana'] ?? '') }}">
                        <label><input type="checkbox" name="corporate_kana_fuzzy" value="1" {{ isset($searchRow['corporate_kana_fuzzy']) && ($searchRow['corporate_kana_fuzzy']=='1')? 'checked="checked"' : ''  }}>&nbsp;あいまい検索</label>
                    </td>
                </tr>
            </tbody>
        </table>
	</div>
</div>
<br>
<button class="btn btn-success btn-lg u-mt--sm" type="submit" name="submit" id="submit_search" value="search">検索</button>
<br>
<button class="btn btn-default btn-lg u-mt--sm" type="submit" name="submit" id="submit_custnew" value="custnew">顧客新規登録</button>
</div>

<br>
@if ($paginator)
<div>
@include('common.elements.paginator_header')
@include('common.elements.page_list_count')
@include('common.elements.sorting_script')
<br>
<table class="table table-bordered c-tbl table-link nowrap">
    <tr>
		<th>@include('common.elements.sorting_column_name', ['columnName' => 'm_cust_id', 'columnViewName' => '顧客ID']) </th>
        <th>@include('common.elements.sorting_column_name', ['columnName' => 'm_itemname_type_name', 'columnViewName' => '顧客ランク'])</th>
        <th>@include('common.elements.sorting_column_name', ['columnName' => 'newest_order_date', 'columnViewName' => '最終購入日'])</th>
		<th>顧客コード </th>
        <th>Web会員番号</th>
		<th>@include('common.elements.sorting_column_name', ['columnName' => 'name_kanji', 'columnViewName' => '名前']) </th>
		<th>@include('common.elements.sorting_column_name', ['columnName' => 'name_kana', 'columnViewName' => 'フリガナ']) </th>
		<th>@include('common.elements.sorting_column_name', ['columnName' => 'email1', 'columnViewName' => 'メールアドレス']) </th>
		<th>電話番号</th>
		<th>FAX番号</th>
		<th>郵便番号</th>
		<th>@include('common.elements.sorting_column_name', ['columnName' => 'address1', 'columnViewName' => '都道府県']) </th>
		<th>@include('common.elements.sorting_column_name', ['columnName' => 'address2', 'columnViewName' => '住所'])</th>
		<th>備考</th>
	</tr>
    @if ($paginator->count() > 0)
		@foreach($paginator as $customer)
		<tr>
			<td>
                <a href="{{ route('cc.cc-customer.info', ['id' => $customer->m_cust_id ]) }}"  target="_blank">{{ $customer->m_cust_id }}<i class="fas fa-external-link-alt"></i></a>
            </td>
            <td>{{ $customer->m_itemname_type_name }}</td>
			<td>{{ $customer->newest_order_date }}</td>
			<td>{{ $customer->cust_cd }}</td>
			<td>{{ $customer->reserve10 }}</td>
			<td>{{ $customer->name_kanji }}</td>
			<td>{{ $customer->name_kana }}</td>
			<td>{{ $customer->email1 }}</td>
			<td>{{ $customer->tel1 }}</td>
			<td>{{ $customer->fax }}</td>
			<td>{{ $customer->postal }}</td>
			<td>{{ $customer->address1 }}</td>
            <td title="{{ $customer->address2 }}">{{ $customer->displayAddress2 }}</td>
            <td title="{{ $customer->note }}">{{ $customer->displayNote }}</td>
		</tr>
		@endforeach
    @else
		@if($searchResult['search_record_count'] == 0)
			<tr>
				<td colspan="11">該当顧客が見つかりません。</td>
			</tr>
		@endif
	@endif
</table>
@include('common.elements.paginator_footer')
</div>
<button class="btn btn-default btn-lg u-mt--sm" type="submit" name="submit" id="submit_custnew" value="custnew">顧客新規登録</button>
</form>
@endif

@push('css')
<link rel="stylesheet" href="{{ esm_internal_asset('css/custCommunication/gfh_1207/app.css') }}">
@endpush

@endsection
