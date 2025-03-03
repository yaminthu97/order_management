{{-- NEMSMJ0030:支払方法マスタ確認 --}}
@php
    $ScreenCd = 'NEMSMJ0030';
@endphp

{{-- layout設定 --}}
@extends('common.layouts.default')

{{-- タイトル設定 --}}
@section('title', '支払方法マスタ確認')

{{-- ぱんくず設定 --}}
@section('breadcrumb')
    <li>支払方法マスタ確認</li>
@endsection

@section('content')
    @if (!empty($viewMessage))
        <div class="c-box--1700 c-tbl-border-all u-p--sm sy_notice u-mb--ss">
            @foreach ($viewMessage as $message)
                <p class="icon_sy_notice_03">{{ $message }}</p>
            @endforeach
        </div>
    @endif

    <div class="u-mt--xs">
        <form method="POST" action="">
            @csrf
            @if ($mode == 'edit')
                @method('PUT')
            @endif
            {{-- 検索入力フォーム --}}
            <table class="table table-bordered c-tbl c-tbl--800 tbl-notify">
                <tr>
                    <th class="c-box--300">支払方法マスタID</th>
                    <td>{{ !empty($editRow['m_payment_types_id']) ? $editRow['m_payment_types_id'] : '自動' }}
                    </td>
                </tr>
                <tr>
                    <th class="c-box--150 must">使用区分</th>
                    <td>
                        @foreach (\App\Enums\DeleteFlg::cases() as $deleteFlg)
                            @if (old('delete_flg', $editRow['delete_flg'] ?? null) == $deleteFlg->value)
                                {{ $deleteFlg->label() }}
                            @endif
                        @endforeach
                    </td>
                </tr>
                <tr>
                    <th class="c-box--150 must">支払方法種類</th>
                    <td>
                        @foreach (\App\Enums\PaymentMethodTypeEnum::cases() as $payment_type)
                            @if (old('payment_type', $editRow['payment_type'] ?? null) == $payment_type->value)
                                {{ $payment_type->label() }}
                            @endif
                        @endforeach
                    </td>
                </tr>
                <tr>
                    <th class="c-box--150 must">支払方法名</th>
                    <td>{{ old('m_payment_types_name', $editRow->m_payment_types_name ?? '') }}</td>
                </tr>
                <tr>
                    <th class="c-box--150">支払方法コード</th>
                    <td>{{ old('m_payment_types_code', $editRow->m_payment_types_code ?? '') }}</td>
                </tr>
                <tr>
                    <th class="c-box--150 must">配送条件</th>
                    <td>
                        @foreach (\App\Modules\Master\Gfh1207\Enums\DeliveryConditionEnum::cases() as $deliveryCondition)
                            @if (old('delivery_condition', $editRow['delivery_condition'] ?? null) == $deliveryCondition->value)
                                {{ $deliveryCondition->label() }}
                            @endif
                        @endforeach
                    </td>
                </tr>
                <tr>
                    <th class="c-box--150">決済管理画面URL</th>
                    <td>{{ old('settlement_management_url', $editRow->settlement_management_url ?? '') }}</td>
                </tr>
                <tr>
                    <th class="c-box--150 must">並び順</th>
                    <td>{{ old('m_payment_types_sort', $editRow->m_payment_types_sort ?? '100') }}</td>
                </tr>
                <tr>
                    <th class="c-box--150">手数料</th>
                    <td>
                        {{ old('payment_fee', isset($editRow['payment_fee']) ? (fmod($editRow['payment_fee'], 1) == 0 ? (int) $editRow['payment_fee'] : rtrim(rtrim($editRow['payment_fee'], '0'), '.')) : '') }}
                    </td>
                </tr>
                <tr>
                    <th class="c-box--150">後払い.com連携区分</th>
                    <td>
                        @foreach (\App\Modules\Master\Gfh1207\Enums\CooperationType::cases() as $cooperationType)
                            @if (isset($editRow['atobarai_com_cooperation_type']) &&
                                    $editRow['atobarai_com_cooperation_type'] == $cooperationType->value)
                                {{ $cooperationType->label() }}
                            @endif
                        @endforeach
                    </td>
                </tr>
                <tr>
                    <th class="c-box--150">後払い.com-接続先URL</th>
                    <td>{{ old('atobarai_com_url', $editRow->atobarai_com_url ?? '') }}</td>
                </tr>
                <tr>
                    <th class="c-box--150">後払い.com-受付事業者ID</th>
                    <td>{{ old('atobarai_com_acceptance_company_id', $editRow->atobarai_com_acceptance_company_id ?? '') }}
                    </td>
                </tr>
                <tr>
                    <th class="c-box--150">後払い.com-APIユーザID</th>
                    <td>{{ old('atobarai_com_apiuser_id', $editRow->atobarai_com_apiuser_id ?? '') }}</td>
                </tr>
            </table>
            <div class="u-mt--sm">
                <button type="submit" name="submit" value="cancel" class="btn btn-default btn-lg">キャンセル</button>
                <input type="submit" name="submit" value="登録" class="btn btn-success btn-lg">
            </div>
            <input type="hidden" name="{{ config('define.master.session_key_id') }}" value="{{ $param }}">
        </form>
        <style>
            .tbl-notify td {
                word-break: break-all;
            }
        </style>
    </div>
@endsection
