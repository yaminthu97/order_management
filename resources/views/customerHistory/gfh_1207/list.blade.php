{{-- NECSM0120:顧客対応履歴検索 --}}
@php
    $ScreenCd = 'NECSM0120';
@endphp

{{-- layout設定 --}}
@extends('common.layouts.default')

{{-- タイトル設定 --}}
@section('title', '顧客対応履歴検索')

{{-- ぱんくず設定 --}}
@section('breadcrumb')
    <li>
        顧客対応履歴検索</li>
@endsection

@section('content')
@include('common.elements.datetime_picker_script')
    <div id="messageContainer"></div><!--/sy_notice-->
    <form enctype="multipart/form-data" method="POST" action="{{ route('cc.customer-history.list') }}" name="Form1" id="Form1">
        {{ csrf_field() }}
        <div>
            <table class="table table-bordered c-tbl">
                <tbody>
                    <tr>
                        <th class="c-box--200 col-xs-2">対応履歴ID</th>
                        <td class="col-xs-4">
                            <input class="form-control" type="text" name="t_cust_communication_id"
                                value="{{ old('t_cust_communication_id', $searchRow['t_cust_communication_id'] ?? '') }}"/>
                            @include('common.elements.error_tag', ['name' => 't_cust_communication_id'])
                        </td>
                        <th class="c-box--200 col-xs-2">タイトル</th>
                        <td class="col-xs-4">
                            <input class="form-control" type="text" name="title"
                                value="{{ old('title', $searchRow['title'] ?? '') }}" />
                            @include('common.elements.error_tag', ['name' => 'title'])
                        </td>
                    </tr>
                    <tr>
                        <th class="c-box--200 col-xs-2">顧客ID</th>
                        <td class="col-xs-4">
                            <input class="form-control" type="text" name="m_cust_id"
                                value="{{ old('m_cust_id', $searchRow['m_cust_id'] ?? '') }}" />
                            @include('common.elements.error_tag', ['name' => 'm_cust_id'])
                        </td>

                        <th class="c-box--200 col-xs-2">ステータス</th>
                        <td class="col-xs-4">
                            <select name="status" class="form-control c-box--200">
                                <option value=""></option>
                                @foreach ($viewExtendData['statusList'] as $tableIdName1 => $tableIdValue1)
                                    <option value="{{ $tableIdValue1 }}"
                                        {{ old('status', isset($searchRow['status']) ? $searchRow['status'] : '') == $tableIdValue1 ? 'selected' : '' }}>
                                        {{ $tableIdName1 }}
                                    </option>
                                @endforeach
                            </select>
                            @include('common.elements.error_tag', ['name' => 'status'])
                        </td>
                    </tr>
                    <tr>
                        <th class="col-xs-2">受注ID</th>
                        <td class="col-xs-4">
                            <input class="form-control" type="text" name="t_order_hdr_id"
                                value="{{ old('t_order_hdr_id', $searchRow['t_order_hdr_id'] ?? '') }}" />
                            @include('common.elements.error_tag', ['name' => 't_order_hdr_id'])
                        </td>
                        <th class="col-xs-2">分類</th>
                        <td class="col-xs-4">
                            <select name="category" class="form-control c-box--200">
                                <option value=""></option>
                                @foreach ($viewExtendData['categoryList'] as $tableIdName1 => $tableIdValue1)
                                    <option value="{{ $tableIdValue1 }}"
                                        {{ old('category', isset($searchRow['category']) ? $searchRow['category'] : '') == $tableIdValue1 ? 'selected' : '' }}>
                                        {{ $tableIdName1 }}
                                    </option>
                                @endforeach
                            </select>
                            @include('common.elements.error_tag', ['name' => 'category'])
                        </td>
                    </tr>
                    <tr>
                        <th class="col-xs-2">商品ページコード</th>
                        <td class="col-xs-4">
                            <input class="form-control" type="text" name="page_cd"
                                value="{{ old('page_cd', $searchRow['page_cd'] ?? '') }}" />
                            @include('common.elements.error_tag', ['name' => 'page_cd'])
                        </td>
                        <th class="c-box--200 col-xs-2">受信日時</th>
                        <td class="col-xs-4">
                            <div class="row col-xs-offset-0 mr-0">
                                <div class="col-xs-5 p-0">
                                    <div class="input-group date datetime-picker" id="datetimepicker1">
                                        <input type="text" class="form-control c-box--140" name="receive_datetime_from"
                                            value="{{ old('receive_datetime_from', $searchRow['receive_datetime_from'] ?? '') }}" />
                                        <span class="input-group-addon">
                                            <span class="glyphicon glyphicon-calendar"></span>
                                        </span>
                                    </div>
                                    @include('common.elements.error_tag', [
                                        'name' => 'receive_datetime_from',
                                    ])
                                </div>

                                <div class="col-xs-1 text-center icn">
                                    ～
                                </div>

                                <div class="col-xs-5 p-0">
                                    <div class="input-group date datetime-picker" id="datetimepicker2">
                                        <input type="text" class="form-control c-box--140" name="receive_datetime_to"
                                            value="{{ old('receive_datetime_to', $searchRow['receive_datetime_to'] ?? '') }}" />
                                        <span class="input-group-addon">
                                            <span class="glyphicon glyphicon-calendar"></span>
                                        </span>
                                    </div>
                                    @include('common.elements.error_tag', [
                                        'name' => 'receive_datetime_to',
                                    ])
                                </div>

                                <div class="col-xs-1"></div>
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <th class="col-xs-2">連絡方法</th>
                        <td class="col-xs-4">
                            <select name="contact_way_type" class="form-control c-box--200">
                                <option value=""></option>
                                @foreach ($viewExtendData['contactWayTypes'] as $tableIdName1 => $tableIdValue1)
                                    <option value="{{ $tableIdValue1 }}"
                                        {{ old('contact_way_type', isset($searchRow['contact_way_type']) ? $searchRow['contact_way_type'] : '') == $tableIdValue1 ? 'selected' : '' }}>
                                        {{ $tableIdName1 }}
                                    </option>
                                @endforeach
                            </select>
                            @include('common.elements.error_tag', ['name' => 'contact_way_type'])
                        </td>
                        <th class="col-xs-2">受信者</th>
                        <td class="col-xs-4">
                            <select name="receive_operator_id" class="form-control c-box--200">
                                <option value=""></option>
                                @foreach ($viewExtendData['operatorNameList'] as $tableIdName1 => $tableIdValue1)
                                    <option value="{{ $tableIdValue1 }}"
                                        {{ old('receive_operator_id', isset($searchRow['receive_operator_id']) ? $searchRow['receive_operator_id'] : '') == $tableIdValue1 ? 'selected' : '' }}>
                                        {{ $tableIdName1 }}
                                    </option>
                                @endforeach
                            </select>
                            @include('common.elements.error_tag', ['name' => 'receive_operator_id'])
                        </td>
                    </tr>
                    <tr>
                        <th class="col-xs-2">販売窓口</th>
                        <td class="col-xs-4">
                            <select name="sales_channel" class="form-control c-box--200">
                                <option value=""></option>
                                @foreach ($viewExtendData['salesChannel'] as $tableIdName1 => $tableIdValue1)
                                    <option value="{{ $tableIdValue1 }}"
                                        {{ old('sales_channel', isset($searchRow['sales_channel']) ? $searchRow['sales_channel'] : '') == $tableIdValue1 ? 'selected' : '' }}>
                                        {{ $tableIdName1 }}
                                    </option>
                                @endforeach
                            </select>
                            @include('common.elements.error_tag', ['name' => 'sales_channel'])
                        </td>
                        <th class="col-xs-2">受信内容</th>
                        <td class="col-xs-4">
                            <input class="form-control" name="receive_detail"
                                onchange="changeToolTip('receive_detail_tip', 'receive_detail');"
                                value="{{ old('receive_detail', $searchRow['receive_detail'] ?? '') }}">
                            @include('common.elements.error_tag', ['name' => 'receive_detail'])
                        </td>
                    </tr>
                    <tr>
                        <th class="col-xs-2">問合せ内容種別</th>
                        <td class="col-xs-4">
                            <select name="inquiry_type" class="form-control c-box--200">
                                <option value=""></option>
                                @foreach ($viewExtendData['inquiryType'] as $tableIdName1 => $tableIdValue1)
                                    <option value="{{ $tableIdValue1 }}"
                                        {{ old('inquiry_type', isset($searchRow['inquiry_type']) ? $searchRow['inquiry_type'] : '') == $tableIdValue1 ? 'selected' : '' }}>
                                        {{ $tableIdName1 }}
                                    </option>
                                @endforeach
                            </select>
                            @include('common.elements.error_tag', ['name' => 'inquiry_type'])
                        </td>
                        <th class="col-xs-2">回答日時</th>
                        <td class="col-xs-4">
                            <div class="row col-xs-offset-0 mr-0">
                                <div class="col-xs-5 p-0">
                                    <div class='input-group date datetime-picker' id='datetimepicker3'>
                                        <input type='text' class="form-control c-box--140" name="answer_datetime_from"
                                            value="{{ old('answer_datetime_from', $searchRow['answer_datetime_from'] ?? '') }}" />
                                        <span class="input-group-addon">
                                            <span class="glyphicon glyphicon-calendar"></span>
                                        </span>
                                    </div>
                                    @include('common.elements.error_tag', [
                                        'name' => 'answer_datetime_from',
                                    ])
                                </div>

                                <div class="col-xs-1 text-center icn">
                                    ～
                                </div>

                                <div class="col-xs-5 p-0">
                                    <div class='input-group date datetime-picker' id='datetimepicker4'>
                                        <input type='text' class="form-control c-box--140" name="answer_datetime_to"
                                            value="{{ old('answer_datetime_to', $searchRow['answer_datetime_to'] ?? '') }}" />
                                        <span class="input-group-addon">
                                            <span class="glyphicon glyphicon-calendar"></span>
                                        </span>
                                    </div>
                                    @include('common.elements.error_tag', [
                                        'name' => 'answer_datetime_to',
                                    ])
                                </div>

                                <div class="col-xs-1"></div>
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <th class="c-box--200 col-xs-2">名前</th>
                        <td class="col-xs-4">
                            <input class="form-control" type="text" name="name_kanji"
                                value="{{ old('name_kanji', $searchRow['name_kanji'] ?? '') }}" />
                            @include('common.elements.error_tag', ['name' => 'name_kanji'])
                            <label class="checkbox-inline">
                                <input type="checkbox" name="name_kanji_flag" value="1"
                                    @checked(old('name_kanji_flag', $searchRow['name_kanji_flag'] ?? false))>あいまい検索
                            </label>
                        </td>
                        <th class="c-box--200 col-xs-2">回答者</th>
                        <td class="col-xs-4">
                            <select name="answer_operator_id" class="form-control c-box--200">
                                <option value=""></option>
                                @foreach ($viewExtendData['operatorNameList'] as $tableIdName1 => $tableIdValue1)
                                    <option value="{{ $tableIdValue1 }}"
                                        {{ old('answer_operator_id', isset($searchRow['answer_operator_id']) ? $searchRow['answer_operator_id'] : '') == $tableIdValue1 ? 'selected' : '' }}>
                                        {{ $tableIdName1 }}
                                    </option>
                                @endforeach
                            </select>
                            @include('common.elements.error_tag', ['name' => 'answer_operator_id'])
                        </td>
                    </tr>
                    <tr>
                        <th class="c-box--200 col-xs-2">フリガナ</th>
                        <td class="col-xs-4">
                            <input class="form-control" type="text" name="name_kana"
                                value="{{ old('name_kana', $searchRow['name_kana'] ?? '') }}" />
                            @include('common.elements.error_tag', ['name' => 'name_kana'])
                            <label class="checkbox-inline">
                                <input type="checkbox" name="name_kana_flag" value="1"
                                    @checked(old('name_kana_flag', $searchRow['name_kana_flag'] ?? false))>あいまい検索
                            </label>
                        </td>
                        <th class="c-box--200 col-xs-2">回答内容</th>
                        <td class="col-xs-4">
                            <input class="form-control" name="answer_detail"
                                onchange="changeToolTip('answer_detail_tip', 'answer_detail');"
                                value="{{ old('answer_detail', $searchRow['answer_detail'] ?? '') }}" />
                            @include('common.elements.error_tag', ['name' => 'answer_detail'])
                        </td>
                    </tr>
                    <tr>
                        <th class="c-box--200 col-xs-2">電話番号</th>
                        <td class="col-xs-4">
                            <input class="form-control" type="text" name="tel"
                                value="{{ old('tel', $searchRow['tel'] ?? '') }}" />
                            @include('common.elements.error_tag', ['name' => 'tel'])
                            <label class="checkbox-inline">
                                <input type="checkbox" name="tel_search_flag" value="1"
                                    @checked(old('tel_search_flag', $searchRow['tel_search_flag'] ?? false))>前方一致
                        </td>
                        <th class="c-box--200 col-xs-2">エスカレーション担当者</th>
                        <td class="col-xs-4">
                            <select name="escalation_operator_id" class="form-control c-box--200">
                                <option value=""></option>
                                @foreach ($viewExtendData['operatorNameList'] as $tableIdName1 => $tableIdValue1)
                                    <option value="{{ $tableIdValue1 }}"
                                        {{ old('escalation_operator_id', isset($searchRow['escalation_operator_id']) ? $searchRow['escalation_operator_id'] : '') == $tableIdValue1 ? 'selected' : '' }}>
                                        {{ $tableIdName1 }}
                                    </option>
                                @endforeach
                            </select>
                            @include('common.elements.error_tag', ['name' => 'escalation_operator_id'])
                        </td>
                    </tr>
                    <tr>
                        <th class="c-box--200 col-xs-2">メールアドレス</th>
                        <td class="col-xs-4">
                            <input class="form-control" type="text" name="email"
                                value="{{ old('email', $searchRow['email'] ?? '') }}" />
                            @include('common.elements.error_tag', ['name' => 'email'])
                        </td>
                        <th class="c-box--200 col-xs-2">更新日時</th>
                        <td class="col-xs-4">
                            <div class="row col-xs-offset-0 mr-0">
                                <div class="col-xs-5 p-0">
                                    <div class='input-group date datetime-picker' id='datetimepicker5'>
                                        <input type='text' class="form-control c-box--140"
                                            name="update_timestamp_from"
                                            value="{{ old('update_timestamp_from', $searchRow['update_timestamp_from'] ?? '') }}" />
                                        <span class="input-group-addon">
                                            <span class="glyphicon glyphicon-calendar"></span>
                                        </span>
                                    </div>
                                    @include('common.elements.error_tag', [
                                        'name' => 'update_timestamp_from',
                                    ])
                                </div>

                                <div class="col-xs-1 text-center icn">
                                    ～
                                </div>

                                <div class="col-xs-5 p-0">
                                    <div class='input-group date datetime-picker' id='datetimepicker6'>
                                        <input type='text' class="form-control c-box--140" name="update_timestamp_to"
                                            value="{{ old('update_timestamp_to', $searchRow['update_timestamp_to'] ?? '') }}" />
                                        <span class="input-group-addon">
                                            <span class="glyphicon glyphicon-calendar"></span>
                                        </span>
                                    </div>
                                    @include('common.elements.error_tag', [
                                        'name' => 'update_timestamp_to',
                                    ])
                                </div>

                                <div class="col-xs-1"></div>
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <th class="c-box--200 col-xs-2">郵便番号</th>
                        <td class="col-xs-4">
                            <input class="form-control" type="text" name="postal"
                                value="{{ old('postal', $searchRow['postal'] ?? '') }}" />
                            @include('common.elements.error_tag', ['name' => 'postal'])
                        </td>
                        <th class="c-box--200 col-xs-2 bdr-line">対応結果</th>
                        <td class="col-xs-4 bdr-line">
                            <select name="resolution_status" class="form-control c-box--200">
                                <option value=""></option>
                                @foreach ($viewExtendData['resStatus'] as $tableIdName1 => $tableIdValue1)
                                    <option value="{{ $tableIdValue1 }}"
                                        {{ old('resolution_status', isset($searchRow['resolution_status']) ? $searchRow['resolution_status'] : '') == $tableIdValue1 ? 'selected' : '' }}>
                                        {{ $tableIdName1 }}
                                    </option>
                                @endforeach
                            </select>
                            @include('common.elements.error_tag', ['name' => 'resolution_status'])
                        </td>
                    </tr>
                    <tr>
                        <th class="c-box--200 col-xs-2">都道府県</th>
                        <td class="col-xs-4">
                            <select name="address1" class="form-control c-box--200">
                                <option value=""></option>
                                @foreach ($viewExtendData['prefectuals'] as $row)
                                    @php($prefValue = $row['prefectual_name'])
                                    @php($prefName = $row['prefectual_name'])
                                    <option value="{{ $row['prefectual_name'] }}"
                                        {{ old('address1', $searchRow['address1'] ?? '') == $prefValue ? 'selected' : '' }}>
                                        {{ $prefName }}
                                    </option>
                                @endforeach
                            </select>
                            @include('common.elements.error_tag', ['name' => 'address1'])
                        </td>
                    </tr>
                    <tr>
                        <th class="c-box--200 col-xs-2">住所</th>
                        <td class="col-xs-4">
                            <input class="form-control" type="text" name="address2"
                                value="{{ old('address2', $searchRow['address2'] ?? '') }}" />
                            @include('common.elements.error_tag', ['name' => 'address2'])
                        </td>
                    </tr>
                    <tr>
                        <th class="c-box--200 col-xs-2 bdr-line">連絡先その他</th>
                        <td class="col-xs-4 bdr-line">
                            <input class="form-control" name="note"
                                value="{{ old('note', $searchRow['note'] ?? '') }}">
                            @include('common.elements.error_tag', ['name' => 'note'])
                        </td>
                    </tr>
                </tbody>
            </table>
            <button class="btn btn-success btn-lg u-mt--sm" type="submit" name="submit" id="submit_search" value="search">検索</button>
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
                        <th>対応履歴ID</th>
                        <th>@include('common.elements.sorting_column_name', [
                            'columnName' => 'update_timestamp',
                            'columnViewName' => '最新対応日時',
                        ])
                        </th>
                        <th>@include('common.elements.sorting_column_name', [
                            'columnName' => 'receive_datetime',
                            'columnViewName' => '初回受信日時',
                        ]) </th>
                        <th>電話番号</th>
                        <th>@include('common.elements.sorting_column_name', [
                            'columnName' => 'm_cust_id',
                            'columnViewName' => '顧客ID',
                        ]) </th>
                        <th>名前</th>
                        <th>フリガナ</th>
                        <th>タイトル</th>
                        <th>ステータス</th>
                        <th>受信内容</th>
                        <th>受信者</th>
                        <th>最新回答内容</th>
                        <th>回答者</th>
                    </tr>
                    @if (!empty($paginator->count()) > 0)
                        @foreach ($paginator as $custHist)
                            <tr>
                                <td>{{ $custHist->t_cust_communication_id }}</td>
                                <td>
						            <a href="./edit/{{$custHist['t_cust_communication_id']}}">{{ date('Y-m-d H:i:s', strtotime($custHist->update_timestamp)) }}</a>
                                </td>
                                <td>
                                    <a href="./edit/{{$custHist['t_cust_communication_id']}}">{{ date('Y-m-d H:i:s', strtotime($custHist->receive_datetime)) }}</a>
                                </td>
                                <td>{{ $custHist->tel }}</td>
                                <td>
                                    <a href="{{ esm_external_route('cc/cc-customer/info/{customer_id}', ['customer_id' => $custHist->m_cust_id]) }}"
                                        target="_blank">{{ $custHist->m_cust_id }}<i
                                            class="fas fa-external-link-alt"></i></a>
                                </td>
                                <td>{{ $custHist->name_kanji }}</td>
                                <td>{{ $custHist->name_kana }}</td>
                                <td>{{ $custHist->title }}</td>
                                <td>
                                    @foreach ($viewExtendData['statusList'] as $tableIdName1 => $tableIdValue1)
                                        @if (isset($custHist->status) && $custHist->status == $tableIdValue1)
                                            {{ $tableIdName1 }}
                                        @endif
                                    @endforeach
                                </td>
                                <td>{{ $custHist->receive_detail }}</td>
                                <td>
                                    @foreach ($viewExtendData['operatorNameList'] as $tableIdName1 => $tableIdValue1)
                                        @if (isset($custHist->receive_operator_id) && $custHist->receive_operator_id == $tableIdValue1)
                                            {{ $tableIdName1 }}
                                        @endif
                                    @endforeach
                                </td>
                                <td class="position-relative">
                                    @if (isset($custHist->answer_detail) && mb_strlen($custHist->answer_detail) > 30)
                                        <p data-toggle="tooltip" data-placement="top"
                                            title="{{ e($custHist->answer_detail) }}">
                                            {{ mb_substr($custHist->answer_detail, 0, 30) . '…' }}
                                        </p>
                                    @else
                                        {{ $custHist->answer_detail ?? '' }}
                                    @endif

                                </td>
                                <td>
                                    @foreach ($viewExtendData['operatorNameList'] as $tableIdName1 => $tableIdValue1)
                                        @if (isset($custHist->answer_operator_id) && $custHist->answer_operator_id == $tableIdValue1)
                                            {{ $tableIdName1 }}
                                        @endif
                                    @endforeach
                                </td>
                            </tr>
                        @endforeach
                    @else
                        <tr>
                            <td colspan="13">該当顧客対応履歴が見つかりません。</td>
                        </tr>
                    @endif
                </table>
                @include('common.elements.paginator_footer')
                @if($searchResult['search_record_count'] > 0)
                    <button type="button" name="submit_csv_bulk_output" class="btn btn-default" value="csv_bulk_output" onClick="csvBulkExport(event)">データ出力</button>
                @endif
            </div>
        @endif
        <input type="hidden" name="data_key_id" value="{{ $sessionKeyId }}">
    </form>
    @push('css')
        <link rel="stylesheet" href="{{ esm_internal_asset('css/customer/gfh_1207/NECSM0120.css') }}">
    @endpush
    @push('js')
        <script src="{{ esm_internal_asset('js/customer/gfh_1207/NECSM0120.js') }}"></script>
    @endpush
@endsection
