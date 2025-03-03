<?php
namespace Tests\Feature\Http\Controller\Order\OrderListController\Gfh1207;

use App\Models\Master\Base\OperatorModel;
use Illuminate\Support\Facades\Log;
use PHPUnit\Framework\Attributes\DataProviderExternal;
use PHPUnit\Framework\Attributes\TestDox;
use Tests\Feature\Http\Controller\Order\OrderListController\Gfh1207\DataProviders\LoginOperatorInfoDataProvider;
use Tests\Feature\Http\Controller\Order\OrderListController\Gfh1207\DataProviders\SearchDataProvider;
use Tests\TestCases\Gfh1207TestCase;
use Carbon\Carbon;
use Illuminate\Support\Facades\Artisan;

use App\Services\TenantDatabaseManager;

class EditApiTest extends Gfh1207TestCase
{
    protected $preparedOperator;
    protected $orderTag1;
    protected $orderTag2;
    public function setUp(): void
    {
        //Artisan::call('cache:clear');
        //Artisan::call('config:clear');
        //Artisan::call('optimize:clear');
        //Artisan::call('route:clear');

        parent::setUp();
        $this->startSession();

        // 担当者の作成
        $this->preparedOperator = OperatorModel::factory()->for($this->mAccount, 'account')->create();

        TenantDatabaseManager::setTenantConnection($this->mAccount->account_cd.'_db_testing');
        // シーディング
        $this->seedData();
    }

    #[TestDox('共通認証処理(GET)')]
    public function test_get_api_auth()
    {
        // Arrange
        $this->withSession(['OperatorInfo' => $this->operatorInfo()]);
        $csrfToken = csrf_token();

        // GET リクエスト
        $response = $this->withHeaders([
            'X-Requested-With' => 'XMLHttpRequest',
            'Authorization' => $csrfToken,
        ])->get('order/api/deli_type/list', []);

        // Assert
        $response->assertStatus(200);
    }

    public function test_get_api_noauth()
    {
        // GET リクエスト
        $response = $this->withHeaders([
            'X-Requested-With' => 'XMLHttpRequest',
            'Authorization' => 'dummy_token',
        ])->get('order/api/deli_type/list', []);

        // Assert
        $response->assertStatus(302);
    }

    #[TestDox('共通認証処理(POST)')]
    public function test_post_api_auth()
    {
        // Arrange
        $this->withSession(['OperatorInfo' => $this->operatorInfo()]);
        $csrfToken = csrf_token();

        // Ajaxによるリクエストを行う
        $response = $this->postJson('order/api/customer/list', [
            '_token' => $csrfToken,
        ]);

        // Assert
        $response->assertStatus(200);
    }

    public function test_post_api_noauth()
    {
        // Ajaxによるリクエストを行う
        $response = $this->postJson('order/api/customer/list', [
            '_token' => 'dummy_token',
        ]);

        // Assert
        $response->assertStatus(302);
    }

    #[TestDox('配送方法リスト取得API')]
    public function test_deli_type_api()
    {
        
        // Arrange
        $this->withSession(['OperatorInfo' => $this->operatorInfo()]);
        $csrfToken = csrf_token();

        // GET リクエスト
        $response = $this->withHeaders([
            'X-Requested-With' => 'XMLHttpRequest',
            'Authorization' => $csrfToken,
        ])->get('order/api/deli_type/list', []);

        // Assert
        $response->assertStatus(200);

        // 内容を取得
        $content = $response->getContent();

        // content が json 形式であること
        $this->assertJson($content);

        // json 直下の内容が配列であること
        $this->assertIsArray(json_decode($content, true));

        // 必要な情報が含まれていること
        $this->assertArrayHasKey('m_delivery_types_id', json_decode($content, true)[0]);
        $this->assertArrayHasKey('m_delivery_type_name', json_decode($content, true)[0]);
        $this->assertArrayHasKey('delivery_type', json_decode($content, true)[0]);
        $this->assertArrayHasKey('standard_fee', json_decode($content, true)[0]);
        $this->assertArrayHasKey('frozen_fee', json_decode($content, true)[0]);
        $this->assertArrayHasKey('chilled_fee', json_decode($content, true)[0]);
    }

    #[TestDox('配送方法詳細取得API')]
    public function test_deli_type_info_api()
    {
        // Arrange
        $this->withSession(['OperatorInfo' => $this->operatorInfo()]);
        $csrfToken = csrf_token();

        // GET リクエスト
        $response = $this->withHeaders([
            'X-Requested-With' => 'XMLHttpRequest',
            'Authorization' => $csrfToken,
        ])->get('order/api/deli_type/1', []);

        // Assert
        $response->assertStatus(200);

        // 内容を取得
        $contentJson = $response->getContent();

        // content が json 形式であること
        $this->assertJson($contentJson);

        $content = json_decode($contentJson, true);

        // 必要な情報が含まれていること
        $this->assertArrayHasKey('m_delivery_types_id', $content);
        $this->assertArrayHasKey('m_delivery_type_name', $content);
        $this->assertArrayHasKey('delivery_type', $content);
        $this->assertArrayHasKey('standard_fee', $content);
        $this->assertArrayHasKey('frozen_fee', $content);
        $this->assertArrayHasKey('chilled_fee', $content);
        
        //$this->assertArrayHasKey('m_prefecture_id', $content['delivery_fees'][0]);
        //$this->assertArrayHasKey('delivery_fee', $content['delivery_fees'][0]);
    }

    public function test_deli_type_info_api_error()
    {
        // Arrange
        $this->withSession(['OperatorInfo' => $this->operatorInfo()]);
        $csrfToken = csrf_token();

        // GET リクエスト
        $response = $this->withHeaders([
            'X-Requested-With' => 'XMLHttpRequest',
            'Authorization' => $csrfToken,
        ])->get('order/api/deli_type/99999999', []);

        // Assert
        $response->assertStatus(404);
    }

    #[TestDox('受注タグ追加API')]
    public function test_order_tags_add_api()
    {
        // 受注タグ
        $orderTag = \App\Models\Master\Base\OrderTagModel::factory([
            'm_account_id' => $this->mAccount->m_account_id,
            'tag_name' => '受注タグテスト',
            'tag_display_name' => '受注タグ',
        ])->create();

        // OrderModel 最新の1件を取得
        $order = \App\Models\Order\Gfh1207\OrderHdrModel::latest()->first();

        // Arrange
        $this->withSession(['OperatorInfo' => $this->operatorInfo()]);
        $csrfToken = csrf_token();

        // Ajaxによるリクエストを行う
        $response = $this->post('order/api/order-tags/add', [
            '_token' => $csrfToken,
            'order_hdr_id' => $order->t_order_hdr_id,
            'order_tag_id' => $orderTag->m_order_tag_id,
        ], [
            'X-Requested-With' => 'XMLHttpRequest',
        ]);

        // Assert
        $response->assertStatus(200);

        // 内容を取得
        $contentJson = $response->getContent();

        // content が json 形式であること
        $this->assertJson($contentJson);

        $content = json_decode($contentJson, true);
    }

    public function test_order_tags_multi_add_api()
    {
        // 受注タグ
        $orderTag1 = \App\Models\Master\Base\OrderTagModel::factory([
            'm_account_id' => $this->mAccount->m_account_id,
            'tag_name' => '受注タグテスト',
            'tag_display_name' => '受注タグ',
        ])->create();
        $orderTag2 = \App\Models\Master\Base\OrderTagModel::factory([
            'm_account_id' => $this->mAccount->m_account_id,
            'tag_name' => '受注タグテスト',
            'tag_display_name' => '受注タグ',
        ])->create();

        // OrderModel 最新の1件を取得
        $order = \App\Models\Order\Gfh1207\OrderHdrModel::latest()->first();

        // Arrange
        $this->withSession(['OperatorInfo' => $this->operatorInfo()]);
        $csrfToken = csrf_token();

        // Ajaxによるリクエストを行う
        $response = $this->post('order/api/order-tags/add', [
            '_token' => $csrfToken,
            'order_hdr_id' => $order->t_order_hdr_id,
            'order_tag_id' => [$orderTag1->m_order_tag_id,$orderTag2->m_order_tag_id],

        ], [
            'X-Requested-With' => 'XMLHttpRequest',
        ]);

        // Assert
        $response->assertStatus(200);

        // 内容を取得
        $contentJson = $response->getContent();

        // content が json 形式であること
        $this->assertJson($contentJson);

        $content = json_decode($contentJson, true);
    }

    public function test_order_tags_add_api_error()
    {
        // 受注タグ
        $orderTag = \App\Models\Master\Base\OrderTagModel::factory([
            'm_account_id' => $this->mAccount->m_account_id,
            'tag_name' => '受注タグテスト',
            'tag_display_name' => '受注タグ',
        ])->create();

        // OrderModel 最新の1件を取得
        $order = \App\Models\Order\Gfh1207\OrderHdrModel::latest()->first();

        // Arrange
        $this->withSession(['OperatorInfo' => $this->operatorInfo()]);
        $csrfToken = csrf_token();

        // Ajaxによるリクエストを行う
        $response = $this->post('order/api/order-tags/add', [
            '_token' => $csrfToken,
            'order_hdr_id' => 99999999,
            'order_tag_id' => $orderTag->m_order_tag_id,
        ], [
            'X-Requested-With' => 'XMLHttpRequest',
        ]);

        // Assert
        $response->assertStatus(500);

        // Ajaxによるリクエストを行う
        $response = $this->post('order/api/order-tags/add', [
            '_token' => $csrfToken,
            'order_hdr_id' => $order->t_order_hdr_id,
            'order_tag_id' => 99999999,
        ], [
            'X-Requested-With' => 'XMLHttpRequest',
        ]);

        // Assert
        $response->assertStatus(404);
    }

    #[TestDox('受注タグ削除API')]
    public function test_order_tags_remove_api()
    {
        // 受注タグ
        $orderTag = \App\Models\Master\Base\OrderTagModel::factory([
            'm_account_id' => $this->mAccount->m_account_id,
            'tag_name' => '受注タグテスト',
            'tag_display_name' => '受注タグ',
        ])->create();

        // OrderModel 最新の1件を取得
        $order = \App\Models\Order\Gfh1207\OrderHdrModel::latest()->first();

        // Arrange
        $this->withSession(['OperatorInfo' => $this->operatorInfo()]);
        $csrfToken = csrf_token();

        // Ajaxによるリクエストを行う
        $response = $this->post('order/api/order-tags/add', [
            '_token' => $csrfToken,
            'order_hdr_id' => $order->t_order_hdr_id,
            'order_tag_id' => $orderTag->m_order_tag_id,
        ], [
            'X-Requested-With' => 'XMLHttpRequest',
        ]);

        // Assert
        $response->assertStatus(200);
        // 結果を取得
        $content = json_decode($response->getContent());

        // Ajaxによるリクエストを行う
        $response = $this->post('order/api/order-tags/remove', [
            '_token' => $csrfToken,
            'order_hdr_id' => $order->t_order_hdr_id,
            'order_tag_id' => $orderTag->m_order_tag_id,
        ], [
            'X-Requested-With' => 'XMLHttpRequest',
        ]);
        // 内容を取得
        $contentJson = $response->getContent();

        // Assert
        //$response->assertStatus(200);
        // content が json 形式であること
        //$this->assertJson($contentJson);
    }

    public function test_order_tags_multi_remove_api()
    {
        // 受注タグ
        $orderTag1 = \App\Models\Master\Base\OrderTagModel::factory([
            'm_account_id' => $this->mAccount->m_account_id,
            'tag_name' => '受注タグテスト',
            'tag_display_name' => '受注タグ',
        ])->create();
        $orderTag2 = \App\Models\Master\Base\OrderTagModel::factory([
            'm_account_id' => $this->mAccount->m_account_id,
            'tag_name' => '受注タグテスト',
            'tag_display_name' => '受注タグ',
        ])->create();

        // OrderModel 最新の1件を取得
        $order = \App\Models\Order\Gfh1207\OrderHdrModel::latest()->first();

        // Arrange
        $this->withSession(['OperatorInfo' => $this->operatorInfo()]);
        $csrfToken = csrf_token();

        // Ajaxによるリクエストを行う
        $response = $this->post('order/api/order-tags/add', [
            '_token' => $csrfToken,
            'order_hdr_id' => $order->t_order_hdr_id,
            'order_tag_id' => [$orderTag1->m_order_tag_id,$orderTag2->m_order_tag_id],
        ], [
            'X-Requested-With' => 'XMLHttpRequest',
        ]);

        // Assert
        $response->assertStatus(200);
        // 結果を取得
        $content = json_decode($response->getContent());

        // Ajaxによるリクエストを行う
        $response = $this->post('order/api/order-tags/remove', [
            '_token' => $csrfToken,
            'order_hdr_id' => $order->t_order_hdr_id,
            'order_tag_id' => [$orderTag1->m_order_tag_id,$orderTag2->m_order_tag_id],
        ], [
            'X-Requested-With' => 'XMLHttpRequest',
        ]);
        // 内容を取得
        $contentJson = $response->getContent();

        // Assert
        //$response->assertStatus(200);
        // content が json 形式であること
        //$this->assertJson($contentJson);
    }

    public function test_order_tags_remove_api_error()
    {
        // 受注タグ
        $orderTag = \App\Models\Master\Base\OrderTagModel::factory([
            'm_account_id' => $this->mAccount->m_account_id,
            'tag_name' => '受注タグテスト',
            'tag_display_name' => '受注タグ',
        ])->create();

        // OrderModel 最新の1件を取得
        $order = \App\Models\Order\Gfh1207\OrderHdrModel::latest()->first();

        // Arrange
        $this->withSession(['OperatorInfo' => $this->operatorInfo()]);
        $csrfToken = csrf_token();

        // Ajaxによるリクエストを行う
        $response = $this->post('order/api/order-tags/remove', [
            '_token' => $csrfToken,
            'order_hdr_id' => 99999999, // TODO
            'order_tag_id' => $orderTag->m_order_tag_id, // TODO
        ], [
            'X-Requested-With' => 'XMLHttpRequest',
        ]);

        // Assert
        $response->assertStatus(500);

        // Ajaxによるリクエストを行う
        $response = $this->post('order/api/order-tags/remove', [
            '_token' => $csrfToken,
            'order_hdr_id' => $order->t_order_hdr_id, // TODO
            'order_tag_id' => 99999999, // TODO
        ], [
            'X-Requested-With' => 'XMLHttpRequest',
        ]);

        // Assert
        $response->assertStatus(404);
    }

    #[TestDox('受注に対する受注タグ一覧取得API')]
    public function test_order_tags_order_info_api()
    {
        // 受注タグ
        $orderTagMaster = \App\Models\Master\Base\OrderTagModel::factory([
            'm_account_id' => $this->mAccount->m_account_id,
            'tag_name' => '受注タグテスト',
            'tag_display_name' => '受注タグ',
        ])->create();

        // OrderModel 最新の1件を取得
        $order = \App\Models\Order\Gfh1207\OrderHdrModel::latest()->first();

        // Arrange
        $this->withSession(['OperatorInfo' => $this->operatorInfo()]);
        $csrfToken = csrf_token();

        // Tag 追加
        $orderTag = new \App\Models\Order\Base\OrderTagModel();
        $orderTag->t_order_hdr_id = $order->t_order_hdr_id;
        $orderTag->m_order_tag_id = $orderTagMaster->m_order_tag_id;
        $orderTag->entry_operator_id = $this->preparedOperator->m_operator_id;
        $orderTag->entry_timestamp = Carbon::now();
        $orderTag->m_account_id = $this->mAccount->m_account_id;
        $orderTag->cancel_operator_id = 0;
        $orderTag->save();

        // GET リクエスト
        $response = $this->withHeaders([
            'X-Requested-With' => 'XMLHttpRequest',
            'Authorization' => $csrfToken,
        ])->get("order/api/order-tags/order/{$order->t_order_hdr_id}", []);

        // 内容を取得
        $contentJson = $response->getContent();
        
        // Assert
        $response->assertStatus(200);
        // content が json 形式であること
        $this->assertJson($contentJson);
    }

    public function test_order_tags_order_info_api_error()
    {
        // 受注タグ
        $orderTagMaster = \App\Models\Master\Base\OrderTagModel::factory([
            'm_account_id' => $this->mAccount->m_account_id,
            'tag_name' => '受注タグテスト',
            'tag_display_name' => '受注タグ',
        ])->create();

        // OrderModel 最新の1件を取得
        $order = \App\Models\Order\Gfh1207\OrderHdrModel::latest()->first();

        // Arrange
        $this->withSession(['OperatorInfo' => $this->operatorInfo()]);
        $csrfToken = csrf_token();

        // GET リクエスト
        $response = $this->withHeaders([
            'X-Requested-With' => 'XMLHttpRequest',
            'Authorization' => $csrfToken,
        ])->get("order/api/order-tags/order/99999999", []);

        // 内容を取得
        $contentJson = $response->getContent();
        
        // Assert
        $response->assertStatus(404);
        // content が json 形式であること
        $this->assertJson($contentJson);
    }

    #[TestDox('顧客情報取得API取得')]
    public function test_order_customer_info_api()
    {
        // Cust 最新の1件を取得
        $cust = \App\Models\Cc\Base\CustModel::latest()->first();

        // Arrange
        $this->withSession(['OperatorInfo' => $this->operatorInfo()]);
        $csrfToken = csrf_token();

        // GET リクエスト
        $response = $this->withHeaders([
            'X-Requested-With' => 'XMLHttpRequest',
            'Authorization' => $csrfToken,
        ])->get("order/api/customer/{$cust->m_cust_id}", []);

        // 内容を取得
        $contentJson = $response->getContent();
        
        // Assert
        $response->assertStatus(200);

        // 内容を取得
        $contentJson = $response->getContent();

        // content が json 形式であること
        $this->assertJson($contentJson);

        $content = json_decode($contentJson, true);

        // 必要な情報が含まれていること
        $this->assertArrayHasKey('m_cust_id', $content);
        $this->assertArrayHasKey('cust_cd', $content);
        $this->assertArrayHasKey('m_cust_runk_id', $content);
        $this->assertArrayHasKey('customer_category', $content);
        $this->assertArrayHasKey('name_kanji', $content);
        $this->assertArrayHasKey('name_kana', $content);
        $this->assertArrayHasKey('sex_type', $content);
        $this->assertArrayHasKey('birthday', $content);
        $this->assertArrayHasKey('tel1', $content);
        $this->assertArrayHasKey('tel2', $content);
        $this->assertArrayHasKey('tel3', $content);
        $this->assertArrayHasKey('tel4', $content);
        $this->assertArrayHasKey('fax', $content);
        $this->assertArrayHasKey('postal', $content);
        $this->assertArrayHasKey('address1', $content);
        $this->assertArrayHasKey('address2', $content);
        $this->assertArrayHasKey('address3', $content);
        $this->assertArrayHasKey('address4', $content);
        $this->assertArrayHasKey('corporate_kanji', $content);
        $this->assertArrayHasKey('corporate_kana', $content);
        $this->assertArrayHasKey('division_name', $content);
        $this->assertArrayHasKey('corporate_tel', $content);
        $this->assertArrayHasKey('email1', $content);
        $this->assertArrayHasKey('email2', $content);
        $this->assertArrayHasKey('email3', $content);
        $this->assertArrayHasKey('email4', $content);
        $this->assertArrayHasKey('email5', $content);
        $this->assertArrayHasKey('discount_rate', $content);
        $this->assertArrayHasKey('customer_type', $content);
        $this->assertArrayHasKey('dm_send_letter_flg', $content);
        $this->assertArrayHasKey('dm_send_mail_flg', $content);
        $this->assertArrayHasKey('alert_cust_type', $content);
        $this->assertArrayHasKey('alert_cust_comment', $content);
        $this->assertArrayHasKey('note', $content);
        for ($i = 1; $i <= 20; $i++) {
            $this->assertArrayHasKey('reserve' . $i, $content);
        }
    }

    public function test_order_customer_info_api_error()
    {
        // Cust 最新の1件を取得
        $cust = \App\Models\Cc\Base\CustModel::latest()->first();

        // Arrange
        $this->withSession(['OperatorInfo' => $this->operatorInfo()]);
        $csrfToken = csrf_token();

        // GET リクエスト
        $response = $this->withHeaders([
            'X-Requested-With' => 'XMLHttpRequest',
            'Authorization' => $csrfToken,
        ])->get("order/api/customer/99999999", []);

        // 内容を取得
        $contentJson = $response->getContent();
        
        // Assert
        $response->assertStatus(404);
    }

    #[TestDox('送付先情報取得API')]
    public function test_order_customer_destination_info_api()
    {
        // Cust 最新の1件を取得
        $cust = \App\Models\Cc\Base\CustModel::latest()->first();

        // 送付先追加
        $destination = \App\Models\Order\Base\DestinationModel::factory([
            'm_account_id' => $this->mAccount->m_account_id,
            'cust_id' => $cust->m_cust_id,
        ])->create();

        // Arrange
        $this->withSession(['OperatorInfo' => $this->operatorInfo()]);
        $csrfToken = csrf_token();

        // GET リクエスト
        $response = $this->withHeaders([
            'X-Requested-With' => 'XMLHttpRequest',
            'Authorization' => $csrfToken,
        ])->get("order/api/customer/destination/{$destination->m_destination_id}", []);

        // 内容を取得
        $contentJson = $response->getContent();
        
        // Assert
        $response->assertStatus(200);

        // 内容を取得
        $contentJson = $response->getContent();

        // content が json 形式であること
        $this->assertJson($contentJson);

        $content = json_decode($contentJson, true);

        $this->assertArrayHasKey('cust_id', $content);
        $this->assertArrayHasKey('destination_tel', $content);
        $this->assertArrayHasKey('destination_postal', $content);
        $this->assertArrayHasKey('destination_address1', $content);
        $this->assertArrayHasKey('destination_address2', $content);
        $this->assertArrayHasKey('destination_address3', $content);
        $this->assertArrayHasKey('destination_address4', $content);
        $this->assertArrayHasKey('destination_company_name', $content);
        $this->assertArrayHasKey('destination_division_name', $content);
    }

    public function test_order_customer_destination_info_api_error()
    {
        // Arrange
        $this->withSession(['OperatorInfo' => $this->operatorInfo()]);
        $csrfToken = csrf_token();

        // GET リクエスト
        $response = $this->withHeaders([
            'X-Requested-With' => 'XMLHttpRequest',
            'Authorization' => $csrfToken,
        ])->get("order/api/customer/destination/99999999", []);

        // 内容を取得
        $contentJson = $response->getContent();
        
        // Assert
        $response->assertStatus(404);
    }


    #[TestDox('商品情報取得API')]
    public function test_order_ami_page_info_api()
    {
        // amiEcPage 最新の1件を取得
        $amiEcPage = \App\Models\Ami\Base\AmiEcPageModel::latest()->first();

        // Arrange
        $this->withSession(['OperatorInfo' => $this->operatorInfo()]);
        $csrfToken = csrf_token();

        // GET リクエスト
        $response = $this->withHeaders([
            'X-Requested-With' => 'XMLHttpRequest',
            'Authorization' => $csrfToken,
        ])->get("order/api/ami_page/{$amiEcPage->m_ami_ec_page_id}", []);

        // 内容を取得
        $contentJson = $response->getContent();
        
        // Assert
        $response->assertStatus(200);

        // 内容を取得
        $contentJson = $response->getContent();

        // content が json 形式であること
        $this->assertJson($contentJson);

        $content = json_decode($contentJson, true);

        // 必要な情報が含まれていること
        $this->assertArrayHasKey('m_ami_ec_page_id', $content);
        $this->assertArrayHasKey('m_ami_page_id', $content);
        $this->assertArrayHasKey('m_ecs_id', $content);
        $this->assertArrayHasKey('m_ec_type', $content);
        $this->assertArrayHasKey('auto_stock_cooperation_flg', $content);
        $this->assertArrayHasKey('auto_ec_page_cooperation_flg', $content);
        $this->assertArrayHasKey('ec_page_cd', $content);
        $this->assertArrayHasKey('ec_page_title', $content);
        $this->assertArrayHasKey('ec_page_type', $content);
        $this->assertArrayHasKey('sales_price', $content);
        $this->assertArrayHasKey('tax_rate', $content);

        $this->assertArrayHasKey('page', $content);
        $this->assertArrayHasKey('page_cd', $content['page']);
        $this->assertArrayHasKey('page_title', $content['page']);
        $this->assertArrayHasKey('sales_price', $content['page']);
        $this->assertArrayHasKey('tax_rate', $content['page']);
        $this->assertArrayHasKey('page_desc', $content['page']);
        $this->assertArrayHasKey('image_path', $content['page']);
        $this->assertArrayHasKey('remarks1', $content['page']);
        $this->assertArrayHasKey('remarks2', $content['page']);
        $this->assertArrayHasKey('remarks3', $content['page']);
        $this->assertArrayHasKey('remarks4', $content['page']);
        $this->assertArrayHasKey('remarks5', $content['page']);

        $this->assertArrayHasKey('page_sku', $content['page']);
        $this->assertArrayHasKey('m_ami_sku_id', $content['page']['page_sku'][0]);
        $this->assertArrayHasKey('sku_cd', $content['page']['page_sku'][0]);
        $this->assertArrayHasKey('sku_name', $content['page']['page_sku'][0]);
        $this->assertArrayHasKey('jan_cd', $content['page']['page_sku'][0]);
        $this->assertArrayHasKey('including_package_flg', $content['page']['page_sku'][0]);
        $this->assertArrayHasKey('direct_delivery_flg', $content['page']['page_sku'][0]);
        $this->assertArrayHasKey('three_temperature_zone_type', $content['page']['page_sku'][0]);
        $this->assertArrayHasKey('gift_flg', $content['page']['page_sku'][0]);
        $this->assertArrayHasKey('search_result_display_flg', $content['page']['page_sku'][0]);
        $this->assertArrayHasKey('stock_cooperation_status', $content['page']['page_sku'][0]);
        $this->assertArrayHasKey('warehouse_cooperation_status', $content['page']['page_sku'][0]);
        $this->assertArrayHasKey('m_suppliers_id', $content['page']['page_sku'][0]);
        $this->assertArrayHasKey('sales_price', $content['page']['page_sku'][0]);
        $this->assertArrayHasKey('item_price', $content['page']['page_sku'][0]);
        $this->assertArrayHasKey('item_cost', $content['page']['page_sku'][0]);
        $this->assertArrayHasKey('remarks1', $content['page']['page_sku'][0]);
        $this->assertArrayHasKey('remarks2', $content['page']['page_sku'][0]);
        $this->assertArrayHasKey('remarks3', $content['page']['page_sku'][0]);
        $this->assertArrayHasKey('remarks4', $content['page']['page_sku'][0]);
        $this->assertArrayHasKey('remarks5', $content['page']['page_sku'][0]);
    }

    public function test_order_ami_page_info_api_error()
    {
        // Arrange
        $this->withSession(['OperatorInfo' => $this->operatorInfo()]);
        $csrfToken = csrf_token();

        // GET リクエスト
        $response = $this->withHeaders([
            'X-Requested-With' => 'XMLHttpRequest',
            'Authorization' => $csrfToken,
        ])->get("order/api/ami_page/99999999", []);

        // 内容を取得
        $contentJson = $response->getContent();
        
        // Assert
        $response->assertStatus(404);
    }

    #[TestDox('販売コードによる商品情報取得API')]
    public function test_order_ami_page_ec_page_cd_api()
    {
        // amiEcPage 最新の1件を取得
        $amiEcPage = \App\Models\Ami\Base\AmiEcPageModel::latest()->first();

        // Arrange
        $this->withSession(['OperatorInfo' => $this->operatorInfo()]);
        $csrfToken = csrf_token();

        // GET リクエスト
        $response = $this->withHeaders([
            'X-Requested-With' => 'XMLHttpRequest',
            'Authorization' => $csrfToken,
        ])->get("order/api/ami_page/search?m_ecs_id={$amiEcPage->m_ecs_id}&ec_page_cd={$amiEcPage->ec_page_cd}", []);

        // 内容を取得
        $contentJson = $response->getContent();
        
        // Assert
        $response->assertStatus(200);

        // 内容を取得
        $contentJson = $response->getContent();

        // content が json 形式であること
        $this->assertJson($contentJson);

        $content = json_decode($contentJson, true);

        // 必要な情報が含まれていること
        $this->assertArrayHasKey('m_ami_ec_page_id', $content);
        $this->assertArrayHasKey('m_ami_page_id', $content);
        $this->assertArrayHasKey('m_ecs_id', $content);
        $this->assertArrayHasKey('m_ec_type', $content);
        $this->assertArrayHasKey('auto_stock_cooperation_flg', $content);
        $this->assertArrayHasKey('auto_ec_page_cooperation_flg', $content);
        $this->assertArrayHasKey('ec_page_cd', $content);
        $this->assertArrayHasKey('ec_page_title', $content);
        $this->assertArrayHasKey('ec_page_type', $content);
        $this->assertArrayHasKey('sales_price', $content);
        $this->assertArrayHasKey('tax_rate', $content);

        $this->assertArrayHasKey('page', $content);
        $this->assertArrayHasKey('page_cd', $content['page']);
        $this->assertArrayHasKey('page_title', $content['page']);
        $this->assertArrayHasKey('sales_price', $content['page']);
        $this->assertArrayHasKey('tax_rate', $content['page']);
        $this->assertArrayHasKey('page_desc', $content['page']);
        $this->assertArrayHasKey('image_path', $content['page']);
        $this->assertArrayHasKey('remarks1', $content['page']);
        $this->assertArrayHasKey('remarks2', $content['page']);
        $this->assertArrayHasKey('remarks3', $content['page']);
        $this->assertArrayHasKey('remarks4', $content['page']);
        $this->assertArrayHasKey('remarks5', $content['page']);

        $this->assertArrayHasKey('page_sku', $content['page']);
        $this->assertArrayHasKey('m_ami_sku_id', $content['page']['page_sku'][0]);
        $this->assertArrayHasKey('sku_cd', $content['page']['page_sku'][0]);
        $this->assertArrayHasKey('sku_name', $content['page']['page_sku'][0]);
        $this->assertArrayHasKey('jan_cd', $content['page']['page_sku'][0]);
        $this->assertArrayHasKey('including_package_flg', $content['page']['page_sku'][0]);
        $this->assertArrayHasKey('direct_delivery_flg', $content['page']['page_sku'][0]);
        $this->assertArrayHasKey('three_temperature_zone_type', $content['page']['page_sku'][0]);
        $this->assertArrayHasKey('gift_flg', $content['page']['page_sku'][0]);
        $this->assertArrayHasKey('search_result_display_flg', $content['page']['page_sku'][0]);
        $this->assertArrayHasKey('stock_cooperation_status', $content['page']['page_sku'][0]);
        $this->assertArrayHasKey('warehouse_cooperation_status', $content['page']['page_sku'][0]);
        $this->assertArrayHasKey('m_suppliers_id', $content['page']['page_sku'][0]);
        $this->assertArrayHasKey('sales_price', $content['page']['page_sku'][0]);
        $this->assertArrayHasKey('item_price', $content['page']['page_sku'][0]);
        $this->assertArrayHasKey('item_cost', $content['page']['page_sku'][0]);
        $this->assertArrayHasKey('remarks1', $content['page']['page_sku'][0]);
        $this->assertArrayHasKey('remarks2', $content['page']['page_sku'][0]);
        $this->assertArrayHasKey('remarks3', $content['page']['page_sku'][0]);
        $this->assertArrayHasKey('remarks4', $content['page']['page_sku'][0]);
        $this->assertArrayHasKey('remarks5', $content['page']['page_sku'][0]);
    }

    public function test_order_ami_page_info_ec_page_cd_error()
    {
        // amiEcPage 最新の1件を取得
        $amiEcPage = \App\Models\Ami\Base\AmiEcPageModel::latest()->first();

        // Arrange
        $this->withSession(['OperatorInfo' => $this->operatorInfo()]);
        $csrfToken = csrf_token();

        // GET リクエスト
        $response = $this->withHeaders([
            'X-Requested-With' => 'XMLHttpRequest',
            'Authorization' => $csrfToken,
            ])->get("order/api/ami_page/search?m_ecs_id=99999999&ec_page_cd={$amiEcPage->ec_page_cd}", []);

        // 内容を取得
        $contentJson = $response->getContent();
        
        // Assert
        $response->assertStatus(404);
        
        // GET リクエスト
        $response = $this->withHeaders([
            'X-Requested-With' => 'XMLHttpRequest',
            'Authorization' => $csrfToken,
            ])->get("order/api/ami_page/search?m_ecs_id={$amiEcPage->m_ecs_id}&ec_page_cd=99999999", []);

        // 内容を取得
        $contentJson = $response->getContent();
        
        // Assert
        $response->assertStatus(404);
    }

    #[TestDox('付属品カテゴリ一覧API')]
    public function test_order_ami_attachment_item_category_list_api()
    {
        // 付属品カテゴリを追加
        $itemnameType1 = \App\Models\Master\Base\ItemnameTypeModel::factory([
            'm_account_id' => $this->mAccount->m_account_id,
            'm_itemname_type' => \App\Enums\ItemNameType::AttachmentCategory->value,
            'm_itemname_type_name' => '付属品カテゴリ1',
        ])->create();
        $itemnameType2 = \App\Models\Master\Base\ItemnameTypeModel::factory([
            'm_account_id' => $this->mAccount->m_account_id,
            'm_itemname_type' => \App\Enums\ItemNameType::AttachmentCategory->value,
            'm_itemname_type_name' => '付属品カテゴリ2',
        ])->create();

        // Arrange
        $this->withSession(['OperatorInfo' => $this->operatorInfo()]);
        $csrfToken = csrf_token();

        // GET リクエスト
        $response = $this->withHeaders([
            'X-Requested-With' => 'XMLHttpRequest',
            'Authorization' => $csrfToken,
        ])->get("order/api/attachment_item/category/list", []);

        // 内容を取得
        $contentJson = $response->getContent();
        
        // Assert
        $response->assertStatus(200);

        // content が json 形式であること
        $this->assertJson($contentJson);

        $content = json_decode($contentJson, true);

        // 取得したデータが配列であることを確認
        $this->assertIsArray($content);

        // すべてのキーが数値であることを確認
        foreach ($content as $key => $value) {
            $this->assertIsInt($key, "Key is not an integer: $key");
        }

        // "付属品カテゴリ1" と "付属品カテゴリ2" が含まれていることを確認
        $this->assertContains("付属品カテゴリ1", $content);
        $this->assertContains("付属品カテゴリ2", $content);
    }


    #[TestDox('付属品詳細API')]
    public function test_order_ami_attachment_item_info_api()
    {
        // 付属品カテゴリを追加
        $itemnameType = \App\Models\Master\Base\ItemnameTypeModel::factory([
            'm_account_id' => $this->mAccount->m_account_id,
            'm_itemname_type' => \App\Enums\ItemNameType::AttachmentCategory->value,
            'm_itemname_type_name' => '付属品カテゴリ5',
        ])->create();
        
        // 付属品を追加
        $attachment_item = \App\Models\Ami\Base\AmiAttachmentItemModel::factory([
            'category_id' => $itemnameType->m_itemname_types_id,
        ])->create();

        // Arrange
        $this->withSession(['OperatorInfo' => $this->operatorInfo()]);
        $csrfToken = csrf_token();

        // GET リクエスト
        $response = $this->withHeaders([
            'X-Requested-With' => 'XMLHttpRequest',
            'Authorization' => $csrfToken,
        ])->get("order/api/attachment_item/{$attachment_item->m_ami_attachment_item_id}", []);

        // 内容を取得
        $contentJson = $response->getContent();
        
        // Assert
        $response->assertStatus(200);

        // content が json 形式であること
        $this->assertJson($contentJson);

        $content = json_decode($contentJson, true);

        // 取得したデータが配列であることを確認
        $this->assertIsArray($content);
        
        $this->assertArrayHasKey('m_ami_attachment_item_id', $content);
        $this->assertArrayHasKey('attachment_item_cd', $content);
        $this->assertArrayHasKey('attachment_item_name', $content);
        $this->assertArrayHasKey('category_id', $content);
        $this->assertArrayHasKey('display_flg', $content);
        $this->assertArrayHasKey('invoice_flg', $content);
        $this->assertArrayHasKey('reserve1', $content);
        $this->assertArrayHasKey('reserve2', $content);
        $this->assertArrayHasKey('reserve3', $content);
    }

    public function test_order_ami_attachment_item_info_api_error()
    {
        // Arrange
        $this->withSession(['OperatorInfo' => $this->operatorInfo()]);
        $csrfToken = csrf_token();

        // GET リクエスト
        $response = $this->withHeaders([
            'X-Requested-With' => 'XMLHttpRequest',
            'Authorization' => $csrfToken,
        ])->get("order/api/attachment_item/99999999", []);

        // 内容を取得
        $contentJson = $response->getContent();
        
        // Assert
        $response->assertStatus(404);
    }

    #[TestDox('付属品コードによる付属品詳細API')]
    public function test_order_ami_attachment_item_search_api()
    {
        // 付属品カテゴリを追加
        $itemnameType = \App\Models\Master\Base\ItemnameTypeModel::factory([
            'm_account_id' => $this->mAccount->m_account_id,
            'm_itemname_type' => \App\Enums\ItemNameType::AttachmentCategory->value,
            'm_itemname_type_name' => '付属品カテゴリ5',
        ])->create();
        
        // 付属品を追加
        $attachment_item = \App\Models\Ami\Base\AmiAttachmentItemModel::factory([
            'category_id' => $itemnameType->m_itemname_types_id,
        ])->create();

        // Arrange
        $this->withSession(['OperatorInfo' => $this->operatorInfo()]);
        $csrfToken = csrf_token();

        // GET リクエスト
        $response = $this->withHeaders([
            'X-Requested-With' => 'XMLHttpRequest',
            'Authorization' => $csrfToken,
        ])->get("order/api/attachment_item/search?item_cd={$attachment_item->attachment_item_cd}", []);

        // 内容を取得
        $contentJson = $response->getContent();
        
        // Assert
        $response->assertStatus(200);

        // content が json 形式であること
        $this->assertJson($contentJson);

        $content = json_decode($contentJson, true);

        // 取得したデータが配列であることを確認
        $this->assertIsArray($content);
        
        $this->assertArrayHasKey('m_ami_attachment_item_id', $content);
        $this->assertArrayHasKey('attachment_item_cd', $content);
        $this->assertArrayHasKey('attachment_item_name', $content);
        $this->assertArrayHasKey('category_id', $content);
        $this->assertArrayHasKey('display_flg', $content);
        $this->assertArrayHasKey('invoice_flg', $content);
        $this->assertArrayHasKey('reserve1', $content);
        $this->assertArrayHasKey('reserve2', $content);
        $this->assertArrayHasKey('reserve3', $content);
    }

    public function test_order_ami_attachment_item_search_api_error()
    {
        // Arrange
        $this->withSession(['OperatorInfo' => $this->operatorInfo()]);
        $csrfToken = csrf_token();

        // GET リクエスト
        $response = $this->withHeaders([
            'X-Requested-With' => 'XMLHttpRequest',
            'Authorization' => $csrfToken,
            ])->get("order/api/attachment_item/search?item_cd=99999999999", []);

        // 内容を取得
        $contentJson = $response->getContent();
        
        // Assert
        $response->assertStatus(404);
    }

    #[TestDox('熨斗種類一覧API')]
    public function test_order_noshi_format_list_api()
    {
        // 熨斗データシーディング
        $amiPage = $this->seedNoshiData();
        
        // Arrange
        $this->withSession(['OperatorInfo' => $this->operatorInfo()]);
        $csrfToken = csrf_token();

        // GET リクエスト
        $response = $this->withHeaders([
            'X-Requested-With' => 'XMLHttpRequest',
            'Authorization' => $csrfToken,
        ])->get("order/api/noshi-format/list/{$amiPage->m_ami_page_id}", []);

        // 内容を取得
        $contentJson = $response->getContent();
        
        // Assert
        $response->assertStatus(200);

        // content が json 形式であること
        $this->assertJson($contentJson);

        $content = json_decode($contentJson, true);

        // 取得したデータが配列であることを確認
        $this->assertIsArray($content);

        // 必要な情報が含まれていること
        foreach ($content as $j => $noshiTypeList) {
            foreach ($noshiTypeList as $k => $value) {
                $this->assertArrayHasKey('m_ami_page_noshi_id', $value);
                $this->assertArrayHasKey('m_noshi_format_id', $value);
                $this->assertArrayHasKey('noshi_format_name', $value);
                $this->assertArrayHasKey('noshi_type', $value);
                $this->assertArrayHasKey('attachment_item_group_id', $value);
                $this->assertArrayHasKey('omotegaki', $value);
            }
        }
    }

    public function test_order_noshi_format_list_api_error()
    {
        // Arrange
        $this->withSession(['OperatorInfo' => $this->operatorInfo()]);
        $csrfToken = csrf_token();

        // GET リクエスト
        $response = $this->withHeaders([
            'X-Requested-With' => 'XMLHttpRequest',
            'Authorization' => $csrfToken,
        ])->get("order/api/noshi-format/list/99999999", []);

        // 内容を取得
        $contentJson = $response->getContent();
        
        // Assert
        $response->assertStatus(200);

        // $contentJson の中身は空であること
        $this->assertEmpty(json_decode($contentJson));
    }

    #[TestDox('熨斗種類詳細API')]
    public function test_order_noshi_format_info_api()
    {
        // 熨斗データシーディング
        $amiPage = $this->seedNoshiData();

        // 最新の熨斗フォーマットを取得
        $noshiFormat = \App\Models\Master\Base\NoshiFormatModel::latest()->first();
        
        // Arrange
        $this->withSession(['OperatorInfo' => $this->operatorInfo()]);
        $csrfToken = csrf_token();

        // GET リクエスト
        $response = $this->withHeaders([
            'X-Requested-With' => 'XMLHttpRequest',
            'Authorization' => $csrfToken,
        ])->get("order/api/noshi-format/info/{$noshiFormat->m_noshi_format_id}", []);

        // 内容を取得
        $contentJson = $response->getContent();
        
        // Assert
        $response->assertStatus(200);

        // content が json 形式であること
        $this->assertJson($contentJson);

        $content = json_decode($contentJson, true);

        // 取得したデータが配列であることを確認
        $this->assertIsArray($content);

        // 必要な情報が含まれていること
        $this->assertArrayHasKey('m_noshi_format_id', $content);
        $this->assertArrayHasKey('m_noshi_id', $content);
        $this->assertArrayHasKey('noshi_format_name', $content);
    }

    public function test_order_noshi_format_info_api_error()
    {
        // Arrange
        $this->withSession(['OperatorInfo' => $this->operatorInfo()]);
        $csrfToken = csrf_token();

        // GET リクエスト
        $response = $this->withHeaders([
            'X-Requested-With' => 'XMLHttpRequest',
            'Authorization' => $csrfToken,
            ])->get("order/api/noshi-format/info/99999999", []);

        // 内容を取得
        $contentJson = $response->getContent();
        
        // Assert
        $response->assertStatus(404);
    }

    #[TestDox('名入れパターン一覧API')]
    public function test_order_noshi_naming_pattern_list_api()
    {
        // 熨斗データシーディング
        $amiPage = $this->seedNoshiData();

        // 最新の熨斗フォーマットを取得
        $noshiFormat = \App\Models\Master\Base\NoshiFormatModel::latest()->first();
        
        // Arrange
        $this->withSession(['OperatorInfo' => $this->operatorInfo()]);
        $csrfToken = csrf_token();

        // GET リクエスト
        $response = $this->withHeaders([
            'X-Requested-With' => 'XMLHttpRequest',
            'Authorization' => $csrfToken,
        ])->get("order/api/noshi-naming-pattern/list/{$noshiFormat->m_noshi_format_id}", []);

        // 内容を取得
        $contentJson = $response->getContent();
        
        // Assert
        $response->assertStatus(200);

        // content が json 形式であること
        $this->assertJson($contentJson);

        $content = json_decode($contentJson, true);

        // 取得したデータが配列であることを確認
        $this->assertIsArray($content);

        // 必要な情報が含まれていること
        foreach ($content as $key => $value) {
            $this->assertArrayHasKey('m_noshi_detail_id', $value);
            $this->assertArrayHasKey('m_noshi_id', $value);
            $this->assertArrayHasKey('m_noshi_format_id', $value);
            $this->assertArrayHasKey('m_noshi_naming_pattern_id', $value);
            $this->assertArrayHasKey('template_file_name', $value);
            $this->assertArrayHasKey('noshi_type', $value);
            $this->assertArrayHasKey('attachment_item_group_id', $value);
            $this->assertArrayHasKey('omotegaki', $value);
            $this->assertArrayHasKey('noshi_cd', $value);
            $this->assertArrayHasKey('pattern_code', $value);
            $this->assertArrayHasKey('company_name_count', $value);
            $this->assertArrayHasKey('section_name_count', $value);
            $this->assertArrayHasKey('title_count', $value);
            $this->assertArrayHasKey('f_name_count', $value);
            $this->assertArrayHasKey('name_count', $value);
            $this->assertArrayHasKey('ruby_count', $value);
        }
    }

    public function test_order_noshi_naming_pattern_list_api_error()
    {
        // Arrange
        $this->withSession(['OperatorInfo' => $this->operatorInfo()]);
        $csrfToken = csrf_token();

        // GET リクエスト
        $response = $this->withHeaders([
            'X-Requested-With' => 'XMLHttpRequest',
            'Authorization' => $csrfToken,
            ])->get("order/api/noshi-naming-pattern/list/99999999", []);

        // 内容を取得
        $contentJson = $response->getContent();
        
        // Assert
        $response->assertStatus(200);

        // $contentJson の中身は空であること
        $this->assertEmpty(json_decode($contentJson));
    }

    #[TestDox('名入れパターン詳細API')]
    public function test_order_noshi_naming_pattern_info_api()
    {
        // 熨斗データシーディング
        $amiPage = $this->seedNoshiData();

        // 最新の熨斗名入れパターンを取得
        $noshiNamingPattern = \App\Models\Master\Base\NoshiNamingPatternModel::latest()->first();
        
        // Arrange
        $this->withSession(['OperatorInfo' => $this->operatorInfo()]);
        $csrfToken = csrf_token();

        // GET リクエスト
        $response = $this->withHeaders([
            'X-Requested-With' => 'XMLHttpRequest',
            'Authorization' => $csrfToken,
        ])->get("order/api/noshi-naming-pattern/info/{$noshiNamingPattern->m_noshi_naming_pattern_id}", []);

        // 内容を取得
        $contentJson = $response->getContent();
        
        // Assert
        $response->assertStatus(200);

        // content が json 形式であること
        $this->assertJson($contentJson);

        $content = json_decode($contentJson, true);

        // 取得したデータが配列であることを確認
        $this->assertIsArray($content);

        // 必要な情報が含まれていること
        $this->assertArrayHasKey('m_noshi_naming_pattern_id', $content);
        $this->assertArrayHasKey('pattern_code', $content);
        $this->assertArrayHasKey('company_name_count', $content);
        $this->assertArrayHasKey('section_name_count', $content);
        $this->assertArrayHasKey('title_count', $content);
        $this->assertArrayHasKey('f_name_count', $content);
        $this->assertArrayHasKey('name_count', $content);
        $this->assertArrayHasKey('ruby_count', $content);
    }

    public function test_order_noshi_naming_pattern_info_api_error()
    {
        // Arrange
        $this->withSession(['OperatorInfo' => $this->operatorInfo()]);
        $csrfToken = csrf_token();

        // GET リクエスト
        $response = $this->withHeaders([
            'X-Requested-With' => 'XMLHttpRequest',
            'Authorization' => $csrfToken,
            ])->get("order/api/noshi-naming-pattern/info/99999999", []);

        // 内容を取得
        $contentJson = $response->getContent();
        
        // Assert
        $response->assertStatus(404);
    }

    #[TestDox('項目名称取得API')]
    public function test_order_itemname_name_info_api()
    {
        // 付属品カテゴリを追加
        $itemnameType1 = \App\Models\Master\Base\ItemnameTypeModel::factory([
            'm_account_id' => $this->mAccount->m_account_id,
            'm_itemname_type' => \App\Enums\ItemNameType::AttachmentCategory->value,
            'm_itemname_type_name' => '付属品カテゴリ1',
        ])->create();
        $itemnameType2 = \App\Models\Master\Base\ItemnameTypeModel::factory([
            'm_account_id' => $this->mAccount->m_account_id,
            'm_itemname_type' => \App\Enums\ItemNameType::AttachmentCategory->value,
            'm_itemname_type_name' => '付属品カテゴリ2',
        ])->create();
        // 付属品グループを追加
        $itemnameType3 = \App\Models\Master\Base\ItemnameTypeModel::factory([
            'm_account_id' => $this->mAccount->m_account_id,
            'm_itemname_type' => \App\Enums\ItemNameType::AttachmentGroup->value,
            'm_itemname_type_name' => '付属品グループ1',
        ])->create();
        $itemnameType4 = \App\Models\Master\Base\ItemnameTypeModel::factory([
            'm_account_id' => $this->mAccount->m_account_id,
            'm_itemname_type' => \App\Enums\ItemNameType::AttachmentGroup->value,
            'm_itemname_type_name' => '付属品グループ2',
        ])->create();

        // Arrange
        $this->withSession(['OperatorInfo' => $this->operatorInfo()]);
        $csrfToken = csrf_token();

        // GET リクエスト 付属品カテゴリ
        $response = $this->withHeaders([
            'X-Requested-With' => 'XMLHttpRequest',
            'Authorization' => $csrfToken,
        ])->get("order/api/itemname-type/info/12", []);

        // 内容を取得
        $contentJson = $response->getContent();
        
        // Assert
        $response->assertStatus(200);

        // content が json 形式であること
        $this->assertJson($contentJson);

        $content = json_decode($contentJson, true);

        // 取得したデータが配列であることを確認
        $this->assertIsArray($content);

        // すべてのキーが数値であることを確認
        foreach ($content as $key => $value) {
            $this->assertIsInt($key, "Key is not an integer: $key");
            // 必要な情報が含まれていること
            $this->assertArrayHasKey('m_itemname_types_id', $value);
            $this->assertArrayHasKey('m_itemname_type', $value);
            $this->assertArrayHasKey('m_itemname_type_code', $value);
            $this->assertArrayHasKey('m_itemname_type_name', $value);
            $this->assertArrayHasKey('m_itemname_type_sort', $value);
            // m_itemname_type が \App\Enums\ItemNameType::AttachmentCategory->value であること
            $this->assertEquals(\App\Enums\ItemNameType::AttachmentCategory->value, $value['m_itemname_type']);
        }
    }

    public function test_order_itemname_name_info_group_api()
    {
        // 付属品カテゴリを追加
        $itemnameType1 = \App\Models\Master\Base\ItemnameTypeModel::factory([
            'm_account_id' => $this->mAccount->m_account_id,
            'm_itemname_type' => \App\Enums\ItemNameType::AttachmentCategory->value,
            'm_itemname_type_name' => '付属品カテゴリ1',
        ])->create();
        $itemnameType2 = \App\Models\Master\Base\ItemnameTypeModel::factory([
            'm_account_id' => $this->mAccount->m_account_id,
            'm_itemname_type' => \App\Enums\ItemNameType::AttachmentCategory->value,
            'm_itemname_type_name' => '付属品カテゴリ2',
        ])->create();
        // 付属品グループを追加
        $itemnameType3 = \App\Models\Master\Base\ItemnameTypeModel::factory([
            'm_account_id' => $this->mAccount->m_account_id,
            'm_itemname_type' => \App\Enums\ItemNameType::AttachmentGroup->value,
            'm_itemname_type_name' => '付属品グループ1',
        ])->create();
        $itemnameType4 = \App\Models\Master\Base\ItemnameTypeModel::factory([
            'm_account_id' => $this->mAccount->m_account_id,
            'm_itemname_type' => \App\Enums\ItemNameType::AttachmentGroup->value,
            'm_itemname_type_name' => '付属品グループ2',
        ])->create();

        // Arrange
        $this->withSession(['OperatorInfo' => $this->operatorInfo()]);
        $csrfToken = csrf_token();

        // GET リクエスト 付属品カテゴリ
        $response = $this->withHeaders([
            'X-Requested-With' => 'XMLHttpRequest',
            'Authorization' => $csrfToken,
        ])->get("order/api/itemname-type/info/13", []);

        // 内容を取得
        $contentJson = $response->getContent();
        
        // Assert
        $response->assertStatus(200);

        // content が json 形式であること
        $this->assertJson($contentJson);

        $content = json_decode($contentJson, true);

        // 取得したデータが配列であることを確認
        $this->assertIsArray($content);

        // すべてのキーが数値であることを確認
        foreach ($content as $key => $value) {
            $this->assertIsInt($key, "Key is not an integer: $key");
            // 必要な情報が含まれていること
            $this->assertArrayHasKey('m_itemname_types_id', $value);
            $this->assertArrayHasKey('m_itemname_type', $value);
            $this->assertArrayHasKey('m_itemname_type_code', $value);
            $this->assertArrayHasKey('m_itemname_type_name', $value);
            $this->assertArrayHasKey('m_itemname_type_sort', $value);
            // m_itemname_type が \App\Enums\ItemNameType::AttachmentCategory->value であること
            $this->assertEquals(\App\Enums\ItemNameType::AttachmentGroup->value, $value['m_itemname_type']);
        }
    }

    public function test_order_itemname_name_info_api_error()
    {
        // Arrange
        $this->withSession(['OperatorInfo' => $this->operatorInfo()]);
        $csrfToken = csrf_token();

        // GET リクエスト 付属品カテゴリ
        $response = $this->withHeaders([
            'X-Requested-With' => 'XMLHttpRequest',
            'Authorization' => $csrfToken,
        ])->get("order/api/itemname-type/info/1", []);
        
        // Assert
        $response->assertStatus(400);
    }

    protected function seedData()
    {

        //$this->app->detectEnvironment(fn() => 'testing');
        // 支払方法
        $paymentType = \App\Models\Master\Base\PaymentTypeModel::factory([
            'm_account_id' => $this->mAccount->m_account_id,
        ])->create();

        // 配送方法
        $deliveryType = \App\Models\Master\Base\DeliveryTypeModel::factory([
            'm_account_id' => $this->mAccount->m_account_id,
        ])->create();
        // ECサイト
        $ecs = \App\Models\Master\Base\EcsModel::factory([
                'm_account_id' => $this->mAccount->m_account_id,
        ])->create();

        // 顧客
        $customer = \App\Models\Cc\Base\CustModel::factory([
            'm_account_id' => $this->mAccount->m_account_id,
        ])->create();

        // SKU
        $amiSku = \App\Models\Ami\Base\AmiSkuModel::factory([
            'm_account_id' => $this->mAccount->m_account_id,
        ])->create();

        // 商品
        $amiPage = \App\Models\Ami\Base\AmiPageModel::factory([
            'm_account_id' => $this->mAccount->m_account_id,
        ])->create();

        // 商品SKU
        $amiPageSku = \App\Models\Ami\Base\AmiPageSkuModel::factory([
            'm_account_id' => $this->mAccount->m_account_id,
            'm_ami_page_id' => $amiPage->m_ami_page_id,
            'm_ami_sku_id' => $amiSku->m_ami_sku_id,
        ])->create();

        // EC商品
        $amiEcPage = \App\Models\Ami\Base\AmiEcPageModel::factory([
            'm_account_id' => $this->mAccount->m_account_id,
            'm_ecs_id' => $ecs->m_ecs_id,
            'm_ami_page_id' => $amiPage->m_ami_page_id,
        ])->create();

        // EC商品SKU
        $amiEcPageSku = \App\Models\Ami\Base\AmiEcPageSkuModel::factory([
            'm_account_id' => $this->mAccount->m_account_id,
            'm_ami_ec_page_id' => $amiEcPage->m_ami_ec_page_id,
            'm_ami_sku_id' => $amiSku->m_ami_sku_id,
        ])->create();

        // 受注
        $order = \App\Models\Order\Gfh1207\OrderHdrModel::factory([
            'm_account_id' => $this->mAccount->m_account_id,
            'order_operator_id' => $this->preparedOperator->m_operator_id,
            'm_ecs_id' => $ecs->m_ecs_id,
            'm_cust_id' => $customer->m_cust_id,
            'order_tel1' => $customer->tel1,
            'order_postal' => $customer->postal,
            'order_address1' => $customer->address1,
            'order_address2' => $customer->address2,
            'order_address3' => $customer->address3,
            'order_name' => $customer->name_kanji,
            'm_payment_types_id' => $paymentType->m_payment_type_id,
        ])->create();

        // 受注送付先
        $orderDest = \App\Models\Order\Gfh1207\OrderDestinationModel::factory([
            'm_account_id' => $this->mAccount->m_account_id,
            't_order_hdr_id' => $order->t_order_hdr_id,
            'destination_tel' => $customer->tel1,
            'destination_postal' => $customer->postal,
            'destination_address1' => $customer->address1,
            'destination_address2' => $customer->address2,
            'destination_address3' => $customer->address3,
            'destination_name' => $customer->name_kanji,
        ])->create();

        // 受注明細
        $orderDetail = \App\Models\Order\Gfh1207\OrderDetailModel::factory([
            'm_account_id' => $this->mAccount->m_account_id,
            't_order_hdr_id' => $order->t_order_hdr_id,
            't_order_destination_id' => $orderDest->t_order_destination_id,
            'ecs_id' => $ecs->m_ecs_id,
            'sell_id' => $amiEcPage->m_ami_ec_page_id,
        ])->create();

        // 受注明細SKU
        $orderDetailSku = \App\Models\Order\Gfh1207\OrderDetailSkuModel::factory([
            'm_account_id' => $this->mAccount->m_account_id,
            't_order_hdr_id' => $order->t_order_hdr_id,
            't_order_destination_id' => $orderDest->t_order_destination_id,
            't_order_dtl_id' => $orderDetail->t_order_detail_id,
            'sell_cd' => $amiSku->sku_cd,
            'item_id' => $amiSku->m_ami_sku_id,
        ])->create();
    }
    
    
    protected function seedNoshiData()
    {
        // 付属品グループマスタの付属品グループ
        $attachmentGroupNormal = \App\Models\Master\Base\ItemnameTypeModel::factory([
            'm_account_id' => $this->mAccount->m_account_id,
            'm_itemname_type' => \App\Enums\ItemNameType::AttachmentGroup->value,
            'm_itemname_type_name' => '通常',
        ])->create();
        $attachmentGroupButsu = \App\Models\Master\Base\ItemnameTypeModel::factory([
            'm_account_id' => $this->mAccount->m_account_id,
            'm_itemname_type' => \App\Enums\ItemNameType::AttachmentGroup->value,
            'm_itemname_type_name' => '仏',
        ])->create();
        // m_noshi 熨斗マスタ
        $noshiModelNormal = \App\Models\Master\Base\NoshiModel::factory([
            'm_account_id' => $this->mAccount->m_account_id,
            'attachment_item_group_id' => $attachmentGroupNormal->m_itemname_types_id,
            'noshi_type' => '通常熨斗',
        ])->create();
        $noshiModelButsu = \App\Models\Master\Base\NoshiModel::factory([
            'm_account_id' => $this->mAccount->m_account_id,
            'attachment_item_group_id' => $attachmentGroupButsu->m_itemname_types_id,
            'noshi_type' => '仏熨斗',
        ])->create();
        // m_noshi_format 熨斗種類
        $noshiFormatNormal = \App\Models\Master\Base\NoshiFormatModel::factory([
            'm_account_id' => $this->mAccount->m_account_id,
            'm_noshi_id' => $noshiModelNormal->m_noshi_id,
            'noshi_format_name' => '通常熨斗',
        ])->create();
        $noshiFormatButsu = \App\Models\Master\Base\NoshiFormatModel::factory([
            'm_account_id' => $this->mAccount->m_account_id,
            'm_noshi_id' => $noshiModelButsu->m_noshi_id,
            'noshi_format_name' => '仏熨斗',
        ])->create();
        // m_ami_page_noshi ページ熨斗マスタ
        $amiPage = \App\Models\Ami\Base\AmiPageModel::latest()->first();
        $amiPageNoshiNormal = \App\Models\Ami\Base\AmiPageNoshiModel::factory([
            'm_account_id' => $this->mAccount->m_account_id,
            'm_ami_page_id' => $amiPage->m_ami_page_id,
            'm_noshi_id' => $noshiModelNormal->m_noshi_id,
            'm_noshi_format_id' => $noshiFormatNormal->m_noshi_format_id,
        ])->create();
        $amiPageNoshiButsu = \App\Models\Ami\Base\AmiPageNoshiModel::factory([
            'm_account_id' => $this->mAccount->m_account_id,
            'm_ami_page_id' => $amiPage->m_ami_page_id,
            'm_noshi_id' => $noshiModelButsu->m_noshi_id,
            'm_noshi_format_id' => $noshiFormatButsu->m_noshi_format_id,
        ])->create();
        // m_noshi_naming_pattern 熨斗名入れパターン
        $noshiNamingPatternNormal = \App\Models\Master\Base\NoshiNamingPatternModel::factory([
            'm_account_id' => $this->mAccount->m_account_id,
            'pattern_name' => '通常熨斗名入れパターン',
            'pattern_code' => 'NORMAL_PATTERN_001',
        ])->create();
        $noshiNamingPatternButsu = \App\Models\Master\Base\NoshiNamingPatternModel::factory([
            'm_account_id' => $this->mAccount->m_account_id,
            'pattern_name' => '仏熨斗名入れパターン',
            'pattern_code' => 'BUTSU_PATTERN_001',
        ])->create();
        // m_noshi_detail 熨斗詳細
        $noshiDetailNormal = \App\Models\Master\Base\NoshiDetailModel::factory([
            'm_account_id' => $this->mAccount->m_account_id,
            'm_noshi_id' => $noshiModelNormal->m_noshi_id,
            'm_noshi_format_id' => $noshiFormatNormal->m_noshi_format_id,
            'm_noshi_naming_pattern_id' => $noshiNamingPatternNormal->m_noshi_naming_pattern_id,
        ])->create();
        $noshiDetailButsu = \App\Models\Master\Base\NoshiDetailModel::factory([
            'm_account_id' => $this->mAccount->m_account_id,
            'm_noshi_id' => $noshiModelButsu->m_noshi_id,
            'm_noshi_format_id' => $noshiFormatButsu->m_noshi_format_id,
            'm_noshi_naming_pattern_id' => $noshiNamingPatternButsu->m_noshi_naming_pattern_id,
        ])->create();

        return $amiPage;
    }

    protected function operatorInfo()
    {
        return json_decode('{
            "m_account_id": 1,
            "account_cd": "gfh_1207",
            "account_name": "株式会社スクロール360",
            "syscom_use_version": "v1_0",
            "master_use_version": "v1_0",
            "warehouse_use_version": "v1_0",
            "common_use_version": "v1_0",
            "stock_use_version": "v1_0",
            "order_use_version": "v1_0",
            "cc_use_version": "v1_0",
            "claim_use_version": "v1_0",
            "ami_use_version": "v1_0",
            "goto_use_version": "v1_0",
            "m_operators_id": 1,
            "m_operator_name": "システム管理者",
            "user_type": "99",
            "password_update_timestamp": "2023-04-01 00:00:00.000000",
            "operation_authority_detail": [
                {
                    "menu_type": "10",
                    "available_flg": "1"
                },
                {
                    "menu_type": "20",
                    "available_flg": "1"
                },
                {
                    "menu_type": "30",
                    "available_flg": "1"
                },
                {
                    "menu_type": "40",
                    "available_flg": "1"
                },
                {
                    "menu_type": "50",
                    "available_flg": "1"
                },
                {
                    "menu_type": "60",
                    "available_flg": "1"
                }
            ],
            "m_operation_authority_id": 1,
            "m_operation_authority_name": "全権限",
            "CommonHeader": {
                "NoticeInfo": [],
                "AlertInfo": []
            }
        }', true);
    }

    protected function operatorInfoNoLogin()
    {
        return null;
    }

}
