{{-- NECSM0113:顧客登録・修正確認 --}}
@php
    $ScreenCd = 'NECSM0113';
@endphp

{{-- layout設定 --}}
@extends('common.layouts.default')

{{-- タイトル設定 --}}
@section('title', '顧客登録・修正確認')

{{-- ぱんくず設定 --}}
@section('breadcrumb')
    <li>顧客登録・修正確認</li>
@endsection

@section('content')
    <form method="POST" action="">
        @csrf
        @if($mode == 'new')
        @elseif($mode == 'edit')
            @method('PUT')
        @elseif($mode == 'delete')
            @method('DELETE')
        @endif

        <div>
            @if ($mode == 'delete')
                <label class="btn cl-ff0000">削除</label>
            @endif

            <table class="table table-bordered c-tbl c-tbl--800">
                <tr>
                    <th class="c-box--150">使用区分</th>
                    <td>
                        {{ \App\Enums\DeleteFlg::tryFrom($sample->delete_flg)?->label() }}
                    </td>
                </tr>
                <tr>
                    <th><label for="">顧客コード</label></th>
                    <td>{{ $sample->cust_cd }}</td>
                </tr>
                <tr>
                    <th><label for="">電話番号</label></th>
                    <td>{{ $sample->tel1 }}&nbsp;
                        {{ $sample->tel2 }}&nbsp;
                        {{ $sample->tel3 }}&nbsp;
                        {{ $sample->tel4 }}&nbsp;
                    </td>
                </tr>
                <tr>
                    <th><label for="">FAX番号</label></th>
                    <td>{{ $sample->fax }}</td>
                </tr>
                <tr>
                    <th><label for="">フリガナ</label></th>
                    <td>{{ $sample->name_kana }}</td>
                </tr>
                <tr>
                    <th><label for="">名前</label></th>
                    <td>{{ $sample->name_kanji }}</td>
                </tr>
                <tr>
                    <th><label for="">郵便番号</label></th>
                    <td>{{ $sample->display_postal }}</td>
                </tr>
                <tr>
                    <th><label for="">都道府県</label></th>
                    <td>{{ $sample->address1 }}</td>
                </tr>
                <tr>
                    <th><label for="">市区町村</label></th>
                    <td>{{ $sample->address2 }}</td>
                </tr>
                <tr>
                    <th><label for="">番地</label></th>
                    <td>{{ $sample->address3 }}</td>
                </tr>
                <tr>
                    <th><label for="">建物名</label></th>
                    <td>{{ $sample->address4 }}</td>
                </tr>
                <tr>
                    <th><label for="">メールアドレス</label></th>
                    <td>{{ $sample->email1 }}&nbsp;
                        {{ $sample->email2 }}&nbsp;
                        {{ $sample->email3 }}&nbsp;
                        {{ $sample->email4 }}&nbsp;
                        {{ $sample->email5 }}&nbsp;
                    </td>
                </tr>
                <tr>
                    <th><label for="">備考</label></th>
                    <td>{!! nl2br(e($sample->note)) !!}</td>
                </tr>
                <tr>
                    <th><label for="">性別</label></th>
                    <td>
                        {{ \App\Enums\SexTypeEnum::tryFrom($sample->sex_type)?->label() }}
                    </td>
                </tr>
                <tr>
                    <th><label for="">誕生日</label></th>
                    <td>{{ $sample->birthday }}</td>
                </tr>
                <tr>
                    <th><label for="">フリガナ</label></th>
                    <td>{{ $sample->corporate_kana }}</td>
                </tr>
                <tr>
                    <th><label for="">法人名・団体名</label></th>
                    <td>{{ $sample->corporate_kanji }}</td>
                </tr>
                <tr>
                    <th><label for="">部署名</label></th>
                    <td>{{ $sample->division_name }}</td>
                </tr>
                <tr>
                    <th><label for="">勤務先電話番号</label></th>
                    <td>{{ $sample->corporate_tel }}</td>
                </tr>
                <tr>
                    <th><label for="">顧客ランク</label></th>
                    <td>
                        {{$sample->custRunk?->m_itemname_type_name }}
                    </td>
                </tr>
                <tr>
                    <th><label for="">要注意区分</label></th>
                    <td>
                        {{ \App\Enums\AlertCustTypeEnum::tryFrom($sample->alert_cust_type)?->label() }}
                    </td>
                </tr>
                <tr>
                    <th><label for="">要注意コメント</label></th>
                    <td>{!! nl2br(e($sample->alert_cust_comment)) !!}</td>
                </tr>
                <tr>
                    <th><label for="">自由項目１</label></th>
                    <td>{{ $sample->reserve1 }}</td>
                </tr>
                <tr>
                    <th><label for="">自由項目２</label></th>
                    <td>{{ $sample->reserve2 }}</td>
                </tr>
                <tr>
                    <th><label for="">自由項目３</label></th>
                    <td>{{ $sample->reserve3 }}</td>
                </tr>
                <tr>
                    <th><label for="">自由項目４</label></th>
                    <td>{{ $sample->reserve4 }}</td>
                </tr>
                <tr>
                    <th><label for="">自由項目５</label></th>
                    <td>{{ $sample->reserve5 }}</td>
                </tr>
                <tr>
                    <th><label for="">自由項目６</label></th>
                    <td>{{ $sample->reserve6 }}</td>
                </tr>
                <tr>
                    <th><label for="">自由項目７</label></th>
                    <td>{{ $sample->reserve7 }}</td>
                </tr>
                <tr>
                    <th><label for="">自由項目８</label></th>
                    <td>{{ $sample->reserve8 }}</td>
                </tr>
                <tr>
                    <th><label for="">自由項目９</label></th>
                    <td>{{ $sample->reserve9 }}</td>
                </tr>
                <tr>
                    <th><label for="">自由項目１０</label></th>
                    <td>{{ $sample->reserve10 }}</td>
                </tr>
                <tr>
                    <th><label for="">自由項目１１</label></th>
                    <td>{{ $sample->reserve11 }}</td>
                </tr>
                <tr>
                    <th><label for="">自由項目１２</label></th>
                    <td>{{ $sample->reserve12 }}</td>
                </tr>
                <tr>
                    <th><label for="">自由項目１３</label></th>
                    <td>{{ $sample->reserve13 }}</td>
                </tr>
                <tr>
                    <th><label for="">自由項目１４</label></th>
                    <td>{{ $sample->reserve14 }}</td>
                </tr>
                <tr>
                    <th><label for="">自由項目１５</label></th>
                    <td>{{ $sample->reserve15 }}</td>
                </tr>
                <tr>
                    <th><label for="">自由項目１６</label></th>
                    <td>{{ $sample->reserve16 }}</td>
                </tr>
                <tr>
                    <th><label for="">自由項目１７</label></th>
                    <td>{{ $sample->reserve17 }}</td>
                </tr>
                <tr>
                    <th><label for="">自由項目１８</label></th>
                    <td>{{ $sample->reserve18 }}</td>
                </tr>
                <tr>
                    <th><label for="">自由項目１９</label></th>
                    <td>{{ $sample->reserve19 }}</td>
                </tr>
                <tr>
                    <th><label for="">自由項目２０</label></th>
                    <td>{{ $sample->reserve20 }}</td>
                </tr>
            </table>

            <div class="u-mt--ss">
                {{-- <input type="submit" name="submit_cancel" class="btn btn-default btn-lg"value="キャンセル">
                <input type="submit" name="submit_register" class="btn btn-success btn-lg"value="登録"> --}}
                <button type="submit" name="submit" value="cancel" class="btn btn-default btn-lg">キャンセル</button>
                <button type="submit" name="submit" value="register" class="btn btn-success btn-lg">登録</button>
            </div>
            <input type="hidden" name="{{ config('define.cc.session_key_id') }}"
                value="{{ $param }}">

        </div>
    </form>

@endsection
