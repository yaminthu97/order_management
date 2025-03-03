<?php

namespace Tests\Feature;

use Tests\TestCase;


use Database\Factories\CustModelFactory;

use Database\Factories\AmiSkuModelFactory;
use Database\Factories\AmiPageModelFactory;
use Database\Factories\AmiEcPageModelFactory;

use Database\Factories\OrderHdrModelFactory;
use Database\Factories\OrderDestinationModelFactory;
use Database\Factories\OrderDtlModelFactory;
use Database\Factories\OrderDtlSkuModelFactory;

class OrderListTest extends TestCase
{

    public $local_db;
    public $cust;

    // public function setUp(): void
    // {
    //     parent::setUp();

    //     // TODO: m_aaccount から取る
    //     $this->local_db = 'gfh_1207_db_testing';
    //     // TODO: ECS作成

    //     // 顧客
    //     $this->cust = CustModelFactory::new()->createWithDatabase([], $this->local_db);

    //     // 商品
    //     $sku = AmiSkuModelFactory::new()->createWithDatabase([], $this->local_db);
    //     $page = AmiPageModelFactory::new()->createWithDatabase([
    //         //'t_order_hdr_id' => $sku->t_order_hdr_id,
    //     ], $this->local_db);
    //     $ecpage = AmiEcPageModelFactory::new()->createWithDatabase([
    //         'm_ami_page_id' => $page->m_ami_page_id,
    //     ], $this->local_db);

    //     // 受注
    //     $orderHdr = OrderHdrModelFactory::new()->createWithDatabase([], $this->local_db);
    //     $orderDestinations = OrderDestinationModelFactory::new()->count(3)->createWithDatabase([
    //         't_order_hdr_id' => $orderHdr->t_order_hdr_id,
    //     ], $this->local_db);
    //     $orderDestinations->each(function ($orderDestination) use ($orderHdr, $page, $sku) {
    //         $orderDtls = OrderDtlModelFactory::new()->count(3)->createWithDatabase([
    //             't_order_hdr_id' => $orderHdr->t_order_hdr_id,
    //             't_order_destination_id' => $orderDestination->t_order_destination_id,
    //             'sell_id' => $page->m_ami_page_id,
    //             'sell_cd' => $page->page_cd,
    //             'sell_name' => $page->page_title,
    //         ], $this->local_db);
    //         $orderDtls->each(function ($orderDtl) use ($orderHdr, $orderDestination, $page, $sku) {
    //             $orderDtlSkus = OrderDtlSkuModelFactory::new()->count(1)->createWithDatabase([
    //                 't_order_hdr_id' => $orderHdr->t_order_hdr_id,
    //                 't_order_destination_id' => $orderDestination->t_order_destination_id,
    //                 't_order_dtl_id' => $orderDtl->t_order_dtl_id,
    //                 'sell_cd' => $page->ec_page_cd,
    //                 'item_id' => $sku->m_ami_sku_id,
    //                 'item_cd' => $sku->sku_cd,
    //             ], $this->local_db);
    //         });
    //     });
    // }

    // public function test_get_request(): void
    // {
    //     $response = $this->get('/order/list');

    //     // レスポンスが200 OKであることを確認
    //     $response->assertStatus(200);
    // }

    // public function test_post_request(): void
    // {
    //     // POSTリクエストで通常の検索を行う
    //     $responsePost = $this->post('/order/list', [
    //         'order_name' => 'テスト'
    //     ]);

    //     // Ajaxによるリクエストを行う
    //     $responseAjax = $this->withHeaders([
    //         'X-Requested-With' => 'XMLHttpRequest',
    //     ])->post('/orders', [
    //         'order_name' => 'テスト',
    //     ]);

    //     // ステータスコードを確認
    //     $responsePost->assertStatus(200);
    //     $responseAjax->assertStatus(200);

    //     // レスポンスの内容が異なることを確認
    //     $this->assertNotEquals($responsePost->getContent(), $responseAjax->getContent(), '');
    // }

    /*
    // 追加項目のテスト
    public function test_ajax_request_search(): void
    {
        // 販売窓口検索

        // Web会員番号検索

        // ピッキングコメントの有無

        // ピッキングコメント
    }

    // 検索条件関連
    public function test_search_cond(): void
    {
        // 検索条件の保存

        // 検索条件の読み出し

        // 検索条件の変更

        // 検索条件の削除
    }

    // 各種操作のテスト
    public function test_add_batch(): void
    {
    }
    */
}
