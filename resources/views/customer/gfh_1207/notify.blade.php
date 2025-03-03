{{-- NECSM0113:顧客登録・修正確認 --}}
@php
$ScreenCd='NECSM0113';
@endphp

{{-- layout設定 --}}
@extends('common.layouts.default')

{{-- タイトル設定 --}}
@section('title', '顧客登録・修正確認')

{{-- ぱんくず設定 --}}
@section('breadcrumb')
<li>顧客登録・修正確認</li>
@endsection

@section('content')

<form method="POST" action="">
{{ csrf_field() }}
<div>

@if(isset($editRow['submit']) && $editRow['submit'] == 'delete')<label class="btn cl-ff0000">削除</label> @endif

<table class="table table-bordered c-tbl c-tbl--800">
<tr>
  <th class="c-box--150">使用区分</th>
  <td>@if(isset($editRow['delete_flg']) && $editRow['delete_flg'] == \App\Enums\DeleteFlg::Use->value) {{\App\Enums\DeleteFlg::Use->label()}} @endif
      @if(isset($editRow['delete_flg']) && $editRow['delete_flg'] == \App\Enums\DeleteFlg::Notuse->value) {{\App\Enums\DeleteFlg::Notuse->label()}} @endif
  </td>
</tr>
<tr>
  <th><label for="">顧客コード</label></th>
  <td>{{$editRow['cust_cd'] ?? ''}}</td>
</tr>
<tr>
  <th><label for="">電話番号</label></th>
  <td>{{$editRow['tel1'] ?? ''}}&nbsp;
      {{$editRow['tel2'] ?? ''}}&nbsp;
      {{$editRow['tel3'] ?? ''}}&nbsp;
      {{$editRow['tel4'] ?? ''}}&nbsp;
  </td>
</tr>
<tr>
<th><label for="">FAX番号</label></th>
<td>{{$editRow['fax'] ?? ''}}</td>
</tr>
<tr>
<th><label for="">フリガナ</label></th>
<td>{{$editRow['name_kana'] ?? ''}}</td>
</tr>
<tr>
<th><label for="">名前</label></th>
<td>{{$editRow['name_kanji'] ?? ''}}</td>
</tr>
<tr>
<th><label for="">郵便番号</label></th>
<td>{{$editRow['postal'] ?? ''}}</td>
</tr>
<tr>
<th><label for="">都道府県</label></th>
<td>{{$editRow['address1'] ?? ''}}</td>
</tr>
<tr>
<th><label for="">市区町村</label></th>
<td>{{$editRow['address2'] ?? ''}}</td>
</tr>
<tr>
<th><label for="">番地</label></th>
<td>{{$editRow['address3'] ?? ''}}</td>
</tr>
<tr>
<th><label for="">建物名</label></th>
<td>{{$editRow['address4'] ?? ''}}</td>
</tr>
<tr>
<th><label for="">メールアドレス</label></th>
<td>{{$editRow['email1'] ?? ''}}&nbsp;
　　{{$editRow['email2'] ?? ''}}&nbsp;
　　{{$editRow['email3'] ?? ''}}&nbsp;
　　{{$editRow['email4'] ?? ''}}&nbsp;
　　{{$editRow['email5'] ?? ''}}&nbsp;
</td>
</tr>
<tr>
<th><label for="">備考</label></th>
<td>{!! nl2br(e($editRow['note'])) !!}</td>
</tr>
<tr>
<th><label for="">性別</label></th>
  <td>
    @if(isset($editRow['sex_type']) && $editRow['sex_type'] == \App\Enums\SexTypeEnum::UNKNOWN->value) {{\App\Enums\SexTypeEnum::UNKNOWN->label()}} @endif
      @if(isset($editRow['sex_type']) && $editRow['sex_type'] == \App\Enums\SexTypeEnum::MALE->value) {{\App\Enums\SexTypeEnum::MALE->label()}} @endif
      @if(isset($editRow['sex_type']) && $editRow['sex_type'] == \App\Enums\SexTypeEnum::FEMALE->value) {{\App\Enums\SexTypeEnum::FEMALE->label()}} @endif
  </td>
</tr>
<tr>
<th><label for="">誕生日</label></th>
<td>{{$editRow['birthday'] ?? ''}}</td>
</tr>
<tr>
<th><label for="">フリガナ</label></th>
<td>{{$editRow['corporate_kana'] ?? ''}}</td>
</tr>
<tr>
<th><label for="">法人名・団体名</label></th>
<td>{{$editRow['corporate_kanji'] ?? ''}}</td>
</tr>
<tr>
<th><label for="">部署名</label></th>
<td>{{$editRow['division_name'] ?? ''}}</td>
</tr>
<tr>
<th><label for="">勤務先電話番号</label></th>
<td>{{$editRow['corporate_tel'] ?? ''}}</td>
</tr>
<tr>
<th><label for="">顧客ランク</label></th>
<td>
@foreach($viewExtendData['cust_runk_list'] as $runkId => $runkName)
  @if(isset($editRow['m_cust_runk_id']) && $editRow['m_cust_runk_id'] == $runkName) {{$runkId}} @endif
@endforeach
</td>
</tr>
<tr>
    <th><label for="">顧客区分</label></th>
        <td>
            @foreach($viewExtendData['item_name_type'] as $itemName => $itemId)
                @if(isset($editRow['customer_type']) && $editRow['customer_type'] == $itemId) {{ $itemName }}  @break @endif
            @endforeach
        </td>
    </tr>
<tr>
<tr>
    <th><label for="">割引率</label></th>
    <td>{{$editRow['discount_rate'] ?? ''}}</td>
</tr>
<tr>
    <th><label for="">DM配送方法 郵便</label></th>
      <td>@if(isset($editRow['dm_send_letter_flg']) && $editRow['dm_send_letter_flg'] == \App\Modules\Customer\Gfh1207\Enums\DmSendLetterWishFlgEnum::NO_WISH->value) {{\App\Modules\Customer\Gfh1207\Enums\DmSendLetterWishFlgEnum::NO_WISH->label()}} @endif
        @if(isset($editRow['dm_send_letter_flg']) && $editRow['dm_send_letter_flg'] == \App\Modules\Customer\Gfh1207\Enums\DmSendLetterWishFlgEnum::WISH->value)  {{\App\Modules\Customer\Gfh1207\Enums\DmSendLetterWishFlgEnum::WISH->label()}} @endif
    </td>
</tr>
<tr>
    <th><label for="">DM配送方法 郵便</label></th>
    <td>@if(isset($editRow['dm_send_mail_flg']) && $editRow['dm_send_mail_flg'] == \App\Modules\Customer\Gfh1207\Enums\DmSendMailWishFlgEnum::NO_WISH->value) {{\App\Modules\Customer\Gfh1207\Enums\DmSendMailWishFlgEnum::NO_WISH->label()}} @endif
        @if(isset($editRow['dm_send_mail_flg']) && $editRow['dm_send_mail_flg'] == \App\Modules\Customer\Gfh1207\Enums\DmSendMailWishFlgEnum::WISH->value)  {{\App\Modules\Customer\Gfh1207\Enums\DmSendMailWishFlgEnum::WISH->label()}} @endif
    </td>
</tr>
<tr>
<th><label for="">要注意区分</label></th>
  <td>@if(isset($editRow['alert_cust_type']) && $editRow['alert_cust_type'] == \App\Enums\AlertCustTypeEnum::NO_ALERT->value) {{\App\Enums\AlertCustTypeEnum::NO_ALERT->label()}} @endif
      @if(isset($editRow['alert_cust_type']) && $editRow['alert_cust_type'] == \App\Enums\AlertCustTypeEnum::ATTENTION->value) {{\App\Enums\AlertCustTypeEnum::ATTENTION->label()}} @endif
      @if(isset($editRow['alert_cust_type']) && $editRow['alert_cust_type'] == \App\Enums\AlertCustTypeEnum::BANNED->value) {{\App\Enums\AlertCustTypeEnum::BANNED->label()}} @endif
  </td>
</tr>
<tr>
<th><label for="">要注意コメント</label></th>
<td>{!! nl2br(e($editRow['alert_cust_comment'])) !!}</td>
</tr>
<tr>
<th><label for="">ブラック理由</label></th>
<td>{{$editRow['reserve1'] ?? ''}}</td>
</tr>
<tr>
<th><label for="">注意顧客理由</label></th>
<td>{{$editRow['reserve2'] ?? ''}}</td>
</tr>
<tr>
<th><label for="">貸倒</label></th>
<td>{{$editRow['reserve3'] ?? ''}}</td>
</tr>
<tr>
<th><label for="">お客様情報</label></th>
<td>{{$editRow['reserve4'] ?? ''}}</td>
</tr>
<tr>
<th><label for="">問合せ連絡</label></th>
<td>{{$editRow['reserve5'] ?? ''}}</td>
</tr>
<tr>
<th><label for="">備考</label></th>
<td>{{$editRow['reserve6'] ?? ''}}</td>
</tr>
<tr>
<th><label for="">請求関連</label></th>
<td>{{$editRow['reserve7'] ?? ''}}</td>
</tr>
<tr>
<th><label for="">督促</label></th>
<td>{{$editRow['reserve8'] ?? ''}}</td>
</tr>
<tr>
<th><label for="">住所変更</label></th>
<td>{{$editRow['reserve9'] ?? ''}}</td>
</tr>
<tr>
<th><label for="">Web会員番号</label></th>
<td>{{$editRow['reserve10'] ?? ''}}</td>
</tr>
<tr>
<th><label for="">自由項目１１</label></th>
<td>{{$editRow['reserve11'] ?? ''}}</td>
</tr>
<tr>
<th><label for="">自由項目１２</label></th>
<td>{{$editRow['reserve12'] ?? ''}}</td>
</tr>
<tr>
<th><label for="">自由項目１３</label></th>
<td>{{$editRow['reserve13'] ?? ''}}</td>
</tr>
<tr>
<th><label for="">自由項目１４</label></th>
<td>{{$editRow['reserve14'] ?? ''}}</td>
</tr>
<tr>
<th><label for="">自由項目１５</label></th>
<td>{{$editRow['reserve15'] ?? ''}}</td>
</tr>
<tr>
<th><label for="">自由項目１６</label></th>
<td>{{$editRow['reserve16'] ?? ''}}</td>
</tr>
<tr>
<th><label for="">自由項目１７</label></th>
<td>{{$editRow['reserve17'] ?? ''}}</td>
</tr>
<tr>
<th><label for="">自由項目１８</label></th>
<td>{{$editRow['reserve18'] ?? ''}}</td>
</tr>
<tr>
<th><label for="">自由項目１９</label></th>
<td>{{$editRow['reserve19'] ?? ''}}</td>
</tr>
<tr>
<th><label for="">自由項目２０</label></th>
<td>{{$editRow['reserve20'] ?? ''}}</td>
</tr>
</table>

<div class="u-mt--ss">
<button class="btn btn-default btn-lg u-mt--sm" type="submit" name="submit" id="submit_cancel" value="cancel">キャンセル</button>
<button class="btn btn-success btn-lg u-mt--sm" type="submit" name="submit" id="submit_register" value="register">登録</button>
</div>
<input type="hidden" name="data_key_id" value="{{$editRow['data_key_id'] ?? ''}}">

<div class="u-mt--sl c-box--800">
@if(isset($paginator))
{{$paginator->appends('search')->render()}}
@endif
</div>
</form>
@endsection
