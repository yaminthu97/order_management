{{-- NECSM0114:顧客照会 --}}
@php
    $ScreenCd = 'GFOSMF0010';
@endphp

{{-- layout設定 --}}
@extends('common.layouts.default')

{{-- タイトル設定 --}}
@section('title', 'EC受注')

{{-- ぱんくず設定 --}}
@section('breadcrumb')
    <li>EC受注</li>
@endsection

@section('content')
@session('messages.error.exception_message')
    <span class="font-FF0000">{{ $value }}</span>
@endsession
<form enctype="multipart/form-data" method="POST" action="" name="Form1" id="Form1">
    {{ csrf_field() }}
    <div class="d-table c-box--1200">
        <div class="c-box--800">
            <p class="c-ttl--02">顧客・受注取込</p>

            <table class="table c-tbl c-tbl--800">
                <tr>
                    <th class="c-box--200">処理タイプ</th>
                    <td>
                        <div class="u-p--ss">
                            @foreach( $orderCustomerRunTypeEnum::cases() as $runType )
                            <label class="radio-inline"><input type="radio" id="import_type_{{ $runType->value }}" name="import_type" value="{{ $runType->value }}" @if( isset($searchRow['import_type']) && $searchRow['import_type'] == $runType->value ) checked @endif> {{ $runType->label() }}</label>
                            @endforeach
                        </div>
                    </td>
                </tr>
                <tr>
                    <th class="c-box--200">受注データ取込</th>
                    <td>
                        <div class="u-p--ss">
                            <div class="u-pr--ss d-table-cell u-vam c-box--200">
                                <input type="checkbox" name="order_input" id="order_input" value="1" @if( isset($searchRow['order_input']) && $searchRow['order_input'] == 1 ) checked @endif>
                                <label for="order_input">受注データ取込</label>
                            </div>
                            
                            <div class="u-pr--ss d-table-cell u-vam">
                                <input type="file" class="u-ib" id="order_input_file" name="order_input_file" form="Form1" style="visibility:hidden;">
                            </div>
                        </div>
                    </td>
                </tr>
                <tr>
                    <th class="c-box--200">顧客データ取込</th>
                    <td>
                        <div class="u-p--ss">
                            <div class="u-pr--ss d-table-cell u-vam c-box--200">
                                <input type="checkbox" name="customer_input" id="customer_input" value="1" @if( isset($searchRow['customer_input']) && $searchRow['customer_input'] == 1 ) checked @endif>
                                <label for="customer_input">顧客データ取込</label>
                            </div>
                            
                            <div class="u-pr--ss d-table-cell u-vam">
                                <input type="file" class="u-ib" id="customer_input_file" name="customer_input_file" form="Form1" style="visibility:hidden;">
                            </div>
                        </div>
                    </td>
                </tr>
                <tr>
                    <th class="c-box--200">&nbsp;</th>
                    <td>
                        <div class="u-p--ss">
                            <button class="btn btn-success btn-lg" type="submit" name="submit" id="submit_import" value="import">実行</button>
                        </div>
                    </td>
                </tr>
            </table>

            <p class="c-ttl--02 u-mt--mm">出荷確定データ出力、入金・受注修正データ出力</p>

            <table class="table c-tbl c-tbl--800">
                <tr>
                    <th class="c-box--200">処理タイプ</th>
                    <td>
                        <div class="u-p--ss">
                            @foreach( $shipNyukinRunTypeEnum::cases() as $runType )
                            <label class="radio-inline"><input type="radio" id="export_type_{{ $runType->value }}" name="export_type" value="{{ $runType->value }}" @if( isset($searchRow['export_type']) && $searchRow['export_type'] == $runType->value ) checked @endif> {{ $runType->label() }}</label>
                            @endforeach
                        </div>
                    </td>
                </tr>
                <tr>
                    <th class="c-box--200">出荷確定データ出力</th>
                    <td>
                        <div class="u-p--ss">
                            <div class="u-pr--ss d-table-cell u-vam c-box--200">
                                <input type="checkbox" name="ship_output" id="ship_output" value="1" @if( isset($searchRow['ship_output']) && $searchRow['ship_output'] == 1 ) checked @endif>
                                <label for="ship_output">出荷確定データ出力</label>
                            </div>
                            
                            <div class="u-pr--ss d-table-cell u-vam">
                                <input type="file" class="u-ib" id="ship_output_file" name="ship_output_file" form="Form1" style="visibility:hidden;">
                            </div>
                        </div>
                    </td>
                </tr>
                <tr>
                    <th class="c-box--200">入金・受注修正データ出力</th>
                    <td>
                        <div class="u-p--ss">
                            <div class="u-pr--ss d-table-cell u-vam c-box--200">
                                <input type="checkbox" name="nyukin_output" id="nyukin_output" value="1" @if( isset($searchRow['nyukin_output']) && $searchRow['nyukin_output'] == 1 ) checked @endif>
                                <label for="nyukin_output">入金・受注修正データ出力</label>
                            </div>
                            
                            <div class="u-pr--ss d-table-cell u-vam">
                                <input type="file" class="u-ib" id="nyukin_output_file" name="nyukin_output_file" form="Form1" style="visibility:hidden;">
                            </div>
                        </div>
                    </td>
                </tr>
                <tr>
                    <th class="c-box--200">対象検品日</th>
                    <td>
                        <div class="u-p--ss">
                            <div class="c-box--218">
                                <div class="input-group date date-picker">
                                    <input type="text" class="form-control c-box--180" id="inspection_date" name="inspection_date" value="{{ isset($searchRow['inspection_date']) ? $searchRow['inspection_date'] : '' }}">
                                    <span class="input-group-addon">
                                        <span class="glyphicon glyphicon-calendar"></span>
                                    </span>
                                </div>
                            </div>
                        </div>
                    </td>
                </tr>
                <tr>
                    <th class="c-box--200">&nbsp;</th>
                    <td>
                        <div class="u-p--ss">
                            <button class="btn btn-success btn-lg" type="submit" name="submit" id="submit_export" value="export">実行</button>
                        </div>
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
<script src="{{ esm_internal_asset('js/order/gfh_1207/GFOSMF0010.js') }}"></script>
@endpush
@endsection
