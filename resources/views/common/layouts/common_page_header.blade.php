@php
$opeInfo = session()->get('OperatorInfo');
$isAuth = !empty($opeInfo) ? true : false;
$screenName = '';
$linkScreenCd = '';
$manual_link_uri = '';

if ($isAuth && !empty($ScreenCd)) {
    $linkScreenCd = $ScreenCd;
    $insVer = !empty($ver) ? $ver : 'v1_0';
    $path = "App\\Modules\\Common\\CommonModule";
    $manager = new $path;
    $bcResult = $manager->searchBreadcrumb($ScreenCd);

    if (0 < count($bcResult)) {
        $hierarchyDef = Config::get('Common.const.Breadcrumb_Hierarchy');
        foreach($hierarchyDef as $def) {
            if($def == 'display')
            {
                $breadcrumbs[] = [
                    'name' => $bcResult[$def.'_screen_name'],
                    'uri' => $bcResult[$def.'_screen_uri'],
                    'display' => 1
                ];
            }
            else{
                $breadcrumbs[] = [
                    'name' => $bcResult[$def.'_screen_name'],
                    'uri' => $bcResult[$def.'_screen_uri'],
                    'display' => 2
                ];
            }
        }
    } else {
        $breadcrumbs = [];
    } 
    
    $screenInfo = $manager->searchScreens($ScreenCd);

    if (!empty($screenInfo) && 0 < count($screenInfo)) {
        $screenName = $screenInfo[0]['screen_name'];
        $manual_link_uri = $screenInfo[0]['manual_link_uri'];
    }
}
@endphp

@if ($isAuth && !empty($breadcrumbs))
<div class="cp_breadcrumb">
    <ul class="breadcrumbs">
        @foreach($breadcrumbs as $item)
            @if (empty($item['uri']) || empty($item['name']) || empty($item['display'])) @continue @endif
            @if($item['display'] == 2)
            <li><a href="{{ $item['uri'] }}">{{ $item['name'] }}</a></li>
            @else
            <li>{{ $item['name'] }}</li>
            @endif
        @endforeach
    </ul>
</div>
@endif

<style>
    .page-title {
        float: left;
    }

    .manual-link-box {
        min-width: 600px;
        height: 50px;
        display:flow-root;
        padding-bottom:90px;
    }

    .manual-link {
        float: right;
        margin-right: 50px;
        padding-top: 30px;
    }

    .page-header-box {
        margin-top: 25px;
    }

    .page-header-contents {
        padding-top: 5px;
        padding-left: 10px;
    }
</style>

<div class="manual-link-box">
    <div class="page-title"><h2 class="c-ttl--01">{{ $screenName }}</h2></div>
    <div class="manual-link"><a href="{{ Config::get('Common.const.MANUAL_SITE_URL') . $manual_link_uri }}" target="_blank">マニュアルリンク</a></div>
</div>
@yield("PageHeaderContents")

@if (isset($total_record_count) && $total_record_count == 0)
    <div class="news c-tbl-border-all sy_notice page-header-box">
        <p class="page-header-contents">{{ Config::get('Common.const.PageHeader.NoResultsMessage') }}</p>
    </div>
@endif
