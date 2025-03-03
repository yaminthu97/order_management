<?php

namespace App\Http\Requests\Gfh1207;

use Illuminate\Foundation\Http\FormRequest;

/**
 * 顧客登録用 値チェック
 *
 * @author Satomi Takeshima（Scroll360）
 * @copyright 2018-2018 Scroll360
 * @category Cc
 * @package Validator
 */
class RegisterCustomerRequests extends FormRequest
{
    /**
     * 検証する項目と検証内容
     *
     * @var array
     */
    public function rules(): array
    {
        return [
              'name_sorting_flg'        => [ 'nullable', 'in:0,1' , ]
            , 'm_cust_id'               => [ 'nullable', 'exists:local.m_cust,m_cust_id', ]
            , 'cust_cd'                 => [ 'nullable', 'str_length_max_count:255', ]
            , 'm_cust_runk_id'          => [ 'nullable', 'numeric' , ]
            , 'name_kanji'              => [ 'required', 'str_length_max_count:100', ]
            , 'name_kana'               => [ 'nullable', 'str_length_max_count:100', ]
            //, 'sex_type'                => [ 'nullable', 'in:0,1,2' , ]
            , 'birthday'                => [ 'nullable', 'date' , ]
            , 'tel1'                    => [ 'nullable', 'max:20'  , 'required_without_all:tel2,tel3,tel4']
            , 'tel2'                    => [ 'nullable', 'max:20'  , 'required_without_all:tel1,tel3,tel4']
            , 'tel3'                    => [ 'nullable', 'max:20'  , 'required_without_all:tel1,tel2,tel4']
            , 'tel4'                    => [ 'nullable', 'max:20'  , 'required_without_all:tel1,tel2,tel3']
            , 'fax'                     => [ 'nullable', 'max:20'  ,]
            , 'postal'                  => [ 'nullable', 'max:8'   , 'postal',]
            , 'address1'                => [ 'required', 'str_length_max_count:100', ]
            , 'address2'                => [ 'required', 'str_length_max_count:100', ]
            , 'address3'                => [ 'nullable', 'str_length_max_count:100', ]
            , 'address4'                => [ 'nullable', 'str_length_max_count:100', ]
            , 'address5'                => [ 'nullable', 'str_length_max_count:100', ]
            , 'corporate_kanji'         => [ 'nullable', 'str_length_max_count:100', ]
            , 'corporate_kana'          => [ 'nullable', 'str_length_max_count:100', ]
            , 'division_name'           => [ 'nullable', 'str_length_max_count:100', ]
            , 'corporate_tel'           => [ 'nullable', 'max:20' , ]
            , 'email1'                  => [ 'nullable', 'max:255', 'email',   ]
            , 'email2'                  => [ 'nullable', 'max:255', 'email',   ]
            , 'email3'                  => [ 'nullable', 'max:255', 'email',   ]
            , 'email4'                  => [ 'nullable', 'max:255', 'email',   ]
            , 'email5'                  => [ 'nullable', 'max:255', 'email',   ]
            //, 'alert_cust_type'         => [ 'nullable', 'in:0,1,2' , ]
            , 'alert_cust_comment'      => [ 'nullable', ]
            , 'note'                    => [ 'nullable', ]
            , 'reserve1'                => [ 'nullable', 'str_length_max_count:100', ]
            , 'reserve2'                => [ 'nullable', 'str_length_max_count:100', ]
            , 'reserve3'                => [ 'nullable', 'str_length_max_count:100', ]
            , 'reserve4'                => [ 'nullable', 'str_length_max_count:100', ]
            , 'reserve5'                => [ 'nullable', 'str_length_max_count:100', ]
            , 'reserve6'                => [ 'nullable', 'str_length_max_count:100', ]
            , 'reserve7'                => [ 'nullable', 'str_length_max_count:100', ]
            , 'reserve8'                => [ 'nullable', 'str_length_max_count:100', ]
            , 'reserve9'                => [ 'nullable', 'str_length_max_count:100', ]
            , 'reserve10'               => [ 'nullable', 'str_length_max_count:100', ]
            , 'reserve11'               => [ 'nullable', 'str_length_max_count:100', ]
            , 'reserve12'               => [ 'nullable', 'str_length_max_count:100', ]
            , 'reserve13'               => [ 'nullable', 'str_length_max_count:100', ]
            , 'reserve14'               => [ 'nullable', 'str_length_max_count:100', ]
            , 'reserve15'               => [ 'nullable', 'str_length_max_count:100', ]
            , 'reserve16'               => [ 'nullable', 'str_length_max_count:100', ]
            , 'reserve17'               => [ 'nullable', 'str_length_max_count:100', ]
            , 'reserve18'               => [ 'nullable', 'str_length_max_count:100', ]
            , 'reserve19'               => [ 'nullable', 'str_length_max_count:100', ]
            , 'reserve20'               => [ 'nullable', 'str_length_max_count   :100', ]
            , 'discount_rate'           => [ 'nullable', 'numeric',  ]
            , 'customer_type'           => [ 'nullable',  ]
            , 'dm_send_letter_flg'      => [ 'nullable', 'in:0,1' , ]
            , 'dm_send_mail_flg'       => [ 'nullable', 'in:0,1' , ]
            //, 'operator_id'             => [ 'required', 'numeric', ]
            //, 'delete_flg'              => [ 'nullable', 'in:0,1' , ]
            //, 'delete_operator_id'      => [ 'nullable', 'numeric', ]
        ];
    }

    /**
     * 各項目名を書く（書かないと物理名が表示される）
     *
     * @var array
     */
    public function attributes()
    {
        return [
            'name_sorting_flg'        => '名寄せ実行フラグ'
            , 'm_cust_id'               => '顧客ID'
            , 'cust_cd'                 => '顧客コード'
            , 'm_cust_runk_id'          => '顧客ランク'
            , 'name_kanji'              => '名前'
            , 'name_kana'               => 'フリガナ'
            , 'sex_type'                => '性別区分'
            , 'birthday'                => '生年月日'
            , 'tel1'                    => '電話番号１'
            , 'tel2'                    => '電話番号２'
            , 'tel3'                    => '電話番号３'
            , 'tel4'                    => '電話番号４'
            , 'fax'                     => 'ＦＡＸ番号'
            , 'postal'                  => '郵便番号'
            , 'address1'                => '都道府県'
            , 'address2'                => '市区町村'
            , 'address3'                => '番地'
            , 'address4'                => '建物名'
            , 'corporate_kanji'         => '法人・団体名'
            , 'corporate_kana'          => '法人・団体名カナ'
            , 'division_name'           => '部署名'
            , 'corporate_tel'           => '電話番号（勤務先）'
            , 'email1'                  => 'メールアドレス１'
            , 'email2'                  => 'メールアドレス２'
            , 'email3'                  => 'メールアドレス３'
            , 'email4'                  => 'メールアドレス４'
            , 'email5'                  => 'メールアドレス５'
            , 'alert_cust_type'         => '要注意顧客区分'
            , 'alert_cust_comment'      => '要注意コメント'
            , 'note'                    => '備考'
            , 'reserve1'                => 'ブラック理由'
            , 'reserve2'                => '注意顧客理由'
            , 'reserve3'                => '貸倒'
            , 'reserve4'                => 'お客様情報'
            , 'reserve5'                => '問合せ連絡'
            , 'reserve6'                => '備考'
            , 'reserve7'                => '請求関連'
            , 'reserve8'                => '督促'
            , 'reserve9'                => '住所変更'
            , 'reserve10'               => 'Web会員番号'
            , 'reserve11'               => '自由項目１１'
            , 'reserve12'               => '自由項目１２'
            , 'reserve13'               => '自由項目１３'
            , 'reserve14'               => '自由項目１４'
            , 'reserve15'               => '自由項目１５'
            , 'reserve16'               => '自由項目１６'
            , 'reserve17'               => '自由項目１７'
            , 'reserve18'               => '自由項目１８'
            , 'reserve19'               => '自由項目１９'
            , 'reserve20'               => '自由項目２０'
            , 'operator_id'             => '作業ユーザID'
            , 'delete_flg'              => '削除指定'
            , 'delete_operator_id'      => '削除ユーザID'
        ];
    }
}
