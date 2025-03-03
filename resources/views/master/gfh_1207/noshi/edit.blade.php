{{-- GFMSMD0020:熨斗マスタ登録・更新 --}}
@php
$ScreenCd='GFMSMD0020';
@endphp

{{-- layout設定 --}}
@extends('common.layouts.default')

{{-- タイトル設定 --}}
@section('title', '熨斗マスタ登録・更新')

{{-- ぱんくず設定 --}}
@section('breadcrumb')
<li>熨斗マスタ登録・更新</li>
@endsection

@section('content')
<form  method="POST" action="" name="Form1" id="Form1">
    {{ csrf_field() }}
    <div>
        <table class="table table-bordered c-tbl c-tbl--800">
            <tr>
                <th class="c-box--200">熨斗ID</th>
                <td>
                    <input type="hidden" name="m_noshi_id" value="{{ old('m_noshi_id', $editData['m_noshi_id'] ?? '') }}">
                    {{ $editData['m_noshi_id'] ?? '' }}
                    @include('common.elements.error_tag', ['name' => 'm_noshi_id'])
                </td>
            </tr>
            <tr>
                <th class="must">熨斗タイプ</th>
                <td>
                    <input class="form-control c-box--full" type="text" name="noshi_type" value="{{ old('noshi_type', $editData['noshi_type'] ?? '') }}">
                    @include('common.elements.error_tag', ['name' => 'noshi_type'])
                </td>
            </tr>
            <tr>
                <th class="must">使用区分</th>
                <td>
                    @foreach (\App\Enums\DeleteFlg::cases() as $target)
                        <input class="form-check-input" type="radio" name="delete_flg" id="radio{{ $target->value }}" value="{{ $target->value }}" @if( old('delete_flg', $editData['delete_flg'] ?? \App\Enums\DeleteFlg::Use->value ) == $target->value ) checked @endif>
                        <label class="form-check-label" for="radio{{ $target->value }}">{{ $target->label() }}</label>
                    @endforeach
                    @include('common.elements.error_tag', ['name' => 'delete_flg'])
                </td>
            </tr>
            <tr>
                <th class="must">種別</th>
                <td>
                    <select class="form-control" id="attachment_item_group_id" name="attachment_item_group_id">
                        <option value="" @if( old('attachment_item_group_id', $editData['attachment_item_group_id'] ?? '' ) == 0 )selected @endif></option>
                        @if( isset( $viewExtendData['attachment_item_group_list'] ) )
                            @foreach( $viewExtendData['attachment_item_group_list'] as $group )
                                <option value="{{ $group['m_itemname_types_id'] }}" @if( old('attachment_item_group_id', $editData['attachment_item_group_id'] ?? 0 ) == $group['m_itemname_types_id'] )selected @endif>
                                    {{ $group['m_itemname_type_name'] }}
                                </option>
                            @endforeach
                        @endif
					</select>
                    @include('common.elements.error_tag', ['name' => 'attachment_item_group_id'])
                </td>
            </tr>
            <tr>
                <th>表書き（初期値）</th>
                <td>
                    <input class="form-control c-box--full" type="text" name="omotegaki" value="{{ old('omotegaki', $editData['omotegaki'] ?? '') }}">
                    @include('common.elements.error_tag', ['name' => 'omotegaki'])
                </td>
            </tr>
            <tr>
                <th>熨斗コード</th>
                <td>
                    <input class="form-control c-box--full" type="text" name="noshi_cd" value="{{ old('noshi_cd', $editData['noshi_cd'] ?? '') }}">
                    @include('common.elements.error_tag', ['name' => 'noshi_cd'])
                </td>
            </tr>
        </table>

        <div class="u-mt--sl">
            <table class="table table-bordered c-tbl table-link">
                <tr>
                    <th class="c-box--100">熨斗種類ID</th>
                    <th class="c-box--300">熨斗種類名</th>
                    <th class="c-box--200">使用区分</th>
                </tr>
                @foreach( old('noshiFormatList', $editData['noshiFormatList'] ?? [] ) as $idx => $format )
                    <tr>
                        <td>
                            <input type="hidden" name="noshiFormatList[{{ $idx }}][m_noshi_format_id]" value="{{ old('noshiFormatList[' . $idx . '][m_noshi_format_id]', $format['m_noshi_format_id'] ?? '') }}">{{ old('noshiFormatList[' . $idx . '][m_noshi_format_id]', $format['m_noshi_format_id'] ?? '') }}
                        </td>
                        <td>
                            <input class="form-control c-box--full" type="text" name="noshiFormatList[{{ $idx }}][noshi_format_name]" value="{{ old('noshiFormatList[' . $idx . '][noshi_format_name]', $format['noshi_format_name'] ?? '') }}">
                            @include('common.elements.error_tag', ['name' => 'noshiFormatList.' . $idx . '.noshi_format_name'])
                        </td>
                        <td>
                            @foreach (\App\Enums\DeleteFlg::cases() as $target)
                                <input class="form-check-input" type="radio" name="noshiFormatList[{{ $idx }}][delete_flg]" id="format_radio[{{ $idx }}]{{ $target->value }}" value="{{ $target->value }}"  @if( old('noshiFormatList[' . $idx . '][delete_flg]', $format['delete_flg'] ?? \App\Enums\DeleteFlg::Use->value ) == $target->value ) checked @endif>
                                <label class="form-check-label" for="format_radio[{{ $idx }}]{{ $target->value }}">{{ $target->label() }}</label>
                            @endforeach
                        </td>
                    </tr>
                @endforeach
            </table>
            <button class="btn btn-success btn-lg u-mt--sm" type="submit" name="submit" id="submit_add_format" value="add_format">追加</button>
        </div>
    </div>
    <div class="u-mt--ss">
        <a href="{{ route('master.noshi.list') }}" class="btn btn-default btn-lg u-mt--sm">キャンセル</a>
        &nbsp;&nbsp;
        <button class="btn btn-success btn-lg u-mt--sm" type="submit" name="submit" id="submit_register" value="register">登録</button>
        @include('common.elements.on_enter_script', ['target_button_name' => 'submit_register'])
    </div>
</form>
@endsection
