{{-- GFMSMB0020:キャンペーン登録・修正 --}}
@php
$ScreenCd='GFMSMB0020';
@endphp
{{-- layout設定 --}}
@extends('common.layouts.default')
{{-- タイトル設定 --}}
@section('title', 'キャンペーン登録・修正')
{{-- ぱんくず設定 --}}
@section('breadcrumb')
<li>キャンペーン登録・修正</li>
@endsection
@section('content')
<form method="POST" action="{{ route('campaign.postNotify') }}" name="Form1" id="Form1">
{{ csrf_field() }}
<div>
    <table class="table table-bordered c-tbl c-tbl--800">
        <tr>
            <th class="">キャンペーンID
            </th>
            <td>
                <span>{{ !empty($editRow['m_campaign_id']) ? $editRow['m_campaign_id'] : '自動' }}</span>
                <input type="hidden" name="m_campaign_id" value = "{{ old('m_campaign_id', $editRow['m_campaign_id'] ?? '')}}">
            </td>
        </tr>
        <tr>
            <th class="c-box--300 must">キャンペーン名</th>
            <td>
                <input class="form-control" type="text" name="campaign_name" value="{{ old('campaign_name', $editRow['campaign_name'] ?? '')}}">
	            @include('common.elements.error_tag', ['name' => 'campaign_name'])
            </td>
        </tr>

        <tr>
            <th class="c-box--300 must">使用区分</th>
            <td>
                @foreach (\App\Enums\DeleteFlg::cases() as $target)
                <input class="form-check-input" type="radio" name="delete_flg" id="radio{{$target->value}}" value="{{$target->value}}" @if(old('delete_flg',$editRow['delete_flg'] ?? \App\Enums\DeleteFlg::Use->value)==$target->value) checked @endif>
                <label class="form-check-label" for="radio{{$target->value}}">{{$target->label()}}</label>
                @endforeach
	            @include('common.elements.error_tag', ['name' => 'delete_flg'])
            </td>
        </tr>


        <tr>
            <th class="c-box--300 must">キャンペーン期間</th>
            <td>
                <div class="u-mt--xs d-table">
                    <div class='c-box--218 d-table-cell'>
                        <div class='input-group date date-picker' id='from_date'>
                            <input type='text' name="from_date" id="from_date" class="form-control c-box--180" value="{{ old('from_date', $editRow['from_date'] ?? '') }}" />
                            <span class="input-group-addon"><span class="glyphicon glyphicon-calendar"></span></span>
                        </div>
                    </div>
                    <div class="d-table-cell">&nbsp;～&nbsp;</div>
                    <div class='c-box--218 d-table-cell'>
                        <div class='input-group date date-picker' id='to_date'>
                            <input type='text' name="to_date" id="to_date" class="form-control c-box--180" value="{{ old('to_date', $editRow['to_date'] ?? '') }}" />
                            <span class="input-group-addon"><span class="glyphicon glyphicon-calendar"></span></span>
                        </div>
                    </div>
                </div>

				@include('common.elements.error_tag', ['name' => 'from_date'])
                @include('common.elements.error_tag', ['name' => 'to_date'])
            </td>
        </tr>

        <tr>
            <th class="c-box--300 must">キャンペーン金額</th>
            <td>
                <input class="form-control" type="text" name="giving_condition_amount" 
                    value="{{ old('giving_condition_amount', $editRow['giving_condition_amount'] ?? '') }}" 
                    style="text-align: right;">
                @include('common.elements.error_tag', ['name' => 'giving_condition_amount'])
            </td>
        </tr>


        <tr>
            <th class="c-box--300 must">金額ごとに追加</th>
            <td>
                @foreach (\App\Enums\GivingConditionEvery::cases() as $target)
                <input class="form-check-input" type="radio" name="giving_condition_every" id="radio{{$target->value}}" value="{{$target->value}}" @if(old('giving_condition_every',$editRow['giving_condition_every'] ?? \App\Enums\GivingConditionEvery::Use->value)==$target->value) checked @endif>
                <label class="form-check-label" for="radio{{$target->value}}">{{$target->label()}}</label>
                @endforeach
	            @include('common.elements.error_tag', ['name' => 'giving_condition_every'])
            </td>
        </tr>

        <tr>
            <th class="c-box--300 must">商品コード</th>
            <td>
                <input class="form-control" type="text" name="giving_page_cd" value="{{old('giving_page_cd',$editRow['giving_page_cd'] ?? '')}}">
	            @include('common.elements.error_tag', ['name' => 'giving_page_cd'])
            </td>
        </tr>
    </table>
</div>
<div class="u-mt--ss">
    <input class="btn btn-default btn-lg u-mt--sm" type="button" name="cancel" value="キャンセル" onClick="location.href='{{route('campaign.list')}}';" />
	&nbsp;&nbsp;
    <input type="hidden" name="add" value="0" />
    <button class="btn btn-success btn-lg u-mt--sm" type="submit" name="submit" id="submit_notify" value="notify">確認</button>
	@include('common.elements.on_enter_script', ['target_button_name' => 'submit_register'])
</div>
</form>
@include('common.elements.datetime_picker_script')

@endsection