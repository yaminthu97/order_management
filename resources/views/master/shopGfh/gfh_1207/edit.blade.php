{{-- GFISMB0010:基本設定マスタ（GFH）更新 --}}
@php
    $ScreenCd = 'GFISMB0010';
@endphp

{{-- layout設定 --}}
@extends('common.layouts.default')

{{-- タイトル設定 --}}
@section('title', '基本設定マスタ（GFH）更新')

{{-- ぱんくず設定 --}}
@section('breadcrumb')
    <li>基本設定マスタ（GFH）更新</li>
@endsection

@section('content')

    <form method="POST" action="" name="Form1" id="Form1">
        {{ csrf_field() }}
        <table class="table table-bordered c-tbl c-tbl--full">
            <tr>
                <th class="c-box--500 must">振込期限日数</th>
                <td>
                    <input type="text" class="form-control c-box--full" name="payment_due_dates" id="payment_due_dates"
                        placeholder="" value="{{ old('payment_due_dates', $editRow['payment_due_dates'] ?? '') }}">
                    @include('common.elements.error_tag', ['name' => 'payment_due_dates'])
                </td>
            </tr>
            </th>
            <tr>
                <th class="c-box--500 must">メールアドレス（新売上データ）</th>
                <td>
                    <input type="text" class="form-control c-box--full" name="mail_address_festa_sales"
                        id="mail_address_festa_sales" placeholder=""
                        value="{{ old('mail_address_festa_sales', $editRow['mail_address_festa_sales'] ?? '') }}">
                    @include('common.elements.error_tag', ['name' => 'mail_address_festa_sales'])
                </td>
            </tr>
            <tr>
                <th class="c-box--500 must">メールアドレス（検品データ）</th>
                <td>
                    <input type="text" class="form-control c-box--full" name="mail_address_festa_inspection"
                        id="mail_address_festa_inspection" placeholder=""
                        value="{{ old('mail_address_festa_inspection', $editRow['mail_address_festa_inspection'] ?? '') }}">
                    @include('common.elements.error_tag', ['name' => 'mail_address_festa_inspection'])
                </td>
            </tr>
            <tr>
                <th class="c-box--500 must">メールアドレス（日別商品別出荷未出荷）</th>
                <td>
                    <input type="text" class="form-control c-box--full" name="mail_address_prod_dept"
                        id="mail_address_prod_dept" placeholder=""
                        value="{{ old('mail_address_prod_dept', $editRow['mail_address_prod_dept'] ?? '') }}">
                    @include('common.elements.error_tag', ['name' => 'mail_address_prod_dept'])
                </td>
            </tr>
            <tr>
                <th class="c-box--500 must">メールアドレス（通信販売の売上げと受注残）</th>
                <td>
                    <input type="text" class="form-control c-box--full" name="mail_address_ec_uriage"
                        id="mail_address_ec_uriage" placeholder=""
                        value="{{ old('mail_address_ec_uriage', $editRow['mail_address_ec_uriage'] ?? '') }}">
                    @include('common.elements.error_tag', ['name' => 'mail_address_ec_uriage'])
                </td>
            </tr>
            <tr>
                <th class="c-box--500 must">メールアドレス（経理部門連携用）</th>
                <td>
                    <input type="text" class="form-control c-box--full" name="mail_address_accounting_dept"
                        id="mail_address_accounting_dept" placeholder=""
                        value="{{ old('mail_address_accounting_dept', $editRow['mail_address_accounting_dept'] ?? '') }}">
                    @include('common.elements.error_tag', ['name' => 'mail_address_accounting_dept'])
                </td>
            </tr>
            <tr>
                <th class="c-box--500 must">fromメールアドレス</th>
                <td>
                    <input type="text" class="form-control c-box--full" name="mail_address_from" id="mail_address_from"
                        placeholder="" value="{{ old('mail_address_from', $editRow['mail_address_from'] ?? '') }}">
                    @include('common.elements.error_tag', ['name' => 'mail_address_from'])
                </td>
            </tr>
            <tr>
                <th class="c-box--500 must">FTPサーバ - ホスト名(ヤマト)</th>
                <td>
                    <input type="text" class="form-control c-box--full" name="ftp_server_host_yamato"
                        id="ftp_server_host_yamato" placeholder=""
                        value="{{ old('ftp_server_host_yamato', $editRow['ftp_server_host_yamato'] ?? '') }}">
                    @include('common.elements.error_tag', ['name' => 'ftp_server_host_yamato'])
                </td>
            </tr>
            <tr>
                <th class="c-box--500 must">FTPサーバ - ユーザ名(ヤマト)</th>
                <td>
                    <input type="text" class="form-control c-box--full" name="ftp_server_user_yamato"
                        id="ftp_server_user_yamato" placeholder=""
                        value="{{ old('ftp_server_user_yamato', $editRow['ftp_server_user_yamato'] ?? '') }}">
                    @include('common.elements.error_tag', ['name' => 'ftp_server_user_yamato'])
                </td>
            </tr>
            <tr>
                <th class="c-box--500 must">FTPサーバ - パスワード(ヤマト)</th>
                <td>
                    <input type="text" class="form-control c-box--full" name="ftp_server_password_yamato"
                        id="ftp_server_password_yamato" placeholder=""
                        value="{{ old('ftp_server_password_yamato', $editRow['ftp_server_password_yamato'] ?? '') }}">
                    @include('common.elements.error_tag', ['name' => 'ftp_server_password_yamato'])
                </td>
            </tr>
            <tr>
                <th class="c-box--500 must">ecbeing APIベースURL</th>
                <td>
                    <input type="text" class="form-control c-box--full" name="ecbeing_api_base_url"
                        id="ecbeing_api_base_url" placeholder=""
                        value="{{ old('ecbeing_api_base_url', $editRow['ecbeing_api_base_url'] ?? '') }}">
                    @include('common.elements.error_tag', ['name' => 'ecbeing_api_base_url'])
                </td>
            </tr>
            <tr>
                <th class="c-box--500 must">ecbeing API 特定文字列_顧客データ作成</th>
                <td>
                    <input type="text" class="form-control c-box--full" name="ecbeing_api_exp_customer"
                        id="ecbeing_api_exp_customer" placeholder=""
                        value="{{ old('ecbeing_api_exp_customer', $editRow['ecbeing_api_exp_customer'] ?? '') }}">
                    @include('common.elements.error_tag', ['name' => 'ecbeing_api_exp_customer'])
                </td>
            </tr>
            <tr>
                <th class="c-box--500 must">ecbeing API 特定文字列_顧客データダウンロード</th>
                <td>
                    <input type="text" class="form-control c-box--full" name="ecbeing_api_dl_customer"
                        id="ecbeing_api_dl_customer" placeholder=""
                        value="{{ old('ecbeing_api_dl_customer', $editRow['ecbeing_api_dl_customer'] ?? '') }}">
                    @include('common.elements.error_tag', ['name' => 'ecbeing_api_dl_customer'])
                </td>
            </tr>
            <tr>
                <th class="c-box--500 must">ecbeing API 特定文字列_注文データ作成</th>
                <td>
                    <input type="text" class="form-control c-box--full" name="ecbeing_api_exp_sales"
                        id="ecbeing_api_exp_sales" placeholder=""
                        value="{{ old('ecbeing_api_exp_sales', $editRow['ecbeing_api_exp_sales'] ?? '') }}">
                    @include('common.elements.error_tag', ['name' => 'ecbeing_api_exp_sales'])
                </td>
            </tr>
            <tr>
                <th class="c-box--500 must">ecbeing API 特定文字列_注文データダウンロード</th>
                <td>
                    <input type="text" class="form-control c-box--full" name="ecbeing_api_dl_sales"
                        id="ecbeing_api_dl_sales" placeholder=""
                        value="{{ old('ecbeing_api_dl_sales', $editRow['ecbeing_api_dl_sales'] ?? '') }}">
                    @include('common.elements.error_tag', ['name' => 'ecbeing_api_dl_sales'])
                </td>
            </tr>
            <tr>
                <th class="c-box--500 must">ecbeing API 特定文字列_出荷確定データ取込</th>
                <td>
                    <input type="text" class="form-control c-box--full" name="ecbeing_api_imp_ship"
                        id="ecbeing_api_imp_ship" placeholder=""
                        value="{{ old('ecbeing_api_imp_ship', $editRow['ecbeing_api_imp_ship'] ?? '') }}">
                    @include('common.elements.error_tag', ['name' => 'ecbeing_api_imp_ship'])
                </td>
            </tr>
            <tr>
                <th class="c-box--500 must">ecbeing API 特定文字列_出荷確定データ更新</th>
                <td>
                    <input type="text" class="form-control c-box--full" name="ecbeing_api_update_ship"
                        id="ecbeing_api_update_ship" placeholder=""
                        value="{{ old('ecbeing_api_update_ship', $editRow['ecbeing_api_update_ship'] ?? '') }}">
                    @include('common.elements.error_tag', ['name' => 'ecbeing_api_update_ship'])
                </td>
            </tr>
            <tr>
                <th class="c-box--500 must">ecbeing API 特定文字列_入金・受注変更データ取込</th>
                <td>
                    <input type="text" class="form-control c-box--full" name="ecbeing_api_imp_nyukin"
                        id="ecbeing_api_imp_nyukin" placeholder=""
                        value="{{ old('ecbeing_api_imp_nyukin', $editRow['ecbeing_api_imp_nyukin'] ?? '') }}">
                    @include('common.elements.error_tag', ['name' => 'ecbeing_api_imp_nyukin'])
                </td>
            </tr>
            <tr>
                <th class="c-box--500 must">ecbeing API 特定文字列_入金・受注変更データ更新</th>
                <td>
                    <input type="text" class="form-control c-box--full" name="ecbeing_api_update_nyukin"
                        id="ecbeing_api_update_nyukin" placeholder=""
                        value="{{ old('ecbeing_api_update_nyukin', $editRow['ecbeing_api_update_nyukin'] ?? '') }}">
                    @include('common.elements.error_tag', ['name' => 'ecbeing_api_update_nyukin'])
                </td>
            </tr>
        </table>

        <div class="u-mt--ss">
            <input class="btn btn-default btn-lg u-mt--sm" type="button" name="cancel" value="キャンセル"
                onClick="location.href='{{ route('master.shop_gfh.edit') }}';" />
            <button class="btn btn-success btn-lg u-mt--sm" type="submit" name="submit" id="submit_register"
                value="register">更新</button>
        </div>
    </form>
@endsection
