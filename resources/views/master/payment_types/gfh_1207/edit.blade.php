{{-- NEMSMJ0020:支払方法マスタ登録・更新 --}}
@php
    $ScreenCd = 'NEMSMJ0020';
@endphp

{{-- layout設定 --}}
@extends('common.layouts.default')

{{-- タイトル設定 --}}
@section('title', '支払方法マスタ登録・更新')

{{-- ぱんくず設定 --}}
@section('breadcrumb')
    <li>支払方法マスタ登録・更新</li>
@endsection

@section('content')

    <div class="u-mt--xs">
        <form enctype="multipart/form-data" method="POST" action="" name="Form1" id="Form1">
            {{ csrf_field() }}

            {{-- 検索入力フォーム --}}
            <table class="table table-bordered c-tbl c-tbl--800">
                <tr>
                    <th class="c-box--300">支払方法マスタID</th>
                    <td>{{ !empty($editRow['m_payment_types_id']) ? $editRow['m_payment_types_id'] : '自動' }}
                    </td>
                    <input type="hidden" name="m_payment_types_id"
                        value="{{ old('m_payment_types_id', $editRow->m_payment_types_id ?? '') }}">
                </tr>
                <tr>
                    <th class="c-box--150 must">使用区分</th>
                    <td>
                        @foreach (\App\Enums\DeleteFlg::cases() as $deleteFlg)
                            <label class="radio-inline">
                                <input type="radio" name="delete_flg" value="{{ $deleteFlg->value }}"
                                    @checked(
                                        ($loop->first && empty($editRow['delete_flg'])) ||
                                            (old('delete_flg') || (isset($editRow['delete_flg']) && $editRow['delete_flg'] == $deleteFlg->value)))>
                                {{ $deleteFlg->label() }}
                            </label>
                        @endforeach

                        @include('common.elements.error_tag', ['name' => 'delete_flg'])
                    </td>
                </tr>
                <tr>
                    <th class="c-box--150 must">支払方法種類</th>
                    <td>
                        <select name="payment_type" class="form-control c-box--200">
                            <option value=""></option>
                            @foreach (\App\Enums\PaymentMethodTypeEnum::cases() as $payment_type)
                                <option value="{{ $payment_type->value }}"
                                    {{ old('payment_type', isset($editRow->payment_type) ? $editRow->payment_type : '') == $payment_type->value ? 'selected' : '' }}>
                                    {{ $payment_type->label() }}
                                </option>
                            @endforeach
                        </select>
                        @include('common.elements.error_tag', ['name' => 'payment_type'])
                    </td>
                </tr>
                <tr>
                    <th class="c-box--150 must">支払方法名</th>
                    <td>
                        <input type="text" class="form-control c-box--300" name="m_payment_types_name"
                            value="{{ old('m_payment_types_name', $editRow->m_payment_types_name ?? '') }}">
                        @include('common.elements.error_tag', ['name' => 'm_payment_types_name'])
                    </td>
                </tr>
                <tr>
                    <th class="c-box--150">支払方法コード</th>
                    <td>
                        <input type="text" class="form-control c-box--300" name="m_payment_types_code"
                            value="{{ old('m_payment_types_code', $editRow->m_payment_types_code ?? '') }}">
                        @include('common.elements.error_tag', ['name' => 'm_payment_types_code'])
                    </td>
                </tr>
                <tr>
                    <th class="c-box--150 must">配送条件</th>
                    <td>
                        <div class="radio-inline">
                            <label>
                                <input type="radio" name="delivery_condition" value="0"
                                    @if (old('delivery_condition', $editRow['delivery_condition'] ?? '') === '0' ||
                                            (empty(old('delivery_condition')) && empty($editRow['delivery_condition']))) checked @endif>
                                {{ \App\Modules\Master\Gfh1207\Enums\DeliveryConditionEnum::NONE->label() }}
                            </label>
                        </div>
                        <div class="radio-inline">
                            <label>
                                <input type="radio" name="delivery_condition" value="1"
                                    @if (old('delivery_condition', $editRow['delivery_condition'] ?? '') == '1') checked @endif>
                                {{ \App\Modules\Master\Gfh1207\Enums\DeliveryConditionEnum::PAID->label() }}
                            </label>
                        </div>
                        <div class="radio-inline">
                            <label>
                                <input type="radio" name="delivery_condition" value="2"
                                    @if (old('delivery_condition', $editRow['delivery_condition'] ?? '') == '2') checked @endif>
                                {{ \App\Modules\Master\Gfh1207\Enums\DeliveryConditionEnum::BILLED->label() }}
                            </label>
                        </div>
                        @include('common.elements.error_tag', ['name' => 'delivery_condition'])
                    </td>
                </tr>
                <tr>
                    <th class="c-box--150">決済管理画面URL</th>
                    <td>
                        <input type="text" class="form-control c-box--300" name="settlement_management_url"
                            value="{{ old('settlement_management_url', $editRow->settlement_management_url ?? '') }}">
                        @include('common.elements.error_tag', ['name' => 'settlement_management_url'])
                    </td>
                </tr>
                <tr>
                    <th class="c-box--150 must">並び順</th>
                    <td>
                        <input type="text" class="form-control c-box--300" name="m_payment_types_sort"
                            value="{{ old('m_payment_types_sort', $editRow->m_payment_types_sort ?? '100') }}">
                        @include('common.elements.error_tag', ['name' => 'm_payment_types_sort'])
                    </td>
                </tr>
                <tr>
                    <th class="c-box--150">手数料</th>
                    <td>
                        <input type="text" class="form-control c-box--300" name="payment_fee"
                            value="{{ old('payment_fee', isset($editRow->payment_fee) ? ($editRow->payment_fee == (int) $editRow->payment_fee ? (int) $editRow->payment_fee : number_format($editRow->payment_fee, 2)) : '') }}">
                        @include('common.elements.error_tag', ['name' => 'payment_fee'])
                    </td>
                </tr>
                <tr>
                    <th class="c-box--150">後払い.com連携区分</th>
                    <td>
                        <div class="radio-inline">
                            <label>
                                <input type="radio" name="atobarai_com_cooperation_type"
                                    value="{{ \App\Modules\Master\Gfh1207\Enums\CooperationType::COOPERATE->value }}"
                                    @if (old('atobarai_com_cooperation_type', $editRow['atobarai_com_cooperation_type']) == \App\Modules\Master\Gfh1207\Enums\CooperationType::COOPERATE->value) checked @endif>
                                {{ \App\Modules\Master\Gfh1207\Enums\CooperationType::COOPERATE->label() }}
                            </label>
                        </div>
                        <div class="radio-inline">
                            <label>
                                <input type="radio" name="atobarai_com_cooperation_type"
                                    value="{{ \App\Modules\Master\Gfh1207\Enums\CooperationType::NO_COOPERATION->value }}"
                                    @if (old('atobarai_com_cooperation_type', $editRow['atobarai_com_cooperation_type']) == \App\Modules\Master\Gfh1207\Enums\CooperationType::NO_COOPERATION->value) checked
                                    @elseif (is_null(old('atobarai_com_cooperation_type')) && is_null($editRow['atobarai_com_cooperation_type'])) checked @endif>
                                {{ \App\Modules\Master\Gfh1207\Enums\CooperationType::NO_COOPERATION->label() }}
                            </label>
                        </div>
                        @include('common.elements.error_tag', ['name' => 'atobarai_com_cooperation_type'])
                    </td>
                </tr>
                <tr>
                    <th class="c-box--150">後払い.com-接続先URL</th>
                    <td>
                        <input type="text" class="form-control c-box--300" name="atobarai_com_url"
                            value="{{ old('atobarai_com_url', $editRow->atobarai_com_url ?? '') }}">
                        @include('common.elements.error_tag', ['name' => 'atobarai_com_url'])
                    </td>
                </tr>
                <tr>
                    <th class="c-box--150">後払い.com-受付事業者ID</th>
                    <td>
                        <input type="text" class="form-control c-box--300" name="atobarai_com_acceptance_company_id"
                            value="{{ old('atobarai_com_acceptance_company_id', $editRow->atobarai_com_acceptance_company_id ?? '') }}">
                        @include('common.elements.error_tag', [
                            'name' => 'atobarai_com_acceptance_company_id',
                        ])
                    </td>
                </tr>
                <tr>
                    <th class="c-box--150">後払い.com-APIユーザID</th>
                    <td>
                        <input type="text" class="form-control c-box--300" name="atobarai_com_apiuser_id"
                            value="{{ old('atobarai_com_apiuser_id', $editRow->atobarai_com_apiuser_id ?? '') }}">
                        @include('common.elements.error_tag', ['name' => 'atobarai_com_apiuser_id'])
                    </td>
                </tr>
            </table>
            <div class="u-mt--sm">
                <a href="{{ route('master.payment_types.list') }}" class="btn btn-default btn-lg">キャンセル</a>
                <input type="submit" name="submit" value="確認" class="btn btn-success btn-lg">
            </div>
        </form>
    </div>
@endsection
