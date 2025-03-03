{{-- NECSM0110:顧客検索 --}}
@php
    $ScreenCd = 'NECSM0110';
@endphp

{{-- layout設定 --}}
@extends('common.layouts.default')

{{-- タイトル設定 --}}
@section('title', '顧客検索')

{{-- ぱんくず設定 --}}
@section('breadcrumb')
    <li>顧客検索</li>
@endsection

@section('content')
    <form enctype="multipart/form-data" method="POST" action="" name="Form1" id="Form1">
        {{ csrf_field() }}

        <div>

            <table class="table c-tbl">
                <tr>
                    <th>使用区分</th>
                    <td>
                        @foreach(\App\Enums\DeleteFlg::cases() as $delteFlg)
                            <label class="checkbox-inline">
                                <input type="checkbox" name="delete_flg[]" value="{{ $delteFlg->value }}"
                                @checked(isset($searchForm['delete_flg']) && in_array($delteFlg->value, $searchForm['delete_flg']))>
                                {{ $delteFlg->label() }}
                            </label>
                        @endforeach
                    </td>
                    <th>顧客ID</th>
                    <td>
                        <input class="form-control" type="text" name="m_cust_id"
                            value="{{ $searchForm['m_cust_id'] ?? '' }}">
                    </td>
                    <th>顧客コード</th>
                    <td>
                        <input class="form-control" type="text" name="cust_cd" value="{{ $searchForm['cust_cd'] ?? '' }}">
                    </td>
                </tr>
                <tr>
                    <th>顧客ランク</th>
                    <td>
                        @foreach($custRunks as $custRunk)
                            <label class="checkbox-inline">
                                <input type="checkbox" name="m_cust_runk_id[]" value="{{ $custRunk->m_itemname_types_id }}"
                                @checked(isset($searchForm['m_cust_runk_id']) && in_array($custRunk->m_itemname_types_id, $searchForm['m_cust_runk_id']))>
                                {{ $custRunk->m_itemname_type_name }}
                            </label>
                        @endforeach
                    </td>
                    <th>法人名・団体名</th>
                    <td>
                        <input class="form-control" type="text" name="corporate_kanji"
                            value="{{ $searchForm['corporate_kanji'] ?? '' }}">
                    </td>
                    <th>法人名・団体名（フリガナ）</th>
                    <td>
                        <input class="form-control" type="text" name="corporate_kana"
                            value="{{ $searchForm['corporate_kana'] ?? '' }}">
                    </td>
                </tr>
                <tr>
                    <th>電話番号（勤務先）</th>
                    <td>
                        <input class="form-control" type="text" name="corporate_tel"
                            value="{{ $searchForm['corporate_tel'] ?? '' }}">
                    </td>
                    <th>名前</th>
                    <td>
                        <input class="form-control" type="text" name="name_kanji"
                            value="{{ $searchForm['name_kanji'] ?? '' }}">
                        <label class="checkbox-inline">
                            <input type="checkbox" name="name_kanji_fuzzy" value="{{\App\Enums\FuzzyType::ON->value}}"
                                @checked(isset($searchForm['name_kanji_fuzzy']) && $searchForm['name_kanji_fuzzy'] == \App\Enums\FuzzyType::ON->value)>
                                {{\App\Enums\FuzzyType::ON->label()}}
                        </label>
                    </td>
                    <th>フリガナ</th>
                    <td>
                        <input class="form-control" type="text" name="name_kana"
                            value="{{ $searchForm['name_kana'] ?? '' }}">
                        <label class="checkbox-inline">
                            <input type="checkbox" name="name_kana_fuzzy" value="{{\App\Enums\FuzzyType::ON->value}}"
                                @checked(isset($searchForm['name_kana_fuzzy']) && $searchForm['name_kana_fuzzy'] == \App\Enums\FuzzyType::ON->value)>
                                {{\App\Enums\FuzzyType::ON->label()}}
                            </label>
                    </td>
                </tr>
                <tr>
                    <th>性別</th>
                    <td>
                        @foreach(\App\Enums\SexTypeEnum::cases() as $sexType)
                            <label class="checkbox-inline">
                                <input type="checkbox" name="sex_type[]" value="{{ $sexType->value }}"
                                @checked(isset($searchForm['sex_type']) && in_array($sexType->value, $searchForm['sex_type']))>
                                {{ $sexType->label() }}
                            </label>
                        @endforeach
                    </td>
                    <th>メールアドレス</th>
                    <td>
                        <input class="form-control" type="text" name="email" value="{{ $searchForm['email'] ?? '' }}">
                    </td>
                    <th>電話番号</th>
                    <td>
                        <input class="form-control" type="text" name="tel" value="{{ $searchForm['tel'] ?? '' }}">
                        <label class="checkbox-inline">
                            <input type="checkbox" name="tel_forward_match" value="{{\App\Enums\LikeMatchType::FORWARD->value}}"
                                @checked(isset($searchForm['tel_forward_match']) && $searchForm['tel_forward_match'] == \App\Enums\LikeMatchType::FORWARD->value)>
                                {{\App\Enums\LikeMatchType::FORWARD->label()}}
                        </label>
                    </td>
                </tr>
                <tr>
                    <th>FAX番号</th>
                    <td>
                        <input class="form-control" type="text" name="fax" value="{{ $searchForm['fax'] ?? '' }}">
                        <label class="checkbox-inline">
                            <input type="checkbox" name="fax_forward_match" value="{{\App\Enums\LikeMatchType::FORWARD->value}}"
                                @checked(isset($searchForm['fax_forward_match']) && $searchForm['fax_forward_match'] == \App\Enums\LikeMatchType::FORWARD->value)>
                                {{\App\Enums\LikeMatchType::FORWARD->label()}}
                        </label>
                    </td>
                    <th>郵便番号</th>
                    <td>
                        <input class="form-control" type="text" name="postal" value="{{ $searchForm['postal'] ?? '' }}">
                    </td>
                    <th>都道府県</th>
                    <td>
                        <select name="address1" class="form-control c-box--200">
                            <option value=""></option>
                            @foreach ($prefectuals as $prefectual)
                                <option value="{{ $prefectual->prefectual_name }}"
                                    @selected(isset($seachForm['address1']) && $seachForm['address1'] == $prefectual->prefectual_name) >
                                    {{ $prefectual->prefectual_name }}</option>
                            @endforeach
                        </select>
                    </td>
                </tr>
                <tr>
                    <th>備考の有無</th>
                    <td>
                        @foreach(\App\Enums\CustomerNoteExistenceType::cases() as $noteExistence)
                            <label class="checkbox-inline">
                                <input type="checkbox" name="note_existence[]" value="{{ $noteExistence->value }}"
                                @checked(isset($searchForm['note_existence']) && in_array($noteExistence->value, $searchForm['note_existence']))>
                                {{ $noteExistence->label() }}
                            </label>
                        @endforeach
                    </td>
                    <th>備考</th>
                    <td>
                        <input class="form-control" type="text" name="note"
                            value="{{ $searchForm['note'] ?? '' }}">
                    </td>
                    <td></td>
                    <td></td>
                </tr>
                <tr>
                    <th>累計購入金額</th>
                    <td>
                        <input class="form-control u-input--mid" type="text" name="total_order_money_from"
                            value="{{ $searchForm['total_order_money_from'] ?? '' }}">
                        ～
                        <input class="form-control u-input--mid" type="text" name="total_order_money_to"
                            value="{{ $searchForm['total_order_money_to'] ?? '' }}">
                    </td>
                    <th>購入回数</th>
                    <td>
                        <input class="form-control u-input--mid" type="text" name="total_order_count_from"
                            value="{{ $searchForm['total_order_count_from'] ?? '' }}">
                        ～
                        <input class="form-control u-input--mid" type="text" name="total_order_count_to"
                            value="{{ $searchForm['total_order_count_to'] ?? '' }}">
                    </td>
                    <td></td>
                    <td></td>
                </tr>
                <tr>
                    <th>要注意区分</th>
                    <td>
                        @foreach(\App\Enums\AlertCustTypeEnum::cases() as $alertCustTypeEnum)
                            <label class="checkbox-inline">
                                <input type="checkbox" name="alert_cust_type[]" value="{{ $alertCustTypeEnum->value }}"
                                @checked(isset($searchForm['alert_cust_type']) && in_array($alertCustTypeEnum->value, $searchForm['alert_cust_type']))>
                                {{ $alertCustTypeEnum->label() }}
                            </label>
                        @endforeach
                    </td>
                    <th>要注意コメント</th>
                    <td>
                        <input class="form-control" type="text" name="alert_cust_comment"
                            value="{{ $searchForm['alert_cust_comment'] ?? '' }}">
                    </td>
                    <th>削除顧客を含む</th>
                    <td>
                        <input type="checkbox" name="delete_include" value="{{\App\Enums\DeleteIncludeType::ON->value}}"
                            @checked(isset($searchForm['delete_include']) && $searchForm['delete_include'] == \App\Enums\DeleteIncludeType::ON->value)>
                    </td>
                </tr>
            </table>

            <input type="hidden" name="should_paginate" value="{{\App\Enums\ShouldPaginate::YES->value}}">
            <input type="hidden" name="limit" value="{{ isset($searchForm['limit'])? $searchForm['limit']:config('esm.default_page_size.cc')}}">
            <button class="btn btn-success btn-lg" type="submit" name="submit_" id="submit_search" value="search">検索</button>
            &nbsp;
            <button type="button" class="btn btn-default btn-lg" name="new" onClick="location.href='{{route('sample.sample.new')}}'">顧客新規登録</button>
            <input type="hidden" name="{{ config('define.session_key_id') }}"
                value="{{ $searchForm[config('define.session_key_id')] ?? '' }}">

            <x-common.csv-input-button
                displayName="顧客"
            />

        </div>
        <br>
        @isset($samples)
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
                    :paginator="$samples"
                />
                @if ($samples->count() > 0)
                    <x-common.page-list-count
                        :pageListCount="$searchForm['page_list_count'] ?? null"
                    />
                @endif
                <table class="table table-bordered c-tbl table-link nowrap u-mt--sm">
                    <tr>
                        <th>
                          @if($samples->count() > 0)
                            <x-common.sorting-link
                                columnName="m_cust_id"
                                :columnViewName="'顧客ID'"
                                :sortColumn="$searchForm['sorting_column'] ?? null"
                                :sortShift="$searchForm['sorting_shift'] ?? null"
                                />
                            @else
                                顧客ID
                            @endif
                        </th>
                        <th>
                            @if($samples->count() > 0)
                                <x-common.sorting-link
                                    columnName="cust_cd"
                                    :columnViewName="'顧客コード'"
                                    :sortColumn="$searchForm['sorting_column'] ?? null"
                                    :sortShift="$searchForm['sorting_shift'] ?? null"
                                    />
                                @else
                                    顧客コード
                                @endif
                        </th>
                        <th>
                           @if($samples->count() > 0)
                                <x-common.sorting-link
                                    columnName="m_cust_runk_id"
                                    :columnViewName="'顧客ランク'"
                                    :sortColumn="$searchForm['sorting_column'] ?? null"
                                    :sortShift="$searchForm['sorting_shift'] ?? null"
                                    />
                            @else
                                顧客ランク
                            @endif
                        </th>
                        <th>法人名・団体名</th>
                        <th>
                            @if($samples->count() > 0)
                                <x-common.sorting-link
                                    columnName="name_kanji"
                                    :columnViewName="'名前'"
                                    :sortColumn="$searchForm['sorting_column'] ?? null"
                                    :sortShift="$searchForm['sorting_shift'] ?? null"
                                    />
                            @else
                                名前
                            @endif
                        </th>
                        <th>
                            @if($samples->count() > 0)
                                <x-common.sorting-link
                                    columnName="email"
                                    :columnViewName="'メールアドレス'"
                                    :sortColumn="$searchForm['sorting_column'] ?? null"
                                    :sortShift="$searchForm['sorting_shift'] ?? null"
                                    />
                            @else
                                メールアドレス
                            @endif
                        </th>
                        <th>電話</th>
                        <th>FAX</th>
                        <th>
                            @if($samples->count() > 0)
                                <x-common.sorting-link
                                    columnName="postal"
                                    :columnViewName="'郵便番号'"
                                    :sortColumn="$searchForm['sorting_column'] ?? null"
                                    :sortShift="$searchForm['sorting_shift'] ?? null"
                                    />
                            @else
                                郵便番号
                            @endif
                        </th>
                        <th>
                            @if($samples->count() > 0)
                                <x-common.sorting-link
                                    columnName="address1"
                                    :columnViewName="'都道府県'"
                                    :sortColumn="$searchForm['sorting_column'] ?? null"
                                    :sortShift="$searchForm['sorting_shift'] ?? null"
                                    />
                            @else
                                都道府県
                            @endif
                        </th>
                        <th>備考</th>
                    </tr>
                    @if (!empty($samples->count()) > 0)
                        @foreach ($samples as $cust)
                            <tr>
                                <td>
                                    <x-common.output-check-box
                                        name="csv_output_check_key_id"
                                        :keyValue="$cust->m_cust_id"
                                    />
                                    &nbsp;
                                    {{-- <a href='@createUrl(cc, customer / edit / {{ $cust->m_cust_id }})'>
                                        {{ $cust->m_cust_id }}
                                    </a> --}}
                                    <a href="{{route('sample.sample.edit', ['id' => $cust->m_cust_id])}}">
                                        {{ $cust->m_cust_id }}
                                </td>
                                <td>{{ $cust->cust_cd }}</td>
                                <td>{{ $cust->custRunk?->m_itemname_type_name }}</td>
                                <td>{{ $cust->corporate_kanji }}</td>
                                <td>{{ $cust->name_kanji }}</td>
                                <td>{{ $cust->email }}</td>
                                <td>{{ $cust->tel1 }}</td>
                                <td>{{ $cust->fax }}</td>
                                <td>{{ $cust->postal }}</td>
                                <td>{{ $cust->address1 }}</td>
                                <td title="{{ $cust->note_min }}">
                                    {{ $cust->note }}
                                </td>
                            </tr>
                        @endforeach
                    @else
                        @if ($samples->count() == 0)
                            <tr>
                                <td colspan="11">該当顧客が見つかりません。</td>
                            </tr>
                        @endif
                    @endif
                </table>
                <x-common.paginator-foot
                    :paginator="$samples"
                />
                <x-common.csv-output-button />
            </div>
        @endisset
    </form>
@endsection
