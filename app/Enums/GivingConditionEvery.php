<?php

namespace App\Enums;

/*金額追加付与コード キャンペーン登録編集時に使用するフラグ*/
enum GivingConditionEvery: int
{
    case Do = 1;
    case Notdo = 0;
    
    public function label(): string
    {
        return match($this){
            self::Do => 'する',
            self::Notdo => 'しない',
        };
    }
}