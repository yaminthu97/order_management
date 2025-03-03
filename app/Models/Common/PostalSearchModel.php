<?php

namespace App\Models\Common;

class PostalSearchModel extends ApiSyscomModel
{
    protected $fillAble = [
        'id',
        'jis_code',
        'post_3',
        'post_7',
        'pref_kana',
        'address1_kana',
        'address2_kana',
        'pref',
        'address1',
        'address2',
        'key_1',
        'key_2',
        'key_3',
        'key_4',
        'key_5',
        'key_6',
        'address',
        'address_kana',
    ];

    protected $table = 'm_post_list';

    protected $primaryKey = 'id';

    protected $localDbFlag = false;

    /**
     * m_userテーブルをjoinするか
     * (デフォルトはするが、しない場合のみfalseを設定する)
     */
    protected $joinUser = false;

    /**
     * 検索のWhere条件
     */
    protected function addWhere($select, $searchInfo)
    {
        $dbSelect = $select;

        logger($searchInfo);

        if (! empty($searchInfo)) {
            if (isset($searchInfo['post_7']) && mb_strlen($searchInfo['post_7']) > 0) {
                $dbSelect->where('post_7', 'like', "{$searchInfo['post_7']}%%");
            }

            if ((!isset($searchInfo['post_7']) || mb_strlen($searchInfo['post_7']) == 0) && (isset($searchInfo['address']) && mb_strlen($searchInfo['address']) > 0)) {
                $dbSelect = $this->whereFulltext($dbSelect, 'address', $searchInfo['address']);
            }
        }

        return $dbSelect;
    }

    /**
     * 検索のWhere条件
     */
    protected function _addWhere($searchInfo)
    {
        if (! empty($searchInfo)) {
            if (isset($searchInfo['post_7']) && mb_strlen($searchInfo['post_7']) > 0) {
                $this->dbSelect->where('post_7', 'like', "{$searchInfo['post_7']}%%");
            }

            if ((!isset($searchInfo['post_7']) || mb_strlen($searchInfo['post_7']) == 0) && (isset($searchInfo['address']) && mb_strlen($searchInfo['address']) > 0)) {
                $this->dbSelect = $this->whereFulltext($this->dbSelect, 'address', $searchInfo['address']);
            }
        }
    }
}
