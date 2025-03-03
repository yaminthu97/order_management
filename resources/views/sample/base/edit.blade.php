{{-- NECSM0112:顧客登録・修正 --}}
@php
    $ScreenCd = 'NECSM0112';
@endphp

{{-- layout設定 --}}
@extends('common.layouts.default')

{{-- タイトル設定 --}}
@section('title', '顧客登録・修正')

{{-- ぱんくず設定 --}}
@section('breadcrumb')
    <li>顧客登録・修正</li>
@endsection

@section('content')
    @session('messages.error.exception_message')
    <span class="font-FF0000">{{$value}}</span>
    @endsession
    <form method="POST" action="" name="Form1" id="Form1">
        {{ csrf_field() }}
        <table class="table table-bordered c-tbl c-tbl--1160">
            <tr>
                <th>
                    <span class="c-box--300">使用区分</span><br>
                    @if ($sample->isDeleted())
                        <span class="font-FF0000">削除済み顧客</span>
                    @endif
                </th>
                <td>
                    @foreach(\App\Enums\DeleteFlg::cases() as $deleteFlg)
                        <div class="radio-inline">
                            <label>
                                <input type="radio" name="delete_flg" value="{{ $deleteFlg->value }}"
                                    @checked(old('delete_flg', $sample->delete_flg) == $deleteFlg->value)>
                                {{ $deleteFlg->label() }}
                            </label>
                        </div>
                    @endforeach
                    <x-common.error-tag name="delete_flg" />
                </td>
            </tr>

            <tr>
                <th>顧客コード</th>
                <td>
                    <input type="text" class="form-control c-box--300" name="cust_cd" id="cust_cd" placeholder=""
                        value="{{ old('cust_cd', $sample->cust_cd) }}">
                    <x-common.error-tag name="cust_cd" />
                </td>
            </tr>

            <tr>
                <th class="must">電話番号</th>
                <td>
                    <ul>
                        <li class="u-input--mid"><input type="text" class="form-control c-box--200" name="tel1"
                                id="tel1" placeholder="" value="{{ old('tel1', $sample->tel1) }}"></li>
                        <li class="u-input--mid"><input type="text" class="form-control c-box--200" name="tel2"
                                id="tel2" placeholder="" value="{{ old('tel2', $sample->tel2) }}"></li>
                        <li class="u-input--mid"><input type="text" class="form-control c-box--200" name="tel3"
                                id="tel3" placeholder="" value="{{ old('tel3', $sample->tel3) }}"></li>
                        <li class="u-input--mid"><input type="text" class="form-control c-box--200" name="tel4"
                                id="tel4" placeholder="" value="{{ old('tel4', $sample->tel4) }}"></li>
                    </ul>
                    <x-common.error-tag name="tel1" />
                    <x-common.error-tag name="tel2" />
                    <x-common.error-tag name="tel3" />
                    <x-common.error-tag name="tel4" />
                </td>
            </tr>

            <tr>
                <th>FAX番号</th>
                <td>
                    <input type="text" class="form-control c-box--200" name="fax" id="fax" placeholder=""
                        value="{{ old('fax', $sample->fax) }}">
                    <x-common.error-tag name="fax" />
                </td>
            </tr>

            <tr>
                <th>フリガナ</th>
                <td>
                    <input type="text" class="form-control c-box--300" name="name_kana" id="name_kana" placeholder=""
                        value="{{ old('name_kana', $sample->name_kana) }}">
                    <x-common.error-tag name="name_kana" />
                </td>
            </tr>

            <tr>
                <th class="must">名前</th>
                <td>
                    <input type="text" class="form-control c-box--300" name="name_kanji" id="name_kanji" placeholder=""
                        value="{{ old('name_kanji', $sample->name_kanji) }}">
                    <x-common.error-tag name="name_kanji" />
                </td>
            </tr>


            <tr>
                <th class="must">住所</th>
                <td>
                    <div class="d-table c-tbl--800">
                        <div class="d-table-cell c-box--100">郵便番号</div>
                        <div class="d-table-cell">
                            <input type="text" class="form-control c-box--100" name="postal" id="postal"
                                maxlength="8" placeholder=""
                                onKeyUp="AjaxZip3.zip2addr(this,'','address1','address2','dummy','address3');"
                                value="{{ old('postal', $sample->postal) }}">
                        </div>
                    </div>
                    <x-common.error-tag name="postal" />
                    <div class="d-table c-tbl--800 u-mt--xs">
                        <div class="d-table-cell c-box--100">都道府県</div>
                        <div class="d-table-cell">
                            <select name="address1" id="address1" class="form-control c-box--200">
                                <option value=""></option>
                                @foreach ($prefectuals as $prefectual)
                                <option value="{{ $prefectual->prefectual_name }}"
                                    @selected(old('address1', $sample->address1) == $prefectual->prefectual_name) >
                                    {{ $prefectual->prefectual_name }}
                                </option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <x-common.error-tag name="address1" />
                    <div class="d-table c-tbl--800 u-mt--xs">
                        <div class="d-table-cell c-box--100">市区町村</div>
                        <div class="d-table-cell"><input type="text" name="address2" id="address2"
                                class="form-control c-box--300" placeholder="" value="{{ old('address2', $sample->address2) }}">
                        </div>
                    </div>
                    <x-common.error-tag name="address2" />
                    <div class="d-table c-tbl--800 u-mt--xs">
                        <div class="d-table-cell c-box--100">番地</div>
                        <div class="d-table-cell"><input type="text" class="form-control c-box--300" name="address3"
                                id="address3" placeholder="" value="{{ old('address3', $sample->address3) }}"></div>
                    </div>
                    <x-common.error-tag name="address3" />
                    <div class="d-table c-tbl--800 u-mt--xs">
                        <div class="d-table-cell c-box--100">建物名</div>
                        <div class="d-table-cell"><input type="text" class="form-control c-box--300" name="address4"
                                id="address4" placeholder="" value="{{ old('address4', $sample->address4) }}"></div>
                    </div>
                    <x-common.error-tag name="address4" />
                </td>
            </tr>

            <tr>
                <th>メールアドレス</th>
                <td>
                    <ul>
                        <li class="u-input--mid"><input type="text" class="form-control c-box--200" name="email1"
                                id="email1" placeholder="" value="{{ old('email1', $sample->email1) }}"></li>
                        <li class="u-input--mid"><input type="text" class="form-control c-box--200" name="email2"
                                id="email2" placeholder="" value="{{ old('email2', $sample->email2) }}"></li>
                        <li class="u-input--mid"><input type="text" class="form-control c-box--200" name="email3"
                                id="email3" placeholder="" value="{{ old('email3', $sample->email3) }}"></li>
                        <li class="u-input--mid"><input type="text" class="form-control c-box--200" name="email4"
                                id="email4" placeholder="" value="{{ old('email4', $sample->email4) }}"></li>
                        <li class="u-input--mid"><input type="text" class="form-control c-box--200" name="email5"
                                id="email5" placeholder="" value="{{ old('email5', $sample->email5) }}"></li>
                    </ul>
                    <x-common.error-tag name="email1" />
                    <x-common.error-tag name="email2" />
                    <x-common.error-tag name="email3" />
                    <x-common.error-tag name="email4" />
                    <x-common.error-tag name="email5" />
                </td>
            </tr>

            <tr>
                <th>備考</th>
                <td>
                    <textarea class="form-control c-box--500" name="note" id="note" rows="5">{{ old('note', $sample->note) }}</textarea>
                    <x-common.error-tag name="note" />
                </td>
            </tr>
            <tr>
                <th>性別</th>
                <td>
                    @foreach(\App\Enums\SexTypeEnum::cases() as $sexType)
                        <div class="radio-inline">
                            <label>
                                <input type="radio" name="sex_type" value="{{ $sexType->value }}"
                                    @checked(old('sex_type', $sample->sex_type) == $sexType->value)>
                                {{ $sexType->label() }}
                            </label>
                        </div>
                    @endforeach
                    <x-common.error-tag name="sex_type" />
                </td>
            </tr>

            <tr>
                <th>誕生日</th>
                <td>
                    <div class="d-table-cell"><input type="text" class="form-control c-box--100" name="birthday"
                            id="birthday" maxlength="10" placeholder="" value="{{ old('birthday', $sample->birthday) }}">
                    </div>
                    <x-common.error-tag name="birthday" />
                </td>
            </tr>

            <tr>
                <th>法人情報</th>
                <td>
                    <div class="d-table c-tbl--600">
                        <div class="d-table-cell c-box--150">フリガナ</div>
                        <div class="d-table-cell">
                            <input type="text" class="form-control c-box--full"
                                name="corporate_kana" id="corporate_kana" placeholder=""
                                value="{{ old('corporate_kana', $sample->corporate_kana) }}">
                        </div>
                    </div>
                    <x-common.error-tag name="corporate_kana" />
                    <div class="d-table c-tbl--600 u-mt--xs">
                        <div class="d-table-cell c-box--150">法人名・団体名</div>
                        <div class="d-table-cell">
                            <input type="text" class="form-control c-box--full"
                                name="corporate_kanji" id="corporate_kanji" placeholder=""
                                value="{{ old('corporate_kanji',$sample->corporate_kanji) }}">
                        </div>
                    </div>
                    <x-common.error-tag name="corporate_kanji" />
                    <div class="d-table c-tbl--600 u-mt--xs">
                        <div class="d-table-cell c-box--150">部署名</div>
                        <div class="d-table-cell">
                            <input type="text" class="form-control c-box--full"
                                name="division_name" id="division_name" placeholder=""
                                value="{{ old('division_name', $sample->division_name) }}">
                        </div>
                    </div>
                    <x-common.error-tag name="division_name" />
                    <div class="d-table c-tbl--600 u-mt--xs">
                        <div class="d-table-cell c-box--150">勤務先電話番号</div>
                        <div class="d-table-cell">
                            <input type="text" class="form-control c-box--full"
                                name="corporate_tel" id="corporate_tel" placeholder=""
                                value="{{ old('corporate_tel', $sample->corporate_tel) }}">
                        </div>
                    </div>
                    <x-common.error-tag name="corporate_tel" />
                </td>
            </tr>


            <tr>
                <th>顧客ランク</th>
                <td>
                    <select class="form-control c-box--200" data-required-error="" name="m_cust_runk_id"
                        id="m_cust_runk_id">
                        @foreach ($custRunks as $custRunk)
                            <option value="{{ $custRunk->m_itemname_types_id }}"
                                @selected(old('m_cust_runk_id', $sample->m_cust_runk_id) == $custRunk->m_itemname_types_id)>
                                {{ $custRunk->m_itemname_type_name }}
                            </option>
                        @endforeach
                    </select>
                    <x-common.error-tag name="m_cust_runk_id" />
                </td>
            </tr>

            <tr>
                <th>要注意区分</th>
                <td>
                    @foreach(\App\Enums\AlertCustTypeEnum::cases() as $alertCustTypeEnum)
                        <div class="radio-inline">
                            <label>
                                <input type="radio" name="alert_cust_type" value="{{ $alertCustTypeEnum->value }}"
                                    @checked(old('alert_cust_type', $sample->alert_cust_type) == $alertCustTypeEnum->value)>
                                {{ $alertCustTypeEnum->label() }}
                            </label>
                        </div>
                    @endforeach
                    <x-common.error-tag name="alert_cust_type" />
                </td>
            </tr>

            <tr>
                <th>要注意コメント</th>
                <td>
                    <textarea class="form-control c-box--500" name="alert_cust_comment" id="alert_cust_comment" rows="5">
                        {{ old('alert_cust_comment', $sample->alert_cust_comment) }}
                    </textarea>
                    <x-common.error-tag name="alert_cust_comment" />
                </td>
            </tr>
            <tr>
                <th>自由項目１</th>
                <td><input type="text" class="form-control c-box--full" name="reserve1" id="reserve1"
                        placeholder="" value="{{ old('reserve1', $sample->reserve1) }}">
                    <x-common.error-tag name="reserve1" />
                </td>
            </tr>
            <tr>
                <th>自由項目２</th>
                <td><input type="text" class="form-control c-box--full" name="reserve2" id="reserve2"
                        placeholder="" value="{{ old('reserve2', $sample->reserve2) }}">
                    <x-common.error-tag name="reserve2" />
                </td>
            </tr>
            <tr>
                <th>自由項目３</th>
                <td><input type="text" class="form-control c-box--full" name="reserve3" id="reserve3"
                        placeholder="" value="{{ old('reserve3', $sample->reserve3) }}">
                    <x-common.error-tag name="reserve3" />
                </td>
            </tr>
            <tr>
                <th>自由項目４</th>
                <td><input type="text" class="form-control c-box--full" name="reserve4" id="reserve4"
                        placeholder="" value="{{ old('reserve4', $sample->reserve4) }}">
                    <x-common.error-tag name="reserve4" />
                </td>
            </tr>
            <tr>
                <th>自由項目５</th>
                <td><input type="text" class="form-control c-box--full" name="reserve5" id="reserve5"
                        placeholder="" value="{{ old('reserve5', $sample->reserve5) }}">
                    <x-common.error-tag name="reserve5" />
                </td>
            </tr>
            <tr>
                <th>自由項目６</th>
                <td><input type="text" class="form-control c-box--full" name="reserve6" id="reserve6"
                        placeholder="" value="{{ old('reserve6', $sample->reserve6) }}">
                    <x-common.error-tag name="reserve6" />
                </td>
            </tr>
            <tr>
                <th>自由項目７</th>
                <td><input type="text" class="form-control c-box--full" name="reserve7" id="reserve7"
                        placeholder="" value="{{ old('reserve7', $sample->reserve7) }}">
                    <x-common.error-tag name="reserve7" />
                </td>
            </tr>
            <tr>
                <th>自由項目８</th>
                <td><input type="text" class="form-control c-box--full" name="reserve8" id="reserve8"
                        placeholder="" value="{{ old('reserve8', $sample->reserve8) }}">
                    <x-common.error-tag name="reserve8" />
                </td>
            </tr>
            <tr>
                <th>自由項目９</th>
                <td><input type="text" class="form-control c-box--full" name="reserve9" id="reserve9"
                        placeholder="" value="{{ old('reserve9', $sample->reserve9) }}">
                    <x-common.error-tag name="reserve9" />
                </td>
            </tr>
            <tr>
                <th>自由項目１０</th>
                <td><input type="text" class="form-control c-box--full" name="reserve10" id="reserve10"
                        placeholder="" value="{{ old('reserve10', $sample->reserve10) }}">
                    <x-common.error-tag name="reserve10" />
                </td>
            </tr>
            <tr>
                <th>自由項目１１</th>
                <td><input type="text" class="form-control c-box--full" name="reserve11" id="reserve11"
                        placeholder="" value="{{ old('reserve11', $sample->reserve11) }}">
                    <x-common.error-tag name="reserve11" />
                </td>
            </tr>
            <tr>
                <th>自由項目１２</th>
                <td><input type="text" class="form-control c-box--full" name="reserve12" id="reserve12"
                        placeholder="" value="{{ old('reserve12', $sample->reserve12) }}">
                    <x-common.error-tag name="reserve12" />
                </td>
            </tr>
            <tr>
                <th>自由項目１３</th>
                <td><input type="text" class="form-control c-box--full" name="reserve13" id="reserve13"
                        placeholder="" value="{{ old('reserve13', $sample->reserve13) }}">
                    <x-common.error-tag name="reserve13" />
                </td>
            </tr>
            <tr>
                <th>自由項目１４</th>
                <td><input type="text" class="form-control c-box--full" name="reserve14" id="reserve14"
                        placeholder="" value="{{ old('reserve14', $sample->reserve14) }}">
                    <x-common.error-tag name="reserve14" />
                </td>
            </tr>
            <tr>
                <th>自由項目１５</th>
                <td><input type="text" class="form-control c-box--full" name="reserve15" id="reserve15"
                        placeholder="" value="{{ old('reserve15', $sample->reserve15) }}">
                    <x-common.error-tag name="reserve15" />
                </td>
            </tr>
            <tr>
                <th>自由項目１６</th>
                <td><input type="text" class="form-control c-box--full" name="reserve16" id="reserve16"
                        placeholder="" value="{{ old('reserve16', $sample->reserve16) }}">
                    <x-common.error-tag name="reserve16" />
                </td>
            </tr>
            <tr>
                <th>自由項目１７</th>
                <td><input type="text" class="form-control c-box--full" name="reserve17" id="reserve17"
                        placeholder="" value="{{ old('reserve17', $sample->reserve17) }}">
                    <x-common.error-tag name="reserve17" />
                </td>
            </tr>
            <tr>
                <th>自由項目１８</th>
                <td><input type="text" class="form-control c-box--full" name="reserve18" id="reserve18"
                        placeholder="" value="{{ old('reserve18', $sample->reserve18) }}">
                    <x-common.error-tag name="reserve18" />
                </td>
            </tr>
            <tr>
                <th>自由項目１９</th>
                <td><input type="text" class="form-control c-box--full" name="reserve19" id="reserve19"
                        placeholder="" value="{{ old('reserve19', $sample->reserve19) }}">
                    <x-common.error-tag name="reserve19" />
                </td>
            </tr>
            <tr>
                <th>自由項目２０</th>
                <td><input type="text" class="form-control c-box--full" name="reserve20" id="reserve20"
                        placeholder="" value="{{ old('reserve20', $sample->reserve20) }}">
                    <x-common.error-tag name="reserve20" />
                </td>
            </tr>
        </table>

        <div class="u-mt--ss">
            <input class="btn btn-default btn-lg u-mt--sm" type="button" name="cancel" value="キャンセル"
                onClick="location.href='{{$previousUrl}}'" />
            &nbsp;&nbsp;
            @if (isset($sample->m_cust_id) && !$sample->isDeleted())
                <button type="submit" name="submit" value="delete" class="btn btn-danger btn-lg u-mt--sm">削除</button>
            @endif
            @if ( !$sample->isDeleted())
                <button type="submit" name="submit" value="register" class="btn btn-success btn-lg u-mt--sm">確認</button>
            @endif
        </div>

        @include('common.elements.on_enter_script', ['target_button_name' => 'submit_register'])
    </form>
@endsection
