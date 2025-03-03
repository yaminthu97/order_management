<?php

namespace App\Models\Master\Gfh1207;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class NoshiModel extends Model
{
    use HasFactory;

    /**
     * テーブル名称
     *
     * @var string
     */
    protected $table = 'm_noshi';


    /**
     * テーブルの主キー
     *
     * @var string
     */
    protected $primaryKey = 'm_noshi_id';

    /**
     * モデルの日付カラムの保存用フォーマット
     *
     * @var string
     */
    protected $dateFormat = 'Y-m-d H:i:s';

    const CREATED_AT = 'entry_timestamp';
    const UPDATED_AT = 'update_timestamp';

    /**
     * 熨斗詳細マスタとのリレーション
     */
    public function noshiDetail()
    {
        return $this->hasOne(NoshiDetailModel::class, 'm_noshi_id', 'm_noshi_id');
    }

}
