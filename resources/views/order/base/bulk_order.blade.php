{{-- GFOSMG0010:大口注文取込 --}}
{{-- 画面設定 --}}
@php
$ScreenCd='GFOSMG0010';
@endphp

{{-- layout設定 --}}
@extends('common.layouts.default')

{{-- タイトル設定 --}}
@section('title', '大口注文取込')

{{-- ぱんくず設定 --}}
@section('breadcrumb')
<li>大口注文取込</li>
@endsection
@section('content')
@session('messages.error.exception_message')
    <span class="font-FF0000">{{ $value }}</span>
@endsession
<form enctype="multipart/form-data" method="POST" action="" name="Form1" id="Form1">
    {{ csrf_field() }}
    <div class="d-table c-box--1200">
        <div class="c-box--800">
            <p class="c-ttl--02">大口注文取込</p>
            <table class="table c-tbl c-tbl--800">
                <tr>
                    <th class="c-box--200">受注/顧客データ</th>
                    <td>
                        <div class="u-p--ss">
                            
                            <div class="u-pr--ss d-table-cell u-vam">
                                <input type="file" class="u-ib" id="bulk_order_file" name="bulk_order_file" form="Form1" accept=".xls, .xlsx">
                            </div>
                        </div>
                    </td>
                </tr>
                <tr>
                    <th class="c-box--200">&nbsp;</th>
                    <td>
                        <div class="u-p--ss">
                            <button class="btn btn-success btn-lg" type="submit" name="submit" id="submit_bulk_order" value="submit_bulk_order">実行</button>
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
@endpush
@endsection
