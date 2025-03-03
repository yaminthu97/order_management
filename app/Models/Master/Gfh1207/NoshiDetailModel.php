<?php

namespace App\Models\Master\Gfh1207;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class NoshiDetailModel extends Model
{
    use HasFactory;

    /**
     * テーブル名称
     *
     * @var string
     */
    protected $table = 'm_noshi_detail';


    /**
     * テーブルの主キー
     *
     * @var string
     */
    protected $primaryKey = 'm_noshi_detail_id';

    /**
     * モデルの日付カラムの保存用フォーマット
     *
     * @var string
     */
    protected $dateFormat = 'Y-m-d H:i:s';

    const CREATED_AT = 'entry_timestamp';
    const UPDATED_AT = 'update_timestamp';

    /**
     * 名入れパターンマスタとのリレーション
     */
    public function noshiNamingPattern()
    {
        return $this->hasOne(NoshiNamingPatternModel::class, 'm_noshi_naming_pattern_id', 'm_noshi_naming_pattern_id');
    }

    /**
     * 熨斗マスタとのリレーション
     */
    public function noshi()
    {
        return $this->belongsTo(NoshiModel::class, 'm_noshi_id', 'm_noshi_id');
    }
}
