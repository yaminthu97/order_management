{{-- NEMSMC0030:社員マスタ確認画面 --}}
@php
    $ScreenCd = 'NEMSMC0030';
@endphp

{{-- layout設定 --}}
@extends('common.layouts.default')

{{-- タイトル設定 --}}
@section('title', '社員マスタ確認画面')

{{-- ぱんくず設定 --}}
@section('breadcrumb')
    <li>社員マスタ確認画面</li>
@endsection

@section('content')
    @if (!empty($viewMessage))
        <div class="c-box--1700 c-tbl-border-all u-p--sm sy_notice u-mb--ss">
            @foreach ($viewMessage as $message)
                <p class="icon_sy_notice_03">{{ $message }}</p>
            @endforeach
        </div>
    @endif

    <div class="u-mt--xs">
        <form method="POST" action="">
            @csrf
            @if ($mode == 'edit')
                @method('PUT')
            @endif
            {{-- 検索入力フォーム --}}
            <table class="table table-bordered c-tbl c-tbl--800" style="table-layout: fixed; word-break: break-all;">
                <tr>
                    <th class="c-box--300">社員ID</th>
                    <td>{{ !empty($editRow['m_operators_id']) ? $editRow['m_operators_id'] : '自動' }}
                        <input type="hidden" name="m_operators_id" value="{{ !empty($editRow['m_operators_id']) ? $editRow['m_operators_id'] : '' }}">
                    </td>
                </tr>
                <tr>
                    <th class="c-box--150 must">使用区分</th>
                    <td>
                        @foreach (\App\Enums\DeleteFlg::cases() as $deleteFlg)
                            @if (old('delete_flg', $editRow['delete_flg'] ?? '') == $deleteFlg->value)
                                {{ $deleteFlg->label() }}
                            @endif
                        @endforeach
                    </td>
                </tr>
                <tr>
                    <th class="c-box--150 must">社員名</th>
                    <td>
                        {{ old('m_operator_name', $editRow['m_operator_name'] ?? '') }}
                    </td>
                </tr>
                <tr>
                    <th class="c-box--150">メールアドレス</th>
                    <td>{{ old('m_operator_email', $editRow['m_operator_email'] ?? '') }}
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
                            @foreach (\App\Modules\Master\Gfh1207\Enums\UserTypeEnum::cases() as $userType)
                                @if (old('user_type', $editRow['user_type'] ?? '') == $userType->value)
                                    {{ $userType->label() }}
                                @endif
                            @endforeach
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
                        @if(isset($operationAuthorities))
                            @foreach ($operationAuthorities as $tableIdName1 => $tableIdValue1)
                                @if (old('m_operation_authority_id', $editRow['m_operation_authority_id'] ?? '') == $tableIdValue1)
                                    {{ $tableIdName1 }}
                                @endif
                            @endforeach
                        @endif
                        @if ($isEditMode && $isGeneralUser)
                            <input type="hidden" name="m_operation_authority_id"
                                value="{{ $editRow['m_operation_authority_id'] }}">
                        @endif
                    </td>
                </tr>
                <tr>
                    <th class="c-box--150 must">対応履歴権限</th>
                    <td>
                        @foreach (\App\Modules\Customer\Gfh1207\Enums\AuthorityCode::cases() as $authorityCode)
                            @if (old('cc_authority_code', $editRow['cc_authority_code'] ?? '') == $authorityCode->value)
                                {{ $authorityCode->label() }}
                            @endif
                        @endforeach

                    </td>
                </tr>
                <tr>
                    <th class="c-box--150 must">ログインID</th>
                    <td>
                        {{ old('login_id', $editRow['login_id'] ?? '') }}
                    </td>
                </tr>
                <tr>
                    <th class="c-box--150 {{ empty($editRow['m_operators_id']) ? 'must' : '' }}">ログインパスワード</th>
                    <td>******</td>
                </tr>
                <tr>
                    <th class="c-box--150 {{ empty($editRow['m_operators_id']) ? 'must' : '' }}">パスワードを入力（確認）</th>
                    <td>******</td>
                </tr>
                <tr>
                    <th class="c-box--150 must">多要素認証</th>
                    <td>
                        @foreach (\App\Modules\Master\Gfh1207\Enums\MultiFactorAuthentication::cases() as $multiFactorAuth)
                            @if (old('g2fa_key', $editRow['g2fa_key'] ?? '') == $multiFactorAuth->value)
                                {{ $multiFactorAuth->label() }}
                            @endif
                        @endforeach
                    </td>
                </tr>
            </table>
            <div class="u-mt--sm">
                <button type="submit" name="submit" value="cancel" class="btn btn-default btn-lg">キャンセル</button>
                <input type="submit" name="submit" value="登録" class="btn btn-success btn-lg">
            </div>
            <input type="hidden" name="{{ config('define.master.session_key_id') }}" value="{{ $param }}">
        </form>
    </div>
@endsection
