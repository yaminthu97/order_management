<?php
namespace App\Modules\Sample\Base;

/**
 * サンプル機能の登録チェック処理インターフェース
 * モジュールのインターフェースは、executeメソッドのみを持つ
 * 登録チェックのみを行う場合は、StoreCheckをプレフィックスとする。
 * 確認画面が存在し、入力画面から保存まで1クッション挟む場合で、DBチェックなどが発生する場合に作成する
 */
interface StoreCheckSampleInterface
{
    /**
     * FormRequest外で行うDB参照や複雑な相関バリデーションを行う場合に使用
     * @param array $input バリデーション対象の入力値
     * @return bool バリデーション結果(true以外はExceptionを投げる)
     * @exception \App\Exceptions\ModuleValidationException バリデーションエラー時
     */
    public function execute(array $input): bool;
}
