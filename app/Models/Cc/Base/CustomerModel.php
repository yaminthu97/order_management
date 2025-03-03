<?php

namespace App\Models\Cc\Base;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class CustomerModel extends Model
{
    protected $fillAble = [
        'delete_flg',
        'm_cust_id',
        'cust_cd',
        'm_cust_runk_id',
        'name_kanji',
        'name_kana',
        'sex_type',
        'birthday',
        'tel1',
        'tel2',
        'tel3',
        'tel4',
        'fax',
        'postal',
        'address1',
        'address2',
        'address3',
        'address4',
        'corporate_kanji',
        'corporate_kana',
        'division_name',
        'corporate_tel',
        'email1',
        'email2',
        'email3',
        'email4',
        'email5',
        'alert_cust_type',
        'alert_cust_comment',
        'note',
        'reserve1',
        'reserve2',
        'reserve3',
        'reserve4',
        'reserve5',
        'reserve6',
        'reserve7',
        'reserve8',
        'reserve9',
        'reserve10',
        'reserve11',
        'reserve12',
        'reserve13',
        'reserve14',
        'reserve15',
        'reserve16',
        'reserve17',
        'reserve18',
        'reserve19',
        'reserve20',
        'delete_operator_id',
        'delete_timestamp',
    ];

    protected $table = 'm_cust';

    protected $primaryKey = 'm_cust_id';

    /**
     * 参照先のデータベースがグローバルなのか、ローカルなのか
     * (デフォルトはグローバルへ接続)
     */
    // protected $connection = 'local';

    /**
     * オペレータIDに対応するカラム
     * entry_operator_id, update_operator_idに対応
     */
    protected $operatorIdColumn = 'operator_id';

    /**
     * 結合するテーブル
     *
     * @var array
     */
    protected $joinTables = [
        'sumsum' => [
            'join_table_name'	=> 'm_cust_order_sum',
            'local_db_flag'		=> true,
            'join_rules'		=> [['base_table_column' => 'm_cust_id', 'join_table_column' => 'm_cust_id']],
            'select_columns'	=> [
                'total_order_money' => 'total_order_money',
                'total_order_count' => 'total_order_count',
                'newest_order_date' => 'newest_order_date',
                'first_ec_id' => 'first_ecs_id',
                'newest_ec_id' => 'newest_ecs_id'
            ],
        ],
    ];

    /**
     * 上書きするかどうかを判定する項目
     */
    protected $updateCheckColumns = [
        'note' => '',
        'm_cust_runk_id' => '0',
        'birthday' => '0000-00-00',
        'alert_cust_type' => 0,
        'alert_cust_comment' => '',
        'reserve1' => '',
        'reserve2' => '',
        'reserve3' => '',
        'reserve4' => '',
        'reserve5' => '',
        'reserve6' => '',
        'reserve7' => '',
        'reserve8' => '',
        'reserve9' => '',
        'reserve10' => '',
        'reserve11' => '',
        'reserve12' => '',
        'reserve13' => '',
        'reserve14' => '',
        'reserve15' => '',
        'reserve16' => '',
        'reserve17' => '',
        'reserve18' => '',
        'reserve19' => '',
        'reserve20' => '',
    ];

    /**
     * 国外フラグ
     */
    protected $foreignFlg = false;

    /**
     * 顧客検索のWhere条件
     *
     * @param $searchInfo array
     */
    public function addWhere($searchInfo)
    {
        // 削除は対象外
        if(!(isset($searchInfo['delete_include']) && $searchInfo['delete_include'] == 1)) {
            $this->dbSelect->where('delete_operator_id', '=', '0');
        }

        // 使用区分
        if  (isset($searchInfo['delete_flg']) && strlen($searchInfo['delete_flg']) > 0) {
            $this->dbSelect->whereIn('delete_flg', explode(",", $searchInfo['delete_flg']));
        }

        // 顧客ID（複数対応）
        if (isset($searchInfo['m_cust_id']) && strlen($searchInfo['m_cust_id']) > 0) {
            $this->dbSelect->whereIn($this->table. '.m_cust_id', explode(',', $searchInfo['m_cust_id']));
        }

        // 顧客コード
        if (isset($searchInfo['cust_cd']) && strlen($searchInfo['cust_cd']) > 0) {
            $this->dbSelect->where('cust_cd', 'like', "{$searchInfo['cust_cd']}%%");
        }
        // 顧客ランク
        if (isset($searchInfo['m_cust_runk_id']) && strlen($searchInfo['m_cust_runk_id']) > 0) {
            $this->dbSelect->whereIn('m_cust_runk_id', explode(",", $searchInfo['m_cust_runk_id']));
        }

        // 法人名・団体名
        if(isset($searchInfo['corporate_kanji_fuzzy']) && $searchInfo['corporate_kanji_fuzzy'] == 1) {
            // あいまい検索する場合
            if (isset($searchInfo['corporate_kanji']) && strlen($searchInfo['corporate_kanji']) > 0) {
                $str = str_replace([" ", "　"], "", $searchInfo['corporate_kanji']);
                $this->dbSelect->where('corporate_kanji', 'like', "%%{$str}%%");
            }
        } else {
            // そうでない場合は前方一致
            if (isset($searchInfo['corporate_kanji']) && strlen($searchInfo['corporate_kanji']) > 0) {
                $str = str_replace([" ", "　"], "", $searchInfo['corporate_kanji']);
                $this->dbSelect->where('corporate_kanji', 'like', "{$str}%%");
            }
        }


        // 法人名・団体名（フリガナ）
        if(isset($searchInfo['corporate_kana_fuzzy']) && $searchInfo['corporate_kana_fuzzy'] == 1) {
            // あいまい検索する場合
            if (isset($searchInfo['corporate_kana']) && strlen($searchInfo['corporate_kana']) > 0) {
                $str = str_replace([" ", "　"], "", $searchInfo['corporate_kana']);
                $this->dbSelect->where('corporate_kana', 'like', "%%{$str}%%");
            }
        } else {
            // そうでない場合は前方一致
            if (isset($searchInfo['corporate_kana']) && strlen($searchInfo['corporate_kana']) > 0) {
                $str = str_replace([" ", "　"], "", $searchInfo['corporate_kana']);
                $this->dbSelect->where('corporate_kana', 'like', "{$str}%%");
            }
        }

        // 電話番号(勤務先）
        if (isset($searchInfo['corporate_tel']) && strlen($searchInfo['corporate_tel']) > 0) {
            $this->dbSelect->where('corporate_tel', 'like', "{$searchInfo['corporate_tel']}%%");
        }

        // 名前漢字
        if(isset($searchInfo['name_kanji_fuzzy']) && $searchInfo['name_kanji_fuzzy'] == 1) {
            // あいまい検索する場合
            if (isset($searchInfo['name_kanji']) && strlen($searchInfo['name_kanji']) > 0) {
                $str = str_replace([" ", "　"], "", $searchInfo['name_kanji']);
                $this->dbSelect->where('gen_search_name_kanji', 'like', "%%{$str}%%");
            }
        } else {
            // そうでない場合は前方一致
            if (isset($searchInfo['name_kanji']) && strlen($searchInfo['name_kanji']) > 0) {
                $str = str_replace([" ", "　"], "", $searchInfo['name_kanji']);
                $this->dbSelect->where('gen_search_name_kanji', 'like', "{$str}%%");
            }
        }

        // フリガナ
        if(isset($searchInfo['name_kana_fuzzy']) && $searchInfo['name_kana_fuzzy'] == 1) {
            // あいまい検索する場合
            if (isset($searchInfo['name_kana']) && strlen($searchInfo['name_kana']) > 0) {
                $str = str_replace([" ", "　"], "", $searchInfo['name_kana']);
                $this->dbSelect->where('gen_search_name_kana', 'like', "%%{$str}%%");
            }
        } else {
            // そうでない場合は前方一致
            if (isset($searchInfo['name_kana']) && strlen($searchInfo['name_kana']) > 0) {
                $str = str_replace([" ", "　"], "", $searchInfo['name_kana']);
                $this->dbSelect->where('gen_search_name_kana', 'like', "{$str}%%");
            }
        }

        // 性別
        if (isset($searchInfo['sex_type']) && strlen($searchInfo['sex_type']) > 0) {
            $this->dbSelect->whereIn('sex_type', explode(",", $searchInfo['sex_type']));
        }

        // メールアドレス
        if (isset($searchInfo['email']) && strlen($searchInfo['email']) > 0) {
            $str = mb_strtolower($searchInfo['email']);
            $this->dbSelect->whereExists(function ($query) use ($str) {
                $query->select('*')
                ->from('m_cust_email')
                ->whereRaw("m_cust_email.m_cust_id = m_cust.m_cust_id AND email like ?")->setBindings([$str. '%']);
            });
        }

        // 電話番号
        if (isset($searchInfo['tel_forward_match']) && ($searchInfo['tel_forward_match']) == 1) {
            // 前方一致
            if (isset($searchInfo['tel']) && strlen($searchInfo['tel']) > 0) {
                $str = str_replace("-", "", $searchInfo['tel']);

                $this->dbSelect->whereExists(function ($query) use ($str) {
                    $query->select('*')
                    ->from('m_cust_tel')
                    ->whereRaw("m_cust_tel.m_cust_id = m_cust.m_cust_id AND tel like ?")->setBindings([$str. '%']);
                });
            }
        } else {
            // そうでない場合
            if (isset($searchInfo['tel']) && strlen($searchInfo['tel']) > 0) {
                $str = str_replace("-", "", $searchInfo['tel']);

                $this->dbSelect->whereExists(function ($query) use ($str) {
                    $query->select('*')
                    ->from('m_cust_tel')
                    ->whereRaw("m_cust_tel.m_cust_id = m_cust.m_cust_id AND tel = ?")->setBindings([$str]);
                });
            }
        }

        // FAX
        if (isset($searchInfo['fax_forward_match']) && ($searchInfo['fax_forward_match']) == 1) {
            // 前方一致
            if (isset($searchInfo['fax']) && strlen($searchInfo['fax']) > 0) {
                $str = str_replace("-", "", $searchInfo['fax']);
                //$this->dbSelect->where('fax', 'like', "{$str}%%");


                $this->dbSelect->whereRaw('REPLACE(fax,\'-\',\'\') like ?', "{$str}%%");
            }
        } else {
            // そうでない場合
            if (isset($searchInfo['fax']) && strlen($searchInfo['fax']) > 0) {
                $str = str_replace("-", "", $searchInfo['fax']);
                $this->dbSelect->whereRaw('REPLACE(fax,\'-\',\'\')=?', "{$str}");
            }
        }

        // 郵便番号
        if (isset($searchInfo['postal']) && strlen($searchInfo['postal']) > 0) {
            $this->dbSelect->where('postal', 'like', "{$searchInfo['postal']}%%");
        }

        // 都道府県
        if (isset($searchInfo['address1']) && strlen($searchInfo['address1']) > 0) {
            $this->dbSelect->where('address1', '=', $searchInfo['address1']);
        }

        // 住所
        if (isset($searchInfo['address2_forward_match']) && ($searchInfo['address2_forward_match']) == 1) {
            // あいまい検索する場合
            if (isset($searchInfo['address2']) && strlen($searchInfo['address2']) > 0) {
                $this->dbSelect->whereRaw("CONCAT(IFNULL(m_cust.address2,''), IFNULL(m_cust.address3,''), IFNULL(m_cust.address4,'')) like ?", '%%' .$searchInfo['address2'] .'%%');
            }
        } else {
            // そうでない場合は前方一致
            if (isset($searchInfo['address2']) && strlen($searchInfo['address2']) > 0) {
                $this->dbSelect->whereRaw("CONCAT(IFNULL(m_cust.address2,''), IFNULL(m_cust.address3,''), IFNULL(m_cust.address4,'')) like ?", $searchInfo['address2'].'%%');
            }
        }

        // 備考の有無（1:有りのみ(null、sp除外)、2:無しのみ(null、sp)）
        if (isset($searchInfo['note_existence']) && strlen($searchInfo['note_existence']) == 1) {
            switch ($searchInfo['note_existence']) {
                case 1:
                    $this->dbSelect->whereRaw('(note is not null and  note <>\'\')');
                    break;
                case 2:
                    $this->dbSelect->whereRaw('(note is null or note =\'\')');
                    break;
            }
        }

        // 備考
        // あいまい検索のみ
        if (isset($searchInfo['note']) && strlen($searchInfo['note']) > 0) {
            $this->dbSelect->where('note', 'like', "%%{$searchInfo['note']}%%");
        }

        // 購入累計金額FROM
        if (isset($searchInfo['total_order_money_from']) && strlen($searchInfo['total_order_money_from']) > 0) {
            $this->dbSelect->where('sumsum.total_order_money', '>=', $searchInfo['total_order_money_from']);
        }

        // 購入累計金額TO
        if (isset($searchInfo['total_order_money_to']) && strlen($searchInfo['total_order_money_to']) > 0) {
            $this->dbSelect->where('sumsum.total_order_money', '<=', $searchInfo['total_order_money_to']);
        }

        // 購入回数FROM
        if (isset($searchInfo['total_order_count_from']) && strlen($searchInfo['total_order_count_from']) > 0) {
            $this->dbSelect->where('sumsum.total_order_count', '>=', $searchInfo['total_order_count_from']);
        }

        // 購入回数TO
        if (isset($searchInfo['total_order_count_to']) && strlen($searchInfo['total_order_count_to']) > 0) {
            $this->dbSelect->where('sumsum.total_order_count', '<=', $searchInfo['total_order_count_to']);
        }

        // 要注意区分
        if (isset($searchInfo['alert_cust_type']) && strlen($searchInfo['alert_cust_type']) > 0) {
            $this->dbSelect->whereIn('alert_cust_type', explode(",", $searchInfo['alert_cust_type']));
        }

        // 要注意コメント
        if (isset($searchInfo['alert_cust_comment']) && strlen($searchInfo['alert_cust_comment']) > 0) {
            // あいまい検索のみ
            $this->dbSelect->where('alert_cust_comment', 'like', "%%{$searchInfo['alert_cust_comment']}%%");
        }

    }

    /**
     * SQLクエリの追加（拡張）
     * （GROUP BYなどの条件はここに書く）
     *
     * @param $searchInfo array
     */
    protected function addQueryExtend($searchInfo)
    {

        $this->dbSelect->distinct();

    }

    /**
     * 登録用バリデータのセット
     */
    public function setRegsiterValidator()
    {
        // 顧客の登録更新用ヴァリデーションチェック
        $this->validator = $this->getValidator('RegisterCustomer');
    }

    /**
     * UPDATE or INSERT
     *
     * @param $request Request
     */
    public function updateInsert($request)
    {
        $registerData = $this->getRegisterData($request);
        $req = array_shift($request);

        $errorMsg = '';

        $retIds = '';

        //名寄せ確認
        //        $localDbName = $this->accountData['local_database_name'];
        $localDbName = $this->localDbName;
        $insertFlg = 1;
        $mCustId = "";

        //会員IDがある場合は更新する
        if(!empty($registerData[$this->primaryKey])) {
            $insertFlg = 0;
            $mCustId = $registerData[$this->primaryKey];
        } else {
            //会員IDが0またはNullの場合削除
            if(array_key_exists($this->primaryKey, $registerData)) {
                unset($registerData[$this->primaryKey]);
            }
            if (isset($registerData['name_sorting_flg']) && $registerData['name_sorting_flg'] == 1) {
                //名寄せしない
                $insertFlg = 1;
            } else {
                $nameKanji =  str_replace(" ", "", str_replace("　", "", $registerData['name_kanji']));
                $nameKana =  "";
                if (isset($registerData['name_kana'])) {
                    $nameKana =  str_replace(" ", "", str_replace("　", "", $registerData['name_kana']));
                }
                $telArray = [];
                if (isset($registerData['tel1'])) {
                    array_push($telArray, str_replace("-", "", $registerData['tel1']));
                }
                if (isset($registerData['tel2'])) {
                    array_push($telArray, str_replace("-", "", $registerData['tel2']));
                }
                if (isset($registerData['tel3'])) {
                    array_push($telArray, str_replace("-", "", $registerData['tel3']));
                }
                if (isset($registerData['tel4'])) {
                    array_push($telArray, str_replace("-", "", $registerData['tel4']));
                }
                $mailArray = [];
                if (isset($registerData['email1'])) {
                    array_push($mailArray, strtolower($registerData['email1']));
                }
                if (isset($registerData['email2'])) {
                    array_push($mailArray, strtolower($registerData['email2']));
                }
                if (isset($registerData['email3'])) {
                    array_push($mailArray, strtolower($registerData['email3']));
                }
                if (isset($registerData['email4'])) {
                    array_push($mailArray, strtolower($registerData['email4']));
                }
                if (isset($registerData['email5'])) {
                    array_push($mailArray, strtolower($registerData['email5']));
                }

                //氏名漢字＋電話で一致
                $custDb = DB::table($localDbName. '.m_cust');
                $custDb->where('m_cust.gen_search_name_kanji', '=', $nameKanji);
                $custDb->where('m_cust.delete_operator_id', '=', 0);
                $custDb->join($localDbName.'.m_cust_tel', 'm_cust.m_cust_id', '=', 'm_cust_tel.m_cust_id');
                $custDb->whereIn('m_cust_tel.tel', $telArray);
                $mCustId = $custDb->max('m_cust.m_cust_id');
                if (!is_null($mCustId)) {
                    $insertFlg = 0;
                }

                //氏名カナ＋電話で一致
                if ($insertFlg <> 0 && $nameKana <> "") {
                    $custDb2 = DB::table($localDbName. '.m_cust');
                    $custDb2->where('m_cust.gen_search_name_kana', '=', $nameKana);
                    $custDb2->where('m_cust.delete_operator_id', '=', 0);
                    $custDb2->join($localDbName.'.m_cust_tel', 'm_cust.m_cust_id', '=', 'm_cust_tel.m_cust_id');
                    $custDb2->whereIn('m_cust_tel.tel', $telArray);
                    $mCustId = $custDb2->max('m_cust.m_cust_id');
                    if (!is_null($mCustId)) {
                        $insertFlg = 0;
                    }
                }
                //氏名漢字＋メールアドレスで一致
                if ($insertFlg <> 0 && count($mailArray) <> 0) {
                    $custDb3 = DB::table($localDbName. '.m_cust');
                    $custDb3->where('m_cust.gen_search_name_kanji', '=', $nameKanji);
                    $custDb3->where('m_cust.delete_operator_id', '=', 0);
                    $custDb3->join($localDbName.'.m_cust_email', 'm_cust.m_cust_id', '=', 'm_cust_email.m_cust_id');
                    $custDb3->whereIn('m_cust_email.email', $mailArray);
                    $mCustId = $custDb3->max('m_cust.m_cust_id');
                    if (!is_null($mCustId)) {
                        $insertFlg = 0;
                    }
                }
                //氏名カナ＋メールアドレスで一致
                if ($insertFlg <> 0 && $nameKana <> "" && count($mailArray) <> 0) {
                    $custDb3 = DB::table($localDbName. '.m_cust');
                    $custDb3->where('m_cust.gen_search_name_kana', '=', $nameKana);
                    $custDb3->where('m_cust.delete_operator_id', '=', 0);
                    $custDb3->join($localDbName.'.m_cust_email', 'm_cust.m_cust_id', '=', 'm_cust_email.m_cust_id');
                    $custDb3->whereIn('m_cust_email.email', $mailArray);
                    $mCustId = $custDb3->max('m_cust.m_cust_id');
                    if (!is_null($mCustId)) {
                        $insertFlg = 0;
                    }
                }
            }
        }
        //try
        // {
        if ($insertFlg == 0) {
            //            if(isset($registerData[$this->primaryKey]))
            $registerData[$this->primaryKey] = $mCustId;
            if (isset($registerData['delete_operator_id']) && $registerData['delete_operator_id'] != 0) {
                //DELETE
                $registerCopy = $registerData;
                unset($registerCopy['cust_cd']);
                unset($registerCopy['m_cust_runk_id']);
                unset($registerCopy['name_kanji']);
                unset($registerCopy['name_kana']);
                unset($registerCopy['sex_type']);
                unset($registerCopy['birthday']);
                unset($registerCopy['tel1']);
                unset($registerCopy['tel2']);
                unset($registerCopy['tel3']);
                unset($registerCopy['tel4']);
                unset($registerCopy['fax']);
                unset($registerCopy['postal']);
                unset($registerCopy['address1']);
                unset($registerCopy['address2']);
                unset($registerCopy['address3']);
                unset($registerCopy['address4']);
                unset($registerCopy['corporate_kanji']);
                unset($registerCopy['corporate_kana']);
                unset($registerCopy['division_name']);
                unset($registerCopy['corporate_tel']);
                unset($registerCopy['email1']);
                unset($registerCopy['email2']);
                unset($registerCopy['email3']);
                unset($registerCopy['email4']);
                unset($registerCopy['email5']);
                unset($registerCopy['alert_cust_type']);
                unset($registerCopy['alert_cust_comment']);
                unset($registerCopy['note']);
                unset($registerCopy['reserve1']);
                unset($registerCopy['reserve2']);
                unset($registerCopy['reserve3']);
                unset($registerCopy['reserve4']);
                unset($registerCopy['reserve5']);
                unset($registerCopy['reserve6']);
                unset($registerCopy['reserve7']);
                unset($registerCopy['reserve8']);
                unset($registerCopy['reserve9']);
                unset($registerCopy['reserve10']);
                unset($registerCopy['reserve11']);
                unset($registerCopy['reserve12']);
                unset($registerCopy['reserve13']);
                unset($registerCopy['reserve14']);
                unset($registerCopy['reserve15']);
                unset($registerCopy['reserve16']);
                unset($registerCopy['reserve17']);
                unset($registerCopy['reserve18']);
                unset($registerCopy['reserve19']);
                unset($registerCopy['reserve20']);
                unset($registerCopy['delete_flg']);
                $updateTime = Carbon::now();
                $registerCopy['delete_timestamp'] = $updateTime->format('Y-m-d H:i:s.u');
                $retIds = $this->updateData($registerCopy);
            } else {
                // UPDATE
                $retIds = $this->updateData($registerData);
            }

        } else {
            // INSERT
            $retIds = $this->insertData($registerData);
        }
        //}
        //catch(\Exception $ex)
        // {
        //    $errorMsg = $ex->getMessage();
        //    throw new \Exception($errorMsg);
        //}

        return [
            $this->primaryKey => $retIds,
            'error_message' => $errorMsg
        ];
    }

    /**
     * その他データの検証を行う(登録)
     */
    public function checkRegisterColumns($request)
    {
        $validateError = $this->validateError;
        if(!isset($validateError['m_cust_id'])) {
            //顧客ID関連チェック
            //	    	 $localDbName = $this->accountData['local_database_name'];
            $localDbName = $this->localDbName;
            $registerData = $this->getRegisterData($request);
            if(isset($registerData['m_cust_id'])) {
                //顧客ID設定がある場合、有効顧客がないならエラー
                $custDb = DB::table($localDbName. '.m_cust');
                $custDb->where('m_cust_id', '=', $registerData['m_cust_id']);
                //$custDb->where('delete_operator_id', '=', 0);		//削除顧客の編集があるため
                $count = $custDb->count();
                //logger($custDb->toSql());
                if ($count == 0) {
                    $this->setError('削除された顧客IDです。', 'm_cust_id');
                }
            } else {
                //顧客ID設定がない場合、削除指定はエラー
                if(isset($registerData['delete_flg']) && $registerData['delete_flg'] == 1) {
                    $this->setError('削除指定の場合は顧客IDを指定してください。', 'm_cust_id');
                }
            }
        }
        //顧客ランクがマスタに存在するか
        $findFlg = true;
        if(!isset($validateError['m_cust_runk_id'])) {
            if(isset($registerData['m_cust_runk_id']) && $registerData['m_cust_runk_id'] != '') {
                $response = $this->searchItemNameTypes($request);
                if (isset($response['response']['total_record_count']) && $response['response']['total_record_count'] == 0) {
                    $findFlg = false;
                }
            }
        }
        if (!$findFlg) {
            $this->setError('顧客ランクが正しくありません。', 'm_cust_runk_id');
        }

        // 画面からの登録の場合、都道府県チェック
        $this->foreignFlg = false;
        if (isset($registerData['address1'])) {
            $prefecturalRows = $this->searchPrefectural(
                ['m_account_id' => $this->accountData['m_account_id']],
                'cc/registerCustomer',
                true,
                ['prefectual_name' => $registerData['address1']]
            );
            if(empty($prefecturalRows)) {
                // 画面からの登録の場合はエラーとする
                if (!empty($registerData['app_register_flg'])) {
                    $this->setError('都道府県が正しくありません。', 'address1');
                } else {
                    // とりあえず外国扱いで郵便番号の検証はしない
                    $this->foreignFlg = true;

                    $address = $registerData['address1'] . ($registerData['address2'] ?? '');
                    if (mb_strlen($address) > 100) {
                        $this->setError('住所１、住所２が長すぎます。', 'address2');
                    }
                }
            } else {
                $pRow = collect($prefecturalRows)->first();

                if(empty($pRow) || $pRow['prefectual_region'] == config('define.prefectual_region_foreign')) {
                    $this->foreignFlg = true;
                }
            }
        }
        // if ($this->foreignFlg == false)
        // {
        //     // 国外扱い以外は郵便番号のフォーマットを検証する
        //     if(!isset($registerData['postal']) || strlen($registerData['postal']) < 1)
        //     {
        //         $this->setError('郵便番号が入力されていません。', 'postal');
        //     }
        //     elseif(!$this->checkPostal($registerData['postal']))
        //     {
        //         $this->setError('郵便番号が正しくありません。', 'postal');
        //     }
        // }
    }

    /**
     * 項目名称マスタ情報取得
     *
     * @param $request array
     *
     * @return array APIレスポンス
     */
    public function searchItemNameTypes($request)
    {
        // リクエストパラメータ作成
        $registerData = $this->getRegisterData($request);
        $registerRequest = [
            'request' => [
                'm_account_id' => $request['request']['m_account_id'],
                'operator_id' => $registerData['operator_id'],
                'feature_id' => '',
                'display_csv_flag' => '0',
                'search_info' => [
                    'delete_flg' => '0',
                    'm_itemname_type' => '3',
                    'm_itemname_types_id' => $registerData['m_cust_runk_id'],
                ],
            ]
        ];

        // API実行
        $apiResponse = $this->getApiData($registerRequest, 'master', 'searchItemnameTypes');

        return $apiResponse;
    }


    /**
     * 登録データを加工する場合
     */
    protected function editInsertData($requestData, $insertData)
    {
        //生年月日
        if (isset($insertData['birthday']) && $insertData['birthday'] == '') {
            $insertData['birthday'] = '0000-00-00';
        }
        //顧客ランク
        if (isset($insertData['m_cust_runk_id']) && $insertData['m_cust_runk_id'] == '') {
            $insertData['m_cust_runk_id'] = '0';
        }
        //使用区分
        if (isset($insertData['delete_flg']) && $insertData['delete_flg'] == '') {
            unset($insertData['delete_flg']);
        }
        //郵便番号
        if (isset($insertData['postal']) && strpos($insertData["postal"], '-') !== false) {
            $insertData['postal'] = str_replace('-', '', $insertData['postal']);
            ;
        }
        //都道府県が存在しない場合は住所１を住所２の先頭に付加する
        if (empty($requestData['app_register_flg'])) {
            $prefecturalRows = $this->searchPrefectural(
                ['m_account_id' => $this->accountData['m_account_id']],
                'cc/registerCustomer',
                true,
                ['prefectual_name' => $insertData['address1']]
            );
            if(empty($prefecturalRows)) {
                $insertData['address2'] = $insertData['address1'] . $insertData['address2'];
                $insertData['address1'] = '';
            }
        }
        return $insertData;
    }

    /**
     * 更新データを加工する場合
     */
    protected function editUpdateData($requestData, $updateData)
    {
        // 名寄せする場合で、未設定or初期値の場合は項目を上書きしない
        if(empty($requestData['name_sorting_flg'])) {
            foreach($this->updateCheckColumns as $columnName => $value) {
                if(!isset($updateData[$columnName]) || strlen($updateData[$columnName]) == 0 || $updateData[$columnName] == $value) {
                    unset($updateData[$columnName]);
                }
            }
        } else {
            //生年月日
            if (isset($updateData['birthday']) && $updateData['birthday'] == '') {
                $updateData['birthday'] = '0000-00-00';
            }
            //顧客ランク
            if (isset($updateData['m_cust_runk_id']) && $updateData['m_cust_runk_id'] == '') {
                $updateData['m_cust_runk_id'] = '0';
            }
        }

        //使用区分
        if (isset($updateData['delete_flg']) && $updateData['delete_flg'] == '') {
            $updateData['delete_flg'] = '0';
        }

        //郵便番号
        if($this->foreignFlg  && (!isset($updateData['postal']) || strlen($updateData['postal']) == 0)) {
            // 国外設定の場合で郵便番号未入力の場合、郵便番号をいったん削除する
            $updateData['postal'] = '';
        } elseif (isset($updateData['postal']) && strpos($updateData["postal"], '-') !== false) {
            $updateData['postal'] = str_replace('-', '', $updateData['postal']);
            ;
        }

        return $updateData;
    }
}
