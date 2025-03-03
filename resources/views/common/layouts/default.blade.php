@php
    $viewVer = 'v1_0';
    $opeInfo = session()->get('OperatorInfo');
    $isAuth = !empty($opeInfo) && array_key_exists('m_account_id', $opeInfo) ? true : false
@endphp
<!DOCTYPE html>
<html lang="ja">
<head>
<meta charset="utf-8">
<title>@yield('title') | eシェルパモール</title>
<meta name="keywords" content="">
<meta name="description" content="">
<meta http-equiv="X-UA-Compatible" content="IE=edge">
<meta name="format-detection" content="telephone=no">
<link rel="stylesheet" href="{{config('env.design_path')}}css/bootstrap.css">
<link rel="stylesheet" href="{{config('env.design_path')}}css/base.css">
<link rel="stylesheet" href="{{config('env.design_path')}}css/modal.css">
<link rel="stylesheet" href="{{config('env.design_path')}}css/esm.css">
<link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.2.0/css/all.css" integrity="sha384-hWVjflwFxL6sNzntih27bfxkr27PmbbK/iSvJ+a4+0owXq79v+lsFkW54bOGbiDQ" crossorigin="anonymous">
<script src="https://ajax.googleapis.com/ajax/libs/jquery/1.12.4/jquery.min.js"></script>
<script src="https://code.jquery.com/ui/1.12.1/jquery-ui.js"></script>
<script src="//cdnjs.cloudflare.com/ajax/libs/jquery-cookie/1.4.1/jquery.cookie.min.js"></script>
<script src="{{config('env.design_path')}}js/bootstrap.min.js"></script>
<script src="{{config('env.design_path')}}js/general.js"></script>
<script src="{{config('env.design_path')}}js/validator.js"></script>
<!-- 郵便番号検索 ここから -->
<script src="https://ajaxzip3.github.io/ajaxzip3.js" charset="UTF-8"></script>
<!-- 郵便番号検索 ここまで -->
<!--　日時ピッカーここから　-->
<link rel="stylesheet" type="text/css" href="{{config('env.design_path')}}css/bootstrap-datetimepicker.css">
<script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.9.0/moment-with-locales.js"></script>
<script src="{{config('env.design_path')}}js/bootstrap-datetimepicker.js"></script>
<!--　日時ピッカーここまで　-->
<!-- タブcssここから -->
<link rel="stylesheet" href="https://code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css">
<!-- タブcssここまで -->
@include('common.elements.error_class_add');
<!--　Microsoft clarityここから　-->
<script type="text/javascript">
    (function(c,l,a,r,i,t,y){
        c[a]=c[a]||function(){(c[a].q=c[a].q||[]).push(arguments)};
        t=l.createElement(r);t.async=1;t.src="https://www.clarity.ms/tag/"+i;
        y=l.getElementsByTagName(r)[0];y.parentNode.insertBefore(t,y);
    })(window, document, "clarity", "script", "{{Config::get('Common.const.CLARITY_TAG_ID')}}");
</script>
<!--　Microsoft clarityここまで　-->
@stack('css')
</head>

<body>
<div class="l-container">
	@include("common.layouts.common_header")
	<div class="l-content clearfix {{ !$isAuth ? 'no-menu' : '' }}" id="js-content">
		@if ($isAuth)
			<div class="l-side">
            <p class="l-side__icon" id="js-sideIcon"></p>
			@include("common.layouts.common_menu")
			</div>
		@endif
		<div class="l-main">
			<div class="l-wrap">
				@include("common.layouts.common_page_header")
				<div class="c-box--1800">
					<!-- メッセージ領域 -->
                    @session('errors')
                    <script>
                    window.addEventListener('load', function(){
                        @foreach($value->keys() as $columnName)
                            document.getElementById("{{$columnName}}").classList.add("error-txtfield");
                        @endforeach
                    })
                    </script>
                    @endsession
                    {{-- 空の配列を無視するため、session()->has()ではなく、empty()で判定する --}}
                    @if(!empty(session('messages')) || !empty(session('errors')))
                    <div class="c-box--1800 c-tbl-border-all u-p--sm sy_notice u-mb--ss">
                        {{-- 通常のメッセージ --}}
                        @session('messages.info.message')
                            @if(is_array($value))
                                @foreach($value as $message)
                                    <p class="icon_sy_notice_03">{{$message}}</p>
                                @endforeach
                            @else
                                <p class="icon_sy_notice_03">{{$value}}</p>
                            @endif
                        @endsession
                        {{-- 入力エラーのメッセージ --}}
                        @if(session()->has('errors'))
                        <p class="icon_sy_notice_01">＜異常＞入力にエラーがあります。</p>
                        @endif
                        {{-- その他のエラーメッセージ --}}
                        @session('messages.error.message')
                            @if(is_array($value))
                                @foreach($value as $message)
                                    <p class="icon_sy_notice_01">{{$message}}</p>
                                @endforeach
                            @else
                                <p class="icon_sy_notice_01">{{$value}}</p>
                            @endif
                        @endsession
                    </div>
                    <!--/sy_notice-->
                    @endif
                    {{-- @if(!empty($searchResult['search_record_count']))
                        @if($searchResult['search_record_count'] < $searchResult['total_record_count'])
                        <div class="c-box--1800 c-tbl-border-all u-p--sm sy_notice u-mb--ss">
                            <p class="icon_sy_notice_02">検索結果の件数が表示可能な件数を超えています。</p>
                        </div><!--/sy_notice-->
                        @endif
                    @endif --}}
                    {{-- @endsection --}}

					<!-- コンテンツ領域 -->
					@yield('content')
				</div>
			</div>
        </div>
		<div class="l-footer u-mt--ml">
			@include("common.layouts.common_footer")
		</div>
	</div>
</div>

<p id="pagetop"><a href="#"><i class="fas fa-angle-up"></i></a></p>

<!-- ツールチップここから -->
<script>
$(function () {
$('[data-toggle="tooltip"]').tooltip();
});
</script>
<!-- ツールチップここまで -->

<!-- ページトップここから -->
<script type="text/javascript">
$(function(){
    $('#pagetop').hide();
    $(window).scroll(function(){
        if ($(this).scrollTop() > 100) {
            $('#pagetop').fadeIn();
        }
        else {
            $('#pagetop').fadeOut();
        }
    });
    $('#pagetop').click(function(){
        $('html,body').animate({
            scrollTop: 0
        }, 300);
        return false;
    });

});
</script>
@stack('js')
<!-- ページトップここまで -->
</body>
</html>
