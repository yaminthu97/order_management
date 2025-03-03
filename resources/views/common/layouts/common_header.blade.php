@php
$opeInfo = session()->get('OperatorInfo');
$userName = Config::get('Common.const.commonHeader.defaultUserName');
$noticeList = [];
$alertList = [];
$noticeCount = 0;
$alertCount = 0;
$isAuth = false;

$insVer = !empty($ver) ? $ver : 'v1_0';
$path = "App\\Modules\\Common\\CommonModule";
$manager = new $path;

// ToDo：お知らせ情報、アラート情報を取得して、情報を表示する。

if (!empty($opeInfo)) {
    $userName = $opeInfo['m_operator_name'];

	if (count($opeInfo['CommonHeader']['NoticeInfo']) === 0) {
		$noticeList += [Config::get('Common.const.commonHeader.noNoticeMessage')];
	} else {
	    $noticeList = $opeInfo['CommonHeader']['NoticeInfo'];
		$noticeCount = count($noticeList);
	}

	if (count($opeInfo['CommonHeader']['AlertInfo']) === 0) {
	    $alertList += [Config::get('Common.const.commonHeader.noAlertMessage')];
	} else {
		$alertList = $opeInfo['CommonHeader']['AlertInfo'];
		$alertCount = count($alertList);
	}

    $isAuth = true;
} else {
	$noticeList += [Config::get('Common.const.commonHeader.noNoticeMessage')];
    $alertList += [Config::get('Common.const.commonHeader.noAlertMessage')];
}
@endphp
<style>
	.modal-wrap.from-top {
		min-height: 150px;
	}

	.modal-content-btn {
		position: absolute;
		right: 10px;
		bottom: 10px;
		width: 150px;
		height: 50px;
	}

	.modal-btn-text {
		padding-top: 5px;
		font-size: 20px;
	}

	header.l-header.clearfix {
		width: 100%;
		min-width: 1200px;
	}
</style>
<div class="headerFix">
    <header class="l-header clearfix">
		<h1 class="l-header__logo">
			<a href="{{ Config::get('env.app_subsys_url.common') }}top">
				<img src="{{ Config::get('env.app_subsys_url.common') }}/images/common/logo_01.png" alt="シェルパモール">
			</a>
		</h1>
        <ul class="l-header__info">
            <li><a href="{{ Config::get('Common.const.MANUAL_SITE_URL') }}" target="_blank"><i class="fas fa-book"></i>マニュアル</a></li>
            <li>
                @if ($isAuth)
                <a href="#login-box" data-toggle="collapse" class="collapsed l-header__name"><i class="fas fa-user-circle"></i>{{ $userName }} 様</a>
                @else
                <i class="fas fa-user-circle"></i>{{ $userName }}様
                @endif
            </li>
        </ul>
        <form id="logout" method="post" action="{{ Config::get('env.app_subsys_url.common') }}logout/auth">
            {{ csrf_field() }}
            <div class="l-side__list collapse" id="login-box">
                <div class="login-box-inner">
                    <button class="btn btn-primary" onclick="submit()">ログアウト</button>
                </div>
            </div>
        </form>
    </header>
</div>
<div class="modals">
	<input id="modal-trigger-info" class="checkbox" type="checkbox" data-an-typ="modal-trigger-alert">
    <div class="modal-overlay">
		<label for="modal-trigger-info" class="o-close"></label>
        <div class="modal-wrap from-top">
			<label for="modal-trigger-info" class="close">&#10006;</label>
			<div class="c-box--1000 modal-contents">
				@foreach ($noticeList as $notice)
					<p>{{ $notice }}</p>
				@endforeach
			</div>
			@if ($isAuth)
			<label for="modal-trigger-info" class="btn btn-primary modal-content-btn">
				<p class="modal-btn-text">確認</p>
			</label>
			@endif
		</div>
	</div>
</div>
<div class="modals">
	<input id="modal-trigger-alert" class="checkbox" type="checkbox" data-an-typ="modal-trigger-info">
	<div class="modal-overlay">
		<label for="modal-trigger-alert" class="o-close"></label>
		<div class="modal-wrap from-top">
			<label for="modal-trigger-alert" class="close">&#10006;</label>
			<div class="c-box--1000 modal-contents">
            @foreach ($alertList as $alert)
                <p>{{ $alert }}</p>
            @endforeach
        </div>
			@if ($isAuth)
			<label for="modal-trigger-alert" class="btn btn-primary modal-content-btn">
				<p class="modal-btn-text">確認</p>
			</label>
			@endif
		</div>
    </div>
</div>

<script type="text/javascript">
	$(document).ready(function(){
		$('#modal-trigger-info').on('click', function(){
			$('#'+$(this).data('an-typ')).prop('checked', false)
		});

		$('#modal-trigger-alert').on('click', function(){
			$('#'+$(this).data('an-typ')).prop('checked', false)
		});

		$('.modal-content-btn').on('click', function(){
            var t = $(this).prev('.modal-contents');
            var b = this;
            var lbln = $(t).prev('label').attr('for');
            $('label[for="'+lbln+'"]').next('span.badge').empty();

			setTimeout(function(){
				$(t).children('p').empty();
				$(t).append('<p>お知らせはありません。</p>');
				$(b).remove();
			}, 500);

			var u = '';
			if (lbln === 'modal-trigger-info') {
				u = '/common/registerNoticeConfirm';
			} else if (lbln === 'modal-trigger-alert') {
				u = '/common/registerAlertConfirm';
			} else {
				return;
			}

			$.ajax({
				url: u,
				type:'get',
				dataType: 'json',
				timeout: 5000,
			}).done(function(data) {
			}).fail(function(XMLHttpRequest, textStatus, errorThrown) {
			})
        });
	});
</script>