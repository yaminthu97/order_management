{{-- NECSM0112:顧客登録・修正 --}}
@php
$ScreenCd='NECSM0112';
@endphp

{{-- layout設定 --}}
@extends('common.layouts.default')

{{-- タイトル設定 --}}
@section('title', '顧客登録・修正')

{{-- ぱんくず設定 --}}
@section('breadcrumb')
<li>顧客登録・修正</li>
@endsection

@section('content')

<form  method="POST" action="" name="Form1" id="Form1">
{{ csrf_field() }}
<table class="table table-bordered c-tbl c-tbl--full">
<tr>
<th style="width: 13%;"><span class="c-box--300">使用区分</span><br>
@if(!empty($editRow['delete_operator_id']))
	<span class="font-FF0000">削除済み顧客</span>
@endif
</th>
<td>
<div class="radio-inline">
<label><input type="radio" name="delete_flg[]" value="{{\App\Enums\DeleteFlg::Use->value}}" {{ old('delete_flg', $editRow['delete_flg'] ?? null) === null ? 'checked' : (old('delete_flg', $editRow['delete_flg'] ?? null) == \App\Enums\DeleteFlg::Use->value ? 'checked' : '') }}>
    {{ \App\Enums\DeleteFlg::Use->label() }}
</label>
</div>
<div class="radio-inline">
<label><input type="radio" name="delete_flg[]" value="{{\App\Enums\DeleteFlg::Notuse->value}}" {{ old('delete_flg', $editRow['delete_flg'] ?? null) === null ? '' : (old('delete_flg', $editRow['delete_flg'] ?? null) == \App\Enums\DeleteFlg::Notuse->value ? 'checked' : '') }}>
    {{ \App\Enums\DeleteFlg::Notuse->label() }}
</label>
</div>
	@include('common.elements.error_tag', ['name' => 'delete_flg'])
</td>
</tr>

<tr>
<th>顧客コード</th>
<td>
<input type="text" class="form-control c-box--300" name="cust_cd" id="cust_cd" placeholder="" value="{{ old('cust_cd', $editRow['cust_cd'] ?? '') }}">
@include('common.elements.error_tag', ['name' => 'cust_cd'])
</td>
</tr>

<tr>
<th class="must">電話番号</th>
<td>
<ul>
<li class="u-input--mid"><input type="text" class="form-control c-box--200" name="tel1" id="tel1" placeholder="" value="{{ old('tel1', $editRow['tel1'] ?? '') }}"></li>
<li class="u-input--mid"><input type="text" class="form-control c-box--200" name="tel2" id="tel2" placeholder="" value="{{ old('tel2', $editRow['tel2'] ?? '') }}"></li>
<li class="u-input--mid"><input type="text" class="form-control c-box--200" name="tel3" id="tel3" placeholder="" value="{{ old('tel3', $editRow['tel3'] ?? '') }}"></li>
<li class="u-input--mid"><input type="text" class="form-control c-box--200" name="tel4" id="tel4" placeholder="" value="{{ old('tel4', $editRow['tel4'] ?? '') }}"></li>
</ul>
@include('common.elements.error_tag', ['name' => 'tel1'])
@include('common.elements.error_tag', ['name' => 'tel2'])
@include('common.elements.error_tag', ['name' => 'tel3'])
@include('common.elements.error_tag', ['name' => 'tel4'])
</td>
</tr>

<tr>
<th>FAX番号</th>
<td>
<input type="text" class="form-control c-box--200" name="fax" id="fax" placeholder="" value="{{ old('fax', $editRow['fax'] ?? '') }}">
@include('common.elements.error_tag', ['name' => 'fax'])
</td>
</tr>

<tr>
<th>フリガナ</th>
<td>
<input type="text" class="form-control c-box--300" name="name_kana" id="name_kana" placeholder="" value="{{ old('name_kana', $editRow['name_kana'] ?? '') }}">
@include('common.elements.error_tag', ['name' => 'name_kana'])
</td>
</tr>

<tr>
<th class="must">名前</th>
<td>
<input type="text" class="form-control c-box--300 check_textbyte" data-item_name="名前" data-max_byte="32" name="name_kanji" id="name_kanji" placeholder="" value="{{ old('name_kanji', $editRow['name_kanji'] ?? '') }}">
@include('common.elements.error_tag', ['name' => 'name_kanji'])
</td>
</tr>


<tr>
<th class="must">住所</th>
<td>
<div class="d-table c-tbl--800">
<div class="d-table-cell c-box--100">郵便番号</div>
<div class="d-table-cell">
<input type="text" class="form-control c-box--100" name="postal" id="postal" maxlength="8" placeholder="" value="{{ old('postal', $editRow['postal'] ?? '') }}"></div>
</div>
@include('common.elements.error_tag', ['name' => 'postal'])
<div class="d-table c-tbl--800 u-mt--xs">
<div class="d-table-cell c-box--100">都道府県</div>
<div class="d-table-cell">
<select class="form-control c-box--200 check_textbyte" data-item_name="都道府県" data-max_byte="8" name="address1" id="address1">
<option value=""></option>
@foreach($viewExtendData['pref'] as $prefecture)
<option value="{{ $prefecture['prefectual_name'] }}"
    @if(old('address1', isset($editRow['address1']) ? $editRow['address1'] : '') == $prefecture['prefectual_name'])selected @endif> {{ $prefecture['prefectual_name'] }} </option>
@endforeach
</select>
</div>
</div>
@include('common.elements.error_tag', ['name' => 'address1'])
<div class="d-table c-tbl--800 u-mt--xs">
<div class="d-table-cell c-box--100">市区町村</div>
<div class="d-table-cell d-flex">
    <input type="text" name="address2" id="address2" class="form-control c-box--300 check_textbyte" data-item_name="市区町村" data-max_byte="24" placeholder="" value="{{ old('address2', $editRow['address2'] ?? '') }}"> &nbsp;
    <input type="text" name="address5" id="address5" class="form-control c-box--300 check_textbyte" data-item_name="市区町村" data-max_byte="24" placeholder="" value="{{ old('address5', $editRow['address5'] ?? '') }}" readonly style="background: white; ">
</div>
</div>
@include('common.elements.error_tag', ['name' => 'address2'])
<div class="d-table c-tbl--800 u-mt--xs">
<div class="d-table-cell c-box--100">番地</div>
<div class="d-table-cell"><input type="text" class="form-control c-box--300 check_textbyte" data-item_name="番地" data-max_byte="32" name="address3" id="address3" placeholder="" value="{{ old('address3', $editRow['address3'] ?? '') }}"></div>
</div>
@include('common.elements.error_tag', ['name' => 'address3'])
<div class="d-table c-tbl--800 u-mt--xs">
<div class="d-table-cell c-box--100">建物名</div>
<div class="d-table-cell"><input type="text" class="form-control c-box--300 check_textbyte" data-item_name="建物名" data-max_byte="32"  name="address4" id="address4" placeholder="" value="{{ old('address4', $editRow['address4'] ?? '') }}"></div>
</div>
@include('common.elements.error_tag', ['name' => 'address4'])
</td>
</tr>

<tr>
<th>メールアドレス</th>
<td>
<ul>
<li class="u-input--mid"><input type="text" class="form-control c-box--200" name="email1" id="email1" placeholder="" value="{{ old('email1', $editRow['email1'] ?? '') }}"></li>
<li class="u-input--mid"><input type="text" class="form-control c-box--200" name="email2" id="email2" placeholder="" value="{{ old('email2', $editRow['email2'] ?? '') }}"></li>
<li class="u-input--mid"><input type="text" class="form-control c-box--200" name="email3" id="email3" placeholder="" value="{{ old('email3', $editRow['email3'] ?? '') }}"></li>
<li class="u-input--mid"><input type="text" class="form-control c-box--200" name="email4" id="email4" placeholder="" value="{{ old('email4', $editRow['email4'] ?? '') }}"></li>
<li class="u-input--mid"><input type="text" class="form-control c-box--200" name="email5" id="email5" placeholder="" value="{{ old('email5', $editRow['email5'] ?? '') }}"></li>
</ul>
@include('common.elements.error_tag', ['name' => 'email1'])
@include('common.elements.error_tag', ['name' => 'email2'])
@include('common.elements.error_tag', ['name' => 'email3'])
@include('common.elements.error_tag', ['name' => 'email4'])
@include('common.elements.error_tag', ['name' => 'email5'])
</td>
</tr>

<tr>
<th>備考</th>
<td>
<textarea class="form-control c-box--500" name="note" id="note" rows="5">{{ old('note', $editRow['note'] ?? '') }}</textarea>
@include('common.elements.error_tag', ['name' => 'note'])
</td>
</tr>
<tr>
<th>性別</th>
<td>
    <div class="radio-inline">
    <label><input type="radio" name="sex_type[]" id="sex_type[]" value="{{\App\Enums\SexTypeEnum::UNKNOWN->value}}" {{ old('sex_type', $editRow['sex_type'] ?? null) === null ? 'checked' : (old('sex_type', $editRow['sex_type'] ?? null) == \App\Enums\SexTypeEnum::UNKNOWN->value ? 'checked' : '') }}>
        {{\App\Enums\SexTypeEnum::UNKNOWN->label()}}
    </label>
    </div>
    <div class="radio-inline">
    <label><input type="radio" name="sex_type[]" id="sex_type[]" value="{{\App\Enums\SexTypeEnum::MALE->value}}" {{ old('sex_type', $editRow['sex_type'] ?? null) === null ? '' : (old('sex_type', $editRow['sex_type'] ?? null) == \App\Enums\SexTypeEnum::MALE->value ? 'checked' : '') }}>
        {{\App\Enums\SexTypeEnum::MALE->label()}}
    </label>
    </div>
    <div class="radio-inline">
    <label><input type="radio" name="sex_type[]" id="sex_type[]" value="{{\App\Enums\SexTypeEnum::FEMALE->value}}" {{ old('sex_type', $editRow['sex_type'] ?? null) === null ? '' : (old('sex_type', $editRow['sex_type'] ?? null) == \App\Enums\SexTypeEnum::FEMALE->value ? 'checked' : '') }}>
        {{\App\Enums\SexTypeEnum::FEMALE->label()}}
    </label>
    </div>
    @include('common.elements.error_tag', ['name' => 'sex_type'])
    </td>
</tr>

<tr>
<th>誕生日</th>
<td>
<!--<div class='c-box--218'>
<div class='input-group date' id='datetimepicker1'>
<input type='text' name="birthday" id="birthday" class="form-control c-box--180" value="{{ old('birthday', $editRow['birthday'] ?? '') }}"/>
<span class="input-group-addon">
<span class="glyphicon glyphicon-calendar"></span>
</span>
<script type="text/javascript">
$(function () {
	$('#datetimepicker1').datetimepicker({
		format: 'YYYY/MM/DD'
	});
});
	</script>
	</div>
	</div>
-->
<div class="d-table-cell"><input type="text" class="form-control c-box--100" name="birthday" id="birthday" maxlength="10" placeholder="" value="{{ old('birthday', $editRow['birthday'] ?? '') }}"></div>
@include('common.elements.error_tag', ['name' => 'birthday'])
	</td>
	</tr>

	<tr>
	<th>法人情報</th>
	<td>
	<div class="d-table c-tbl--600">
	<div class="d-table-cell c-box--150">フリガナ</div>
	<div class="d-table-cell"><input type="text" class="form-control c-box--full" name="corporate_kana" id="corporate_kana" placeholder="" value="{{ old('corporate_kana', $editRow['corporate_kana'] ?? '') }}"></div>
	</div>
	@include('common.elements.error_tag', ['name' => 'corporate_kana'])
	<div class="d-table c-tbl--600 u-mt--xs">
	<div class="d-table-cell c-box--150">法人名・団体名</div>
	<div class="d-table-cell"><input type="text" class="form-control c-box--full" name="corporate_kanji" id="corporate_kanji" placeholder="" value="{{ old('corporate_kanji', $editRow['corporate_kanji'] ?? '') }}"></div>
	</div>
	@include('common.elements.error_tag', ['name' => 'corporate_kanji'])
	<div class="d-table c-tbl--600 u-mt--xs">
	<div class="d-table-cell c-box--150">部署名</div>
	<div class="d-table-cell"><input type="text" class="form-control c-box--full" name="division_name" id="division_name" placeholder="" value="{{ old('division_name', $editRow['division_name'] ?? '') }}"></div>
	</div>
	@include('common.elements.error_tag', ['name' => 'division_name'])
	<div class="d-table c-tbl--600 u-mt--xs">
	<div class="d-table-cell c-box--150">勤務先電話番号</div>
	<div class="d-table-cell"><input type="text" class="form-control c-box--full" name="corporate_tel" id="corporate_tel" placeholder="" value="{{ old('corporate_tel', $editRow['corporate_tel'] ?? '') }}"></div>
	</div>
	@include('common.elements.error_tag', ['name' => 'corporate_tel'])
	</td>
	</tr>


	<tr>
	<th>顧客ランク</th>
	<td>
    <select class="form-control c-box--200" data-required-error="" name="m_cust_runk_id" id="m_cust_runk_id">
        <option value="0"></option>
        @foreach($viewExtendData['cust_runk_list'] as $runkName => $runkId)
            <option value="{{$runkId}}" @if(isset($editRow['m_cust_runk_id']) && $editRow['m_cust_runk_id'] == $runkId) {{'selected'}} @endif >
                {{$runkName}}
            </option>
        @endforeach
    </select>
	@include('common.elements.error_tag', ['name' => 'm_cust_runk_id'])
	</td>
	</tr>
    <tr>
        <th>顧客区分</th>
        <td>
            @foreach($viewExtendData['item_name_type'] as $itemName => $itemId)
                <div class="radio-inline">
                    <label>
                        <input type="radio" name="customer_type" value="{{ $itemId }}"
                        {{ old('customer_type', $editRow['customer_type'] ?? null) === null ? ($loop->first ? 'checked' : '') : (old('customer_type', $editRow['customer_type'] ?? null) == $itemId ? 'checked' : '') }}>
                       {{ $itemName }}
                    </label>
                </div>
            @endforeach
            @include('common.elements.error_tag', ['name' => 'customer_type'])
        </td>
    </tr>
    <tr>
        <th>割引率</th>
        <td>
            <div style="display: flex; align-items: center; ">
                <input type="number" class="form-control c-box--200" name="discount_rate" id="discount_rate" placeholder="" value="{{ old('discount_rate', $editRow['discount_rate'] ?? '') }}"> &nbsp;％
            </div>
            @include('common.elements.error_tag', ['name' => 'discount_rate'])
        </td>
    </tr>
    <tr>
        <th>DM配送方法 郵便</th>
        <td>
            <div class="radio-inline">
                <label><input type="radio" name="dm_send_letter_flg" value="{{\App\Modules\Customer\Gfh1207\Enums\DmSendLetterWishFlgEnum::NO_WISH->value}}" {{ old('dm_send_letter_flg', $editRow['dm_send_letter_flg'] ?? null) === null ? 'checked' : (old('dm_send_letter_flg', $editRow['dm_send_letter_flg'] ?? null) == \App\Modules\Customer\Gfh1207\Enums\DmSendLetterWishFlgEnum::NO_WISH->value ? 'checked' : '') }}>
                    {{\App\Modules\Customer\Gfh1207\Enums\DmSendLetterWishFlgEnum::NO_WISH->label()}}
                </label>
            </div>
            <div class="radio-inline">
                <label><input type="radio" name="dm_send_letter_flg" value="{{\App\Modules\Customer\Gfh1207\Enums\DmSendLetterWishFlgEnum::WISH->value}}" {{ old('dm_send_letter_flg', $editRow['dm_send_letter_flg'] ?? null) === null ? '' : (old('dm_send_letter_flg', $editRow['dm_send_letter_flg'] ?? null) == \App\Modules\Customer\Gfh1207\Enums\DmSendLetterWishFlgEnum::WISH->value ? 'checked' : '') }}>
                    {{\App\Modules\Customer\Gfh1207\Enums\DmSendLetterWishFlgEnum::WISH->label()}}
                </label>
            </div>
            @include('common.elements.error_tag', ['name' => 'dm_send_letter_flg'])
        </td>
    </tr>
    <tr>
        <th>DM配送方法メール</th>
        <td>
            <div class="radio-inline">
                <label><input type="radio" name="dm_send_mail_flg" value="{{\App\Modules\Customer\Gfh1207\Enums\DmSendMailWishFlgEnum::NO_WISH->value}}" {{ old('dm_send_mail_flg', $editRow['dm_send_mail_flg'] ?? null) === null ? 'checked' : (old('dm_send_mail_flg', $editRow['dm_send_mail_flg'] ?? null) == \App\Modules\Customer\Gfh1207\Enums\DmSendMailWishFlgEnum::NO_WISH->value ? 'checked' : '') }}>
                    {{\App\Modules\Customer\Gfh1207\Enums\DmSendMailWishFlgEnum::NO_WISH->label()}}
                </label>
            </div>
            <div class="radio-inline">
                <label><input type="radio" name="dm_send_mail_flg" value="{{\App\Modules\Customer\Gfh1207\Enums\DmSendMailWishFlgEnum::WISH->value}}" {{ old('dm_send_mail_flg', $editRow['dm_send_mail_flg'] ?? null) === null ? '' : (old('dm_send_mail_flg', $editRow['dm_send_mail_flg'] ?? null) == \App\Modules\Customer\Gfh1207\Enums\DmSendMailWishFlgEnum::WISH->value ? 'checked' : '') }}>
                    {{\App\Modules\Customer\Gfh1207\Enums\DmSendMailWishFlgEnum::WISH->label()}}
                </label>
            </div>
            @include('common.elements.error_tag', ['name' => 'dm_send_mail_flg'])
        </td>
    </tr>

	<th>要注意区分</th>
	<td>
	<div class="radio-inline">
	<label><input type="radio" id="alert_cust_type[]" name="alert_cust_type[]" value="{{\App\Enums\AlertCustTypeEnum::NO_ALERT->value}}" {{ old('alert_cust_type', $editRow['alert_cust_type'] ?? null) === null ? 'checked' : (old('alert_cust_type', $editRow['alert_cust_type'] ?? null) == \App\Enums\AlertCustTypeEnum::NO_ALERT->value ? 'checked' : '') }}>{{\App\Enums\AlertCustTypeEnum::NO_ALERT->label()}}</label>
	</div>
	<div class="radio-inline">
	<label><input type="radio" id="alert_cust_type[]" name="alert_cust_type[]" value="{{\App\Enums\AlertCustTypeEnum::ATTENTION->value}}" {{ old('alert_cust_type', $editRow['alert_cust_type'] ?? null) === null ? '' : (old('alert_cust_type', $editRow['alert_cust_type'] ?? null) == \App\Enums\AlertCustTypeEnum::ATTENTION->value ? 'checked' : '') }}>{{\App\Enums\AlertCustTypeEnum::ATTENTION->label()}}</label>
	</div>
	<div class="radio-inline">
	<label><input type="radio" id="alert_cust_type[]" name="alert_cust_type[]" value="{{\App\Enums\AlertCustTypeEnum::BANNED->value}}"  {{ old('alert_cust_type', $editRow['alert_cust_type'] ?? null) === null ? '' : (old('alert_cust_type', $editRow['alert_cust_type'] ?? null) == \App\Enums\AlertCustTypeEnum::BANNED->value ? 'checked' : '') }}>{{\App\Enums\AlertCustTypeEnum::BANNED->label()}}</label>
	</div>
	@include('common.elements.error_tag', ['name' => 'alert_cust_type'])
	</td>
	</tr>

	<tr>
	<th>要注意コメント</th>
	<td>
	<textarea class="form-control c-box--500" name="alert_cust_comment" id="alert_cust_comment" rows="5">{{ old('alert_cust_comment', $editRow['alert_cust_comment'] ?? '') }}</textarea>
	@include('common.elements.error_tag', ['name' => 'alert_cust_comment'])
	</td>
	</tr>
		<tr>
	<th>ブラック理由</th>
	<td><input type="text" class="form-control c-box--full" name="reserve1" id="reserve1" placeholder="" value="{{ old('reserve1', $editRow['reserve1'] ?? '') }}">
	@include('common.elements.error_tag', ['name' => 'reserve1'])
	</td>
	</tr>
	<tr>
	<th>注意顧客理由</th>
	<td><input type="text" class="form-control c-box--full" name="reserve2" id="reserve2" placeholder="" value="{{ old('reserve2', $editRow['reserve2'] ?? '') }}">
	@include('common.elements.error_tag', ['name' => 'reserve2'])
	</td>
	</tr>
	<tr>
	<th>貸倒</th>
	<td><input type="text" class="form-control c-box--full" name="reserve3" id="reserve3" placeholder="" value="{{ old('reserve3', $editRow['reserve3'] ?? '') }}">
	@include('common.elements.error_tag', ['name' => 'reserve3'])
	</td>
	</tr>
	<tr>
	<th>お客様情報</th>
	<td><input type="text" class="form-control c-box--full" name="reserve4" id="reserve4" placeholder="" value="{{ old('reserve4', $editRow['reserve4'] ?? '') }}">
	@include('common.elements.error_tag', ['name' => 'reserve4'])
	</td>
	</tr>
	<tr>
	<th>問合せ連絡</th>
	<td><input type="text" class="form-control c-box--full" name="reserve5" id="reserve5" placeholder="" value="{{ old('reserve5', $editRow['reserve5'] ?? '') }}">
	@include('common.elements.error_tag', ['name' => 'reserve5'])
	</td>
	</tr>
	<tr>
	<th>備考</th>
	<td><input type="text" class="form-control c-box--full" name="reserve6" id="reserve6" placeholder="" value="{{ old('reserve6', $editRow['reserve6'] ?? '') }}">
	@include('common.elements.error_tag', ['name' => 'reserve6'])
	</td>
	</tr>
	<tr>
	<th>請求関連</th>
	<td><input type="text" class="form-control c-box--full" name="reserve7" id="reserve7" placeholder="" value="{{ old('reserve7', $editRow['reserve7'] ?? '') }}">
	@include('common.elements.error_tag', ['name' => 'reserve7'])
	</td>
	</tr>
	<tr>
	<th>督促</th>
	<td><input type="text" class="form-control c-box--full" name="reserve8" id="reserve8" placeholder="" value="{{ old('reserve8', $editRow['reserve8'] ?? '') }}">
	@include('common.elements.error_tag', ['name' => 'reserve8'])
	</td>
	</tr>
	<tr>
	<th>住所変更</th>
	<td><input type="text" class="form-control c-box--full" name="reserve9" id="reserve9" placeholder="" value="{{ old('reserve9', $editRow['reserve9'] ?? '') }}">
	@include('common.elements.error_tag', ['name' => 'reserve9'])
	</td>
	</tr>
	<tr>
	<th>Web会員番号</th>
	<td><input type="text" class="form-control c-box--full" name="reserve10" id="reserve10" placeholder="" value="{{ old('reserve10', $editRow['reserve10'] ?? '') }}">
	@include('common.elements.error_tag', ['name' => 'reserve10'])
	</td>
	</tr>
	<tr>
	<th>自由項目１１</th>
	<td><input type="text" class="form-control c-box--full" name="reserve11" id="reserve11" placeholder="" value="{{ old('reserve11', $editRow['reserve11'] ?? '') }}">
	@include('common.elements.error_tag', ['name' => 'reserve11'])
	</td>
	</tr>
	<tr>
	<th>自由項目１２</th>
	<td><input type="text" class="form-control c-box--full" name="reserve12" id="reserve12" placeholder="" value="{{ old('reserve12', $editRow['reserve12'] ?? '') }}">
	@include('common.elements.error_tag', ['name' => 'reserve12'])
	</td>
	</tr>
	<tr>
	<th>自由項目１３</th>
	<td><input type="text" class="form-control c-box--full" name="reserve13" id="reserve13" placeholder="" value="{{ old('reserve13', $editRow['reserve13'] ?? '') }}">
	@include('common.elements.error_tag', ['name' => 'reserve13'])
	</td>
	</tr>
	<tr>
	<th>自由項目１４</th>
	<td><input type="text" class="form-control c-box--full" name="reserve14" id="reserve14" placeholder="" value="{{ old('reserve14', $editRow['reserve14'] ?? '') }}">
	@include('common.elements.error_tag', ['name' => 'reserve14'])
	</td>
	</tr>
	<tr>
	<th>自由項目１５</th>
	<td><input type="text" class="form-control c-box--full" name="reserve15" id="reserve15" placeholder="" value="{{ old('reserve15', $editRow['reserve15'] ?? '') }}">
	@include('common.elements.error_tag', ['name' => 'reserve15'])
	</td>
	</tr>
	<tr>
	<th>自由項目１６</th>
	<td><input type="text" class="form-control c-box--full" name="reserve16" id="reserve16" placeholder="" value="{{ old('reserve16', $editRow['reserve16'] ?? '') }}">
	@include('common.elements.error_tag', ['name' => 'reserve16'])
	</td>
	</tr>
	<tr>
	<th>自由項目１７</th>
	<td><input type="text" class="form-control c-box--full" name="reserve17" id="reserve17" placeholder="" value="{{ old('reserve17', $editRow['reserve17'] ?? '') }}">
	@include('common.elements.error_tag', ['name' => 'reserve17'])
	</td>
	</tr>
	<tr>
	<th>自由項目１８</th>
	<td><input type="text" class="form-control c-box--full" name="reserve18" id="reserve18" placeholder="" value="{{ old('reserve18', $editRow['reserve18'] ?? '') }}">
	@include('common.elements.error_tag', ['name' => 'reserve18'])
	</td>
	</tr>
	<tr>
	<th>自由項目１９</th>
	<td><input type="text" class="form-control c-box--full" name="reserve19" id="reserve19" placeholder="" value="{{ old('reserve19', $editRow['reserve19'] ?? '') }}">
	@include('common.elements.error_tag', ['name' => 'reserve19'])
	</td>
	</tr>
	<tr>
	<th>自由項目２０</th>
	<td><input type="text" class="form-control c-box--full" name="reserve20" id="reserve20" placeholder="" value="{{ old('reserve20', $editRow['reserve20'] ?? '') }}">
	@include('common.elements.error_tag', ['name' => 'reserve20'])
	</td>
	</tr>
	</table>

	<div class="u-mt--ss">
	@if(!empty($editRow['previous_subsys']))
		@php
			$previousUrl = config('env.app_subsys_url.') .'/gfh'. '/'. $editRow['previous_subsys'] . '/'. $editRow['previous_url'];
		@endphp
		<input class="btn btn-default btn-lg u-mt--sm" type="button" name="cancel" value="キャンセル" onClick="location.href='{{ $previousUrl }}';" />
	@else
		<input class="btn btn-default btn-lg u-mt--sm" type="button" name="cancel" value="キャンセル" onClick="location.href='{{ route('cc.customer.list') }}';" />
	@endif
	&nbsp;&nbsp;
	@if(isset($editRow['m_cust_id']) && empty($editRow['delete_operator_id']))
    <button class="btn btn-danger btn-lg u-mt--sm" type="submit" name="submit" id="submit_delete" value="delete">削除</button>&nbsp;&nbsp;
	@endif
	@if(empty($editRow['delete_operator_id']))
    <button class="btn btn-success btn-lg u-mt--sm" type="submit" name="submit" id="submit_register" value="register">確認</button>
	@endif
	</div>

	<input type="hidden" name="previous_url" value="{{ old('previous_url', $editRow['previous_url'] ?? '') }}">
	<input type="hidden" name="previous_subsys" value="{{ old('previous_subsys', $editRow['previous_subsys'] ?? '') }}">
	<input type="hidden" name="previous_key" value="{{ old('previous_key', $editRow['previous_key'] ?? '') }}">
	@include('common.elements.on_enter_script', ['target_button_name' => 'submit_register'])

    </form>
    @push('css')
    <link rel="stylesheet" href="{{ esm_internal_asset('css/customer/gfh_1207/style.css') }}">
    @endpush
    @push('js')
        <script src="{{ esm_internal_asset('js/customer/gfh_1207/jquery.autoKana.js') }}"></script>
        <script src="{{ esm_internal_asset('js/customer/gfh_1207/app.js') }}"></script>
        <script src="{{ esm_internal_asset('js/common/gfh_1207/check_textbyte.js') }}"></script>
    @endpush
@endsection
