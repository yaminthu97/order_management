@php
//10:コンタクトセンター、20:受注、30:債権、40:出荷、50:発注・仕入、70:在庫、80:商品・販売、90:コンテンツ
$opeInfo = session()->get('OperatorInfo');
$menuList = [];

if (!empty($opeInfo) && array_key_exists('operation_authority_detail', $opeInfo)) {
    $colOpeAuthDtl = collect($opeInfo['operation_authority_detail']);
    $menuList = $colOpeAuthDtl->map(function($item, $key) {
        return (int)$item['menu_type'];
    })->toArray();
}

$insVer = !empty($ver) ? $ver : 'v1_0';
$path = "App\\Modules\\Common\\CommonModule";
$manager = new $path;

// ページ補足情報表示フラグ
$isSuppleinfo = false;

// ページナビ表示フラグ
$isPageNavi = false;
// ページナビ情報
$pageNaviInfo = [];

if (!empty($ScreenCd)) {
    // 画面情報取得
    $screenInfo = $manager->searchScreens($ScreenCd);
    if (!empty($screenInfo) && 0 < count($screenInfo)) {
        $isSuppleinfo = $screenInfo[0]['suppleinfo_flg'] == 1 ? true : false;
        $isPageNavi = $screenInfo[0]['pagenavi_flg'] == 1 ? true : false;
    }

    if ($isPageNavi) {
        // ページナビ情報取得
        $pageNaviInfo = $manager->searchPagenavi($ScreenCd);
    }
}

// メニューマスタ情報取得
$menuInfo = $manager->searchMenu($menuList);
@endphp

{{--ページ補足情報--}}
@if ($isSuppleinfo)
    @yield('ProductInfo')
@endif

{{--ページナビ情報--}}
@if($isPageNavi && 0 < count($pageNaviInfo))
    <div class="l-side__ttl">ページナビ</div>
    <div class="">
        <ul class="l-side__inr l-side__pagenav">
        {!! $pageNaviInfo[0]['pagenavi_info'] !!}
        </ul>
    </div>
@endif

    <div class="l-side__ttl">メニュー</div>
    <div class="l-side__inr">
@foreach($menuInfo as $menu_1_key => $menu_1)
{{--  @if(empty($menu_1['menu_name']))
        @continue;
    @endif  --}}
        <div class="l-side__head l-side__head--tgl">
            <a class="sideItemFrom" href="#sideItem{{ $menu_1_key }}" data-toggle="collapse">{{ $menu_1['menu_name'] }}</a>
        </div>
    @php
        $tmp = array_keys($menu_1['children']);
        $first = $tmp['0'];
        $last = $tmp[count($tmp) - 1];
    @endphp
    @if($menu_1_key < 5000 || $menu_1_key > 5009)
    <ul class="sideItem l-side__list collapse" id="sideItem{{ $menu_1_key }}">
    @endif
        @foreach($menu_1['children'] as $menu_2_key => $menu_2)
        @if(!empty($menu_2['children']))
            @if(empty($menu_2['menu_name']))
                @continue;
            @endif
                <li>
                    <a class="sideItemFrom" href="#sideItem-order{{ $menu_2_key }}" data-toggle="collapse">{{ $menu_2['menu_name'] }}</a>
                </li>
                <li> 
                <ul  class="sideItem l-side__list__detail collapse" id="sideItem-order{{ $menu_2_key }}">
                @foreach($menu_2['children'] as $menu_3)
                    <li>
                        <a href="{{ $menu_3['menu_uri'] }}">{{ $menu_3['menu_name'] }}</a>
                    </li>
                @endforeach
                </ul>
            </li>
        @else
            @if($menu_2_key == $first)
            <ul class="sideItem l-side__list__detail collapse" id="sideItem{{ $menu_1_key }}">
            @endif
                <li>
                    <a href="{{ $menu_2['menu_uri'] }}">{{ $menu_2['menu_name'] }}</a>
                </li>
            @if($menu_2_key == $last)
            </ul>
            @endif
        @endif
    @endforeach
    @if($menu_1_key < 5000 || $menu_1_key > 5009)
    </ul>
    @endif
@endforeach
    </div>

<script type="text/javascript">
    var mpid = $.cookie('mpid');
    if (mpid != undefined) {
        ms = JSON.parse(mpid);
        ms.forEach(function(value) {
            $(value).addClass('in');
        });
    }

    $('#js-content').on('shown.bs.collapse', function () {
        save();
    });
    $('#js-content').on('hidden.bs.collapse', function () {
        save();
    });
    function save() {
        ms = [];
        $('.sideItemFrom').each(function(index, element){
            href = $(element).attr('href');
            if ($(href).hasClass('in')) {
                ms.push(href);
            }
        });
        $.cookie('mpid', JSON.stringify(ms), {path: '/', expires: 36500});
    }
</script>
