<?php
namespace App\Modules\Sample\Base;


/**
 * サンプル機能の削除処理インターフェース
 * モジュールのインターフェースは、executeメソッドのみを持つ
 * 削除処理を行う場合は、Deleteをプレフィックスとする。
 */
interface DeleteSampleInterface
{
    /**
     * (論理)削除処理
     * @param string|int $id 削除対象のID
     * @return bool 削除結果(削除できないときは例外を投げるので、trueしか返さないはず)
     * @throws ModelNotFoundException データが見つからなかった場合
     */
    public function execute(string|int $id):bool;
}
