{{-- layout設定 --}}
@extends('common.layouts.default')

{{-- タイトル設定 --}}
@section('title', 'エラー')

{{-- ぱんくず設定 --}}
@section('breadcrumb')
<li>エラー</li>
@endsection

@section('content')

    <p class="icon_sy_notice_01">
        エラーが発生しました
    </p>
    <div class="c-box--1200 c-tbl-border-all u-p--sm sy_notice u-mb--ss">
        <div class="error u-mt--xs">
            {{$errorMessage}}
        </div>
    </div>
	<u><a href="javascript:history.back();">一つ前のページへ戻る</a></u>
@endsection
