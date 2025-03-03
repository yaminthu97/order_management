{{-- GFOSMH0010:出荷一覧 --}}
@php
    $ScreenCd = 'GFOSMH0010';
@endphp

{{-- layout設定 --}}
@extends('common.layouts.default')

{{-- タイトル設定 --}}
@section('title', '出荷一覧')

{{-- ぱんくず設定 --}}
@section('breadcrumb')
    <li>出荷一覧</li>
@endsection

@section('content')
    <form enctype="multipart/form-data" method="POST" action="" name="Form1" id="Form1">
        {{ csrf_field() }}

        <div>

            <table class="table table-bordered c-tbl c-tbl--1180">
                <tr>
                    <th>出荷連携状態</th>
                    <td colspan="5">
                        @foreach(\App\Enums\ShipmentLinkageStatusEnum::cases() as $linkageStatus)
                            <label class="checkbox-inline">
                                <input type="checkbox" name="cooperation_status[]" value="{{ $linkageStatus->value }}"
                                @checked(isset($searchForm['cooperation_status']) && in_array($linkageStatus->value, $searchForm['cooperation_status']))>
                                {{ $linkageStatus->label() }}
                            </label>
                        @endforeach
                    </td>
                </tr>
                <tr>
                    <th>受注日</th>
                    <td>
                        <div class="c-box--218 d-table-cell">
                            <div class='input-group date datetime-picker'>
                                <input type="text" class="form-control c-box--180" name="order_date_from" id="order_date_from" value="{{ isset($searchForm['order_date_from']) ? $searchForm['order_date_from'] : '' }}">
                                <span class="input-group-addon">
                                    <span class="glyphicon glyphicon-calendar"></span>
                                </span>
                            </div>
                        </div>
                        &nbsp;～&nbsp;
                        <div class='c-box--218 d-table-cell'>
                            <div class='input-group date datetime-picker'>
                                <input type="text" class="form-control c-box--180" name="order_date_to" id="order_date_to" value="{{ isset($searchForm['order_date_to']) ? $searchForm['order_date_to'] : '' }}">
                                <span class="input-group-addon">
                                    <span class="glyphicon glyphicon-calendar"></span>
                                </span>
                            </div>
                        </div>
                    </td>
                    <th>出荷予定日</th>
                    <td>
                        <div class="c-box--218 d-table-cell">
                            <div class='input-group date datetime-picker'>
                                <input type="text" class="form-control c-box--180" name="deli_plan_date_from" id="deli_plan_date_from" value="{{ isset($searchForm['deli_plan_date_from']) ? $searchForm['deli_plan_date_from'] : '' }}">
                                <span class="input-group-addon">
                                    <span class="glyphicon glyphicon-calendar"></span>
                                </span>
                            </div>
                        </div>
                        &nbsp;～&nbsp;
                        <div class='c-box--218 d-table-cell'>
                            <div class='input-group date datetime-picker'>
                                <input type="text" class="form-control c-box--180" name="deli_plan_date_to" id="deli_plan_date_to" value="{{ isset($searchForm['deli_plan_date_to']) ? $searchForm['deli_plan_date_to'] : '' }}">
                                <span class="input-group-addon">
                                    <span class="glyphicon glyphicon-calendar"></span>
                                </span>
                            </div>
                        </div>
                    </td>
                    <th>受注ID</th>
                    <td>
                        <div class="c-box--218 d-table-cell">
                            <input class="form-control u-input--mid" type="text" name="order_id_from"
                                value="{{ $searchForm['order_id_from'] ?? '' }}">
                        </div>
                        &nbsp;～&nbsp;
                        <div class="c-box--218 d-table-cell">
                        <input class="form-control u-input--mid" type="text" name="order_id_to"
                            value="{{ $searchForm['order_id_to'] ?? '' }}">
                        </div>
                    </td>
                </tr>
                <tr>
                    <th>商品コード</th>
                    <td>
                        <input class="form-control" type="text" name="item_cd"
                            value="{{ $searchForm['item_cd'] ?? '' }}">
                    </td>
                    <th>店舗集計グループ</th>
                    <td>
                        @php
                        @endphp
                        <select name="store_group" class="form-control c-box--200">
                            <option value=""></option>
                            @foreach ($storeGroups as $key => $value)
                                <option value="{{ $key }}"
                                    @selected(isset($searchForm['store_group']) && $searchForm['store_group'] == $value) >
                                    {{ $value }}</option>
                            @endforeach
                        </select>
                    </td>
                    <th>受注方法</th>
                    <td>
                        <select name="order_type" class="form-control c-box--200">
                            <option value=""></option>
                            @foreach ($orderTypes as $orderType)
                                <option value="{{ $orderType["m_itemname_types_id"] }}"
                                    @selected(isset($searchForm['order_type']) && $searchForm['order_type'] == $orderType["m_itemname_types_id"]) >
                                    {{ $orderType["m_itemname_type_name"] }}</option>
                            @endforeach
                        </select>
                    </td>
                </tr>
                <tr>
                    <th>注文主ID</th>
                    <td>
                        <input class="form-control" type="text" name="cust_id"
                            value="{{ $searchForm['cust_id'] ?? '' }}">
                    </td>
                    <th>注文主氏名</th>
                    <td>
                        <input class="form-control" type="text" name="cust_name"
                            value="{{ $searchForm['cust_name'] ?? '' }}">
                    </td>
                    <th>配送先氏名</th>
                    <td>
                        <input class="form-control" type="text" name="deli_name"
                            value="{{ $searchForm['deli_name'] ?? '' }}">
                    </td>
                </tr>
            </table>

            <input type="hidden" name="hdnDispmode">
            <div class="c-btn--02 u-mt--xs"><a data-toggle="collapse" href="#collapse-menu" class="{{ $collapsed ? 'collapsed' : '' }}" onclick="kirikae()">詳細検索</a></div>

            <div class="collapse{{ $collapsed ? '' : ' in' }}" id="collapse-menu">
                <div class="c-box--850Half u-mt--xs">
                    <table class="table table-bordered c-tbl c-tbl--1180">
                        <tr>
                            <th>出荷ステータス</th>
                            <td colspan="5">
                                @foreach(\App\Enums\ShipmentStatusEnum::cases() as $shipmentstatus)
                                    <label class="checkbox-inline">
                                        <input type="checkbox" name="gp2_type[]" value="{{ $shipmentstatus->value }}"
                                        @checked(isset($searchForm['gp2_type']) && in_array($shipmentstatus->value, $searchForm['gp2_type']))>
                                        {{ $shipmentstatus->label() }}
                                    </label>
                                @endforeach
                            </td>
                        </tr>
                        <tr>
                            <th>登録者</th>
                            <td>
                                <select name="entry_operator_id" class="form-control c-box--200">
                                    <option value=""></option>
                                    @foreach ($operators as $operator)
                                        <option value="{{ $operator["m_operators_id"] }}"
                                            @selected(isset($searchForm['entry_operator_id']) && $searchForm['entry_operator_id'] == $operator["m_operators_id"]) >
                                            {{ $operator["m_operator_name"] }}</option>
                                    @endforeach
                                </select>
                            </td>
                            <th>送り状番号</th>
                            <td rowspan="3">
                                <input class="form-control" type="text" name="invoice_num"
                                    value="{{ $searchForm['invoice_num'] ?? '' }}">
                            </td>
                        </tr>
                    </table>
                </div><!-- /1200 -->
            </div>


            <input type="hidden" name="should_paginate" value="{{\App\Enums\ShouldPaginate::YES->value}}">
            <input type="hidden" name="limit" value="{{ isset($searchForm['limit'])? $searchForm['limit']:config('esm.default_page_size.cc')}}">
            <button class="btn btn-success btn-lg u-mt--xs" type="submit" name="submit_" id="submit_search" value="search">検索</button>
            &nbsp;
            <input type="hidden" name="{{ config('define.session_key_id') }}"
                value="{{ $searchForm[config('define.session_key_id')] ?? '' }}">

        </div>
        <br>
        @isset($shipments)
            <input type="hidden" name="sorting_column" id="sorting_column" value="{{ isset($searchForm['sorting_column']) ? $searchForm['sorting_column'] : '' }}">
            <input type="hidden" name="sorting_shift" id="sorting_shift" value="{{ isset($searchForm['sorting_shift']) ? $searchForm['sorting_shift'] : '' }}">
            <script>
                function setNextSort(sortColumn, sortShift)
                {
                    document.getElementById("sorting_column").value = sortColumn;

                    document.getElementById("sorting_shift").value = sortShift;

                    document.Form1.submit();

                    return false;
                }
            </script>
            <div>
                <x-common.paginator-head
                    :paginator="$shipments"
                />
                @if ($shipments->count() > 0)
                    <x-common.page-list-count
                        :pageListCount="$searchForm['page_list_count'] ?? null"
                    />
                @endif
                <table class="table table-bordered c-tbl table-link nowrap u-mt--sm">
                    <tr>
                        <th>
                          @if($shipments->count() > 0)
                            <x-common.sorting-link
                                columnName="t_order_hdr_id"
                                :columnViewName="'受注ID'"
                                :sortColumn="$searchForm['sorting_column'] ?? null"
                                :sortShift="$searchForm['sorting_shift'] ?? null"
                                />
                            @else
                                受注ID
                            @endif
                        </th>
                        <th>
                            @if($shipments->count() > 0)
                                <x-common.sorting-link
                                    columnName="t_order_destination_id"
                                    :columnViewName="'配送ID'"
                                    :sortColumn="$searchForm['sorting_column'] ?? null"
                                    :sortShift="$searchForm['sorting_shift'] ?? null"
                                    />
                            @else
                                配送ID
                            @endif
                        </th>
                        <th>配No</th>
                        <th>
                            @if($shipments->count() > 0)
                                <x-common.sorting-link
                                    columnName="order_datetime"
                                    :columnViewName="'受注日時'"
                                    :sortColumn="$searchForm['sorting_column'] ?? null"
                                    :sortShift="$searchForm['sorting_shift'] ?? null"
                                    />
                            @else
                                受注日時
                            @endif
                        </th>
                        <th>
                            @if($shipments->count() > 0)
                                <x-common.sorting-link
                                    columnName="deli_plan_date"
                                    :columnViewName="'出荷予定日'"
                                    :sortColumn="$searchForm['sorting_column'] ?? null"
                                    :sortShift="$searchForm['sorting_shift'] ?? null"
                                    />
                            @else
                                出荷予定日
                            @endif
                        </th>
                        <th>配送希望日</th>
                        <th>
                            @if($shipments->count() > 0)
                                <x-common.sorting-link
                                    columnName="deli_inspection_date"
                                    :columnViewName="'検品日'"
                                    :sortColumn="$searchForm['sorting_column'] ?? null"
                                    :sortShift="$searchForm['sorting_shift'] ?? null"
                                    />
                            @else
                                検品日
                            @endif
                        </th>
                        <th>注文主</th>
                        <th>配送先</th>
                        <th>進捗区分</th>
                        <th>出荷ステータス</th>
                        <th>送り状番号</th>
                    </tr>
                    @if (!empty($shipments->count()) > 0)
                        @foreach ($shipments as $shipment)
                            <tr>
                                <td>
                                    <x-common.output-check-box
                                        name="csv_output_check_key_id"
                                        :keyValue="$shipment->t_order_destination_id"
                                    />
                                    &nbsp;
                                    <a href="{{route('order.order.edit', ['id' => $shipment->t_order_hdr_id])}}" target="_blank">
                                        {{ $shipment->t_order_hdr_id }}<i class="fas fa-external-link-alt"></i>
                                </td>
                                <td>
                                    <a href="{{route('order.order.edit', ['id' => $shipment->t_order_hdr_id])}}" target="_blank">
                                        {{ $shipment->t_order_destination_id }}<i class="fas fa-external-link-alt"></i>
                                </td>
                                <td>{{ $shipment->order_destination_seq }}</td>
                                <td>{{ $shipment->order_datetime }}</td>
                                <td>{{ $shipment->deli_plan_date }}</td>
                                <td>{{ $shipment->deli_hope_date }}</td>
                                <td>{{ $shipment->deli_inspection_date  ?? ''}}</td>
                                <td>{{ $shipment->order_name }}</td>
                                <td>{{ $shipment->destination_name }}</td>
                                <td>{{ \App\Enums\ProgressTypeEnum::tryfrom( $shipment->progress_type ) ? \App\Enums\ProgressTypeEnum::tryfrom( $shipment->progress_type )->label() : '' }}</td>
                                <td>{{ \App\Enums\ShipmentStatusEnum::tryfrom( $shipment->gp2_type ) ? \App\Enums\ShipmentStatusEnum::tryfrom( $shipment->gp2_type )->label() : '' }}</td>
                                <td>
                                    @foreach ($shipment->shippingLabels as $shippingLabel)
                                        <a href="http://jizen.kuronekoyamato.co.jp/jizen/servlet/crjz.b.NQ0010?id={{ $shippingLabel->shipping_label_number }}" target="_blank">{{ $shippingLabel->shipping_label_number }}
                                        <i class="fas fa-external-link-alt"></i></a>
                                    @endforeach
                                </td>
                            </tr>
                        @endforeach
                    @else
                        @if ($shipments->count() == 0)
                            <tr>
                                <td colspan="12">該当出荷一覧が見つかりません。</td>
                            </tr>
                        @endif
                    @endif
                </table>
                <x-common.paginator-foot
                    :paginator="$shipments"
                />
                <table class="table table-bordered c-tbl c-tbl--1180">
                    <tr>
                        <th>操作対象</th>
                        <td>
                            <label class="radio-inline"><input type="radio" id="bulk_target_type" name="bulk_target_type" value="1" checked=""> 選択データを対象</label>
                            <label class="radio-inline"><input type="radio" id="bulk_target_type" name="bulk_target_type" value="2"> 検索データを対象</label>
                        </td>
                    </tr>
                    <tr>
                    <th>出荷連携</th>
                        <td>
                            <input type="submit" name="submit_shipping_order_csv" class="btn btn-default" value="実行">
                            @include('common.elements.error_tag', ['name' => 'shipping_order_csv'])
                        </td>
                    </tr>
                </table>

            </div>
        @endisset
    </form>
@endsection
