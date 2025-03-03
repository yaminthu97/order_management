<?php
namespace Tests\Unit\Modules\Order\Gfh1207;

use App\Models\Order\Gfh1207\OrderHdrModel;
use App\Modules\Order\Gfh1207\Search;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use PHPUnit\Framework\Attributes\DataProviderExternal;
use Tests\TestCases\Gfh1207TestCase;
use PHPUnit\Framework\Attributes\TestDox;
use ReflectionClass;
use Tests\Unit\Modules\Order\Gfh1207\DataProviders\DeliveryNameDataprovider;
use Tests\Unit\Modules\Order\Gfh1207\DataProviders\DisplayPeriodDataprovider;
use Tests\Unit\Modules\Order\Gfh1207\DataProviders\MailAddressDataprovider;
use Tests\Unit\Modules\Order\Gfh1207\DataProviders\MultiWarehouseFlgDataprovider;
use Tests\Unit\Modules\Order\Gfh1207\DataProviders\OperatorCommentDataprovider;
use Tests\Unit\Modules\Order\Gfh1207\DataProviders\OrderCommentDataprovider;
use Tests\Unit\Modules\Order\Gfh1207\DataProviders\OrderNameDataprovider;
use Tests\Unit\Modules\Order\Gfh1207\DataProviders\OrderTagDataprovider;
use Tests\Unit\Modules\Order\Gfh1207\DataProviders\ReceiptTypeDataprovider;
use Tests\Unit\Modules\Order\Gfh1207\DataProviders\SellNameDataprovider;
use Tests\Unit\Modules\Order\Gfh1207\DataProviders\SetWhereOrTypeColumnDataprovider;
use Tests\Unit\Modules\Order\Gfh1207\DataProviders\TelFaxDataprovider;

class SearchTest extends Gfh1207TestCase
{
    public function setUp(): void
    {
        parent::setUp();

    }

    #[TestDox("query に進捗区分の検索条件が追加されていること")]
    public function test_query_added_progress_type()
    {
        // Arrange
        $reflection = new ReflectionClass(Search::class);
        $method = $reflection->getMethod('setCondition');
        $method->setAccessible(true);
        $instance = new Search();
        $query = OrderHdrModel::query();
        $conditions = [
            'progress_type' => '1,2'
        ];
        $expected = "`progress_type` in ('1', '2')";

        // Act
        $query = $method->invokeArgs($instance, [$query, $conditions]);
        $result = $query->toRawSql();

        // Assert
        $this->assertStringContainsString($expected, $result);
    }

    #[TestDox("queryに進捗区分自動手動の検索条件が追加されていること")]
    #[DataProviderExternal(className: SetWhereOrTypeColumnDataprovider::class, methodName: "progressTypeAutoSelfDataprovider")]
    public function test_query_added_progress_type_auto_self($value, $expected)
    {
        // Arrange
        $reflection = new ReflectionClass(Search::class);
        $method = $reflection->getMethod('setCondition');
        $method->setAccessible(true);
        $instance = new Search();
        $query = OrderHdrModel::query();
        $conditions = [
            'progress_type_auto_self' => $value
        ];

        // Act
        $query = $method->invokeArgs($instance, [$query, $conditions]);
        $result = $query->toRawSql();

        // Assert
        $this->assertStringContainsString($expected, $result);
    }

    #[TestDox("queryに進捗区分変更日時fromの検索条件が追加されていること")]
    public function test_query_added_progress_update_datetime_from()
    {
        // Arrange
        $reflection = new ReflectionClass(Search::class);
        $method = $reflection->getMethod('setCondition');
        $method->setAccessible(true);
        $instance = new Search();
        $query = OrderHdrModel::query();
        $conditions = [
            'progress_update_datetime_from' => "2024-01-01 00:00:00"
        ];
        $expected = "`progress_update_datetime` >= '2024-01-01 00:00:00'";

        // Act
        $query = $method->invokeArgs($instance, [$query, $conditions]);
        $result = $query->toRawSql();

        // Assert
        $this->assertStringContainsString($expected, $result);
    }

    #[TestDox("queryに進捗区分変更日時toの検索条件が追加されていること")]
    public function test_query_added_progress_update_datetime_to()
    {
        // Arrange
        $reflection = new ReflectionClass(Search::class);
        $method = $reflection->getMethod('setCondition');
        $method->setAccessible(true);
        $instance = new Search();
        $query = OrderHdrModel::query();
        $conditions = [
            'progress_update_datetime_to' => "2024-01-01 00:00:00"
        ];
        $expected = "`progress_update_datetime` <= '2024-01-01 00:00:00'";

        // Act
        $query = $method->invokeArgs($instance, [$query, $conditions]);
        $result = $query->toRawSql();

        // Assert
        $this->assertStringContainsString($expected, $result);
    }

    #[TestDox("queryに要注意顧客の検索条件が追加されていること")]
    public function test_query_added_alert_cust_check_type()
    {
        // Arrange
        $reflection = new ReflectionClass(Search::class);
        $method = $reflection->getMethod('setCondition');
        $method->setAccessible(true);
        $instance = new Search();
        $query = OrderHdrModel::query();
        $conditions = [
            'alert_cust_check_type' => '1,2'
        ];
        $expected = "`alert_cust_check_type` in ('1', '2')";

        // Act
        $query = $method->invokeArgs($instance, [$query, $conditions]);
        $result = $query->toRawSql();

        // Assert
        $this->assertStringContainsString($expected, $result);
    }

    #[TestDox("queryに住所エラーの検索条件が追加されていること")]
    public function test_query_added_address_check_type()
    {
        // Arrange
        $reflection = new ReflectionClass(Search::class);
        $method = $reflection->getMethod('setCondition');
        $method->setAccessible(true);
        $instance = new Search();
        $query = OrderHdrModel::query();
        $conditions = [
            'address_check_type' => '1,2'
        ];
        $expected = "`address_check_type` in ('1', '2')";

        // Act
        $query = $method->invokeArgs($instance, [$query, $conditions]);
        $result = $query->toRawSql();

        // Assert
        $this->assertStringContainsString($expected, $result);
    }

    #[TestDox("queryに配達指定日エラーの検索条件が追加されていること")]
    public function test_query_added_deli_hope_date_check_type()
    {
        // Arrange
        $reflection = new ReflectionClass(Search::class);
        $method = $reflection->getMethod('setCondition');
        $method->setAccessible(true);
        $instance = new Search();
        $query = OrderHdrModel::query();
        $conditions = [
            'deli_hope_date_check_type' => '1,2'
        ];
        $expected = "`deli_hope_date_check_type` in ('1', '2')";

        // Act
        $query = $method->invokeArgs($instance, [$query, $conditions]);
        $result = $query->toRawSql();

        // Assert
        $this->assertStringContainsString($expected, $result);
    }

    #[TestDox("queryに与信区分の検索条件が追加されていること")]
    public function test_query_added_credit_type()
    {
        // Arrange
        $reflection = new ReflectionClass(Search::class);
        $method = $reflection->getMethod('setCondition');
        $method->setAccessible(true);
        $instance = new Search();
        $query = OrderHdrModel::query();
        $conditions = [
            'credit_type' => '1,2'
        ];
        $expected = "`credit_type` in ('1', '2')";

        // Act
        $query = $method->invokeArgs($instance, [$query, $conditions]);
        $result = $query->toRawSql();

        // Assert
        $this->assertStringContainsString($expected, $result);
    }


    #[TestDox("queryに引当区分の検索条件が追加されていること")]
    public function test_query_added_reservation_type()
    {
        // Arrange
        $reflection = new ReflectionClass(Search::class);
        $method = $reflection->getMethod('setCondition');
        $method->setAccessible(true);
        $instance = new Search();
        $query = OrderHdrModel::query();
        $conditions = [
            'reservation_type' => '1,2'
        ];
        $expected = "`reservation_type` in ('1', '2')";

        // Act
        $query = $method->invokeArgs($instance, [$query, $conditions]);
        $result = $query->toRawSql();

        // Assert
        $this->assertStringContainsString($expected, $result);
    }


    #[TestDox("queryに入金区分の検索条件が追加されていること")]
    public function test_query_added_payment_type()
    {
        // Arrange
        $reflection = new ReflectionClass(Search::class);
        $method = $reflection->getMethod('setCondition');
        $method->setAccessible(true);
        $instance = new Search();
        $query = OrderHdrModel::query();
        $conditions = [
            'payment_type' => '1,2'
        ];
        $expected = "`payment_type` in ('1', '2')";

        // Act
        $query = $method->invokeArgs($instance, [$query, $conditions]);
        $result = $query->toRawSql();

        // Assert
        $this->assertStringContainsString($expected, $result);
    }

    #[TestDox("queryに出荷指示区分の検索条件が追加されていること")]
    public function test_query_added_deli_instruct_type()
    {
        // Arrange
        $reflection = new ReflectionClass(Search::class);
        $method = $reflection->getMethod('setCondition');
        $method->setAccessible(true);
        $instance = new Search();
        $query = OrderHdrModel::query();
        $conditions = [
            'deli_instruct_type' => '1,2'
        ];
        $expected = "`deli_instruct_type` in ('1', '2')";

        // Act
        $query = $method->invokeArgs($instance, [$query, $conditions]);
        $result = $query->toRawSql();

        // Assert
        $this->assertStringContainsString($expected, $result);
    }

    #[TestDox("queryに出荷確定区分の検索条件が追加されていること")]
    public function test_query_added_deli_decision_type()
    {
        // Arrange
        $reflection = new ReflectionClass(Search::class);
        $method = $reflection->getMethod('setCondition');
        $method->setAccessible(true);
        $instance = new Search();
        $query = OrderHdrModel::query();
        $conditions = [
            'deli_decision_type' => '1,2'
        ];
        $expected = "`deli_decision_type` in ('1', '2')";

        // Act
        $query = $method->invokeArgs($instance, [$query, $conditions]);
        $result = $query->toRawSql();

        // Assert
        $this->assertStringContainsString($expected, $result);
    }

    #[TestDox("queryに決済ステータスの検索条件が追加されていること")]
    public function test_query_added_payment_status()
    {
        // Arrange
        $reflection = new ReflectionClass(Search::class);
        $method = $reflection->getMethod('setCondition');
        $method->setAccessible(true);
        $instance = new Search();
        $query = OrderHdrModel::query();
        $conditions = [
            'settlement_sales_type' => '1,2'
        ];
        $expected = "`settlement_sales_type` in ('1', '2')";

        // Act
        $query = $method->invokeArgs($instance, [$query, $conditions]);
        $result = $query->toRawSql();

        // Assert
        $this->assertStringContainsString($expected, $result);
    }

    #[TestDox("queryに決済売上計上区分の検索条件が追加されていること")]
    public function test_query_added_disp_settlement_sales_type()
    {
        // Arrange
        $reflection = new ReflectionClass(Search::class);
        $method = $reflection->getMethod('setCondition');
        $method->setAccessible(true);
        $instance = new Search();
        $query = OrderHdrModel::query();
        $conditions = [
            'disp_settlement_sales_type' => '1,2'
        ];
        $expected = "`sales_status_type` in ('1', '2')";

        // Act
        $query = $method->invokeArgs($instance, [$query, $conditions]);
        $result = $query->toRawSql();

        // Assert
        $this->assertStringContainsString($expected, $result);
    }

    #[TestDox("queryに売上ステータス反映区分の検索条件が追加されていること")]
    public function test_query_added_sales_status_type()
    {
        // Arrange
        $reflection = new ReflectionClass(Search::class);
        $method = $reflection->getMethod('setCondition');
        $method->setAccessible(true);
        $instance = new Search();
        $query = OrderHdrModel::query();
        $conditions = [
            'sales_status_type' => '1,2'
        ];
        $expected = "`sales_status_type` in ('1', '2')";

        // Act
        $query = $method->invokeArgs($instance, [$query, $conditions]);
        $result = $query->toRawSql();

        // Assert
        $this->assertStringContainsString($expected, $result);
    }

    #[TestDox("queryに受注日時FROMの検索条件が追加されていること")]
    public function test_query_added_order_datetime_from()
    {
        // Arrange
        $reflection = new ReflectionClass(Search::class);
        $method = $reflection->getMethod('setCondition');
        $method->setAccessible(true);
        $instance = new Search();
        $query = OrderHdrModel::query();
        $conditions = [
            'order_datetime_from' => "2024-01-01 00:00:00"
        ];
        $expected = "`order_datetime` >= '2024-01-01 00:00:00'";

        // Act
        $query = $method->invokeArgs($instance, [$query, $conditions]);
        $result = $query->toRawSql();

        // Assert
        $this->assertStringContainsString($expected, $result);
    }

    #[TestDox("queryに受注日時TOの検索条件が追加されていること")]
    public function test_query_added_order_datetime_to()
    {
        // Arrange
        $reflection = new ReflectionClass(Search::class);
        $method = $reflection->getMethod('setCondition');
        $method->setAccessible(true);
        $instance = new Search();
        $query = OrderHdrModel::query();
        $conditions = [
            'order_datetime_to' => "2024-01-01 00:00:00"
        ];
        $expected = "`order_datetime` <= '2024-01-01 00:00:00'";

        // Act
        $query = $method->invokeArgs($instance, [$query, $conditions]);
        $result = $query->toRawSql();

        // Assert
        $this->assertStringContainsString($expected, $result);
    }

    #[TestDox("queryに表示期間の検索条件が追加されていること(全期間以外)")]
    #[DataProviderExternal(className: DisplayPeriodDataprovider::class, methodName: "provider")]
    public function test_query_added_display_period_type_except_nine($displayPeriod, $expected)
    {
        // Arrange
        // テスト用に固定する日時を指定
        $testNow = Carbon::create(2024, 8, 1, 0, 0, 0);
        Carbon::setTestNow($testNow);
        $reflection = new ReflectionClass(Search::class);
        $method = $reflection->getMethod('setCondition');
        $method->setAccessible(true);
        $instance = new Search();
        $query = OrderHdrModel::query();
        $conditions = [
            'display_period' => $displayPeriod,
        ];

        // Act
        $query = $method->invokeArgs($instance, [$query, $conditions]);
        $result = $query->toRawSql();
        ($result);

        // Assert
        $this->assertStringContainsString($expected, $result);
    }

    #[TestDox("queryに表示期間の検索条件が追加されていること(全期間)")]
    public function test_query_added_display_period_type_nine()
    {
        // Arrange
        // テスト用に固定する日時を指定
        $testNow = Carbon::create(2024, 8, 1, 0, 0, 0);
        Carbon::setTestNow($testNow);
        $reflection = new ReflectionClass(Search::class);
        $method = $reflection->getMethod('setCondition');
        $method->setAccessible(true);
        $instance = new Search();
        $query = OrderHdrModel::query();
        $conditions = [
            'display_period' => '9',
        ];
        $expected = "`order_datetime` <= '2024-08-01 00:00:00'";

        // Act
        $query = $method->invokeArgs($instance, [$query, $conditions]);
        $result = $query->toRawSql();

        // Assert
        // 全期間の場合は表示開始時刻が無視されるため、表示開始時刻の条件が含まれないことを確認
        $this->assertStringNotContainsString($expected, $result);
    }

    #[TestDox("queryに表示開始時刻の検索条件が追加されていること(全期間以外)")]
    #[DataProviderExternal(className: DisplayPeriodDataprovider::class, methodName: "providerWithOrderTimeFrom")]
    public function test_query_added_order_time_from_except_nine($displayPeriod, $orderTimeFrom, $expected)
    {
        // Arrange
        // テスト用に固定する日時を指定
        $testNow = Carbon::create(2024, 8, 1, 0, 0, 0);
        Carbon::setTestNow($testNow);
        $reflection = new ReflectionClass(Search::class);
        $method = $reflection->getMethod('setCondition');
        $method->setAccessible(true);
        $instance = new Search();
        $query = OrderHdrModel::query();
        $conditions = [
            'display_period' => $displayPeriod,
            'order_time_from' => $orderTimeFrom,
        ];
        $expected = $expected;

        // Act
        $query = $method->invokeArgs($instance, [$query, $conditions]);
        $result = $query->toRawSql();

        // Assert
        $this->assertStringContainsString($expected, $result);
    }

    #[TestDox("queryに表示開始時刻の検索条件が追加されていること(全期間)")]
    public function test_query_added_order_time_from_nine()
    {
        // Arrange
        // テスト用に固定する日時を指定
        $testNow = Carbon::create(2024, 8, 1, 0, 0, 0);
        Carbon::setTestNow($testNow);
        $reflection = new ReflectionClass(Search::class);
        $method = $reflection->getMethod('setCondition');
        $method->setAccessible(true);
        $instance = new Search();
        $query = OrderHdrModel::query();
        $conditions = [
            'display_period' => '9',
            'order_time_from' => '12:00:00',
        ];
        $expected = "`order_datetime`";

        // Act
        $query = $method->invokeArgs($instance, [$query, $conditions]);
        $result = $query->toRawSql();

        // Assert
        // 全期間の場合は表示開始時刻が無視されるため、表示開始時刻の条件が含まれないことを確認
        $this->assertStringNotContainsString($expected, $result);
    }

    #[TestDox("queryに支払方法の検索条件が追加されていること")]
    public function test_query_added_payment_method_type()
    {
        // Arrange
        $reflection = new ReflectionClass(Search::class);
        $method = $reflection->getMethod('setCondition');
        $method->setAccessible(true);
        $instance = new Search();
        $query = OrderHdrModel::query();
        $conditions = [
            'm_payment_types_id' => '1,2'
        ];
        $expected = "`m_payment_types_id` in ('1', '2')";

        // Act
        $query = $method->invokeArgs($instance, [$query, $conditions]);
        $result = $query->toRawSql();

        // Assert
        $this->assertStringContainsString($expected, $result);
    }

    #[TestDox("queryに受注IDの検索条件が追加されていること")]
    public function test_query_added_order_id()
    {
        // Arrange
        $reflection = new ReflectionClass(Search::class);
        $method = $reflection->getMethod('setCondition');
        $method->setAccessible(true);
        $instance = new Search();
        $query = OrderHdrModel::query();
        $conditions = [
            't_order_hdr_id' => '1,2'
        ];
        $expected = "`t_order_hdr_id` in ('1', '2')";

        // Act
        $query = $method->invokeArgs($instance, [$query, $conditions]);
        $result = $query->toRawSql();

        // Assert
        $this->assertStringContainsString($expected, $result);
    }

    #[TestDox("queryに即日配送の検索条件が追加されていること")]
    #[DataProviderExternal(className: SetWhereOrTypeColumnDataprovider::class, methodName: "immediatelyDeliFlgDataprovider")]
    public function test_query_added_immediately_deli_flg($value, $expected)
    {
        // Arrange
        $reflection = new ReflectionClass(Search::class);
        $method = $reflection->getMethod('setCondition');
        $method->setAccessible(true);
        $instance = new Search();
        $query = OrderHdrModel::query();
        $conditions = [
            'immediately_deli_flg' => $value
        ];

        // Act
        $query = $method->invokeArgs($instance, [$query, $conditions]);
        $result = $query->toRawSql();

        // Assert
        $this->assertStringContainsString($expected, $result);
    }

    #[TestDox("queryに楽天スーパーDEALの検索条件が追加されていること")]
    #[DataProviderExternal(className: SetWhereOrTypeColumnDataprovider::class, methodName: "rakutenSuperDealFlgDataprovider")]
    public function test_query_added_rakuten_super_deal_flg($value, $expected)
    {
        // Arrange
        $reflection = new ReflectionClass(Search::class);
        $method = $reflection->getMethod('setCondition');
        $method->setAccessible(true);
        $instance = new Search();
        $query = OrderHdrModel::query();
        $conditions = [
            'rakuten_super_deal_flg' => $value
        ];

        // Act
        $query = $method->invokeArgs($instance, [$query, $conditions]);
        $result = $query->toRawSql();

        // Assert
        $this->assertStringContainsString($expected, $result);
    }


    #[TestDox("queryに同梱の検索条件が追加されていること")]
    public function test_query_added_bundle()
    {
        // Arrange
        $reflection = new ReflectionClass(Search::class);
        $method = $reflection->getMethod('setCondition');
        $method->setAccessible(true);
        $instance = new Search();
        $query = OrderHdrModel::query();
        $conditions = [
            'bundle' => '1'
        ];
        $expected = "(`bundle_source_ids` is not null and `bundle_source_ids` <> '')";

        // Act
        $query = $method->invokeArgs($instance, [$query, $conditions]);
        $result = $query->toRawSql();

        // Assert
        $this->assertStringContainsString($expected, $result);
    }

    #[TestDox("queryにECサイトの検索条件が追加されていること")]
    public function test_query_added_m_ecs_id()
    {
        // Arrange
        $reflection = new ReflectionClass(Search::class);
        $method = $reflection->getMethod('setCondition');
        $method->setAccessible(true);
        $instance = new Search();
        $query = OrderHdrModel::query();
        $conditions = [
            'm_ecs_id' => '1,2'
        ];
        $expected = "`m_ecs_id` in ('1', '2')";

        // Act
        $query = $method->invokeArgs($instance, [$query, $conditions]);
        $result = $query->toRawSql();

        // Assert
        $this->assertStringContainsString($expected, $result);
    }

    #[TestDox("queryにECサイト注文IDの検索条件が追加されていること")]
    public function test_query_added_ec_order_num()
    {
        // Arrange
        $reflection = new ReflectionClass(Search::class);
        $method = $reflection->getMethod('setCondition');
        $method->setAccessible(true);
        $instance = new Search();
        $query = OrderHdrModel::query();
        $conditions = [
            'ec_order_num' => '1'
        ];
        $expected = "`ec_order_num` = '1'";

        // Act
        $query = $method->invokeArgs($instance, [$query, $conditions]);
        $result = $query->toRawSql();

        // Assert
        $this->assertStringContainsString($expected, $result);
    }

    #[TestDox("queryにリピート注文の検索条件が追加されていること")]
    public function test_query_added_repeat_order_flg()
    {
        // Arrange
        $reflection = new ReflectionClass(Search::class);
        $method = $reflection->getMethod('setCondition');
        $method->setAccessible(true);
        $instance = new Search();
        $query = OrderHdrModel::query();
        $conditions = [
            'repeat_order' => '1,2'
        ];
        $expected = "`repeat_flg` in ('1', '2')";

        // Act
        $query = $method->invokeArgs($instance, [$query, $conditions]);
        $result = $query->toRawSql();

        // Assert
        $this->assertStringContainsString($expected, $result);
    }

    #[TestDox("queryに受注担当者の検索条件が追加されていること")]
    public function test_query_added_order_operator_id()
    {
        // Arrange
        $reflection = new ReflectionClass(Search::class);
        $method = $reflection->getMethod('setCondition');
        $method->setAccessible(true);
        $instance = new Search();
        $query = OrderHdrModel::query();
        $conditions = [
            'order_operator_id' => '1'
        ];
        $expected = "`order_operator_id` = '1'";

        // Act
        $query = $method->invokeArgs($instance, [$query, $conditions]);
        $result = $query->toRawSql();

        // Assert
        $this->assertStringContainsString($expected, $result);
    }

    #[TestDox("queryに最終更新担当者の検索条件が追加されていること")]
    public function test_query_added_update_operator_id()
    {
        // Arrange
        $reflection = new ReflectionClass(Search::class);
        $method = $reflection->getMethod('setCondition');
        $method->setAccessible(true);
        $instance = new Search();
        $query = OrderHdrModel::query();
        $conditions = [
            'update_operator_id' => '1'
        ];
        $expected = "`update_operator_id` = '1'";

        // Act
        $query = $method->invokeArgs($instance, [$query, $conditions]);
        $result = $query->toRawSql();

        // Assert
        $this->assertStringContainsString($expected, $result);
    }


    #[TestDox("queryに受注方法の検索条件が追加されていること")]
    public function test_query_added_order_type()
    {
        // Arrange
        $reflection = new ReflectionClass(Search::class);
        $method = $reflection->getMethod('setCondition');
        $method->setAccessible(true);
        $instance = new Search();
        $query = OrderHdrModel::query();
        $conditions = [
            'order_type' => '1,2'
        ];
        $expected = "`order_type` in ('1', '2')";

        // Act
        $query = $method->invokeArgs($instance, [$query, $conditions]);
        $result = $query->toRawSql();

        // Assert
        $this->assertStringContainsString($expected, $result);
    }

    #[TestDox("queryにギフトの検索条件が追加されていること")]
    public function test_query_added_gift_flg()
    {
        // Arrange
        $reflection = new ReflectionClass(Search::class);
        $method = $reflection->getMethod('setCondition');
        $method->setAccessible(true);
        $instance = new Search();
        $query = OrderHdrModel::query();
        $conditions = [
            'gift_flg' => '1,2'
        ];
        $expected = "`gift_flg` in ('1', '2')";

        // Act
        $query = $method->invokeArgs($instance, [$query, $conditions]);
        $result = $query->toRawSql();

        // Assert
        $this->assertStringContainsString($expected, $result);
    }

    #[TestDox("queryに警告注文の検索条件が追加されていること")]
    #[DataProviderExternal(className: SetWhereOrTypeColumnDataprovider::class, methodName: "alertOrderFlgDataprovider")]
    public function test_query_added_alert_order_flg($value, $expected)
    {
        // Arrange
        $reflection = new ReflectionClass(Search::class);
        $method = $reflection->getMethod('setCondition');
        $method->setAccessible(true);
        $instance = new Search();
        $query = OrderHdrModel::query();
        $conditions = [
            'alert_order_flg' => $value
        ];

        // Act
        $query = $method->invokeArgs($instance, [$query, $conditions]);
        $result = $query->toRawSql();

        // Assert
        $this->assertStringContainsString($expected, $result);
    }


    #[TestDox("queryに合計金額FROMの検索条件が追加されていること")]
    public function test_query_added_total_price_from()
    {
        // Arrange
        $reflection = new ReflectionClass(Search::class);
        $method = $reflection->getMethod('setCondition');
        $method->setAccessible(true);
        $instance = new Search();
        $query = OrderHdrModel::query();
        $conditions = [
            'total_price_from' => '1000'
        ];
        $expected = "`sell_total_price` >= '1000'";

        // Act
        $query = $method->invokeArgs($instance, [$query, $conditions]);
        $result = $query->toRawSql();

        // Assert
        $this->assertStringContainsString($expected, $result);
    }


    #[TestDox("queryに合計金額TOの検索条件が追加されていること")]
    public function test_query_added_total_price_to()
    {
        // Arrange
        $reflection = new ReflectionClass(Search::class);
        $method = $reflection->getMethod('setCondition');
        $method->setAccessible(true);
        $instance = new Search();
        $query = OrderHdrModel::query();
        $conditions = [
            'total_price_to' => '1000'
        ];
        $expected = "`sell_total_price` <= '1000'";

        // Act
        $query = $method->invokeArgs($instance, [$query, $conditions]);
        $result = $query->toRawSql();

        // Assert
        $this->assertStringContainsString($expected, $result);
    }

    #[TestDox("queryに請求金額FROMの検索条件が追加されていること")]
    public function test_query_added_order_total_price_from()
    {
        // Arrange
        $reflection = new ReflectionClass(Search::class);
        $method = $reflection->getMethod('setCondition');
        $method->setAccessible(true);
        $instance = new Search();
        $query = OrderHdrModel::query();
        $conditions = [
            'order_total_price_from' => '1000'
        ];
        $expected = "`order_total_price` >= '1000'";

        // Act
        $query = $method->invokeArgs($instance, [$query, $conditions]);
        $result = $query->toRawSql();

        // Assert
        $this->assertStringContainsString($expected, $result);
    }

    #[TestDox("queryに請求金額TOの検索条件が追加されていること")]
    public function test_query_added_order_total_price_to()
    {
        // Arrange
        $reflection = new ReflectionClass(Search::class);
        $method = $reflection->getMethod('setCondition');
        $method->setAccessible(true);
        $instance = new Search();
        $query = OrderHdrModel::query();

        $conditions = [
            'order_total_price_to' => '1000'
        ];
        $expected = "`order_total_price` <= '1000'";

        // Act
        $query = $method->invokeArgs($instance, [$query, $conditions]);
        $result = $query->toRawSql();

        // Assert
        $this->assertStringContainsString($expected, $result);
    }

    #[TestDox("queryに送料FROMの検索条件が追加されていること")]
    public function test_query_added_shipping_fee_from()
    {
        // Arrange
        $reflection = new ReflectionClass(Search::class);
        $method = $reflection->getMethod('setCondition');
        $method->setAccessible(true);
        $instance = new Search();

        $query = OrderHdrModel::query();
        $conditions = [
            'shipping_fee_from' => '1000'
        ];
        $expected = "`shipping_fee` >= '1000'";

        // Act
        $query = $method->invokeArgs($instance, [$query, $conditions]);
        $result = $query->toRawSql();

        // Assert
        $this->assertStringContainsString($expected, $result);
    }

    #[TestDox("queryに送料TOの検索条件が追加されていること")]
    public function test_query_added_shipping_fee_to()
    {
        // Arrange
        $reflection = new ReflectionClass(Search::class);
        $method = $reflection->getMethod('setCondition');
        $method->setAccessible(true);

        $instance = new Search();
        $query = OrderHdrModel::query();
        $conditions = [
            'shipping_fee_to' => '1000'
        ];
        $expected = "`shipping_fee` <= '1000'";

        // Act
        $query = $method->invokeArgs($instance, [$query, $conditions]);
        $result = $query->toRawSql();

        // Assert
        $this->assertStringContainsString($expected, $result);
    }

    #[TestDox("queryに備考の有無の検索条件が追加されていること")]
    #[DataProviderExternal(className: OrderCommentDataprovider::class, methodName: "orderCommentFlgProvider")]
    public function test_query_added_order_comment_flg($value, $expected)
    {
        // Arrange
        $reflection = new ReflectionClass(Search::class);
        $method = $reflection->getMethod('setCondition');
        $method->setAccessible(true);

        $instance = new Search();
        $query = OrderHdrModel::query();
        $conditions = [
            'order_comment_flg' => $value
        ];

        // Act
        $query = $method->invokeArgs($instance, [$query, $conditions]);
        $result = $query->toRawSql();

        // Assert
        $this->assertStringContainsString($expected, $result);
    }

    #[TestDox("queryに備考の検索条件が追加されていること")]
    #[DataProviderExternal(className: OrderCommentDataprovider::class, methodName: "orderCommentProvider")]
    public function test_query_added_order_comment($values, $expected)
    {
        // Arrange
        $reflection = new ReflectionClass(Search::class);
        $method = $reflection->getMethod('setCondition');
        $method->setAccessible(true);

        $instance = new Search();
        $query = OrderHdrModel::query();
        $conditions = $values;

        // Act
        $query = $method->invokeArgs($instance, [$query, $conditions]);
        $result = $query->toRawSql();

        // Assert
        $this->assertStringContainsString($expected, $result);
    }


    #[TestDox("queryに社内メモの有無の検索条件が追加されていること")]
    #[DataProviderExternal(className: OperatorCommentDataprovider::class, methodName: "operatorCommentFlgProvider")]
    public function test_query_added_operator_comment_flg($value, $expected)
    {
        // Arrange
        $reflection = new ReflectionClass(Search::class);
        $method = $reflection->getMethod('setCondition');
        $method->setAccessible(true);

        $instance = new Search();
        $query = OrderHdrModel::query();
        $conditions = [
            'operator_comment_flg' => $value
        ];

        // Act
        $query = $method->invokeArgs($instance, [$query, $conditions]);
        $result = $query->toRawSql();

        // Assert
        $this->assertStringContainsString($expected, $result);
    }


    #[TestDox("queryに社内メモの検索条件が追加されていること")]
    #[DataProviderExternal(className: OperatorCommentDataprovider::class, methodName: "operatorCommentProvider")]
    public function test_query_added_operator_comment($values, $expected)
    {
        // Arrange
        $reflection = new ReflectionClass(Search::class);
        $method = $reflection->getMethod('setCondition');
        $method->setAccessible(true);

        $instance = new Search();
        $query = OrderHdrModel::query();
        $conditions = $values;

        // Act
        $query = $method->invokeArgs($instance, [$query, $conditions]);
        $result = $query->toRawSql();

        // Assert
        $this->assertStringContainsString($expected, $result);
    }

    #[TestDox("queryに受注キャンセル日時FROMの検索条件が追加されていること")]
    public function test_query_added_cancel_datetime_from()
    {
        // Arrange
        $reflection = new ReflectionClass(Search::class);
        $method = $reflection->getMethod('setCondition');
        $method->setAccessible(true);

        $instance = new Search();
        $query = OrderHdrModel::query();
        $conditions = [
            'cancel_datetime_from' => '2024-01-01 00:00:00'
        ];
        $expected = "`cancel_timestamp` >= '2024-01-01 00:00:00'";

        // Act
        $query = $method->invokeArgs($instance, [$query, $conditions]);
        $result = $query->toRawSql();

        // Assert
        $this->assertStringContainsString($expected, $result);
    }

    #[TestDox("queryに受注キャンセル日時TOの検索条件が追加されていること")]
    public function test_query_added_cancel_datetime_to()
    {
        // Arrange
        $reflection = new ReflectionClass(Search::class);
        $method = $reflection->getMethod('setCondition');
        $method->setAccessible(true);

        $instance = new Search();
        $query = OrderHdrModel::query();
        $conditions = [
            'cancel_datetime_to' => '2024-01-01 00:00:00'
        ];
        $expected = "`cancel_timestamp` <= '2024-01-01 00:00:00'";

        // Act
        $query = $method->invokeArgs($instance, [$query, $conditions]);
        $result = $query->toRawSql();

        // Assert
        $this->assertStringContainsString($expected, $result);
    }

    #[TestDox("queryに領収証最終出力日時FROMの検索条件が追加されていること")]
    public function test_query_added_receipt_datetime_from()
    {
        // Arrange
        $reflection = new ReflectionClass(Search::class);
        $method = $reflection->getMethod('setCondition');
        $method->setAccessible(true);

        $instance = new Search();
        $query = OrderHdrModel::query();
        $conditions = [
            'receipt_datetime_from' => '2024-01-01 00:00:00'
        ];
        $expected = "`last_receipt_datetime` >= '2024-01-01 00:00:00'";

        // Act
        $query = $method->invokeArgs($instance, [$query, $conditions]);
        $result = $query->toRawSql();

        // Assert
        $this->assertStringContainsString($expected, $result);
    }

    #[TestDox("queryに領収証最終出力日時TOの検索条件が追加されていること")]
    public function test_query_added_receipt_datetime_to()
    {
        // Arrange
        $reflection = new ReflectionClass(Search::class);
        $method = $reflection->getMethod('setCondition');
        $method->setAccessible(true);

        $instance = new Search();
        $query = OrderHdrModel::query();
        $conditions = [
            'receipt_datetime_to' => '2024-01-01 00:00:00'
        ];
        $expected = "`last_receipt_datetime` <= '2024-01-01 00:00:00'";

        // Act
        $query = $method->invokeArgs($instance, [$query, $conditions]);
        $result = $query->toRawSql();

        // Assert
        $this->assertStringContainsString($expected, $result);
    }

    #[TestDox("queryに受注タグ（含む）の検索条件が追加されていること")]
    #[DataProviderExternal(className: OrderTagDataprovider::class, methodName: "testOrderTagIncludeProvider")]
    public function test_query_added_order_tag_include($values, $expected)
    {
        // Arrange
        $reflection = new ReflectionClass(Search::class);
        $method = $reflection->getMethod('setCondition');
        $method->setAccessible(true);

        $instance = new Search();
        $query = OrderHdrModel::query();
        $conditions = $values;

        // Act
        $query = $method->invokeArgs($instance, [$query, $conditions]);
        $result = $query->toRawSql();

        // Assert
        $this->assertStringContainsString($expected, $result);
    }

    #[TestDox("queryに受注タグ（含まない）の検索条件が追加されていること")]
    #[DataProviderExternal(className: OrderTagDataprovider::class, methodName: "testOrderTagExcludeProvider")]
    public function test_query_added_order_tag_exclude($values, $expected)
    {
        // Arrange
        $reflection = new ReflectionClass(Search::class);
        $method = $reflection->getMethod('setCondition');
        $method->setAccessible(true);

        $instance = new Search();
        $query = OrderHdrModel::query();
        $conditions = $values;

        // Act
        $query = $method->invokeArgs($instance, [$query, $conditions]);
        $result = $query->toRawSql();

        // Assert
        $this->assertStringContainsString($expected, $result);
    }

    #[TestDox("queryに電話番号・FAXの検索条件が追加されていること")]
    #[DataProviderExternal(className: TelFaxDataprovider::class, methodName: "provider")]
    public function test_query_added_tel_fax($values, $expected)
    {
        // Arrange
        $reflection = new ReflectionClass(Search::class);
        $method = $reflection->getMethod('setCondition');
        $method->setAccessible(true);

        $instance = new Search();
        $query = OrderHdrModel::query();
        $conditions = $values;

        // Act
        $query = $method->invokeArgs($instance, [$query, $conditions]);
        $result = $query->toRawSql();

        // Assert
        $this->assertStringContainsString($expected, $result);
    }

    #[TestDox("queryに注文者氏名・カナ氏名の検索条件が追加されていること")]
    #[DataProviderExternal(className: OrderNameDataprovider::class, methodName: "provider")]
    public function test_query_added_order_name($values, $expected)
    {
        // Arrange
        $reflection = new ReflectionClass(Search::class);
        $method = $reflection->getMethod('setCondition');
        $method->setAccessible(true);

        $instance = new Search();
        $query = OrderHdrModel::query();
        $conditions = $values;

        // Act
        $query = $method->invokeArgs($instance, [$query, $conditions]);
        $result = $query->toRawSql();

        // Assert
        $this->assertStringContainsString($expected, $result);
    }

    #[TestDox("queryにメールアドレスの検索条件が追加されていること")]
    #[DataProviderExternal(className: MailAddressDataprovider::class, methodName: "provider")]
    public function test_query_added_mail_address($values, $expected)
    {
        // Arrange
        $reflection = new ReflectionClass(Search::class);
        $method = $reflection->getMethod('setCondition');
        $method->setAccessible(true);

        $instance = new Search();
        $query = OrderHdrModel::query();
        $conditions = $values;

        // Act
        $query = $method->invokeArgs($instance, [$query, $conditions]);
        $result = $query->toRawSql();

        // Assert
        $this->assertStringContainsString($expected, $result);
    }

    #[TestDox("queryに顧客IDの検索条件が追加されていること")]
    public function test_query_added_m_cust_id()
    {
        // Arrange
        $reflection = new ReflectionClass(Search::class);
        $method = $reflection->getMethod('setCondition');
        $method->setAccessible(true);

        $instance = new Search();
        $query = OrderHdrModel::query();
        $conditions = [
            'm_cust_id' => '1'
        ];
        $expected = "`m_cust_id` = '1'";

        // Act
        $query = $method->invokeArgs($instance, [$query, $conditions]);
        $result = $query->toRawSql();

        // Assert
        $this->assertStringContainsString($expected, $result);
    }

    #[TestDox("queryに顧客ランクの検索条件が追加されていること")]
    public function test_query_added_m_cust_runk_id()
    {
        // Arrange
        $reflection = new ReflectionClass(Search::class);
        $method = $reflection->getMethod('setCondition');
        $method->setAccessible(true);

        $instance = new Search();
        $query = OrderHdrModel::query();
        $conditions = [
            'm_cust_runk_id' => '1,2'
        ];
        $expected = "`m_cust_runk_id` in ('1', '2')";

        // Act
        $query = $method->invokeArgs($instance, [$query, $conditions]);
        $result = $query->toRawSql();

        // Assert
        $this->assertStringContainsString($expected, $result);
    }

    #[TestDox("queryに顧客コードの検索条件が追加されていること")]
    public function test_query_added_cust_cd()
    {
        // Arrange
        $reflection = new ReflectionClass(Search::class);
        $method = $reflection->getMethod('setCondition');
        $method->setAccessible(true);

        $instance = new Search();
        $query = OrderHdrModel::query();
        $conditions = [
            'cust_cd' => '1'
        ];
        $expected = "`cust_cd` = '1'";

        // Act
        $query = $method->invokeArgs($instance, [$query, $conditions]);
        $result = $query->toRawSql();

        // Assert
        $this->assertStringContainsString($expected, $result);
    }

    #[TestDox("queryに要注意顧客区分の検索条件が追加されていること")]
    public function test_query_added_alert_cust_type()
    {
        // Arrange
        $reflection = new ReflectionClass(Search::class);
        $method = $reflection->getMethod('setCondition');
        $method->setAccessible(true);

        $instance = new Search();
        $query = OrderHdrModel::query();
        $conditions = [
            'alert_cust_type' => '1,2'
        ];
        $expected = "`alert_cust_type` in ('1', '2')";

        // Act
        $query = $method->invokeArgs($instance, [$query, $conditions]);
        $result = $query->toRawSql();

        // Assert
        $this->assertStringContainsString($expected, $result);
    }

    #[TestDox("queryに販売コードの検索条件が追加されていること")]
    public function test_query_added_sell_cd()
    {
        // Arrange
        $reflection = new ReflectionClass(Search::class);
        $method = $reflection->getMethod('setCondition');
        $method->setAccessible(true);

        $instance = new Search();
        $query = OrderHdrModel::query();
        $conditions = [
            'sell_cd' => '1'
        ];
        $expected = "`sell_cd` = '1'";

        // Act
        $query = $method->invokeArgs($instance, [$query, $conditions]);
        $result = $query->toRawSql();

        // Assert
        $this->assertStringContainsString($expected, $result);
    }

    #[TestDox("queryに販売名の検索条件が追加されていること")]
    #[DataProviderExternal(className: SellNameDataprovider::class, methodName: "provider")]
    public function test_query_added_sell_name($values, $expected)
    {
        // Arrange
        $reflection = new ReflectionClass(Search::class);
        $method = $reflection->getMethod('setCondition');
        $method->setAccessible(true);

        $instance = new Search();
        $query = OrderHdrModel::query();
        $conditions = $values;

        // Act
        $query = $method->invokeArgs($instance, [$query, $conditions]);
        $result = $query->toRawSql();

        // Assert
        $this->assertStringContainsString($expected, $result);
    }

    #[TestDox("queryに項目選択肢の検索条件が追加されていること")]
    public function test_query_added_sell_option()
    {
        // Arrange
        $reflection = new ReflectionClass(Search::class);
        $method = $reflection->getMethod('setCondition');
        $method->setAccessible(true);

        $instance = new Search();
        $query = OrderHdrModel::query();
        $conditions = [
            'sell_option' => '1'
        ];
        $expected = "`sell_option` like '%1%'";

        // Act
        $query = $method->invokeArgs($instance, [$query, $conditions]);
        $result = $query->toRawSql();

        // Assert
        $this->assertStringContainsString($expected, $result);
    }

    #[TestDox("queryに商品コードの検索条件が追加されていること")]
    public function test_query_added_item_cd()
    {
        // Arrange
        $reflection = new ReflectionClass(Search::class);
        $method = $reflection->getMethod('setCondition');
        $method->setAccessible(true);

        $instance = new Search();
        $query = OrderHdrModel::query();
        $conditions = [
            'item_cd' => '1'
        ];
        $expected = "`item_cd` = '1'";

        // Act
        $query = $method->invokeArgs($instance, [$query, $conditions]);
        $result = $query->toRawSql();
        // Assert
        $this->assertStringContainsString($expected, $result);
    }

    #[TestDox("queryに配送倉庫の検索条件が追加されていること")]
    public function test_query_added_m_warehouse_id()
    {
        // Arrange
        $reflection = new ReflectionClass(Search::class);
        $method = $reflection->getMethod('setCondition');
        $method->setAccessible(true);

        $instance = new Search();
        $query = OrderHdrModel::query();
        $conditions = [
            'm_warehouse_id' => '1,2'
        ];
        $expected = "`m_warehouse_id` in ('1', '2')";

        // Act
        $query = $method->invokeArgs($instance, [$query, $conditions]);
        $result = $query->toRawSql();
        // Assert
        $this->assertStringContainsString($expected, $result);
    }

    #[TestDox("queryに温度帯の検索条件が追加されていること")]
    public function test_query_added_temperature_zone()
    {
        // Arrange
        $reflection = new ReflectionClass(Search::class);
        $method = $reflection->getMethod('setCondition');
        $method->setAccessible(true);

        $instance = new Search();
        $query = OrderHdrModel::query();
        $conditions = [
            'temperature_zone' => '1,2'
        ];
        $expected = "`temperature_type` in ('1', '2')";

        // Act
        $query = $method->invokeArgs($instance, [$query, $conditions]);
        $result = $query->toRawSql();
        // Assert
        $this->assertStringContainsString($expected, $result);
    }

    #[TestDox("queryに仕入先コードの検索条件が追加されていること")]
    public function test_query_added_m_suppliers_id()
    {
        // Arrange
        $reflection = new ReflectionClass(Search::class);
        $method = $reflection->getMethod('setCondition');
        $method->setAccessible(true);

        $instance = new Search();
        $query = OrderHdrModel::query();
        $conditions = [
            'm_suppliers_id' => '1'
        ];
        $expected = "`m_supplier_id` = '1'";

        // Act
        $query = $method->invokeArgs($instance, [$query, $conditions]);
        $result = $query->toRawSql();
        // Assert
        $this->assertStringContainsString($expected, $result);
    }

    #[TestDox("queryに直送の検索条件が追加されていること")]
    public function test_query_added_direct_deli_flg()
    {
        // Arrange
        $reflection = new ReflectionClass(Search::class);
        $method = $reflection->getMethod('setCondition');
        $method->setAccessible(true);

        $instance = new Search();
        $query = OrderHdrModel::query();
        $conditions = [
            'direct_deli_flg' => '1,2'
        ];
        $expected = "`direct_delivery_type` in ('1', '2')";

        // Act
        $query = $method->invokeArgs($instance, [$query, $conditions]);
        $result = $query->toRawSql();
        // Assert
        $this->assertStringContainsString($expected, $result);
    }

    #[TestDox("queryに後払いcom注文IDの検索条件が追加されていること")]
    public function test_query_added_payment_transaction_id()
    {
        // Arrange
        $reflection = new ReflectionClass(Search::class);
        $method = $reflection->getMethod('setCondition');
        $method->setAccessible(true);

        $instance = new Search();
        $query = OrderHdrModel::query();
        $conditions = [
            'payment_transaction_id' => '1'
        ];
        $expected = "`payment_transaction_id` = '1'";

        // Act
        $query = $method->invokeArgs($instance, [$query, $conditions]);
        $result = $query->toRawSql();
        // Assert
        $this->assertStringContainsString($expected, $result);
    }

    #[TestDox("queryに後払いcom決済ステータスの検索条件が追加されていること")]
    public function test_query_added_cb_credit_status()
    {
        // Arrange
        $reflection = new ReflectionClass(Search::class);
        $method = $reflection->getMethod('setCondition');
        $method->setAccessible(true);

        $instance = new Search();
        $query = OrderHdrModel::query();
        $conditions = [
            'cb_credit_status' => '1,2'
        ];
        $expected = "`cb_credit_status` in ('1', '2')";

        // Act
        $query = $method->invokeArgs($instance, [$query, $conditions]);
        $result = $query->toRawSql();
        // Assert
        $this->assertStringContainsString($expected, $result);
    }

    #[TestDox("queryに後払いcom出荷ステータスの検索条件が追加されていること")]
    public function test_query_added_cb_deli_status()
    {
        // Arrange
        $reflection = new ReflectionClass(Search::class);
        $method = $reflection->getMethod('setCondition');
        $method->setAccessible(true);
        $instance = new Search();
        $query = OrderHdrModel::query();
        $conditions = [
            'cb_deli_status' => '1,2'
        ];
        $expected = "`cb_deli_status` in ('1', '2')";

        // Act
        $query = $method->invokeArgs($instance, [$query, $conditions]);
        $result = $query->toRawSql();
        // Assert
        $this->assertStringContainsString($expected, $result);
    }

    #[TestDox("queryに後払いcom請求書送付ステータスの検索条件が追加されていること")]
    public function test_query_added_cb_billed_status()
    {
        // Arrange
        $reflection = new ReflectionClass(Search::class);
        $method = $reflection->getMethod('setCondition');
        $method->setAccessible(true);
        $instance = new Search();
        $query = OrderHdrModel::query();
        $conditions = [
            'cb_billed_status' => '1,2'
        ];
        $expected = "`cb_billed_status` in ('1', '2')";

        // Act
        $query = $method->invokeArgs($instance, [$query, $conditions]);
        $result = $query->toRawSql();
        // Assert
        $this->assertStringContainsString($expected, $result);
    }

    #[TestDox("queryに請求書送付種別の検索条件が追加されていること")]
    public function test_query_added_cb_billed_type()
    {
        // Arrange
        $reflection = new ReflectionClass(Search::class);
        $method = $reflection->getMethod('setCondition');
        $method->setAccessible(true);
        $instance = new Search();
        $query = OrderHdrModel::query();
        $conditions = [
            'cb_billed_type' => '1,2'
        ];
        $expected = "`cb_billed_type` in ('1', '2')";

        // Act
        $query = $method->invokeArgs($instance, [$query, $conditions]);
        $result = $query->toRawSql();
        // Assert
        $this->assertStringContainsString($expected, $result);
    }

    #[TestDox("queryに決済金額差異の検索条件が追加されていること")]
    public function test_query_added_payment_diff_flg()
    {
        // Arrange
        $reflection = new ReflectionClass(Search::class);
        $method = $reflection->getMethod('setCondition');
        $method->setAccessible(true);
        $instance = new Search();
        $query = OrderHdrModel::query();
        $conditions = [
            'payment_diff_flg' => '1'
        ];
        $expected = "IFNULL(payment_price, 0) <> order_total_price";

        // Act
        $query = $method->invokeArgs($instance, [$query, $conditions]);
        $result = $query->toRawSql();
        // Assert
        $this->assertStringContainsString($expected, $result);
    }

    #[TestDox("queryに入金日FROMの検索条件が追加されていること")]
    public function test_query_added_payment_date_from()
    {
        // Arrange
        $reflection = new ReflectionClass(Search::class);
        $method = $reflection->getMethod('setCondition');
        $method->setAccessible(true);
        $instance = new Search();
        $query = OrderHdrModel::query();
        $conditions = [
            'payment_date_from' => '2024-01-01 00:00:00'
        ];
        $expected = "`payment_date` >= '2024-01-01 00:00:00'";

        // Act
        $query = $method->invokeArgs($instance, [$query, $conditions]);
        $result = $query->toRawSql();
        // Assert
        $this->assertStringContainsString($expected, $result);
    }

    #[TestDox("queryに入金日TOの検索条件が追加されていること")]
    public function test_query_added_payment_date_to()
    {
        // Arrange
        $reflection = new ReflectionClass(Search::class);
        $method = $reflection->getMethod('setCondition');
        $method->setAccessible(true);
        $instance = new Search();
        $query = OrderHdrModel::query();
        $conditions = [
            'payment_date_to' => '2024-01-01 00:00:00'
        ];
        $expected = "`payment_date` <= '2024-01-01 00:00:00'";

        // Act
        $query = $method->invokeArgs($instance, [$query, $conditions]);
        $result = $query->toRawSql();
        // Assert
        $this->assertStringContainsString($expected, $result);
    }

    #[TestDox("queryに入金金額FROMの検索条件が追加されていること")]
    public function test_query_added_payment_price_from()
    {
        // Arrange
        $reflection = new ReflectionClass(Search::class);
        $method = $reflection->getMethod('setCondition');
        $method->setAccessible(true);
        $instance = new Search();
        $query = OrderHdrModel::query();
        $conditions = [
            'payment_price_from' => '1000'
        ];
        $expected = "`payment_price` >= '1000'";

        // Act
        $query = $method->invokeArgs($instance, [$query, $conditions]);
        $result = $query->toRawSql();
        // Assert
        $this->assertStringContainsString($expected, $result);
    }

    #[TestDox("queryに入金金額TOの検索条件が追加されていること")]
    public function test_query_added_payment_price_to()
    {
        // Arrange
        $reflection = new ReflectionClass(Search::class);
        $method = $reflection->getMethod('setCondition');
        $method->setAccessible(true);
        $instance = new Search();
        $query = OrderHdrModel::query();
        $conditions = [
            'payment_price_to' => '1000'
        ];
        $expected = "`payment_price` <= '1000'";

        // Act
        $query = $method->invokeArgs($instance, [$query, $conditions]);
        $result = $query->toRawSql();
        // Assert
        $this->assertStringContainsString($expected, $result);
    }

    #[TestDox("queryに複数配送先の検索条件が追加されていること")]
    public function test_query_added_multiple_deli_flg()
    {
        // Arrange
        $reflection = new ReflectionClass(Search::class);
        $method = $reflection->getMethod('setCondition');
        $method->setAccessible(true);
        $instance = new Search();
        $query = OrderHdrModel::query();
        $conditions = [
            'multiple_deli_flg' => '1'
        ];
        $expected = "`multiple_deli_flg` = '1'";

        // Act
        $query = $method->invokeArgs($instance, [$query, $conditions]);
        $result = $query->toRawSql();
        // Assert
        $this->assertStringContainsString($expected, $result);
    }

    #[TestDox("queryに出荷予定日FROMの検索条件が追加されていること")]
    public function test_query_added_deli_plan_date_from()
    {
        // Arrange
        $reflection = new ReflectionClass(Search::class);
        $method = $reflection->getMethod('setCondition');
        $method->setAccessible(true);
        $instance = new Search();
        $query = OrderHdrModel::query();
        $conditions = [
            'deli_plan_date_from' => '2024-01-01 00:00:00'
        ];
        $expected = "`deli_plan_date` >= '2024-01-01 00:00:00'";

        // Act
        $query = $method->invokeArgs($instance, [$query, $conditions]);
        $result = $query->toRawSql();
        // Assert
        $this->assertStringContainsString($expected, $result);
    }

    #[TestDox("queryに出荷予定日TOの検索条件が追加されていること")]
    public function test_query_added_deli_plan_date_to()
    {
        // Arrange
        $reflection = new ReflectionClass(Search::class);
        $method = $reflection->getMethod('setCondition');
        $method->setAccessible(true);
        $instance = new Search();
        $query = OrderHdrModel::query();
        $conditions = [
            'deli_plan_date_to' => '2024-01-01 00:00:00'
        ];
        $expected = "`deli_plan_date` <= '2024-01-01 00:00:00'";

        // Act
        $query = $method->invokeArgs($instance, [$query, $conditions]);
        $result = $query->toRawSql();
        // Assert
        $this->assertStringContainsString($expected, $result);
    }

    #[TestDox("queryに配送希望日FROMの検索条件が追加されていること")]
    public function test_query_added_deli_hope_date_from()
    {
        // Arrange
        $reflection = new ReflectionClass(Search::class);
        $method = $reflection->getMethod('setCondition');
        $method->setAccessible(true);
        $instance = new Search();
        $query = OrderHdrModel::query();
        $conditions = [
            'deli_hope_date_from' => '2024-01-01 00:00:00'
        ];
        $expected = "`deli_hope_date` >= '2024-01-01 00:00:00'";

        // Act
        $query = $method->invokeArgs($instance, [$query, $conditions]);
        $result = $query->toRawSql();
        // Assert
        $this->assertStringContainsString($expected, $result);
    }

    #[TestDox("queryに配送希望日TOの検索条件が追加されていること")]
    public function test_query_added_deli_hope_date_to()
    {
        // Arrange
        $reflection = new ReflectionClass(Search::class);
        $method = $reflection->getMethod('setCondition');
        $method->setAccessible(true);
        $instance = new Search();
        $query = OrderHdrModel::query();
        $conditions = [
            'deli_hope_date_to' => '2024-01-01 00:00:00'
        ];
        $expected = "`deli_hope_date` <= '2024-01-01 00:00:00'";

        // Act
        $query = $method->invokeArgs($instance, [$query, $conditions]);
        $result = $query->toRawSql();
        // Assert
        $this->assertStringContainsString($expected, $result);
    }

    #[TestDox("queryに配送希望時間帯の検索条件が追加されていること")]
    public function test_query_added_deli_hope_time_cd()
    {
        // Arrange
        $reflection = new ReflectionClass(Search::class);
        $method = $reflection->getMethod('setCondition');
        $method->setAccessible(true);
        $instance = new Search();
        $query = OrderHdrModel::query();
        $conditions = [
            'deli_hope_time_cd' => '1,2'
        ];
        $expected = "`deli_hope_time_cd` in ('1', '2')";

        // Act
        $query = $method->invokeArgs($instance, [$query, $conditions]);
        $result = $query->toRawSql();
        // Assert
        $this->assertStringContainsString($expected, $result);
    }

    #[TestDox("queryに配送先氏名・カナ氏名の検索条件が追加されていること")]
    #[DataProviderExternal(className: DeliveryNameDataprovider::class, methodName: "provider")]
    public function test_query_added_delivery_name($values, $expected)
    {
        // Arrange
        $reflection = new ReflectionClass(Search::class);
        $method = $reflection->getMethod('setCondition');
        $method->setAccessible(true);
        $instance = new Search();
        $query = OrderHdrModel::query();
        $conditions = $values;

        // Act
        $query = $method->invokeArgs($instance, [$query, $conditions]);
        $result = $query->toRawSql();
        // Assert
        $this->assertStringContainsString($expected, $result);
    }

    #[TestDox("queryに注文・送付先不一致の検索条件が追加されていること")]
    public function test_query_added_order_deli_address_check_flag()
    {
        // Arrange
        $reflection = new ReflectionClass(Search::class);
        $method = $reflection->getMethod('setCondition');
        $method->setAccessible(true);
        $instance = new Search();
        $query = OrderHdrModel::query();
        $conditions = [
            'order_deli_address_check_flag' => '1'
        ];
        $expected = "CONCAT(IFNULL(order_address1, ''), IFNULL(order_address2, ''), IFNULL(order_address3, ''), IFNULL(order_address4, '')) <> CONCAT(IFNULL(destination_address1, ''), IFNULL(destination_address2, ''), IFNULL(destination_address3, ''), IFNULL(destination_address4, '')))";

        // Act
        $query = $method->invokeArgs($instance, [$query, $conditions]);
        $result = $query->toRawSql();
        // Assert
        $this->assertStringContainsString($expected, $result);
    }

    #[TestDox("queryに送付先都道府県の検索条件が追加されていること")]
    public function test_query_added_destination_address1()
    {
        // Arrange
        $reflection = new ReflectionClass(Search::class);
        $method = $reflection->getMethod('setCondition');
        $method->setAccessible(true);
        $instance = new Search();
        $query = OrderHdrModel::query();
        $conditions = [
            'destination_address1' => '北海道,青森県'
        ];
        $expected = "`destination_address1` in ('北海道', '青森県')";

        // Act
        $query = $method->invokeArgs($instance, [$query, $conditions]);
        $result = $query->toRawSql();
        // Assert
        $this->assertStringContainsString($expected, $result);
    }

    // #[TestDox("queryにお届け先IDの検索条件が追加されていること")]
    // public function test_query_added_t_order_destinaton_id()
    // {
    //     // Arrange
    //     $reflection = new ReflectionClass(Search::class);
    //     $method = $reflection->getMethod('setCondition');
    //     $method->setAccessible(true);
    //     $instance = new Search();
    //     $query = OrderHdrModel::query();
    //     $conditions = [
    //         't_order_destinaton_id' => '1,2'
    //     ];
    //     $expected = "`t_order_destination_id` in ('1', '2')";

    //     // Act
    //     $query = $method->invokeArgs($instance, [$query, $conditions]);
    //     $result = $query->toRawSql();
    //     // Assert
    //     $this->assertStringContainsString($expected, $result);
    // }


    #[TestDox("queryに送り状コメント有の検索条件が追加されていること")]
    public function test_query_added_invoice_comment_flg()
    {
        // Arrange
        $reflection = new ReflectionClass(Search::class);
        $method = $reflection->getMethod('setCondition');
        $method->setAccessible(true);
        $instance = new Search();
        $query = OrderHdrModel::query();
        $conditions = [
            'invoice_comment_flg' => '1'
        ];
        $expected = "`invoice_comment` <> '' and `invoice_comment` is not null";

        // Act
        $query = $method->invokeArgs($instance, [$query, $conditions]);
        $result = $query->toRawSql();
        // Assert
        $this->assertStringContainsString($expected, $result);
    }

    #[TestDox("queryに送り状コメントの検索条件が追加されていること")]
    public function test_query_added_invoice_comment()
    {
        // Arrange
        $reflection = new ReflectionClass(Search::class);
        $method = $reflection->getMethod('setCondition');
        $method->setAccessible(true);
        $instance = new Search();
        $query = OrderHdrModel::query();
        $conditions = [
            'invoice_comment' => 'test'
        ];
        $expected = "`invoice_comment` like '%test%'";

        // Act
        $query = $method->invokeArgs($instance, [$query, $conditions]);
        $result = $query->toRawSql();
        // Assert
        $this->assertStringContainsString($expected, $result);
    }

    #[TestDox("queryに複数倉庫引当フラグの検索条件が追加されていること")]
    #[DataProviderExternal(className: MultiWarehouseFlgDataprovider::class, methodName: "provider")]
    public function test_query_added_multi_warehouse_flg($values, $expected)
    {
        // Arrange
        $reflection = new ReflectionClass(Search::class);
        $method = $reflection->getMethod('setCondition');
        $method->setAccessible(true);
        $instance = new Search();
        $query = OrderHdrModel::query();
        $conditions = $values;

        // Act
        $query = $method->invokeArgs($instance, [$query, $conditions]);
        $result = $query->toRawSql();
        // Assert
        $this->assertStringContainsString($expected, $result);
    }

    #[TestDox("queryに配送方法の検索条件が追加されていること")]
    public function test_query_added_m_deli_type_id()
    {
        // Arrange
        $reflection = new ReflectionClass(Search::class);
        $method = $reflection->getMethod('setCondition');
        $method->setAccessible(true);
        $instance = new Search();
        $query = OrderHdrModel::query();
        $conditions = [
            'm_deli_type_id' => '1,2'
        ];
        $expected = "`m_delivery_type_id` in ('1', '2')";

        // Act
        $query = $method->invokeArgs($instance, [$query, $conditions]);
        $result = $query->toRawSql();
        // Assert
        $this->assertStringContainsString($expected, $result);
    }

    #[TestDox("queryに出荷予定日なしの検索条件が追加されていること")]
    public function test_query_added_deli_plan_date_nothing_flg()
    {
        // Arrange
        $reflection = new ReflectionClass(Search::class);
        $method = $reflection->getMethod('setCondition');
        $method->setAccessible(true);
        $instance = new Search();
        $query = OrderHdrModel::query();
        $conditions = [
            'deli_plan_date_nothing_flg' => '1'
        ];
        $expected = "`deli_plan_date` is not null and `deli_plan_date` <> '0000-00-00'";

        // Act
        $query = $method->invokeArgs($instance, [$query, $conditions]);
        $result = $query->toRawSql();
        // Assert
        $this->assertStringContainsString($expected, $result);
    }

    #[TestDox("queryに配送希望日なしの検索条件が追加されていること")]
    public function test_query_added_deli_hope_date_nothing_flg()
    {
        // Arrange
        $reflection = new ReflectionClass(Search::class);
        $method = $reflection->getMethod('setCondition');
        $method->setAccessible(true);
        $instance = new Search();
        $query = OrderHdrModel::query();
        $conditions = [
            'deli_hope_date_nothing_flg' => '1'
        ];
        $expected = "`deli_hope_date` is not null and `deli_hope_date` <> '0000-00-00'";

        // Act
        $query = $method->invokeArgs($instance, [$query, $conditions]);
        $result = $query->toRawSql();
        // Assert
        $this->assertStringContainsString($expected, $result);
    }

    #[TestDox("queryに送り状コメント無の検索条件が追加されていること")]
    public function test_query_added_invoice_comment_nothing_flg()
    {
        // Arrange
        $reflection = new ReflectionClass(Search::class);
        $method = $reflection->getMethod('setCondition');
        $method->setAccessible(true);
        $instance = new Search();
        $query = OrderHdrModel::query();
        $conditions = [
            'invoice_comment_flg' => '0'
        ];
        $expected = "not exists (select * from `t_order_destination` where `t_order_hdr`.`t_order_hdr_id` = `t_order_destination`.`t_order_hdr_id` and `invoice_comment` <> '' and `invoice_comment` is not null)";

        // Act
        $query = $method->invokeArgs($instance, [$query, $conditions]);
        $result = $query->toRawSql();
        // Assert
        $this->assertStringContainsString($expected, $result);
    }

    #[TestDox("queryに配送IDの検索条件が追加されていること")]
    public function test_query_added_t_order_hdr_id()
    {
        // Arrange
        $reflection = new ReflectionClass(Search::class);
        $method = $reflection->getMethod('setCondition');
        $method->setAccessible(true);
        $instance = new Search();
        $query = OrderHdrModel::query();
        $conditions = [
            't_order_hdr_id' => '1,2'
        ];
        $expected = "`t_order_hdr_id` in ('1', '2')";

        // Act
        $query = $method->invokeArgs($instance, [$query, $conditions]);
        $result = $query->toRawSql();
        // Assert
        $this->assertStringContainsString($expected, $result);
    }

    #[TestDox("queryに送り状番号の検索条件が追加されていること")]
    public function test_query_added_invoice_no()
    {
        // Arrange
        $reflection = new ReflectionClass(Search::class);
        $method = $reflection->getMethod('setCondition');
        $method->setAccessible(true);
        $instance = new Search();
        $query = OrderHdrModel::query();
        $conditions = [
            'invoice_num' => '0123456789'
        ];
        $expected = "(`invoice_num1` = '0123456789' or `invoice_num2` = '0123456789' or `invoice_num3` = '0123456789' or `invoice_num4` = '0123456789' or `invoice_num5` = '0123456789')";

        // Act
        $query = $method->invokeArgs($instance, [$query, $conditions]);
        $result = $query->toRawSql();
        // Assert
        $this->assertStringContainsString($expected, $result);
    }

    #[TestDox("queryに配送日有の検索条件が追加されていること")]
    public function test_query_added_deli_date_flg()
    {
        // Arrange
        $reflection = new ReflectionClass(Search::class);
        $method = $reflection->getMethod('setCondition');
        $method->setAccessible(true);
        $instance = new Search();
        $query = OrderHdrModel::query();
        $conditions = [
            'deli_decision_date_flg' => '1'
        ];
        $expected = "`deli_decision_date` <> '0000-00-00' and `deli_decision_date` is not null";

        // Act
        $query = $method->invokeArgs($instance, [$query, $conditions]);
        $result = $query->toRawSql();
        // Assert
        $this->assertStringContainsString($expected, $result);
    }

    #[TestDox("queryに配送日FROMの検索条件が追加されていること")]
    public function test_query_added_deli_decision_date_from()
    {
        // Arrange
        $reflection = new ReflectionClass(Search::class);
        $method = $reflection->getMethod('setCondition');
        $method->setAccessible(true);
        $instance = new Search();
        $query = OrderHdrModel::query();
        $conditions = [
            'deli_decision_date_from' => '2024-01-01 00:00:00'
        ];
        $expected = "`deli_decision_date` >= '2024-01-01 00:00:00'";

        // Act
        $query = $method->invokeArgs($instance, [$query, $conditions]);
        $result = $query->toRawSql();
        // Assert
        $this->assertStringContainsString($expected, $result);
    }

    #[TestDox("queryに配送日TOの検索条件が追加されていること")]
    public function test_query_added_deli_decision_date_to()
    {
        // Arrange
        $reflection = new ReflectionClass(Search::class);
        $method = $reflection->getMethod('setCondition');
        $method->setAccessible(true);
        $instance = new Search();
        $query = OrderHdrModel::query();
        $conditions = [
            'deli_decision_date_to' => '2024-01-01 00:00:00'
        ];
        $expected = "`deli_decision_date` <= '2024-01-01 00:00:00'";

        // Act
        $query = $method->invokeArgs($instance, [$query, $conditions]);
        $result = $query->toRawSql();
        // Assert
        $this->assertStringContainsString($expected, $result);
    }

    #[TestDox("queryにピッキングコメントの有無の検索条件が追加されていること")]
    public function test_query_added_picking_comment_flg()
    {
        // Arrange
        $reflection = new ReflectionClass(Search::class);
        $method = $reflection->getMethod('setCondition');
        $method->setAccessible(true);
        $instance = new Search();
        $query = OrderHdrModel::query();
        $conditions = [
            'picking_comment_flg' => '1'
        ];
        $expected = "`picking_comment` <> '' and `picking_comment` is not null";

        // Act
        $query = $method->invokeArgs($instance, [$query, $conditions]);
        $result = $query->toRawSql();
        // Assert
        $this->assertStringContainsString($expected, $result);
    }

    #[TestDox("queryにピッキングコメントの検索条件が追加されていること")]
    public function test_query_added_picking_comment()
    {
        // Arrange
        $reflection = new ReflectionClass(Search::class);
        $method = $reflection->getMethod('setCondition');
        $method->setAccessible(true);
        $instance = new Search();
        $query = OrderHdrModel::query();
        $conditions = [
            'picking_comment' => 'test'
        ];
        $expected = "`picking_comment` like '%test%'";

        // Act
        $query = $method->invokeArgs($instance, [$query, $conditions]);
        $result = $query->toRawSql();
        // Assert
        $this->assertStringContainsString($expected, $result);
    }

    #[TestDox("queryに配送日の有無の検索条件が追加されていること")]
    public function test_query_added_deli_decision_date_flg()
    {
        // Arrange
        $reflection = new ReflectionClass(Search::class);
        $method = $reflection->getMethod('setCondition');
        $method->setAccessible(true);
        $instance = new Search();
        $query = OrderHdrModel::query();
        $conditions = [
            'deli_decision_date_flg' => '1'
        ];
        $expected = "`deli_decision_date` <> '0000-00-00' and `deli_decision_date` is not null";

        // Act
        $query = $method->invokeArgs($instance, [$query, $conditions]);
        $result = $query->toRawSql();
        // Assert
        $this->assertStringContainsString($expected, $result);
    }

    #[TestDox("queryにメールテンプレートIDの検索条件が追加されていること")]
    public function test_query_added_not_send_m_email_templates_id()
    {
        // Arrange
        $reflection = new ReflectionClass(Search::class);
        $method = $reflection->getMethod('setCondition');
        $method->setAccessible(true);
        $instance = new Search();
        $query = OrderHdrModel::query();
        $conditions = [
            'not_send_m_email_templates_id' => '1,2'
        ];
        $expected = "not exists (select * from `t_mail_send_history` where `t_order_hdr`.`t_order_hdr_id` = `t_mail_send_history`.`t_order_hdr_id` and `m_email_templates_id` in ('1', '2'))";

        // Act
        $query = $method->invokeArgs($instance, [$query, $conditions]);
        $result = $query->toRawSql();
        // Assert
        $this->assertStringContainsString($expected, $result);
    }

    #[TestDox('熨斗の設定された受注のみが返却されること')]
    public function test_query_add_noshi_flg()
    {
        // Arrange
        $reflection = new ReflectionClass(Search::class);
        $method = $reflection->getMethod('setCondition');
        $method->setAccessible(true);
        $instance = new Search();
        $query = OrderHdrModel::query();
        $conditions = ['noshi_flg' => '1'];
        $expected = "and exists (select * from `t_order_dtl_noshi` where `t_order_dtl`.`t_order_dtl_id` = `t_order_dtl_noshi`.`t_order_dtl_id`))";

        // Act
        $query = $method->invokeArgs($instance, [$query, $conditions]);
        $result = $query->toRawSql();
        // Assert
        $this->assertStringContainsString($expected, $result);
    }

    #[TestDox('熨斗の設定されていない受注のみが返却されること')]
    public function test_query_add_not_noshi_flg()
    {
        // Arrange
        $reflection = new ReflectionClass(Search::class);
        $method = $reflection->getMethod('setCondition');
        $method->setAccessible(true);
        $instance = new Search();
        $query = OrderHdrModel::query();
        $conditions = ['noshi_flg' => '0'];
        $expected = "not exists (select * from `t_order_dtl_noshi` where `t_order_dtl`.`t_order_dtl_id` = `t_order_dtl_noshi`.`t_order_dtl_id`))";

        // Act
        $query = $method->invokeArgs($instance, [$query, $conditions]);
        $result = $query->toRawSql();
        // Assert
        $this->assertStringContainsString($expected, $result);
    }
    #[TestDox('見積フラグの設定された受注のみが返却されること')]
    public function test_query_add_estimate_flg()
    {
        // Arrange
        $reflection = new ReflectionClass(Search::class);
        $method = $reflection->getMethod('setCondition');
        $method->setAccessible(true);
        $instance = new Search();
        $query = OrderHdrModel::query();
        $conditions = [
            'estimate_flg' => '1'
        ];
        $expected = "`estimate_flg` in ('1')";

        // Act
        $query = $method->invokeArgs($instance, [$query, $conditions]);
        $result = $query->toRawSql();
        // Assert
        $this->assertStringContainsString($expected, $result);
    }
    #[TestDox('領収書発行方法が一括の受注のみが返却されること')]
    #[DataProviderExternal(className: ReceiptTypeDataprovider::class, methodName: "provider")]
    public function test_query_add_receipt_type($values, $expected)
    {
        // Arrange
        $reflection = new ReflectionClass(Search::class);
        $method = $reflection->getMethod('setCondition');
        $method->setAccessible(true);
        $instance = new Search();
        $query = OrderHdrModel::query();

        $conditions = $values;

        // Act
        $query = $method->invokeArgs($instance, [$query, $conditions]);
        $result = $query->toRawSql();

        // Assert
        $this->assertStringContainsString($expected, $result);
    }

    #[TestDox('指定された販売窓口の受注のみが返却されること')]
    public function test_query_add_sales_store()
    {
        // Arrange
        $reflection = new ReflectionClass(Search::class);
        $method = $reflection->getMethod('setCondition');
        $method->setAccessible(true);
        $instance = new Search();
        $query = OrderHdrModel::query();
        $conditions = ['sales_store' => '1,2'];
        $expected = "`sales_store` in ('1', '2')";

        // Act
        $query = $method->invokeArgs($instance, [$query, $conditions]);
        $result = $query->toRawSql();
        // Assert
        $this->assertStringContainsString($expected, $result);
    }
    #[TestDox('指定されたWeb会員番号の顧客の受注のみが返却されること')]
    public function test_query_add_cust_reserve10()
    {
        // Arrange
        $reflection = new ReflectionClass(Search::class);
        $method = $reflection->getMethod('setCondition');
        $method->setAccessible(true);
        $instance = new Search();

        $query = OrderHdrModel::query();
        $conditions = ['cust_reserve10' => 'test_001,test0022'];
        $expected = "`reserve10` in ('test_001', 'test0022')";

        // Act
        $query = $method->invokeArgs($instance, [$query, $conditions]);
        $result = $query->toRawSql();

        // Assert
        $this->assertStringContainsString($expected, $result);
    }

    #[TestDox('ピッキングコメントの設定された受注のみが返却されること')]
    public function test_query_add_destination_picking_comment_flg()
    {
        // Arrange
        $reflection = new ReflectionClass(Search::class);
        $method = $reflection->getMethod('setCondition');
        $method->setAccessible(true);

        $instance = new Search();
        $query = OrderHdrModel::query();
        $conditions = [
            'destination_picking_comment_flg' => '1'
        ];
        $expected = "`picking_comment` <> '' and `picking_comment` is not null";

        // Act
        $query = $method->invokeArgs($instance, [$query, $conditions]);
        $result = $query->toRawSql();

        // Assert
        $this->assertStringContainsString($expected, $result);
    }

    #[TestDox('検索条件で指定されたピッキングコメントの受注のみが返却されること')]
    public function test_query_add_destination_picking_comment()
    {
        // Arrange
        $reflection = new ReflectionClass(Search::class);
        $method = $reflection->getMethod('setCondition');
        $method->setAccessible(true);

        $instance = new Search();
        $query = OrderHdrModel::query();
        $conditions = [
            'destination_picking_comment' => 'test'
        ];
        $expected = "`picking_comment` like '%test%'";

        // Act
        $query = $method->invokeArgs($instance, [$query, $conditions]);
        $result = $query->toRawSql();

        // Assert
        $this->assertStringContainsString($expected, $result);
    }

}
