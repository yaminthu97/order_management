{{-- NEMSMH0020:倉庫マスタ登録・更新画面 --}}
@php
    $ScreenCd = 'NEMSMH0020';
@endphp

{{-- layout設定 --}}
@extends('common.layouts.default')

{{-- タイトル設定 --}}
@section('title', '倉庫マスタ登録・更新画面')

{{-- ぱんくず設定 --}}
@section('breadcrumb')
    <li>
        倉庫マスタ登録・更新画面
    </li>
@endsection

@section('content')
    <div class="u-mt--xs">
        @if (!empty($viewMessage))
            <div class="c-box--1700 c-tbl-border-all u-p--sm sy_notice u-mb--ss">
                @foreach ($viewMessage as $message)
                    <p class="icon_sy_notice_03">{{ $message }}</p>
                @endforeach
            </div><!--/sy_notice-->
        @endif
        <form enctype="multipart/form-data" method="POST" action="" name="Form1" id="Form1">
            {{ csrf_field() }}
            @if (!empty($notify))
                @if ($mode == 'new')
                @elseif($mode == 'edit')
                    @method('PUT')
                @endif
            @endif
            @if (!empty($notify))
                <input type="hidden" name="notifyFlg" value="{{ $notify }}">
            @endif
            <div class="c-box--1200 u-mt--ss">
                <table class="table table-bordered c-tbl c-tbl--1200">
                    <tr>
                        <th>倉庫ID</th>
                        <td>{{ !empty($editRow['m_warehouses_id']) ? $editRow['m_warehouses_id'] : '自動' }}
                        </td>
                        <input type="hidden" name="m_warehouses_id"
                            value="{{ old('m_warehouses_id', $editRow->m_warehouses_id ?? '') }}">
                    </tr>
                    <tr>
                        <th class="c-box--300">倉庫コード</th>
                        <td>
                            @if (!empty($notify))
                                {{ old('m_warehouse_cd', $editRow['m_warehouse_cd'] ?? '') }}
                            @else
                                <input class="form-control" type="text" name="m_warehouse_cd" id="m_warehouse_cd"
                                    value="{{ old('m_warehouse_cd', $editRow->m_warehouse_cd ?? '') }}" />
                                <x-common.error-tag name="m_warehouse_cd" />
                            @endif
                        </td>
                    </tr>
                    <tr>
                        <th class=" c-box--300 must">倉庫名</th>
                        <td>
                            @if (!empty($notify))
                                {{ old('m_warehouse_name', $editRow['m_warehouse_name'] ?? '') }}
                            @else
                                <input class="form-control" type="text" name="m_warehouse_name" id="m_warehouse_name"
                                    value="{{ old('m_warehouse_name', $editRow->m_warehouse_name ?? '') }}" />
                            @endif
                            <x-common.error-tag name="m_warehouse_name" />
                        </td>
                    </tr>
                    <tr>
                        <th class="c-box--300 must">倉庫種類</th>
                        <td>
                            <div>
                                @if (!empty($notify))
                                    @if (isset($editRow['m_warehouse_type']) &&
                                            $editRow['m_warehouse_type'] == \App\Modules\Master\Gfh1207\Enums\WarehouseTypesEnum::Regular->value)
                                        {{ \App\Modules\Master\Gfh1207\Enums\WarehouseTypesEnum::Regular->label() }}
                                    @else
                                        {{ \App\Modules\Master\Gfh1207\Enums\WarehouseTypesEnum::L_Spark->label() }}
                                    @endif
                                @else
                                    <label class="radio-inline">
                                        <input type="radio" id="m_warehouse_type" name="m_warehouse_type"
                                            value="{{ \App\Modules\Master\Gfh1207\Enums\WarehouseTypesEnum::Regular->value }}"
                                            {{ old('m_warehouse_type', $editRow['m_warehouse_type'] ?? null) === null ? 'checked' : (old('m_warehouse_type', $editRow['m_warehouse_type'] ?? null) == \App\Modules\Master\Gfh1207\Enums\WarehouseTypesEnum::Regular->value ? 'checked' : '') }}>
                                        {{ \App\Modules\Master\Gfh1207\Enums\WarehouseTypesEnum::Regular->label() }}
                                    </label>

                                    <label class="radio-inline">
                                        <input type="radio" id="m_warehouse_type" name="m_warehouse_type"
                                            value="{{ \App\Modules\Master\Gfh1207\Enums\WarehouseTypesEnum::L_Spark->value }}"
                                            {{ old('m_warehouse_type', $editRow['m_warehouse_type'] ?? null) === null ? '' : (old('m_warehouse_type', $editRow['m_warehouse_type'] ?? null) == \App\Modules\Master\Gfh1207\Enums\WarehouseTypesEnum::L_Spark->value ? 'checked' : '') }}>
                                        {{ \App\Modules\Master\Gfh1207\Enums\WarehouseTypesEnum::L_Spark->label() }}

                                    </label>
                                    <x-common.error-tag name="m_warehouse_type" />
                                @endif
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <th class="c-box--300 must">並び順</th>
                        <td>
                            @if (!empty($notify))
                                {{ old('m_warehouse_sort', $editRow['m_warehouse_sort'] ?? '100') }}
                            @else
                                <input type="text" class="form-control c-box--300" name="m_warehouse_sort"
                                    value="{{ old('m_warehouse_sort', $editRow->m_warehouse_sort ?? '100') }}">
                            @endif
                            <x-common.error-tag name="m_warehouse_sort" />
                        </td>
                    </tr>
                </table>
            </div>
            <!-- /.cbox1200 -->

            <div id="tabs">
                <div class="c-box--full u-mt--xs">
                    <ul>
                        <li class="">
                            <a href="#tabs-1">基本設定</a>
                        </li>
                        <li class="">
                            <a href="#tabs-2">非稼働日設定</a>
                        </li>
                        @php
                            $tabId = 3; // Start the tab IDs from 3
                        @endphp
                        @if(isset($delivery_types) && count($delivery_types) > 0)
                        @foreach ($delivery_types as $delivery_type)
                            <li class="">
                                {{-- Tab for 送料設定 --}}
                                <a href="#tabs-{{ $tabId }}">
                                    送料設定({{ $delivery_type['m_delivery_type_name'] }})
                                </a>
                            </li>
                            @php
                                $tabId++; // Increment tab ID
                            @endphp

                            <li class="">
                                {{-- Tab for リードタイム設定 --}}
                                <a href="#tabs-{{ $tabId }}">
                                    リードタイム設定({{ $delivery_type['m_delivery_type_name'] }})
                                </a>
                            </li>
                            @php
                                $tabId++; // Increment tab ID
                            @endphp
                        @endforeach
                        @endif
                    </ul>
                </div>
            <div style="overflow-x: auto; white-space: nowrap;">
                <div class="tabs-inner">
                    <!-- tabs-1ここから -->
                    <div id="tabs-1">
                        <div class="d-table c-box--full">
                            <table class="table table-bordered c-tbl c-tbl--full">
                                <tr>
                                    <th class="c-box--300 must">倉庫引当有効</th>
                                    <td>
                                        <div>
                                            @if (!empty($notify))
                                                @if (isset($editRow['m_warehouse_priority_flg']) &&
                                                        $editRow['m_warehouse_priority_flg'] == \App\Modules\Master\Gfh1207\Enums\PriorityFlg::Enabled->value)
                                                    {{ \App\Modules\Master\Gfh1207\Enums\PriorityFlg::Enabled->label() }}
                                                @else
                                                    {{ \App\Modules\Master\Gfh1207\Enums\PriorityFlg::Disabled->label() }}
                                                @endif
                                            @else
                                                <label class="radio-inline">
                                                    <input type="radio" id="m_warehouse_priority_flg"
                                                        name="m_warehouse_priority_flg"
                                                        value="{{ \App\Modules\Master\Gfh1207\Enums\PriorityFlg::Enabled->value }}"
                                                        {{ old('m_warehouse_priority_flg', $editRow['m_warehouse_priority_flg'] ?? null) === null ? 'checked' : (old('m_warehouse_priority_flg', $editRow['m_warehouse_priority_flg'] ?? null) == \App\Modules\Master\Gfh1207\Enums\PriorityFlg::Enabled->value ? 'checked' : '') }}>
                                                    {{ \App\Modules\Master\Gfh1207\Enums\PriorityFlg::Enabled->label() }}
                                                </label>

                                                <label class="radio-inline">
                                                    <input type="radio" id="m_warehouse_priority_flg"
                                                        name="m_warehouse_priority_flg"
                                                        value="{{ \App\Modules\Master\Gfh1207\Enums\PriorityFlg::Disabled->value }}"
                                                        {{ old('m_warehouse_priority_flg', $editRow['m_warehouse_priority_flg'] ?? null) === null ? '' : (old('m_warehouse_priority_flg', $editRow['m_warehouse_priority_flg'] ?? null) == \App\Modules\Master\Gfh1207\Enums\PriorityFlg::Disabled->value ? 'checked' : '') }}>
                                                    {{ \App\Modules\Master\Gfh1207\Enums\PriorityFlg::Disabled->label() }}
                                                </label>
                                                <x-common.error-tag name="m_warehouse_priority_flg" />

                                            @endif
                                        </div>
                                    </td>
                                </tr>
                                <!-- 代引き出荷 -->
                                <tr>
                                    <th class="c-box--300 must">代引き出荷</th>
                                    <td>
                                        <div>
                                            @if (!empty($notify))
                                                @if (isset($editRow['cash_on_delivery_flg']) &&
                                                        $editRow['cash_on_delivery_flg'] == \App\Modules\Master\Gfh1207\Enums\DeliveryFlg::Enabled->value)
                                                    {{ \App\Modules\Master\Gfh1207\Enums\DeliveryFlg::Enabled->label() }}
                                                @else
                                                    {{ \App\Modules\Master\Gfh1207\Enums\DeliveryFlg::Disabled->label() }}
                                                @endif
                                            @else
                                                <label class="radio-inline">
                                                    <input type="radio" id="cash_on_delivery_flg"
                                                        name="cash_on_delivery_flg"
                                                        value="{{ \App\Modules\Master\Gfh1207\Enums\DeliveryFlg::Enabled->value }}"
                                                        {{ old('cash_on_delivery_flg', $editRow['cash_on_delivery_flg'] ?? null) === null ? 'checked' : (old('cash_on_delivery_flg', $editRow['cash_on_delivery_flg'] ?? null) == \App\Modules\Master\Gfh1207\Enums\DeliveryFlg::Enabled->value ? 'checked' : '') }}>
                                                    {{ \App\Modules\Master\Gfh1207\Enums\DeliveryFlg::Enabled->label() }}
                                                </label>

                                                <label class="radio-inline">
                                                    <input type="radio" id="cash_on_delivery_flg"
                                                        name="cash_on_delivery_flg"
                                                        value="{{ \App\Modules\Master\Gfh1207\Enums\DeliveryFlg::Disabled->value }}"
                                                        {{ old('cash_on_delivery_flg', $editRow['cash_on_delivery_flg'] ?? null) === null ? '' : (old('cash_on_delivery_flg', $editRow['cash_on_delivery_flg'] ?? null) == \App\Modules\Master\Gfh1207\Enums\DeliveryFlg::Disabled->value ? 'checked' : '') }}>
                                                    {{ \App\Modules\Master\Gfh1207\Enums\DeliveryFlg::Disabled->label() }}
                                                </label>
                                                <x-common.error-tag name="cash_on_delivery_flg" />

                                            @endif
                                        </div>
                                    </td>
                                </tr>
                                <!-- 納品書に他倉庫分出荷明細 -->
                                <tr>
                                    <th class="c-box--300 must">納品書に他倉庫分出荷明細</th>
                                    <td>
                                        <div>
                                            @if (!empty($notify))
                                                @if (isset($editRow['deliveryslip_bundle_flg']) &&
                                                        $editRow['deliveryslip_bundle_flg'] == \App\Modules\Master\Gfh1207\Enums\DeliveryFlg::Enabled->value)
                                                    {{ \App\Modules\Master\Gfh1207\Enums\BundleFlg::Enabled->label() }}
                                                @else
                                                    {{ \App\Modules\Master\Gfh1207\Enums\BundleFlg::Disabled->label() }}
                                                @endif
                                            @else
                                                <label class="radio-inline">
                                                    <input type="radio" id="deliveryslip_bundle_flg"
                                                        name="deliveryslip_bundle_flg"
                                                        value="{{ \App\Modules\Master\Gfh1207\Enums\BundleFlg::Disabled->value }}"
                                                        {{ old('deliveryslip_bundle_flg', $editRow['deliveryslip_bundle_flg'] ?? null) === null ? 'checked' : (old('deliveryslip_bundle_flg', $editRow['deliveryslip_bundle_flg'] ?? null) == \App\Modules\Master\Gfh1207\Enums\BundleFlg::Disabled->value ? 'checked' : '') }}>
                                                    {{ \App\Modules\Master\Gfh1207\Enums\BundleFlg::Disabled->label() }}
                                                </label>
                                                <label class="radio-inline">
                                                    <input type="radio" id="deliveryslip_bundle_flg"
                                                        name="deliveryslip_bundle_flg"
                                                        value="{{ \App\Modules\Master\Gfh1207\Enums\BundleFlg::Enabled->value }}"
                                                        {{ old('deliveryslip_bundle_flg', $editRow['deliveryslip_bundle_flg'] ?? null) === null ? '' : (old('deliveryslip_bundle_flg', $editRow['deliveryslip_bundle_flg'] ?? null) == \App\Modules\Master\Gfh1207\Enums\BundleFlg::Enabled->value ? 'checked' : '') }}>
                                                    {{ \App\Modules\Master\Gfh1207\Enums\BundleFlg::Enabled->label() }}
                                                </label>
                                                <x-common.error-tag name="deliveryslip_bundle_flg" />

                                            @endif
                                        </div>
                                    </td>
                                </tr>
                                <tr>
                                    <th class="c-box--300 must">倉庫引当順</th>
                                    <td>
                                        @if (!empty($notify))
                                            {{ old('m_warehouse_priority', $editRow['m_warehouse_priority'] ?? '') }}
                                        @else
                                            <input type="text" class="form-control c-box--300"
                                                name="m_warehouse_priority"
                                                value="{{ old('m_warehouse_priority', $editRow->m_warehouse_priority ?? '') }}">
                                        @endif
                                        <x-common.error-tag name="m_warehouse_priority" />
                                    </td>
                                </tr>
                                <tr>
                                    <th class="">倉庫担当者</th>
                                    <td>
                                        @if (!empty($notify))
                                            {{ old('warehouse_personnel_name', $editRow['warehouse_personnel_name'] ?? '' ) }}
                                        @else
                                            <input type="text" class="form-control c-box--300"
                                                name="warehouse_personnel_name"
                                                value="{{ old('warehouse_personnel_name', $editRow['warehouse_personnel_name'] ?? '') }}">
                                        @endif
                                        <x-common.error-tag name="warehouse_personnel_name" />
                                    </td>
                                </tr>
                                <tr>
                                    <th class="">倉庫担当者カナ</th>
                                    <td>
                                        @if (!empty($notify))
                                            {{ old('warehouse_personnel_name_kana', $editRow['warehouse_personnel_name_kana'] ?? '') }}
                                        @else
                                            <input type="text" class="form-control c-box--300"
                                                name="warehouse_personnel_name_kana"
                                                value="{{ old('warehouse_personnel_name_kana', $editRow['warehouse_personnel_name_kana'] ?? '') }}">
                                        @endif
                                        <x-common.error-tag name="warehouse_personnel_name_kana" />

                                    </td>
                                </tr>
                                <tr>
                                    <th class="">倉庫会社名</th>
                                    <td>
                                        @if (!empty($notify))
                                            {{ old('warehouse_company', $editRow['warehouse_company'] ?? '') }}
                                        @else
                                            <input type="text" class="form-control c-box--300"
                                                name="warehouse_company"
                                                value="{{ old('warehouse_company', $editRow['warehouse_company'] ?? '') }}">
                                        @endif
                                        <x-common.error-tag name="warehouse_company" />
                                    </td>
                                </tr>
                                <tr>
                                    <th class="must">倉庫住所</th>
                                    <td>
                                        <div class="d-table c-tbl--600">
                                            <div class="d-table-cell c-box--100">郵便番号</div>
                                            <div class="d-table-cell">

                                                @if (!empty($notify))
                                                    {{ old('warehouse_postal', $editRow['warehouse_postal'] ?? '') }}
                                                @else
                                                    <input class="form-control c-box--300 js_s_addr" type="text"
                                                        name="warehouse_postal"
                                                        value="{{ old('warehouse_postal', $editRow['warehouse_postal'] ?? '') }}"
                                                        maxlength="8">
                                                @endif
                                            </div>
                                            <x-common.error-tag name="warehouse_postal" />
                                        </div>
                                        <div class="d-table c-tbl--600 u-mt--xs">
                                            <div class="d-table-cell c-box--100">都道府県</div>
                                            <div class="d-table-cell">

                                                @if (empty($notify))
                                                    <select class="form-control c-box--120" name="warehouse_prefectural">
                                                @endif
                                                @if(isset($prefecturals) && count($prefecturals) > 0)
                                                @foreach ($prefecturals as $row)
                                                    @if (empty($notify))
                                                        <option value="{{ $row['prefectual_name'] }}"
                                                            {{ old('warehouse_prefectural', $editRow['warehouse_prefectural'] ?? '') == $row['prefectual_name'] ? 'selected' : '' }}>
                                                        @else
                                                            @if ($editRow['warehouse_prefectural'] != $row['prefectual_name'])
                                                                @continue
                                                            @endif
                                                    @endif
                                                    {{ $row['prefectual_name'] }}
                                                    @if (empty($notify))
                                                        </option>
                                                    @endif
                                                @endforeach
                                                @endif
                                                @if (empty($notify))
                                                    </select>
                                                @endif
                                            </div>
                                            <x-common.error-tag name="warehouse_prefectural" />
                                        </div>
                                        <div class="d-table c-tbl--600 u-mt--xs">
                                            <div class="d-table-cell c-box--100">市区町村</div>
                                            <div class="d-table-cell">
                                                @if (!empty($notify))
                                                    {{ old('warehouse_address', $editRow['warehouse_address'] ?? '') }}
                                                @else
                                                    <input type="text" class="form-control c-box--300"
                                                        name="warehouse_address"
                                                        value="{{ old('warehouse_address', $editRow['warehouse_address'] ?? '') }}">
                                                    <x-common.error-tag name="warehouse_address" />
                                                @endif
                                            </div>

                                        </div>
                                        <div class="d-table c-tbl--600 u-mt--xs">
                                            <div class="d-table-cell c-box--100">番地</div>
                                            <div class="d-table-cell">
                                                @if (!empty($notify))
                                                    {{ old('warehouse_house_number', $editRow['warehouse_house_number'] ?? '' ) }}
                                                @else
                                                    <input type="text" class="form-control c-box--300"
                                                        name="warehouse_house_number"
                                                        value="{{ old('warehouse_house_number', $editRow['warehouse_house_number'] ?? '') }}">
                                                    <x-common.error-tag name="warehouse_house_number" />
                                                @endif
                                            </div>
                                        </div>
                                        <div class="d-table c-tbl--600 u-mt--xs">
                                            <div class="d-table-cell c-box--100">建物名</div>
                                            <div class="d-table-cell">
                                                @if (!empty($notify))
                                                    {{ old('warehouse_adding_building', $editRow['warehouse_adding_building'] ?? '' ) }}
                                                @else
                                                    <input type="text" class="form-control c-box--300"
                                                        name="warehouse_adding_building"
                                                        value="{{ old('warehouse_adding_building', $editRow['warehouse_adding_building'] ?? '') }}">
                                                    <x-common.error-tag name="warehouse_adding_building" />
                                                @endif
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                                <tr>
                                    <th class="must">倉庫電話番号</th>
                                    <td>
                                        @if (!empty($notify))
                                            {{ old('warehouse_telephone', $editRow['warehouse_telephone'] ?? '') }}
                                        @else
                                            <input type="text" class="form-control c-box--300"
                                                name="warehouse_telephone"
                                                value="{{ old('warehouse_telephone', $editRow['warehouse_telephone'] ?? '') }}">
                                            <x-common.error-tag name="warehouse_telephone" />
                                        @endif

                                    </td>
                                </tr>
                                <tr>
                                    <th class="">倉庫FAX番号</th>
                                    <td>
                                        @if (!empty($notify))
                                            {{ old('warehouse_fax', $editRow['warehouse_fax'] ?? '' ) }}
                                        @else
                                            <input type="text" class="form-control c-box--300" name="warehouse_fax"
                                                value="{{ old('warehouse_fax', $editRow['warehouse_fax'] ?? '') }}">
                                            <x-common.error-tag name="warehouse_fax" />
                                        @endif
                                    </td>
                                </tr>
                                <tr>
                                    <th class="">当日出荷締め時間</th>
                                    <td>
                                        @if (!empty($notify))
                                            {{ old('delivery_futoff_time', isset($editRow['delivery_futoff_time']) ? date('H:i', strtotime($editRow['delivery_futoff_time'])) : '') }}
                                        @else
                                            <input type="text" class="form-control c-box--300" name="delivery_futoff_time" 
                                            value="{{ old('delivery_futoff_time', isset($editRow['delivery_futoff_time']) ? date('H:i', strtotime($editRow['delivery_futoff_time'])) : '') }}">
                                            <x-common.error-tag name="delivery_futoff_time" />
                                        @endif
                                    </td>
                                </tr>
                            </table>
                        </div>
                    </div>
                    <!-- tabs-1ここまで -->

                    <!-- tabs-2ここから -->
                    <div id="tabs-2">
                    </div>
                    @if (!empty($editRow))
                    <script>
                        $(function() {
                            // Initialize the calendar with the current year or a provided year
                            const year = @json($year ?? date('Y'));
                            calendarRefresh(year);

                            /**
                             * Refresh the calendar by fetching data from the server.
                             * @param {number} year - The year to fetch calendar data for.
                             */
                            function calendarRefresh(year) {
                                const warehouseId = @json($editRow['m_warehouses_id'] ?? 0);
                                const notifyFlag = @json($notify == 1 ? 1 : 0);
                                const url = `/gfh/master/warehouses/${warehouseId}/getCalendar/${year}/${notifyFlag}`;

                                const holidays = @json($holidays ?? old('holidays'));

                                // Set up AJAX with CSRF token
                                $.ajaxSetup({
                                    headers: {
                                        'X-CSRF-TOKEN': '{{ csrf_token() }}' // Use Laravel's built-in helper
                                    }
                                });

                                // Perform the AJAX request
                                var request = $.ajax({
                                    type: "POST",
                                    url: url,
                                    data: {
                                        holidays: {!! json_encode($holidays ?? old('holidays') ) !!}
                                    },
                                    dataType: "html",
                                    success: function(data) {
                                        $("#tabs-2").html(data);
                                    },
                                    error: function(xhr, status, error) {
                                        console.error("Failed to refresh calendar:", error);
                                    }
                                });
                            }

                            /**
                             * Utility function to get a cookie's value by its key.
                             * @param {string} targetKey - The name of the cookie.
                             * @returns {string} - The value of the cookie or an empty string if not found.
                             */
                            function getCookieValue(targetKey) {
                                for (let cookie of document.cookie.split(";")) {
                                    const [key, value] = cookie.trim().split("=");
                                    if (key === targetKey) {
                                        return value;
                                    }
                                }
                                return "";
                            }
                        });
                    </script>
                    @endif
                    <!-- tabs-2ここまで -->

                    @php
                        $tabId = 3; // Start the tab IDs from 3
                    @endphp
                    <!-- tabs ここから -->
                    @if(isset($delivery_types) && count($delivery_types) > 0)
                    @foreach ($delivery_types as $delivery_type)
                        @php
                            if (
                                Config::get('Master.const.m_ecs_delivery_time_hope__m_delivery_id') ==
                                $delivery_type['m_delivery_types_id']
                            ) {
                                continue;
                            }
                            $old_region = 0;
                            $td_count = 1;
                            $m_delivery_types_id = $delivery_type['m_delivery_types_id'];
                        @endphp
                        <script>
                            $(function() {
                                $('.readtime-batch-setting').click(function() {
                                    parent = $(this).prev('.prefecturals{{ $m_delivery_types_id }}{{ $tabId }}');
                                    parent_region = parent.data('region');
                                    parent_val = parent.val();
                                    $('.prefecturals{{ $m_delivery_types_id }}{{ $tabId }}.child[data-region="' +
                                            parent_region + '"]')
                                        .val(parent_val);
                                });
                            });
                        </script>
                        @if(isset($prefecturals) && count($prefecturals) > 0)
                        <div id="tabs-{{ $tabId }}">
                            <x-common.error-tag :name="'delivery_fee.' . $m_delivery_types_id . '.*'" />
                            <table class="c-box--800 table-condensed">
                                <tr>
                                    @foreach ($prefecturals as $prefectural)
                                        <!-- new region -->
                                        @if ($old_region != $prefectural['prefectual_region'])
                                            @if ($old_region !== 0)
                                </tr>
                                <tr>
                    @endif
                    @php
                        $old_region = $prefectural['prefectual_region'];
                        $td_count = 0;
                    @endphp
                    <!-- region name -->
                    <td>
                        @foreach (\App\Modules\Master\Gfh1207\Enums\PrefectualRegionEnum::cases() as $region_names)
                            @if ($region_names->value == $prefectural['prefectual_region'])
                                {{ $region_names->label() }}
                            @endif
                        @endforeach
                    </td>
                    <!-- batch setting -->
                    <td>
                        @if (empty($notify))
                            <input type="text"
                                class="form-control c-box--110 prefecturals{{ $m_delivery_types_id }}{{ $tabId }} parent"
                                data-region="region{{ $prefectural['prefectual_region'] }}" value="{{ Config::get('Master.const.delivery_fee_default') }}">円
                            <a href="javascript:void(0)" class="readtime-batch-setting" id="hokkaido">一括設定</a>
                        @endif
                    </td>
                @else
                    @if ($td_count == 3)
                        </tr>
                        <tr>
                            <td></td>
                            <td></td>
                    @endif
                    @php
                        if ($td_count == 3) {
                            $td_count = 0;
                        }
                    @endphp
                    @endif
                    <td>{{ $prefectural['prefectual_name'] }}</td>
                    <td>
                        @if (!empty($notify))
                            {{ $editDeliveryFees['delivery_fee'][$m_delivery_types_id][$prefectural['m_prefectural_id']] ?? '' }}円
                        @else
                            <input type="text"
                                class="form-control c-box--110 prefecturals{{ $m_delivery_types_id }}{{ $tabId }} child"
                                data-region="region{{ $prefectural['prefectual_region'] }}"
                                name="delivery_fee[{{ $m_delivery_types_id }}][{{ $prefectural['m_prefectural_id'] }}]"
                                value="{{ old('delivery_fee.' . $m_delivery_types_id . '.' . $prefectural['m_prefectural_id'], $editDeliveryFees['delivery_fee'][$m_delivery_types_id][$prefectural['m_prefectural_id']] ?? Config::get('Master.const.delivery_fee_default')) }}">円
                        @endif
                    </td>
                    @php $td_count++ @endphp
                    @endforeach
                    </tr>
                    </table>
                </div>

                @php
                    $tabId++; // Increment tab ID
                @endphp
                @endif
                <script>
                    $(function() {
                        $('.readtime-batch-setting').click(function() {
                            parent = $(this).prev('.prefecturals{{ $m_delivery_types_id }}{{ $tabId }}');
                            parent_region = parent.data('region');
                            parent_val = parent.val();
                            $('.prefecturals{{ $m_delivery_types_id }}{{ $tabId }}.child[data-region="' +
                                    parent_region + '"]')
                                .val(parent_val);
                        });
                    });
                </script>
                @if(isset($delivery_types) && count($delivery_types) > 0)
                <div id="tabs-{{ $tabId }}">
                    <x-common.error-tag :name="'delivery_readtime.' . $m_delivery_types_id . '.*'" />
                    @if ($m_delivery_types_id == 1)
                        <div class="checkbox-container">
                            <label>
                                <input type="checkbox" name="master_pack_apply_flg" value="1"
                                    {{ old('master_pack_apply_flg', isset($editDeliveryReadtime['master_pack_apply_flg']) ? $editDeliveryReadtime['master_pack_apply_flg'] : '') == '1' ? 'checked' : '' }}>
                                マスタパックの算出日数を加算
                            </label>
                        </div>
                    @endif
                    <table class="c-box--800 table-condensed">
                        <tr>
                            @foreach ($prefecturals as $prefectural)
                                <!-- new region -->
                                @if ($old_region != $prefectural['prefectual_region'])
                                    @if ($old_region !== 0)
                        </tr>
                        <tr>
                            @endif
                            @php
                                $old_region = $prefectural['prefectual_region'];
                                $td_count = 0;
                            @endphp
                            <!-- region name -->
                            <td>
                                @foreach (\App\Modules\Master\Gfh1207\Enums\PrefectualRegionEnum::cases() as $region_names)
                                    @if ($region_names->value == $prefectural['prefectual_region'])
                                        {{ $region_names->label() }}
                                    @endif
                                @endforeach
                            </td>
                            <!-- batch setting -->
                            <td>
                                @if (empty($notify))
                                    <input type="text"
                                        class="form-control c-box--110 prefecturals{{ $m_delivery_types_id }}{{ $tabId }} parent"
                                        data-region="region{{ $prefectural['prefectual_region'] }}"
                                        value="{{ Config::get('Master.const.delivery_readtime_default') }}">日
                                    <a href="javascript:void(0)" class="readtime-batch-setting" id="hokkaido">一括設定</a>
                                @endif
                            </td>
                        @else
                            @if ($td_count == 3)
                        </tr>
                        <tr>
                            <td></td>
                            <td></td>
                            @endif
                            @php
                                if ($td_count == 3) {
                                    $td_count = 0;
                                }
                            @endphp
                            @endif
                            <td>{{ $prefectural['prefectual_name'] }}</td>
                            <td>
                                @if (!empty($notify))
                                    {{ $editDeliveryReadtime['delivery_readtime'][$m_delivery_types_id][$prefectural['m_prefectural_id']] ?? '' }}日
                                @else
                                    <input type="text"
                                        class="form-control c-box--110 prefecturals{{ $m_delivery_types_id }}{{ $tabId }} child"
                                        data-region="region{{ $prefectural['prefectual_region'] }}"
                                        name="delivery_readtime[{{ $m_delivery_types_id }}][{{ $prefectural['m_prefectural_id'] }}]"
                                        value="{{ old('delivery_readtime.' . $m_delivery_types_id . '.' . $prefectural['m_prefectural_id'], $editDeliveryReadtime['delivery_readtime'][$m_delivery_types_id][$prefectural['m_prefectural_id']] ?? Config::get('Master.const.delivery_readtime_default')) }}">日
                                @endif
                            </td>
                            @php $td_count++ @endphp
                            @endforeach
                        </tr>
                    </table>
                </div>
                @php
                    $tabId++; // Increment tab ID
                @endphp
                @endif
                @endforeach
                @endif
                <!-- tabs ここまで -->
            </div><!-- tabs-inner -->
        </div>
    </div>
    <!-- tabs -->
    <div class="u-mt--ss">
        @if (!empty($notify))
            {{-- <a href="{{ $input['previousUrl'] }}" class="btn btn-default btn-lg">キャンセル</a> --}}
            <button type="submit" name="submit" value="cancel" class="btn btn-default btn-lg">キャンセル</button>
        @else
            <a href="{{ route('master.warehouses.list') }}" class="btn btn-default btn-lg">キャンセル</a>
        @endif

        <input type="submit" name="submit" value="{{ $submitValue }}" class="btn btn-success btn-lg">
        @if (!empty($notify))
            <input type="hidden" name="{{ config('define.master.session_key_id') }}" value="{{ $param }}">
        @endif
    </div>
    </form>
    </div>
    @push('css')
        <link rel="stylesheet" href="{{ esm_internal_asset('css/master/gfh_1207/NEMSMH0020.css') }}">
    @endpush
    @push('js')
        <script src="{{ esm_internal_asset('js/master/gfh_1207/NEMSMH0020.js') }}"></script>
    @endpush
@endsection
