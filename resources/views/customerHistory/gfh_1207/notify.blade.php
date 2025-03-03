{{-- NECSM0122:顧客対応履歴登録・修正確認 --}}
@php
    $ScreenCd = 'NECSM0122';
@endphp

{{-- layout設定 --}}
@extends('common.layouts.default')

{{-- タイトル設定 --}}
@section('title', '顧客対応履歴登録・修正確認')

{{-- ぱんくず設定 --}}
@section('breadcrumb')
    <li>顧客対応履歴登録・修正確認</li>
@endsection
@section('content')
    <form method="POST" action="" name="Form1" id="Form1">
        {{ csrf_field() }}
        @if ($mode == 'new')
        @elseif($mode == 'edit')
            @method('PUT')
        @endif
        <div>
            <table class="table table-bordered c-tbl c-tbl--960">
                @if (isset($editRow->t_cust_communication_id))
                    <tr>
                        <th class="c-box--200">対応履歴ID</th>
                        <td>{{ $editRow['t_cust_communication_id'] ?? '' }}</td>
                    </tr>
                @endif
                <tr>
                    <th class="c-box--200">顧客ID</th>
                    <td>{{ $editRow['m_cust_id'] ?? '' }}</td>
                </tr>
                <tr>
                    <th class="c-box--200">受注ID</th>
                    <td>{{ $editRow['t_order_hdr_id'] ?? '' }}</td>
                </tr>
                <tr>
                    <th class="c-box--200">商品ページコード</th>
                    <td>{{ $editRow['page_cd'] ?? '' }}</td>
                </tr>
                <tr>
                    <th class="c-box--200">名前</th>
                    <td>{{ $editRow['name_kanji'] ?? '' }}</td>
                </tr>
                <tr>
                    <th class="c-box--200">フリガナ</th>
                    <td>{{ $editRow['name_kana'] ?? '' }}</td>
                </tr>
                <tr>
                    <th class="c-box--200">電話番号</th>
                    <td>{{ $editRow['tel'] ?? '' }}</td>
                </tr>
                <tr>
                    <th class="c-box--200">メールアドレス</th>
                    <td>{{ $editRow['email'] ?? '' }}</td>
                </tr>
                <tr>
                    <th class="c-box--200">郵便番号</th>
                    <td>{{ $editRow['postal'] ?? '' }}</td>
                </tr>
                <tr>
                    <th class="c-box--200">都道府県</th>
                    <td>{{ $editRow['address1'] ?? '' }}</td>
                </tr>
                <tr>
                    <th class="c-box--200">住所</th>
                    <td>{{ $editRow['address2'] ?? '' }}</td>
                </tr>
                <tr>
                    <th class="c-box--200">連絡先その他</th>
                    <td>
                        @if (isset($editRow['note']))
                            {!! nl2br($editRow['note']) !!}
                        @endif
                    </td>
                </tr>
                <tr>
                    <th class="must c-box--200">タイトル</th>
                    <td>{{ $editRow['title'] ?? '' }}</td>
                </tr>
                <tr>
                    <th class="c-box--200">販売窓口</th>
                    <td>
                        @foreach ($viewExtendData['salesChannel'] as $tableIdName1 => $tableIdValue1)
                            @if (isset($editRow['sales_channel']) && $editRow['sales_channel'] == $tableIdValue1)
                                {{ $tableIdName1 }}
                            @endif
                        @endforeach
                    </td>
                </tr>
                <tr>
                    <th class="c-box--200">問合せ内容種別</th>
                    <td>
                        @foreach ($viewExtendData['inquiryType'] as $tableIdName1 => $tableIdValue1)
                            @if (isset($editRow['inquiry_type']) && $editRow['inquiry_type'] == $tableIdValue1)
                                {{ $tableIdName1 }}
                            @endif
                        @endforeach
                    </td>
                </tr>
                <tr>
                    <th class="must c-box--200">公開</th>
                    <td>
                        @if (isset($editRow['open']) && $editRow['open'] == \App\Modules\Customer\Gfh1207\Enums\OpenFlg::PUBLISH->value)
                            {{ \App\Modules\Customer\Gfh1207\Enums\OpenFlg::PUBLISH->label() }}
                        @else
                            {{ \App\Modules\Customer\Gfh1207\Enums\OpenFlg::NOT_PUBLISH->label() }}
                        @endif
                    </td>
                </tr>
                <tr>
                    <th class="must c-box--200">連絡方法</th>
                    <td>
                        @foreach ($viewExtendData['contactWayTypes'] as $tableIdName1 => $tableIdValue1)
                            @if (isset($editRow['contact_way_type']) && $editRow['contact_way_type'] == $tableIdValue1)
                                {{ $tableIdName1 }}
                            @endif
                        @endforeach
                    </td>
                </tr>
                <tr>
                    <th class="c-box--200">ステータス</th>
                    <td>
                        @foreach ($viewExtendData['statusList'] as $tableIdName1 => $tableIdValue1)
                            @if (isset($editRow['status']) && $editRow['status'] == $tableIdValue1)
                                {{ $tableIdName1 }}
                            @endif
                        @endforeach
                    </td>
                </tr>
                <tr>
                    <th class="c-box--200">分類</th>
                    <td>
                        @foreach ($viewExtendData['categoryList'] as $tableIdName1 => $tableIdValue1)
                            @if (isset($editRow['category']) && $editRow['category'] == $tableIdValue1)
                                {{ $tableIdName1 }}
                            @endif
                        @endforeach
                    </td>
                </tr>
                <tr>
                    <th class="must c-box--200">受信内容</th>
                    <td>
                        @if (isset($editRow['receive_detail']))
                            {!! nl2br($editRow['receive_detail']) !!}
                        @endif
                    </td>
                </tr>
                <tr>
                    <th class="c-box--200">受信日時</th>
                    <td>
                        @if (isset($editRow['receive_datetime']))
                            {{ (new Carbon\Carbon($editRow['receive_datetime']))->format('Y/m/d') }}
                        @endif
                    </td>
                </tr>
                <tr>
                    <th class="must c-box--200">受信者</th>
                    <td>
                        @foreach ($viewExtendData['operatorNameList'] as $tableIdName1 => $tableIdValue1)
                            @if (isset($editRow['receive_operator_id']) && $editRow['receive_operator_id'] == $tableIdValue1)
                                {{ $tableIdName1 }}
                            @endif
                        @endforeach
                    </td>
                </tr>
                <tr>
                    <th class="c-box--200">エスカレーション担当者</th>
                    <td>
                        @foreach ($viewExtendData['operatorNameList'] as $tableIdName1 => $tableIdValue1)
                            @if (isset($editRow['escalation_operator_id']) && $editRow['escalation_operator_id'] == $tableIdValue1)
                                {{ $tableIdName1 }}
                            @endif
                        @endforeach
                    </td>
                </tr>
                <tr>
                    <th class="c-box--200">回答内容</th>
                    <td>
                        @if (isset($editRow['answer_detail']))
                            {!! nl2br($editRow['answer_detail']) !!}
                        @endif
                    </td>
                </tr>
                <tr>
                    <th class="c-box--200">回答日時</th>
                    <td>
                        @if (isset($editRow['answer_datetime']))
                            {{ (new Carbon\Carbon($editRow['answer_datetime']))->format('Y/m/d') }}
                        @endif

                    </td>
                </tr>
                <tr>
                    <th class="c-box--200">回答者</th>
                    <td>
                        @foreach ($viewExtendData['operatorNameList'] as $tableIdName1 => $tableIdValue1)
                            @if (isset($editRow['answer_operator_id']) && $editRow['answer_operator_id'] == $tableIdValue1)
                                {{ $tableIdName1 }}
                            @endif
                        @endforeach
                    </td>
                </tr>
                <tr>
                    <th class="c-box--200">対応結果</th>
                    <td>
                        @foreach ($viewExtendData['resStatus'] as $tableIdName1 => $tableIdValue1)
                            @if (isset($editRow['resolution_status']) && $editRow['resolution_status'] == $tableIdValue1)
                                {{ $tableIdName1 }}
                            @endif
                        @endforeach
                    </td>
                </tr>
            </table>
        </div>
        <div class="u-mt--ss">
            <button type="submit" name="submit" value="cancel" class="btn btn-default u-mt--sm btn-lg">キャンセル</button>
            <button type="submit" name="submit" value="register" class="btn btn-success btn-lg u-mt--sm ml-20">登録</button>
            <input type="hidden" name="{{ config('define.cc.session_key_id') }}" value="{{ $param }}">
            <input type="hidden" name="previous_url" value="{{ old('previous_url', $previous_url ?? '') }}">
        </div>
    </form>
    @push('css')
        <link rel="stylesheet" href="{{ esm_internal_asset('css/customer/gfh_1207/NECSM0121.css') }}">
    @endpush
@endsection
