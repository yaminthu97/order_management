{{-- GFISMD0020:熨斗マスタテンプレート登録・更新 --}}
@php
$ScreenCd='GFISMD0020';
@endphp

{{-- layout設定 --}}
@extends('common.layouts.default')

{{-- タイトル設定 --}}
@section('title', '熨斗マスタテンプレート登録・更新')

{{-- ぱんくず設定 --}}
@section('breadcrumb')
<li>熨斗マスタテンプレート登録・更新</li>
@endsection

@section('content')
<form  method="GET" action="" name="Form1" id="Form1">
    {{ csrf_field() }}
    <div id="global_error" class="text-danger mt-20"></div>
    <div id="global_success" class="text-success mt-20"></div>
    <div id="search_error" class="text-danger mt-20 mb-20"></div>
    <div id="search_error_format" class="text-danger mt-20 mb-20"></div>
    <div id="download_class_global" class="text-danger mt-20 mb-20"></div>
    <div>
        <table class="table table-bordered c-tbl c-tbl--800">
            <tr>
                <th class="c-box--200">熨斗ID</th>
                <td>
                    {{$editData['m_noshi_id'] ?? ''}}
                    <input type="hidden" name="m_noshi_id" id="m_noshi_id" value="{{$editData['m_noshi_id'] ?? ''}}">
                </td>
            </tr>
            <tr>
                <th class="c-box--200">使用区分</th>
                <td>
                    <input type="hidden" name="delete_flg" value="{{$editData['delete_flg'] ?? ''}}">
                    @foreach (\App\Enums\DeleteFlg::cases() as $target)
                        @if($target->value == $editData['delete_flg'])
                        {{$target->label()}}
                        @endif
                    @endforeach
                </td>
            </tr>
            <tr>
                <th class="c-box--200">熨斗タイプ</th>
                <td>
                    {{$editData['noshi_type'] ?? ''}}
                    <input type="hidden" name="noshi_type" value="{{$editData['noshi_type'] ?? ''}}">
                </td>
            </tr>
            <tr>
                <th class="c-box--200">熨斗コード</th>
                <td>
                    {{$editData['noshi_cd'] ?? ''}}
                    <input type="hidden" name="noshi_cd" value="{{$editData['noshi_cd'] ?? ''}}">
                </td>
            </tr>
            <tr>
                <th class="c-box--200">熨斗種類名</th>
                <td>
                    <select name="m_noshi_format_id" id="m_noshi_format_id" class="form-control c-box--200">
                        <option></option>
                    @foreach($viewExtendData['noshi_formats'] as $elm)
                        <option value="{{$elm->m_noshi_format_id}}">{{$elm->noshi_format_name}}</option>
                    @endforeach
                    </select>
                    <div id="search_error_format_index" class="text-danger mt-20 mb-20"></div>
                    <input type="hidden" id="m_noshi_format_selected" value="">
                </td>
            </tr>
        </table>
		<div class="u-mt--sm">
            <button type="button" class="btn btn-success action_search btn-lg">検索</button>
        </div>

        <div class="u-mt--sl">
            <table class="table table-bordered c-tbl table-link">
                <thead>
                    <tr>
                        <th class="c-box--200">名入パターン名</th>
                        <th class="c-box--60">会社名</th>
                        <th class="c-box--60">部署名</th>
                        <th class="c-box--50">肩書</th>
                        <th class="c-box--50">苗字</th>
                        <th class="c-box--50">名前</th>
                        <th class="c-box--50">ルビ</th>
                        <th class="c-box--150">使用区分</th>
                        <th class="c-box--120">テンプレート名</th>
                        <th class="c-box--100"></th>
                        <th class="c-box--80"></th>
                        <th class="c-box--100"></th>
                    </tr>
                </thead>
                <tbody id="detail_list">
                </tbody>
            </table>
        </div>
    </div>
    <div class="u-mt--ss">
        <button type="button" class="btn btn-success action_append btn-lg">追加</button>
        <br>
        <br>
        <a href="{{ route('master.noshi.list') }}" class="btn btn-default btn-lg u-mt--sm">キャンセル</a>
    </div>
</form>
<!-- 隠れたテンプレート -->
<!-- <table class="table table-bordered c-tbl table-link" > -->
<form method="POST" action="" name="Form2" id="Form2" enctype="multipart/form-data" >
    {{ csrf_field() }}
    <div id="list-class" data-url="{{ route('master.noshi-templates.list') }}"></div>
    <div id="update-class" data-url="{{ route('master.noshi-templates.update') }}"></div>
        <table class="table table-bordered c-tbl table-link dis_none" >
            <thead>
                <tr>
                    <th class="c-box--300">名入パターン名</th>
                    <th class="c-box--60">会社名</th>
                    <th class="c-box--60">部署名</th>
                    <th class="c-box--50">肩書</th>
                    <th class="c-box--50">苗字</th>
                    <th class="c-box--50">名前</th>
                    <th class="c-box--50">ルビ</th>
                    <th class="c-box--150">使用区分</th>
                    <th class="c-box--120">テンプレート名</th>
                    <th class="c-box--100"></th>
                    <th class="c-box--80"></th>
                    <th class="c-box--100"></th>
                </tr>
            </thead>
            <tbody id="detail_list_template">
                <tr id="detail_##index##">
                    <td type="hidden" class="dis_none">
                        <input type="hidden" id="detail_##index##_m_noshi_detail_id" class="m_noshi_detail_id" value="">

                        <!--テスト追加-->
                        <input type="hidden" name="m_account_id" id="detail_##index##_m_account_id" class="m_account_id" value="">
                        <!--テスト追加-->
                    </td>

                    <td>
                        <select name="m_noshi_naming_pattern_id" id="detail_##index##_m_noshi_naming_pattern_id" class="form-control c-box--full m_noshi_naming_pattern_id">
                            <option></option>
                        @foreach($viewExtendData['naming_patterns'] as $details)
                            <option value="{{$details->m_noshi_naming_pattern_id}}">{{$details->pattern_name}}</option>
                        @endforeach
                        </select>
                        <div id="detail_##index##_m_noshi_naming_pattern_id_error" class="text-danger"></div>
                    </td>
                    <td>
                        <div class="company_name_count" ></div>
                    </td>
                    <td>
                        <div class="section_name_count" ></div>
                    </td>
                    <td>
                        <div class="title_count" ></div>
                    </td>
                    <td>
                        <div class="f_name_count" ></div>
                    </td>
                    <td>
                        <div class="name_count" ></div>
                    </td>
                    <td>
                        <div class="ruby_count"></div>
                    </td>
                    <td>
                        <div class="vertical-radio-group">
                            @foreach (\App\Enums\DeleteFlg::cases() as $target)
                                <input class="form-check-input" type="radio" name="delete_flg_##index##" id="detail_##index##_delete_flg_{{$target->value}}" value="{{ $target->value }}">
                                <label class="form-check-label" for="detail_##index##_delete_flg_{{$target->value}}">{{ $target->label() }}</label>
                            @endforeach
                        </div>
                        <div id="detail_##index##_delete_flg_error" class="text-danger"></div>
                    </td>
                    <td>
                        <div class="template_file_name" id="template_file_name"></div>
                    </td>
                    <td>
                        <div>
                            <span class="">
                                <input type="file"  name="file_##index##" accept=".pptx">
                            </span>
                        </div>
                        <div id="detail_##index##_file_error" class="text-danger"></div>
                    </td>
                    <td>
                        <input type="submit" name="submit_register" id="submit_register_##index##" class="btn btn-success btn-lg u-mt--sm dis_none"> 
                        <button type="button" class="submit_register btn_style_up">更　新</button>
                    </td>
                    <td>
                        <button type="button" class="submit_download btn btn-primary btn_style_dl" data-id="detail_##index##_m_noshi_detail_id" data-m_account_id="{{ $editData['m_account_id'] ?? '' }}">ダウンロード</button>
                        <div id="download_class" class="text-danger" data-url="{{ route('master.noshi-templates.download') }}"></div>
                    </td>
                </tr>
            </tbody>
        </table>
</form>

@push('css')
<link rel="stylesheet" href="{{ esm_internal_asset('css/master/gfh_1207/GFISMD0020.css') }}">
@endpush

@push('js')
<script src="{{ esm_internal_asset('js/master/gfh_1207/GFISMD0020.js') }}"></script>
@endpush

@endsection
