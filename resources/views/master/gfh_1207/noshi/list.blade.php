{{-- GFMSMD0010:熨斗マスタ一覧 --}}
@php
$ScreenCd='GFMSMD0010';
@endphp

{{-- layout設定 --}}
@extends('common.layouts.default')

{{-- タイトル設定 --}}
@section('title', '熨斗マスタ一覧')

{{-- ぱんくず設定 --}}
@section('breadcrumb')
<li>熨斗マスタ一覧</li>
@endsection

@section('content')
    <form method="POST" action="" name="Form1" id="Form1">
        {{ csrf_field() }}
        <div>
        @if($paginator)
            @include('common.elements.paginator_header')
            @include('common.elements.page_list_count')
            <br>
            <table class="table table-bordered c-tbl table-link">
                <tr>
                    <th class="c-box--100">ID</th>
                    <th class="c-box--300">熨斗タイプ</th>
                    <th class="c-box--300">付属品グループ</th>
                    <th class="c-box--100">熨斗コード</th>
                    <th class="c-box--100">使用区分</th>
                    <th class="c-box--150">&nbsp;</th>
                </tr>
                @if(!empty($paginator->count()) > 0)
                    @foreach($paginator as $elm)
                        <tr>
                            <td class="u-right"><a href="{{ route('master.noshi.edit', $elm['m_noshi_id']) }}">{{ $elm['m_noshi_id'] }}</a></td>
                            <td>{{ $elm['noshi_type'] }}</td>
                            <td>{{ $elm->attachmentItemGroup ? $elm->attachmentItemGroup->m_itemname_type_name : '' }} </td>
                            <td>{{ $elm['noshi_cd'] }}</td>
                            <td>{{ \App\Enums\DeleteFlg::tryfrom( $elm['delete_flg'] ) ? \App\Enums\DeleteFlg::tryfrom( $elm['delete_flg'] )->label() : '' }}</td>
                            <td class="u-center">
                                <a href="{{ route('master.noshi.template', ['id' => $elm['m_noshi_id']]) }}" class="btn btn-default">テンプレート</a>
                            </td>
                        </tr>
                    @endforeach
                @else
                    <tr>
                        <td colspan="6">熨斗が登録されていません。</td>
                    </tr>
                @endif
            </table>
            @include('common.elements.paginator_footer')
        @endif
        </div>
        <a href="{{ route('master.noshi.new') }}" class="btn btn-default btn-lg">新規登録</a>
    </form>
@endsection
