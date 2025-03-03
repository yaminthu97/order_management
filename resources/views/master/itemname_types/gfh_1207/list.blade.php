@php
    $ScreenCd = 'NEMSMA0010';
@endphp

{{-- layout設定 --}}
@extends('common.layouts.default')

{{-- タイトル設定 --}}
@section('title', '項目名称マスタ検索画面')

{{-- ぱんくず設定 --}}
@section('breadcrumb')
<li>項目名称マスタ検索画面</li>
@endsection

@section('content') 

<div class="u-mt--xs">
    <form enctype="multipart/form-data" method="POST" action="" name="Form1" id="Form1">
        {{ csrf_field() }}

        {{-- 検索入力フォーム --}}
        <table class="table table-bordered c-tbl c-tbl--600">
            <tr>
                <th class="c-box--150">使用区分</th>
                <td>
                    @foreach ($deleteFlg as $delete_value => $delete_label) 
                    <label class="label-margin">
                        <input class="input-margin" type="checkbox" name="delete_flg[]" value="{{ $delete_value }}"
                        @checked(isset($searchForm['delete_flg']) && in_array($delete_value, $searchForm['delete_flg']))>
                        {{ $delete_label }}
                    </label>
                    @endforeach
                </td>
            </tr>

            <tr>
                <th class="c-box--150">項目種類</th>
                <td>
                    @foreach ($itemnameTypes as $itemname_type_value => $itemname_type_label)
                    <label class="label-margin">
                        <input class="input-margin" type="checkbox" name="m_itemname_type[]" value="{{ $itemname_type_value }}"
                        @checked(isset($searchForm['m_itemname_type']) && in_array($itemname_type_value, $searchForm['m_itemname_type']))>
                        {{ $itemname_type_label }}
                    </label>
                    @endforeach
                </td>
            </tr>

            <tr>
                <th class="c-box--150">項目名</th>
                <td>
                    <input type="text" class="form-control c-box--300 label-margin" name="m_itemname_type_name" value="{{ $searchForm['m_itemname_type_name'] ?? "" }}">
                    <label class="label-margin"><input class="input-margin" type="checkbox" name="m_itemname_type_name_fuzzy_search_flg" value="{{\App\Enums\FuzzyType::ON->value}}" @checked(!empty($searchForm['m_itemname_type_name_fuzzy_search_flg']))>{{\App\Enums\FuzzyType::ON->label()}}</label>
                </td>
            </tr>
        </table>

        <div class="u-mt--sm">
            <button class="btn btn-success btn-lg u-mt--sm js_disabled_button" type="submit" name="submit" id="submit_search" value="search">検索</button>&nbsp;<button type="button" class="btn btn-default btn-lg u-mt--sm" onClick="location.href='./new'">新規登録</button>
        </div>
    

    {{-- 検索結果表示フォーム --}}
    <div class="u-mt--sm">
    @include('common.elements.paginator_header')
    @include('common.elements.page_list_count')
    <br>

    @include('common.elements.sorting_script')
    <table class="table table-bordered c-tbl link-style" name="searchResults">
        <tr>
            <th>使用区分</th>
            <th>@include('common.elements.sorting_column_name', ['columnName' => 'm_itemname_types_id', 'columnViewName' => 'ID'])</th>
            <th>@include('common.elements.sorting_column_name', ['columnName' => 'm_itemname_type', 'columnViewName' => '項目種類'])</th>
            <th>項目名</th>
            <th>@include('common.elements.sorting_column_name', ['columnName' => 'm_itemname_type_sort', 'columnViewName' => '並び順'])</th>
        </tr>
        @if(isset($paginator) && $paginator->count() > 0) 
        @foreach($paginator as $itemnameTypes) 
            <tr>
                <td>{{ $itemnameTypes->displayDeleteFlg ?? null }}</td>
                <td><a href='./edit/{{$itemnameTypes->m_itemname_types_id}}'>{{$itemnameTypes['m_itemname_types_id'] ?? null}}</a></td>
                <td>{{ $itemnameTypes->displayItemnameType ?? null }}</td>
                <td>{{ $itemnameTypes->m_itemname_type_name ?? null}}</td>
                <td>{{ $itemnameTypes->m_itemname_type_sort ?? null }}</a></td>
            </tr>
        @endforeach
        @else 
            <tr>
                <td colspan="5">{{Config::get('Common.const.PageHeader.NoResultsMessage') }}</td>
                <input type="hidden" name="page_list_count" value="{{config('esm.default_page_size.master')}}">
            </tr>
        @endif
    </table>
    </div>
    @include('common.elements.paginator_footer')
    </form>
    @push('css')
    <link rel="stylesheet" href="{{ esm_internal_asset('css/master/gfh_1207/NEMSMA0010.css') }}">
    @endpush
</div>
@endsection
