{{-- NEMSMH0010:倉庫マスタ検索画面 --}}
@php
    $ScreenCd = 'NEMSMH0010';
@endphp

{{-- layout設定 --}}
@extends('common.layouts.default')

{{-- タイトル設定 --}}
@section('title', '倉庫マスタ検索画面')

{{-- ぱんくず設定 --}}
@section('breadcrumb')
    <li>
        倉庫マスタ検索画面
    </li>
@endsection

@section('content')
    <div class="u-mt--xs">
        <form enctype="multipart/form-data" method="POST" action="" name="Form1" id="Form1">
            {{ csrf_field() }}
            <table class="table table-bordered c-tbl c-tbl--600">
                <tr>
                    <th class="c-box--150">使用区分</th>
                    <td>
                        @foreach (\App\Enums\DeleteFlg::cases() as $deleteFlg)
                            <label class="checkbox-inline">
                                <input type="checkbox" name="delete_flg[]" value="{{ $deleteFlg->value }}"
                                    @checked(
                                        ($loop->first && empty($searchRow['delete_flg'])) ||
                                            (isset($searchRow['delete_flg']) && in_array($deleteFlg->value, $searchRow['delete_flg'])))>
                                {{ $deleteFlg->label() }}
                            </label>
                        @endforeach
                    </td>
                </tr>
                <tr>
                    <th class="c-box--150">倉庫名</th>
                    <td>
                        <input class="form-control c-box--300" type="text" name="m_warehouse_name"
                            value="{{ old('m_warehouse_name', $searchRow['m_warehouse_name'] ?? '') }}" />
                        @include('common.elements.error_tag', ['name' => 'm_warehouse_name'])
                        <label class="checkbox-inline"><input type="checkbox" name="m_warehouse_name_fuzzy_search_flg"
                                value="1" @if (!empty($searchRow['m_warehouse_name_fuzzy_search_flg'])) checked @endif>あいまい検索</label>
                    </td>
                </tr>
                <tr>
                    <th class="c-box--150">引当倉庫有効フラグ</th>
                    <td>
                        @foreach (\App\Modules\Master\Gfh1207\Enums\PriorityFlg::cases() as $priorityFlg)
                            <label class="checkbox-inline">
                                <input type="checkbox" name="priority_flg[]" value="{{ $priorityFlg->value }}"
                                    @checked(isset($searchRow['priority_flg']) && in_array($priorityFlg->value, $searchRow['priority_flg']))>
                                {{ $priorityFlg->label() }}
                            </label>
                        @endforeach
                    </td>
                </tr>
            </table>
            <div class="u-mt--sm">
                <button class="btn btn-success btn-lg u-mt--sm" type="submit" name="submit" id="submit_search"
                    value="search">検索</button>
                <button class="btn btn-default btn-lg u-mt--sm ml-20" type="button" name="submit"
                    id="submit_new"value="new" onClick="location.href='{{ route('master.warehouses.new') }}'">新規登録</button>
            </div>
            <div class="u-mt--sm">
                @include('common.elements.paginator_header')
                @include('common.elements.page_list_count')
                @include('common.elements.sorting_script')
                <br>
                <div style="overflow-x: auto; white-space: nowrap;">
                <table class="table table-bordered c-tbl c-tbl-full table-link nowrap" name="searchResults">
                    <tr>
                        <th>使用区分</th>
                        <th>
                            @include('common.elements.sorting_column_name', [
                                'columnName' => 'm_warehouses_id',
                                'columnViewName' => 'ID',
                            ])
                        </th>
                        <th>
                            @include('common.elements.sorting_column_name', [
                                'columnName' => 'm_warehouse_cd',
                                'columnViewName' => '倉庫コード',
                            ])
                        </th>
                        <th>倉庫名</th>
                        <th>倉庫種類</th>
                        <th>
                            @include('common.elements.sorting_column_name', [
                                'columnName' => 'm_warehouse_sort',
                                'columnViewName' => '並び順',
                            ])
                        </th>
                        <th>倉庫引当</th>
                        <th>倉庫引当順</th>
                        <th>倉庫担当者</th>
                        <th>倉庫会社名</th>
                        <th>倉庫郵便番号</th>
                        <th>倉庫都道府県</th>
                        <th>倉庫電話番号</th>
                        <th>倉庫FAX番号</th>
                        <th>基本配送方法</th>
                        <th>稼働日</th>
                        <th>リードタイム設定</th>
                        <th>送料設定</th>
                    </tr>
                    @if(isset($paginator) && $paginator->count() > 0)
                    @foreach ($paginator as $line)
                        <tr>
                            <td name="results_delete_flg">
                                @if (isset($line->delete_flg) && $line->delete_flg == \App\Enums\DeleteFlg::Use->value)
                                    {{ \App\Enums\DeleteFlg::Use->label() }}
                                @else
                                    {{ \App\Enums\DeleteFlg::Notuse->label() }}
                                @endif
                            </td>
                            <td><a
                                    href="{{ route('master.warehouses.edit', ['id' => $line->m_warehouses_id]) }}">{{ $line->m_warehouses_id }}</a>
                            </td>
                            <td>{{ Str::limit($line->m_warehouse_cd, Config::get('Common.const.disp_limit')) }}</td>
                            <td name="results_warehouse_name">
                                {{ Str::limit($line->m_warehouse_name, Config::get('Common.const.disp_limit')) }}</td>
                            <td>
                                @if (isset($line->m_warehouse_type) &&
                                        $line->m_warehouse_type == \App\Modules\Master\Gfh1207\Enums\WarehouseTypesEnum::Regular->value)
                                    {{ \App\Modules\Master\Gfh1207\Enums\WarehouseTypesEnum::Regular->label() }}
                                @else
                                    {{ \App\Modules\Master\Gfh1207\Enums\WarehouseTypesEnum::L_Spark->label() }}
                                @endif
                            </td>
                            <td>{{ Str::limit($line->m_warehouse_sort, Config::get('Common.const.disp_limit')) }}</td>
                            <td>
                                @if (isset($line->m_warehouse_priority_flg) &&
                                        $line->m_warehouse_priority_flg == \App\Modules\Master\Gfh1207\Enums\PriorityFlg::Enabled->value)
                                    {{ \App\Modules\Master\Gfh1207\Enums\PriorityFlg::Enabled->label() }}
                                @else
                                    {{ \App\Modules\Master\Gfh1207\Enums\PriorityFlg::Disabled->label() }}
                                @endif
                            <td>{{ Str::limit($line->m_warehouse_priority, Config::get('Common.const.disp_limit')) }}</td>
                            <td>{{ Str::limit($line->warehouse_personnel_name, Config::get('Common.const.disp_limit')) }}
                            </td>
                            <td>{{ Str::limit($line->warehouse_company, Config::get('Common.const.disp_limit')) }}</td>
                            <td>
                                @php
                                    // 郵便番号（ハイフン付加）
                                    if (mb_strlen($line->warehouse_postal) == 7) {
                                        $line->warehouse_postal =
                                            substr($line->warehouse_postal, 0, 3) .
                                            '-' .
                                            substr($line->warehouse_postal, 3);
                                    }
                                @endphp
                                {{ Str::limit($line->warehouse_postal, Config::get('Common.const.disp_limit')) }}
                            </td>
                            <td>{{ Str::limit($line->warehouse_prefectural, Config::get('Common.const.disp_limit')) }}</td>
                            <td>{{ Str::limit($line->warehouse_telephone, Config::get('Common.const.disp_limit')) }}</td>
                            <td>{{ Str::limit($line->warehouse_fax, Config::get('Common.const.disp_limit')) }}</td>
                            <td>{{ Str::limit($line->base_delivery_type, Config::get('Common.const.disp_limit')) }}</td>
                            <td><a
                                    href="{{ route('master.warehouses.edit', ['id' => $line->m_warehouses_id]) }}?t=1">稼働日</a>
                            </td>
                            <td><a
                                    href="{{ route('master.warehouses.edit', ['id' => $line->m_warehouses_id]) }}?t=3">リードタイム設定</a>
                            </td>
                            <td><a href="{{ route('master.warehouses.edit', ['id' => $line->m_warehouses_id]) }}?t=2">送料設定タブ
                            </td>
                        </tr>
                    @endforeach
                    @else
                    <tr>
                        <td colspan="18">{{Config::get('Common.const.PageHeader.NoResultsMessage') }}</td>
                        <input type="hidden" name="page_list_count" value="{{config('esm.default_page_size.master')}}">
                    </tr>
                    @endif
                </table>
                </div>
                @include('common.elements.paginator_footer')
            </div>
        </form>
    </div>
@endsection
