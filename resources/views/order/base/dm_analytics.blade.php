{{-- layout設定 --}}
@extends('common.layouts.default')

{{-- タイトル設定 --}}
@section('title', 'DM集計取込')

{{-- ぱんくず設定 --}}
@section('breadcrumb')
<li>DM集計取込</li>
@endsection
@section('content')
@session('messages.error.exception_message')
    <span class="font-FF0000">{{ $value }}</span>
@endsession
<form enctype="multipart/form-data" method="POST" action="output" name="Form1" id="Form1">
    {{ csrf_field() }}
    <div class="d-table c-box--1200">
        <div class="c-box--800">
            <p class="c-ttl--02">DM集計取込</p>
            <table class="table c-tbl c-tbl--800">
                <tr>
                    <th class="must c-box--200">顧客データ</th>
                    <td>
                        <div class="u-p--ss">
                            <div class="u-pr--ss d-table-cell u-vam">
                                <input type="file" class="u-ib" id="customer_data" name="customer_data" form="Form1" accept=".xls, .xlsx">
                            </div>
                        </div>
                    </td>
                </tr>
                <tr>
                    <th class="must c-box--200">受注日FROM</th>
                    <td>
                        <div class="input-group date date-picker c-box--180">
                            <input type="text" class="form-control" id="order_date_from" name="order_date_from" value="{{ isset($searchRow['order_date_from']) ? $searchRow['order_date_from'] : '' }}">
                            <span class="input-group-addon">
                                <span class="glyphicon glyphicon-calendar"></span>
                            </span>
                        </div>
                    </td>
                </tr>
                <tr>
                    <th class="must c-box--200">受注日TO</th>
                    <td>
                        <div class="input-group date date-picker c-box--180">
                            <input type="text" class="form-control" id="order_date_to" name="order_date_to" value="{{ isset($searchRow['order_date_to']) ? $searchRow['order_date_to'] : '' }}">
                            <span class="input-group-addon">
                                <span class="glyphicon glyphicon-calendar"></span>
                            </span>
                        </div>
                    </td>
                </tr>
                <tr>
                    <th class="c-box--200">注文方法</th>
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
                    <th class="c-box--200">商品コード1</th>
                    <td>
                        <div class="input-group">
                            <input type="text" class="form-control c-box--600" id="sell_cd_1" name="sell_cd_1" value="{{ isset($searchRow['sell_cd_1']) ? $searchRow['sell_cd_1'] : '' }}">
                        </div>
                    </td>
                </tr>
                <tr>
                    <th class="c-box--200">商品コード2</th>
                    <td>
                        <div class="input-group">
                            <input type="text" class="form-control c-box--600" id="sell_cd_2" name="sell_cd_2" value="{{ isset($searchRow['sell_cd_2']) ? $searchRow['sell_cd_2'] : '' }}">
                        </div>
                    </td>
                </tr>
                <tr>
                    <th class="c-box--200">商品コード3</th>
                    <td>
                        <div class="input-group">
                            <input type="text" class="form-control c-box--600" id="sell_cd_3" name="sell_cd_3" value="{{ isset($searchRow['sell_cd_3']) ? $searchRow['sell_cd_3'] : '' }}">
                        </div>
                    </td>
                </tr>
                <tr>
                    <th class="c-box--200">商品コード4</th>
                    <td>
                        <div class="input-group">
                            <input type="text" class="form-control c-box--600" id="sell_cd_4" name="sell_cd_4" value="{{ isset($searchRow['sell_cd_4']) ? $searchRow['sell_cd_4'] : '' }}">
                        </div>
                    </td>
                </tr>
                <tr>
                    <th class="c-box--200">商品コード5</th>
                    <td>
                        <div class="input-group">
                            <input type="text" class="form-control c-box--600" id="sell_cd_5" name="sell_cd_5" value="{{ isset($searchRow['sell_cd_5']) ? $searchRow['sell_cd_5'] : '' }}">
                        </div>
                    </td>
                </tr>
                <tr>
                    <th class="c-box--200">商品コード6</th>
                    <td>
                        <div class="input-group">
                            <input type="text" class="form-control c-box--600" id="sell_cd_6" name="sell_cd_6" value="{{ isset($searchRow['sell_cd_6']) ? $searchRow['sell_cd_6'] : '' }}">
                        </div>
                    </td>
                </tr>
                <tr>
                    <th class="c-box--200">商品コード7</th>
                    <td>
                        <div class="input-group">
                            <input type="text" class="form-control c-box--600" id="sell_cd_7" name="sell_cd_7" value="{{ isset($searchRow['sell_cd_7']) ? $searchRow['sell_cd_7'] : '' }}">
                        </div>
                    </td>
                </tr>
                <tr>
                    <th class="c-box--200">商品コード8</th>
                    <td>
                        <div class="input-group">
                            <input type="text" class="form-control c-box--600" id="sell_cd_8" name="sell_cd_8" value="{{ isset($searchRow['sell_cd_8']) ? $searchRow['sell_cd_8'] : '' }}">
                        </div>
                    </td>
                </tr>
                <tr>
                    <th class="c-box--200">商品コード9</th>
                    <td>
                        <div class="input-group">
                            <input type="text" class="form-control c-box--600" id="sell_cd_9" name="sell_cd_9" value="{{ isset($searchRow['sell_cd_9']) ? $searchRow['sell_cd_9'] : '' }}">
                        </div>
                    </td>
                </tr>
                <tr>
                    <th class="c-box--200">商品コード10</th>
                    <td>
                        <div class="input-group">
                            <input type="text" class="form-control c-box--600" id="sell_cd_10" name="sell_cd_10" value="{{ isset($searchRow['sell_cd_10']) ? $searchRow['sell_cd_10'] : '' }}">
                        </div>
                    </td>
                </tr>

                <tr>
                    <th class="c-box--200">&nbsp;</th>
                    <td>
                        <button class="btn btn-success btn-lg" type="submit" name="submit" id="submit_dm_analytics" value="submit_dm_analytics">出力</button>
                    </td>
                </tr>
            </table>
        </div>
    </div>
</form>
@include('common.elements.datetime_picker_script')
@push('css')
<link rel="stylesheet" href="{{ esm_internal_asset('css/order/gfh_1207/app.css') }}">
@endpush
@push('js')
<script src="{{ esm_internal_asset('js/order/gfh_1207/app.js') }}"></script>
@endpush
@endsection
