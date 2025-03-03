{{-- GFMSMB0030:キャンペーン確認登録・修正 --}}
@php
$ScreenCd='GFMSMB0030';
@endphp
{{-- layout設定 --}}
@extends('common.layouts.default')
{{-- タイトル設定 --}}
@section('title', 'キャンペーン確認登録・修正')
{{-- ぱんくず設定 --}}
@section('breadcrumb')
<li>キャンペーン確認登録・修正</li>
@endsection
@section('content')
<form method="POST" action="{{ route('campaign.update') }}" name="Form1" id="Form1">
{{ csrf_field() }}
<div>
    <table class="table table-bordered c-tbl c-tbl--800">
        <tr>
            <th class="">キャンペーンID</th>
            <td >
                <span>
                    {{ $editRow['m_campaign_id'] }}
                </span>
            </td>
        </tr>
        <tr>
            <th class="must">キャンペーン名</th>
            <td class="m-box--350">
                <span>{{ $editRow['campaign_name'] }}</span>
                @include('common.elements.error_tag', ['name' => 'campaign_name'])
            </td>
        </tr>

        <tr>
            <th class="must">使用区分</th>
            <td>
                @if(isset($editRow['delete_flg']) && $editRow['delete_flg']=='0') {{'使用中'}} @else {{'使用停止'}} @endif
                @include('common.elements.error_tag', ['name' => 'delete_flg'])
            </td>
        </tr>

        <tr>
            <th class="must">キャンペーン期間</th>
            <td>
                @if(!empty($editRow['from_date']) || !empty($editRow['to_date']))
                <span>{{ ( new Carbon\Carbon($editRow['from_date']) )->format('Y/m/d') }}</span>
                @if(!empty($editRow['from_date']) && !empty($editRow['to_date']))
                    ～
                @endif
                <span>{{ ( new Carbon\Carbon($editRow['to_date']) )->format('Y/m/d') }}</span>
                @endif

                @include('common.elements.error_tag', ['name' => 'from_date'])
                @include('common.elements.error_tag', ['name' => 'to_date'])
            </td>
        </tr>

        <tr>
            <th class="must">キャンペーン金額</th>
            <td>
                <span>{{ $editRow['giving_condition_amount'] }}</span>
                @include('common.elements.error_tag', ['name' => 'giving_condition_amount'])
            </td>
        </tr>


        <tr>
            <th class="must">金額ごとに追加</th>
            <td>
                @if(isset($editRow['giving_condition_every']) && $editRow['giving_condition_every']=='1') {{'する'}} @else {{'しない'}} @endif
                @include('common.elements.error_tag', ['name' => 'giving_condition_every'])
            </td>
        </tr>

        <tr>
            <th class="must">商品コード</th>
            <td class="m-box--350">
                <span>{{ $editRow['giving_page_cd'] }}</span>
                @include('common.elements.error_tag', ['name' => 'giving_page_cd'])
            </td>
        </tr>
    </table>
</div>
<div class="u-mt--ss">
    <input type="hidden" name="cancel" value="0"> <!-- デフォルトの値 -->
    <button class="btn btn-default btn-lg u-mt--sm" type="submit" name="submit" id="submit_cancel" value="cancel">キャンセル</button>
    &nbsp;&nbsp;
    <!-- 登録ボタンを押下-->
    <input type="hidden" name="add" value="1"/>  
    <button class="btn btn-success btn-lg u-mt--sm" type="submit" name="submit" id="submit_register" value="register">登録</button>

    @include('common.elements.on_enter_script', ['target_button_name' => 'submit_notify'])
</div>
</form>
@push('css')
<link rel="stylesheet" href="{{ esm_internal_asset('css/master/gfh_1207/GFMSMB0030.css') }}">
@endpush

@endsection