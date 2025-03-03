@php
    $ScreenCd = 'NEMSMC0010';
@endphp

{{-- layout設定 --}}
@extends('common.layouts.default')

{{-- タイトル設定 --}}
@section('title', '社員マスタ検索画面')

{{-- ぱんくず設定 --}}
@section('breadcrumb')
    <li>社員マスタ検索画面</li>
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
        <form enctype="multipart/form-data" method="POST" action="" name="Form1" id="Form1">
            {{ csrf_field() }}

            {{-- 検索入力フォーム --}}
            <table class="table table-bordered c-tbl c-tbl--600">
                <tr>
                    <th class="c-box--150">使用区分</th>
                    <td>
                        @foreach (\App\Enums\DeleteFlg::cases() as $deleteFlg)
                            <label class="checkbox-inline">
                                <input type="checkbox" name="delete_flg[]" value="{{ $deleteFlg->value }}"
                                    @checked(isset($searchRow['delete_flg']) && in_array($deleteFlg->value, $searchRow['delete_flg']))>
                                {{ $deleteFlg->label() }}
                            </label>
                        @endforeach
                    </td>
                </tr>
                <tr>
                    <th class="c-box--150">社員ID</th>
                    <td>
                        <input type="text" class="form-control c-box--300" name="m_operators_id"
                            value="{{ $searchRow['m_operators_id'] ?? '' }}">
                    </td>
                </tr>
                <tr>
                    <th class="c-box--150">社員名</th>
                    <td>
                        <input type="text" class="form-control c-box--300" name="m_operator_name"
                            value="{{ $searchRow['m_operator_name'] ?? '' }}">
                        <label class="checkbox-inline"><input type="checkbox" name="m_operator_name_fuzzy_search_flg"
                                value="1" @if (!empty($searchRow['m_operator_name_fuzzy_search_flg'])) checked @endif>あいまい検索</label>
                    </td>
                </tr>
            </table>
            <div class="u-mt--sm">
                <button class="btn btn-success btn-lg u-mt--sm" type="submit" name="submit" id="submit_search"
                    value="search">検索</button>
                <button class="btn btn-default btn-lg u-mt--sm ml-20" type="button" name="submit"
                    id="submit_new"value="new" onClick="location.href='{{ route('master.operators.new') }}'">新規登録</button>
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
                        <th>@include('common.elements.sorting_column_name', [
                            'columnName' => 'm_operators_id',
                            'columnViewName' => 'ID',
                        ])</th>
                        <th>社員名</th>
                        <th>ユーザ種類</th>
                        <th>操作権限</th>
                    </tr>
                    @if (isset($paginator) && $paginator->count() > 0)
                        @foreach ($paginator as $operators)
                            <tr>
                                <td>
                                    @if (isset($operators->delete_flg) && $operators->delete_flg == \App\Enums\DeleteFlg::Use->value)
                                        {{ \App\Enums\DeleteFlg::Use->label() }}
                                    @else
                                        {{ \App\Enums\DeleteFlg::Notuse->label() }}
                                    @endif
                                </td>
                                <td>
                                    <a
                                        href="{{ route('master.operators.edit', ['id' => $operators->m_operators_id]) }}">{{ $operators['m_operators_id'] ?? null }}</a>
                                </td>
                                <td>
                                    {{ Str::limit($operators->m_operator_name, 60) }}
                                </td>
                                <td>
                                    @foreach (\App\Modules\Master\Gfh1207\Enums\UserTypeEnum::cases() as $userType)
                                        @if (isset($operators['user_type']) && $operators['user_type'] == $userType->value)
                                            {{ $userType->label() }}
                                        @endif
                                    @endforeach
                                </td>
                                <td>
                                    {{ $operators->authorityName?->m_operation_authority_name ?? null }}
                                </td>
                            </tr>
                        @endforeach
                    @else
                        <tr>
                            <td colspan="5">{{ Config::get('Common.const.PageHeader.NoResultsMessage') }}</td>
                            <input type="hidden" name="page_list_count"
                                value="{{ config('esm.default_page_size.master') }}">
                        </tr>
                    @endif
                </table>
            </div>
            @include('common.elements.paginator_footer')
        </form>
    </div>
@endsection
