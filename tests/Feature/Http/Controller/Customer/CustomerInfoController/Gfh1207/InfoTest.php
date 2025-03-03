<?php
namespace Tests\Feature\Http\Controller\Customer\CustomerInfoController\Gfh1207;

use PHPUnit\Framework\Attributes\DataProviderExternal;
use PHPUnit\Framework\Attributes\TestDox;
use Tests\Feature\Http\Controller\Customer\CustomerInfoController\Gfh1207\DataProviders\LoginOperatorInfoDataProvider;
use Tests\TestCases\Gfh1207TestCase;

class InfoTest extends Gfh1207TestCase
{

    /*
    #[TestDox('顧客照会画面の表示')]
    #[DataProviderExternal(className:LoginOperatorInfoDataProvider::class, methodName:'provider')]
    public function test_get_request($operatorInfo, $expected)
    {
        // Arrange
        $this->withSession(['OperatorInfo' => $operatorInfo]);

        // Act
        $response = $this->get('cc-customer/info/1');

        // Assert
        $response->assertStatus($expected['status']);
    }

    #[TestDox('顧客画面の画面表示(viewの確認)')]
    public function test_view()
    {
        // Arrange
        $this->withSession(['OperatorInfo' => $this->operatorInfo()]);

        // Act
        $response = $this->get('cc-customer/info/1');

        // Assert
        $response->assertViewIs('customer.gfh_1207.info');
        $response->assertViewHas('customer');
    }
    */
}
