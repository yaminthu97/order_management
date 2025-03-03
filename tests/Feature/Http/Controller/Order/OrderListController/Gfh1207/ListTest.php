<?php
namespace Tests\Feature\Http\Controller\Order\OrderListController\Gfh1207;

use App\Models\Master\Base\OperatorModel;
use Illuminate\Support\Facades\Log;
use PHPUnit\Framework\Attributes\DataProviderExternal;
use PHPUnit\Framework\Attributes\TestDox;
use Tests\Feature\Http\Controller\Order\OrderListController\Gfh1207\DataProviders\LoginOperatorInfoDataProvider;
use Tests\Feature\Http\Controller\Order\OrderListController\Gfh1207\DataProviders\SearchDataProvider;
use Tests\TestCases\Gfh1207TestCase;

class ListTest extends Gfh1207TestCase
{
    protected $preparedOperator;
    public function setUp(): void
    {
        parent::setUp();

        // 担当者の作成
        $this->preparedOperator = OperatorModel::factory()->for($this->mAccount, 'account')->create();

    }

    #[TestDox('受注検索画面の表示')]
    #[DataProviderExternal(className:LoginOperatorInfoDataProvider::class, methodName:'provider')]
    public function test_get_request($operatorInfo, $expected)
    {
        // Arrange
        $this->withSession(['OperatorInfo' => $operatorInfo]);

        // Act
        $response = $this->get('order/order/list');

        // Assert
        $response->assertStatus($expected['status']);
    }

    #[TestDox('検索処理')]
    #[DataProviderExternal(className:SearchDataProvider::class, methodName:'provider')]
    public function test_search($search, $expected)
    {
        // Arrange
        // シーディング
        $this->seedData();
        // ログイン状態にする。
        $this->withSession(['OperatorInfo' => $this->operatorInfo()]);
        $this->withHeader('X-Requested-With', 'XMLHttpRequest');

        // Act
        $response = $this->post('order/order/list', $search);

        // Assert
        Log::debug('response', [$response->content()]);
        $response->assertJsonStructure($expected);
    }


    

    protected function seedData()
    {
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
        $order = \App\Models\Order\Gfh1207\OrderModel::factory([
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

}
