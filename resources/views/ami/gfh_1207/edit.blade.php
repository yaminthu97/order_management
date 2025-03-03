{{-- GFISMB0110:ページマスタ追加項目編集 --}}
@php
$ScreenCd='GFISMB0110';
@endphp

{{-- layout設定 --}}
@extends('common.layouts.default')

{{-- タイトル設定 --}}
@section('title', 'ページマスタ追加項目編集')

{{-- ぱんくず設定 --}}
@section('breadcrumb')
<li>ページマスタ追加項目編集</li>
@endsection

@section('content')
<div class="c-box--1000">
    <form  method="POST" action="" name="" id="" enctype="multipart/form-data">
        {{ csrf_field() }}
        {{-- SKU情報 --}}
        @include('ami.elements.page.m_ami_page_sku', ['form' => $form])

        {{-- ページ基本情報 --}}
        @include('ami.elements.page.m_ami_page_input', ['form' => $form])

        {{-- 熨斗設定情報 --}}
        @include('ami.elements.page.m_ami_page_noshi', ['form' => $form])

        {{-- 自由項目情報 --}}
        @include('ami.elements.page.m_ami_page_reserve', ['form' => $form])

        <div class="u-mt--ss">
            <button class="btn btn-success btn-lg u-mt--sm" type="submit" name="submit" id="submit_register" value="register">登録</button>
        </div>
    </form>
</div>
@endsection
