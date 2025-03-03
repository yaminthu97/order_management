{{-- NEMSMC0020:社員マスタ登録・更新 --}}
@php
    $ScreenCd = 'NEMSMC0020';
@endphp

{{-- layout設定 --}}
@extends('common.layouts.default')

{{-- タイトル設定 --}}
@section('title', '社員マスタ登録・更新')

{{-- ぱんくず設定 --}}
@section('breadcrumb')
    <li>社員マスタ登録・更新</li>
@endsection

@section('content')

    <div class="u-mt--xs">
        <form enctype="multipart/form-data" method="POST" action="" name="Form1" id="Form1">
            {{ csrf_field() }}

            {{-- 検索入力フォーム --}}
            <table class="table table-bordered c-tbl c-tbl--800">
                <tr>
                    <th class="c-box--300">社員ID</th>
                    <td>{{ !empty($editRow['m_operators_id']) ? $editRow['m_operators_id'] : '自動' }}
                    </td>
                    <input type="hidden" name="m_operators_id"
                        value="{{ old('m_operators_id', $editRow->m_operators_id ?? '') }}">
                </tr>
                <tr>
                    <th class="c-box--150 must">使用区分</th>
                    <td>
                        @foreach (\App\Enums\DeleteFlg::cases() as $deleteFlg)
                            <label class="radio-inline">
                                <input type="radio" name="delete_flg" value="{{ $deleteFlg->value }}"
                                    @checked(
                                        ($loop->first && empty($editRow['delete_flg'])) ||
                                            (old('delete_flg') || (isset($editRow['delete_flg']) && $editRow['delete_flg'] == $deleteFlg->value)))>
                                {{ $deleteFlg->label() }}
                            </label>
                        @endforeach

                        @include('common.elements.error_tag', ['name' => 'delete_flg'])
                    </td>
                </tr>
                <tr>
                    <th class="c-box--150 must">社員名</th>
                    <td>
                        <input type="text" class="form-control c-box--300" name="m_operator_name"
                            value="{{ old('m_operator_name', $editRow->m_operator_name ?? '') }}">
                        @include('common.elements.error_tag', ['name' => 'm_operator_name'])
                    </td>
                </tr>
                <tr>
                    <th class="c-box--150">メールアドレス</th>
                    <td>
                        <input type="text" class="form-control c-box--300" name="m_operator_email"
                            value="{{ old('m_operator_email', $editRow->m_operator_email ?? '') }}">
                        @include('common.elements.error_tag', ['name' => 'm_operator_email'])
                    </td>
                </tr>
                @php
                    $isSystemAdmin =
                        $operatorUserType === \App\Modules\Master\Gfh1207\Enums\UserTypeEnum::SYSTEM_ADMIN->value;
                    $isGeneralUser =
                        $operatorUserType === \App\Modules\Master\Gfh1207\Enums\UserTypeEnum::GENERAL_USER->value;
                    $isStoreManager =
                        $operatorUserType === \App\Modules\Master\Gfh1207\Enums\UserTypeEnum::STORE_MANAGER->value;
                    $isEditMode = !empty($editRow['m_operators_id']);
                @endphp

                @if ($isSystemAdmin)
                    <tr>
                        <th class="c-box--150 must">ユーザ種類</th>
                        <td>
                            <select name="user_type" class="form-control c-box--200">
                                @foreach (\App\Modules\Master\Gfh1207\Enums\UserTypeEnum::cases() as $userType)
                                    <option value="{{ $userType->value }}"
                                        {{ old('user_type', $editRow['user_type'] ?? '') == $userType->value ? 'selected' : '' }}>
                                        {{ $userType->label() }}
                                    </option>
                                @endforeach
                            </select>
                            @include('common.elements.error_tag', ['name' => 'user_type'])
                        </td>
                    </tr>
                @endif

                @if ($isGeneralUser || $isStoreManager)
                    <input type="hidden" name="user_type"
                        value="{{ \App\Modules\Master\Gfh1207\Enums\UserTypeEnum::GENERAL_USER->value }}">
                @endif

                @if ($isEditMode && $isStoreManager)
                    <input type="hidden" name="user_type" value="{{ $editRow['user_type'] }}">
                @endif
                <tr>
                    <th class="c-box--150 must">操作権限</th>
                    <td>
                        <select class="form-control c-box--120" name="m_operation_authority_id"
                            @if ($isGeneralUser) disabled @endif>
                            @if(isset($operationAuthorities))
                                @foreach ($operationAuthorities as $tableIdName1 => $tableIdValue1)
                                    <option value="{{ $tableIdValue1 }}"
                                        {{ old('m_operation_authority_id', $editRow['m_operation_authority_id'] ?? '') == $tableIdValue1 ? 'selected' : '' }}>
                                        {{ $tableIdName1 }}
                                    </option>
                                @endforeach
                            @endif
                        </select>
                        @if ($isEditMode && $isGeneralUser)
                            <input type="hidden" name="m_operation_authority_id"
                                value="{{ $editRow['m_operation_authority_id'] }}">
                        @endif
                        @include('common.elements.error_tag', ['name' => 'm_operation_authority_id'])
                    </td>
                </tr>
                <tr>
                    <th class="c-box--150 must">対応履歴権限</th>
                    <td>
                        <select name="cc_authority_code" class="form-control c-box--200">
                            @foreach (\App\Modules\Customer\Gfh1207\Enums\AuthorityCode::cases() as $authorityCode)
                                <option value="{{ $authorityCode->value }}"
                                    {{ old('cc_authority_code', isset($editRow->cc_authority_code) ? $editRow->cc_authority_code : '') == $authorityCode->value ? 'selected' : '' }}>
                                    {{ $authorityCode->label() }}
                                </option>
                            @endforeach
                        </select>
                        @include('common.elements.error_tag', ['name' => 'cc_authority_code'])
                    </td>
                </tr>
                <tr>
                    <th class="c-box--150 must">ログインID</th>
                    <td>
                        <input type="text" class="form-control c-box--300" name="login_id"
                            value="{{ old('login_id', $editRow->login_id ?? '') }}">
                        @include('common.elements.error_tag', ['name' => 'login_id'])
                    </td>
                </tr>
                <tr>
                    <th class="c-box--150 {{ empty($editRow['m_operators_id']) ? 'must' : '' }}">ログインパスワード</th>
                    <td>
                        <input type="password" class="form-control c-box--300" name="login_password"
                            value="{{ old('login_password') }}">
                        @include('common.elements.error_tag', ['name' => 'login_password'])
                            <p class="password-rule">
                                【パスワードの制限】<br>
                                使用可能文字：半角英数字記号(!@#$%^&*()_+-=[]{})<br>
                                文字数：12文字以上　上限はなし<br>
                                文字種：半角英字大文字・小文字・数字・記号を1文字以上含むこと<br>
                                ログインパスワードの変更は24時間に1度までです<br>
                                過去6回使用したログインパスワードには変更できません<br>
                                @if(!empty($editRow['m_operators_id']))
                                    ログインパスワードを変更しない場合、入力の必要はありません
                                @endif
                            </p>
                    </td>
                </tr>
                <tr>
                    <th class="c-box--150 {{ empty($editRow['m_operators_id']) ? 'must' : '' }}">パスワードを入力（確認）</th>
                    <td>
                        <input type="password" class="form-control c-box--300" name="login_password_confirmation"
                            value="{{ old('login_password_confirmation') }}">
                        @include('common.elements.error_tag', ['name' => 'login_password_confirmation'])
                    </td>
                </tr>
                <tr>
                    <th class="c-box--150 must">多要素認証</th>
                    <td>
                        @php
                            if ($editRow['g2fa_key'] != '0' && $editRow['g2fa_key'] != '1') {
                                $google2fa = new \PragmaRX\Google2FA\Google2FA();

                                $google2fa_url = $google2fa->getQRCodeUrl(
                                    $account_cd,
                                    '@' . $editRow['login_id'],
                                    $editRow['g2fa_key']
                                );
                            }
                        @endphp
                        <div class="radio-inline">
                            <label>
                                <input type="radio" name="g2fa_key"
                                    value="{{ \App\Modules\Master\Gfh1207\Enums\MultiFactorAuthentication::Use->value }}"
                                    @if (old('g2fa_key', $editRow['g2fa_key'] ?? '') !=
                                            \App\Modules\Master\Gfh1207\Enums\MultiFactorAuthentication::Notuse->value) checked @endif>
                                {{ \App\Modules\Master\Gfh1207\Enums\MultiFactorAuthentication::Use->label() }}
                            </label>
                        </div>
                        <div class="radio-inline">
                            <label>
                                <input type="radio" name="g2fa_key"
                                    value="{{ \App\Modules\Master\Gfh1207\Enums\MultiFactorAuthentication::Notuse->value }}"
                                    @if (old('g2fa_key', $editRow['g2fa_key']) == \App\Modules\Master\Gfh1207\Enums\MultiFactorAuthentication::Notuse->value) checked
                                @elseif (is_null(old('g2fa_key')) && is_null($editRow['g2fa_key'])) checked @endif>
                                {{ \App\Modules\Master\Gfh1207\Enums\MultiFactorAuthentication::Notuse->label() }}
                            </label>
                        </div>
                        @if ($editRow['g2fa_key'] != '0' && $editRow['g2fa_key'] != '1' && !empty($editRow['g2fa_key']))
                            <div id="qrcode"></div>
                            <script>
                                $(function() {
                                    new QRCode(document.getElementById("qrcode"), "{!! $google2fa_url !!}");
                                });
                            </script>
                        @endif
                        @include('common.elements.error_tag', ['name' => 'g2fa_key'])
                    </td>
                </tr>
            </table>
            <div class="u-mt--sm">
                <a href="{{ route('master.operators.list') }}" class="btn btn-default btn-lg">キャンセル</a>
                <input type="submit" name="submit" value="確認" class="btn btn-success btn-lg">
            </div>
        </form>
    </div>
    @push('css')
        <link rel="stylesheet" href="{{ esm_internal_asset('css/master/gfh_1207/NEMSMC0020.css') }}">
    @endpush
    <script src="//cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>
@endsection
