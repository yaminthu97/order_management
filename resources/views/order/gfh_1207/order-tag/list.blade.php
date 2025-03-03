{{-- NEOSM0241:受注タグマスタ検索 --}}
{{-- 画面設定 --}}
@php
$ScreenCd='NEOSM0241';
@endphp

{{-- layout設定 --}}
@extends('common.layouts.default')

{{-- タイトル設定 --}}
@section('title', '受注タグマスタ検索')

{{-- ぱんくず設定 --}}
@section('breadcrumb')
<li>受注タグマスタ検索</li>
@endsection

@section('content')
<div class="u-mt--xs">
    <form enctype="multipart/form-data" method="POST" action="" name="Form1" id="Form1">
        {{ csrf_field() }}

        {{-- 検索入力フォーム --}}
        <table class="table table-bordered c-tbl c-tbl--full">
            <tr>
                <th class="c-box--200">受注タグ名称</th>
                <td>
                    <input type="text" class="form-control c-box--300" name="tag_name" value="{{ old('tag_name', $searchForm['tag_name'] ?? "") }}">
                </td>
                <th class="c-box--200">自動付与タイミング</th>
                <td>
                    <select class="form-control c-box--300" name="auto_timming">
                        <option value=""></option>
                        @foreach ($auto_timming as $auto_timming_value => $auto_timming_label)
                        <option value="{{$auto_timming_value}}" {{ old('deli_stop_flg', isset($searchForm['auto_timming']) ? $searchForm['auto_timming'] : '') == $auto_timming_value ? 'selected' : '' }}>{{$auto_timming_label}}</option>
                        @endforeach
                    </select>               
                </td>
            </tr>

            <tr>
                <th class="c-box--200">進捗停止区分</th>
                <td>
                    <select class="form-control c-box--300" name="deli_stop_flg">
                        <option value=""></option>
                        @foreach ($progress_type as $progress_type_value => $progress_type_label)
                        <option value="{{$progress_type_value}}" {{ old('deli_stop_flg', isset($searchForm['deli_stop_flg']) ? $searchForm['deli_stop_flg'] : '') == $progress_type_value ? 'selected' : '' }}>{{$progress_type_label}}</option>
                        @endforeach
                    </select>               
                </td>
                <th class="c-box--150">説明文</th>
                <td>
                    <input type="text" class="form-control c-box--300" name="tag_context" value="{{ $searchForm['tag_context'] ?? "" }}">
                    
                </td>
            </tr>

        </table>

        <div class="u-mt--sm">
            <button class="btn btn-success btn-lg u-mt--sm js_disabled_button" type="submit" name="submit" id="submit_search" value="search">検索</button>
            &nbsp;
            <button type="button" class="btn btn-default btn-lg u-mt--sm" onClick="location.href='./new'">新規登録</button>
        </div>
        <br>
        @if($paginator)
        <div>
        @include('common.elements.paginator_header')
        @include('common.elements.page_list_count')
        @endif
        <br>
        <table class="table table-bordered c-tbl table-link nowrap">
            <tr>
                <th>表示順</th>
                <th>受注タグ名称</th>
                <th>自動付与タイミング</th>
                <th>色</th>
                <th>進捗停止区分</th>
                <th>説明文</th>
            </tr>
            @if(isset($paginator) && !empty($paginator->count()) > 0)
            @foreach($paginator as $orderTagMaster)

            <tr>
                <td>{{ $orderTagMaster->m_order_tag_sort ?? null }}</a></td>
                <td><a href="./edit/{{$orderTagMaster['m_order_tag_id']}}" >{{ $orderTagMaster->tag_name ?? null }}</a></td>
                <td>{{ $orderTagMaster->displayAutoTimmingName ?? null }}</a></td>
                <td style="background-color:#{{ $orderTagMaster->tag_color }};">
                    <font color="#{{ $orderTagMaster->font_color ?? null }}">
                        @if(blank($orderTagMaster->deli_stop_flg) || $orderTagMaster->deli_stop_flg < 0)
                        {{$orderTagMaster->tag_display_name ?? null}}
                        @else
                        <u>{{$orderTagMaster->tag_display_name ?? null}}</u>
                        @endif
                    </font>
                </td>
                <td>{{ $orderTagMaster->displayDeliStopFlgName ?? null }}</a></td>
                <td >
                    @if(mb_strlen($orderTagMaster->tag_context) > 30)
                        <p data-toggle="tooltip" data-placement="top" title="{{ $orderTagMaster->tag_context }}">{{ $orderTagMaster->tag_context ?? null }}</p>
                    @else
                        {{ $orderTagMaster->tag_context ?? null }}
                    @endif
                </td>
            </tr>
            @endforeach
            @else
            @if (count($searchResult) != 0)
                <tr>
                    <td colspan="6">該当顧客が見つかりません。</td>
                </tr>
            @endif
            @endif
        </table>
        @include('common.elements.paginator_footer')
    </form>
</div>
@endsection