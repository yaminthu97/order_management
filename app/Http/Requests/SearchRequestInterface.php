<?php
namespace App\Http\Requests;

interface SearchRequestInterface
{
    /**
     * 検索条件の取得
     * @return array 検索条件
     */
    public function getSearchConditions(): array;


    /**
     * 検索オプションの取得
     * @return array 検索オプション
     */
    public function getSearchOptions(): array;
}
