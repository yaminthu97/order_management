<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

use App\Http\Controllers\CustCommunicationController;
use App\Http\Controllers\Customer\CcCustomerListController;
use App\Http\Controllers\Customer\CustomerController;
use App\Http\Controllers\Customer\CustomerHistoryController;
use App\Http\Controllers\Customer\CustomerListController;
use App\Http\Controllers\Master\TemplateMasterListController;
use App\Http\Controllers\Order\OrderDeliveryController;
use App\Http\Controllers\Order\OrderShipmentListController;
use App\Http\Controllers\Shipment\ShipmentReportsController;
use App\Http\Middleware\Auth;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::withoutMiddleware([EnsureTokenIsValid::class])->group(function () {
    Route::get('/profile', function () {
        // ...
    });
});

// Route::prefix('gfh')->group(function () {
// 顧客検索
Route::get('cc/customer/list', [CustomerListController::class, 'list'])->name('cc.customer.list')->middleware('custom_auth:10');
Route::post('cc/customer/list', [CustomerListController::class, 'postList'])->name('cc.customer.post-list')->middleware('custom_auth:10');

// 顧客登録・修正
Route::get('/getPostal/{postal}', [CustomerController::class, 'getPostal']);
Route::get('cc/customer/new', [CustomerController::class, 'new'])->name('cc.customer.new')->middleware('custom_auth:10');
Route::post('cc/customer/new', [CustomerController::class, 'postNew'])->name('cc.customer.post-new')->middleware('custom_auth:10');
Route::get('cc/customer/edit/{id?}', [CustomerController::class, 'edit'])->name('cc.customer.edit')->middleware('custom_auth:10');
Route::post('cc/customer/edit/{id?}', [CustomerController::class, 'postEdit'])->name('cc.customer.post-edit')->middleware('custom_auth:10');
Route::get('cc/customer/notify', [CustomerController::class, 'notify'])->name('cc.customer.notify')->middleware('custom_auth:10');
Route::post('cc/customer/notify', [CustomerController::class, 'postNotify'])->name('cc.customer.post-notify')->middleware('custom_auth:10');

// 顧客インポート/エクスポート
Route::get('cc/customer/import', [App\Http\Controllers\Customer\CustomerListController::class, 'import'])->middleware('custom_auth:10')->name('cc.customer.import');
Route::post('cc/customer/import', [App\Http\Controllers\Customer\CustomerListController::class, 'import'])->middleware('custom_auth:10')->name('cc.customer.post-import');
Route::get('cc/customer/export', [App\Http\Controllers\Customer\CustomerListController::class, 'export'])->middleware('custom_auth:10')->name('cc.customer.export');
Route::post('cc/customer/export', [App\Http\Controllers\Customer\CustomerListController::class, 'export'])->middleware('custom_auth:10')->name('cc.customer.post-export');

// 顧客照会対応履歴一覧
// Route::get('cc-customer/list', [CustCommunicationController::class, 'list'])->middleware('custom_auth:10');
// Route::post('cc-customer/list', [CustCommunicationController::class, 'list'])->middleware('custom_auth:10');
// Route::get('cc-customer/edit/{id?}', [CustCommunicationController::class, 'edit'])->middleware('custom_auth:10');
// Route::post('cc-customer/edit/{id?}', [CustCommunicationController::class, 'edit'])->middleware('custom_auth:10');
// Route::get('cc-customer/list', [CustCommunicationController::class, 'list'])->middleware('custom_auth:10');
// Route::post('cc-customer/list', [CustCommunicationController::class, 'list'])->middleware('custom_auth:10');
// Route::get('cc-customer/edit/{id?}', [CustCommunicationController::class, 'edit'])->middleware('custom_auth:10');
// Route::post('cc-customer/edit/{id?}', [CustCommunicationController::class, 'edit'])->middleware('custom_auth:10');

// 顧客照会
Route::get('cc/cc-customer/info/{id}', [App\Http\Controllers\Customer\CustmerInfoController::class, 'info'])->name('cc.cc-customer.info')->middleware('custom_auth:10');
Route::post('cc/cc-customer/info/{id}', [App\Http\Controllers\Customer\CustmerInfoController::class, 'postInfo'])->name('cc.cc-customer.post-info')->middleware('custom_auth:10');

// 受注一覧
Route::get('order/order/list', [App\Http\Controllers\Order\OrderListController::class, 'list'])->middleware('custom_auth:10')->name('order.order.list');
Route::post('order/order/list', [App\Http\Controllers\Order\OrderListController::class, 'search'])->middleware('custom_auth:10')->name('order.order.search');

Route::get('order/shipped_search/list', [OrderShipmentListController::class, 'list'])->middleware('custom_auth:10')->name('order.shipped_search.list');
Route::post('order/shipped_search/list/output', [OrderShipmentListController::class, 'csvOutput'])->middleware('custom_auth:10')->name('order.shipped_search.list.output');

Route::get('order/shipment_reports/list', [ShipmentReportsController::class, 'list'])->middleware('custom_auth:10')->name('order.shipment_reports.list');
Route::post('order/shipment_reports/list/output', [ShipmentReportsController::class, 'csvOutput'])->middleware('custom_auth:10')->name('order.shipment_reports.list.output');

// 受注登録・修正
Route::get('order/order/new', [App\Http\Controllers\Order\OrderEditController::class, 'new'])->middleware('custom_auth:10')->name('order.order.new');
Route::post('order/order/new', [App\Http\Controllers\Order\OrderEditController::class, 'postNew'])->middleware('custom_auth:10')->name('order.order.post-new');
Route::get('order/order/edit/{id?}', [App\Http\Controllers\Order\OrderEditController::class, 'edit'])->middleware('custom_auth:10')->name('order.order.edit');
Route::post('order/order/edit/{id?}', [App\Http\Controllers\Order\OrderEditController::class, 'postEdit'])->middleware('custom_auth:10')->name('order.order.post-edit');
Route::get('order/order/notify', [App\Http\Controllers\Order\OrderNotifyController::class, 'notify'])->middleware('custom_auth:10')->name('order.order.notify');
Route::post('order/order/notify', [App\Http\Controllers\Order\OrderNotifyController::class, 'postNotify'])->middleware('custom_auth:10')->name('order.order.post-notify');


    // 受注タグマスタ
    Route::get('order/order-tag/list', [App\Http\Controllers\Order\OrderTagController::class, 'list'])->middleware('custom_auth:10')->name('order.order-tag.list');
    Route::post('order/order-tag/list', [App\Http\Controllers\Order\OrderTagController::class, 'postList'])->middleware('custom_auth:10')->name('order.order-tag.list');
    Route::get('order/order-tag/new', [App\Http\Controllers\Order\OrderTagController::class, 'new'])->middleware('custom_auth:10')->name('order.order-tag.new');
    Route::post('order/order-tag/new', [App\Http\Controllers\Order\OrderTagController::class, 'postNew'])->middleware('custom_auth:10')->name('order.order-tag.new');
     Route::get('order/order-tag/edit/{id}', [App\Http\Controllers\Order\OrderTagController::class, 'edit'])->middleware('custom_auth:10')->name('order.order-tag.edit');
    Route::post('order/order-tag/edit/{id}', [App\Http\Controllers\Order\OrderTagController::class, 'postEdit'])->middleware('custom_auth:10')->name('order.order-tag.edit');
    Route::get('order/order-tag/notify', [App\Http\Controllers\Order\OrderTagController::class, 'notify'])->middleware('custom_auth:10')->name('order.order-tag.notify');
    Route::post('order/order-tag/notify', [App\Http\Controllers\Order\OrderTagController::class, 'postNotify'])->middleware('custom_auth:10')->name('order.order-tag.notify');
    Route::put('order/order-tag/notify', [App\Http\Controllers\Order\OrderTagController::class, 'putNotify'])->middleware('custom_auth:10')->name('order.order-tag.notify');
    Route::get('/getOrderTagColDict/{table_id}', [App\Http\Controllers\Order\OrderTagController::class, 'getOrderTagColDict']);

    // ページマスタ追加項目編集
    Route::get('ami/page/image', [App\Http\Controllers\Ami\Page\AmiPageRegisterController::class, 'image'])->middleware('custom_auth:10')->name('ami.page.image');
    Route::get('ami/page/edit/{page_id}', [App\Http\Controllers\Ami\Page\AmiPageRegisterController::class, 'edit'])->middleware('custom_auth:10')->name('ami.page.edit');
    Route::post('ami/page/edit/{page_id}', [App\Http\Controllers\Ami\Page\AmiPageRegisterController::class, 'postEdit'])->middleware('custom_auth:10')->name('ami.page.post-edit');

// ページマスタ追加項目編集
Route::get('ami/page/image', [App\Http\Controllers\Ami\Page\AmiPageRegisterController::class, 'image'])->middleware('custom_auth:10')->name('ami.page.image');
Route::get('ami/page/edit/{page_id}', [App\Http\Controllers\Ami\Page\AmiPageRegisterController::class, 'edit'])->middleware('custom_auth:10')->name('ami.page.edit');
Route::post('ami/page/edit/{page_id}', [App\Http\Controllers\Ami\Page\AmiPageRegisterController::class, 'postEdit'])->middleware('custom_auth:10')->name('ami.page.post-edit');


// 受注照会
Route::get('order/order/info/{id?}', [App\Http\Controllers\Order\OrderInfoController::class, 'info'])->name('order.order.info')->middleware('custom_auth:10');
Route::post('order/order/info/{id?}', [App\Http\Controllers\Order\OrderInfoController::class, 'postInfo'])->name('order.order.postInfo')->middleware('custom_auth:10');

// 受注系API
Route::get('order/api/itemname-type/list', [App\Http\Controllers\Order\Api\ItemnameTypeController::class, 'list'])->middleware('custom_auth:10');
// 受注系API(modal)
Route::post('order/api/customer/list', [App\Http\Controllers\Order\Api\CustomerController::class, 'search'])->middleware('custom_auth:10');
Route::post('order/api/customer/destination/list', [App\Http\Controllers\Order\Api\DestinationController::class, 'search'])->middleware('custom_auth:10');
Route::post('order/api/ami_page/list', [App\Http\Controllers\Order\Api\AmiPageController::class, 'search'])->middleware('custom_auth:10');
Route::post('order/api/attachment_item/list', [App\Http\Controllers\Order\Api\AttachmentItemController::class, 'search'])->middleware('custom_auth:10')->name('order.api.attachment_item.list');
// 受注系API(list/detail)
Route::get('order/api/deli_type/list', [App\Http\Controllers\Order\Api\DeliveryTypeController::class, 'list'])->middleware('custom_auth:10');
Route::get('order/api/deli_type/{deli_type_id}', [App\Http\Controllers\Order\Api\DeliveryTypeController::class, 'detail'])->middleware('custom_auth:10');
Route::get('order/api/payment_type/{payment_type_id}', [App\Http\Controllers\Order\Api\PaymentTypeController::class, 'detail'])->middleware('custom_auth:10');

Route::get('order/api/customer/{customer_id}', [App\Http\Controllers\Order\Api\CustomerController::class, 'detail'])->middleware('custom_auth:10');
Route::get('order/api/customer/destination/{destination_id}', [App\Http\Controllers\Order\Api\DestinationController::class, 'detail'])->middleware('custom_auth:10');
Route::get('order/api/ami_page/search', [App\Http\Controllers\Order\Api\AmiPageController::class, 'searchAmiPage'])->middleware('custom_auth:10');
Route::get('order/api/ami_page/{ami_page_id}', [App\Http\Controllers\Order\Api\AmiPageController::class, 'detail'])->middleware('custom_auth:10');
Route::get('order/api/attachment_item/category/list', [App\Http\Controllers\Order\Api\AttachmentItemController::class, 'categoryList'])->middleware('custom_auth:10');
Route::get('order/api/attachment_item/search', [App\Http\Controllers\Order\Api\AttachmentItemController::class, 'searchItemCd'])->middleware('custom_auth:10');
Route::get('order/api/attachment_item/{attachment_item_id}', [App\Http\Controllers\Order\Api\AttachmentItemController::class, 'detail'])->middleware('custom_auth:10');

Route::get('order/api/noshi-format/list/{ami_page_id}', [App\Http\Controllers\Order\Api\NoshiFormatController::class, 'search'])->middleware('custom_auth:10');
Route::get('order/api/noshi-format/info/{noshi_format_id}', [App\Http\Controllers\Order\Api\NoshiFormatController::class, 'detail'])->middleware('custom_auth:10');
Route::get('order/api/noshi-naming-pattern/list/{noshi_format_id}', [App\Http\Controllers\Order\Api\NoshiNamingPatternController::class, 'search'])->middleware('custom_auth:10');
Route::get('order/api/noshi-naming-pattern/info/{noshi_naming_pattern_id}', [App\Http\Controllers\Order\Api\NoshiNamingPatternController::class, 'detail'])->middleware('custom_auth:10');
// 受注系API(その他)
//Route::post('order/api/get_shipping_fee', [App\Http\Controllers\Order\Api\GetShippingFeeController::class, 'search'])->middleware('custom_auth:10');
//Route::post('order/api/get_tempzone_fee', [App\Http\Controllers\Order\Api\GetTempzoneFeeController::class, 'search'])->middleware('custom_auth:10');
Route::post('order/api/order-tags/add', [App\Http\Controllers\Order\Api\OrderTagController::class, 'add'])->middleware('custom_auth:10');
Route::post('order/api/order-tags/remove', [App\Http\Controllers\Order\Api\OrderTagController::class, 'remove'])->middleware('custom_auth:10');
Route::get('order/api/order-tags/order/{id}', [App\Http\Controllers\Order\Api\OrderTagController::class, 'orderInfo'])->middleware('custom_auth:10');
Route::get('order/api/delivery-days/{zip_code}', [App\Http\Controllers\Order\Api\DeliveryDaysController::class, 'search'])->middleware('custom_auth:10');
Route::get('order/api/itemname-type/info/{id}', [App\Http\Controllers\Order\Api\ItemnameTypeController::class, 'info'])->middleware('custom_auth:10');
Route::get('order/api/zipcode/info/{zipcode}', [App\Http\Controllers\Order\Api\ZipCodeController::class, 'detail'])->middleware('custom_auth:10');

Route::get('order/order-delivery/info/{id}', [OrderDeliveryController::class, 'info'])->name('order.order-delivery.info')->middleware('custom_auth:10');
Route::post('order/order-delivery/info/{id}/update', [OrderDeliveryController::class, 'update'])->name('order.order-delivery.update')->middleware('custom_auth:10');

// 配送方法マスタ
Route::get('master/delivery_types/list', [App\Http\Controllers\Master\DeliveryTypeController::class, 'list'])->middleware('custom_auth:10')->name('master.delivery_types.list');
Route::post('master/delivery_types/list', [App\Http\Controllers\Master\DeliveryTypeController::class, 'postList'])->middleware('custom_auth:10')->name('master.delivery_types.list');
Route::get('master/delivery_types/new', [App\Http\Controllers\Master\DeliveryTypeController::class, 'new'])->middleware('custom_auth:10')->name('master.delivery_types.new');
Route::post('master/delivery_types/new', [App\Http\Controllers\Master\DeliveryTypeController::class, 'postNew'])->middleware('custom_auth:10')->name('master.delivery_types.new');
Route::get('master/delivery_types/edit/{id}', [App\Http\Controllers\Master\DeliveryTypeController::class, 'edit'])->middleware('custom_auth:10')->name('master.delivery_types.edit');
Route::post('master/delivery_types/edit/{id}', [App\Http\Controllers\Master\DeliveryTypeController::class, 'postEdit'])->middleware('custom_auth:10')->name('master.delivery_types.edit');
Route::get('master/delivery_types/notify', [App\Http\Controllers\Master\DeliveryTypeController::class, 'notify'])->middleware('custom_auth:10')->name('master.delivery_types.notify');
Route::post('master/delivery_types/notify', [App\Http\Controllers\Master\DeliveryTypeController::class, 'postNotify'])->name('master.delivery_types.postNotify')->middleware('custom_auth:10');
Route::put('master/delivery_types/notify', [App\Http\Controllers\Master\DeliveryTypeController::class, 'putNotify'])->name('master.delivery_types.putNotify')->middleware('custom_auth:10');

// 基本設定更新画面
Route::get('/master/shop_gfh/edit', [App\Http\Controllers\Master\ShopGfhController::class, 'edit'])->middleware('custom_auth:10')->name('master.shop_gfh.edit');
Route::post('/master/shop_gfh/edit', [App\Http\Controllers\Master\ShopGfhController::class, 'postEdit'])->middleware('custom_auth:10')->name('master.shop_gfh.post-edit');

Route::get('master/itemname_types/list', [App\Http\Controllers\Master\ItemnameTypeController::class, 'list'])->middleware('custom_auth:10')->name('master.itemname_types.list');
Route::post('master/itemname_types/list', [App\Http\Controllers\Master\ItemnameTypeController::class, 'postList'])->middleware('custom_auth:10')->name('master.itemname_types.post-list');
Route::get('master/itemname_types/new', [App\Http\Controllers\Master\ItemnameTypeController::class, 'new'])->middleware('custom_auth:10')->name('master.itemname_types.new');
Route::post('master/itemname_types/new', [App\Http\Controllers\Master\ItemnameTypeController::class, 'postNew'])->middleware('custom_auth:10');
Route::get('master/itemname_types/edit/{id}', [App\Http\Controllers\Master\ItemnameTypeController::class, 'edit'])->middleware('custom_auth:10')->name('master.itemname_types.edit');
Route::post('master/itemname_types/edit/{id}', [App\Http\Controllers\Master\ItemnameTypeController::class, 'postEdit'])->middleware('custom_auth:10');
Route::get('master/itemname_types/notify', [App\Http\Controllers\Master\ItemnameTypeController::class, 'notify'])->middleware('custom_auth:10')->name('master.itemname_types.notify');
Route::post('master/itemname_types/notify', [App\Http\Controllers\Master\ItemnameTypeController::class, 'postNotify'])->middleware('custom_auth:10');
Route::put('master/itemname_types/notify', [App\Http\Controllers\Master\ItemnameTypeController::class, 'putNotify'])->name('sample.sample.put-notify')->middleware('custom_auth:10');

// 経理処理用情報照会
Route::get('order/payment-accounting/list', [App\Http\Controllers\Order\PaymentAccountingController::class, 'list'])->name('order.payment-accounting.list')->middleware('custom_auth:10');
Route::post('order/payment-accounting/list', [App\Http\Controllers\Order\PaymentAccountingController::class, 'search'])->name('order.payment-accounting.search')->middleware('custom_auth:10');

// 帳票テンプレート
Route::get('master/template_master/list', [TemplateMasterListController::class, 'list'])->middleware('custom_auth:10')->name('master.templatemaster.list');
Route::post('master/template_master/download', [TemplateMasterListController::class, 'postDownload'])->middleware('custom_auth:10')->name('master.templatemaster.download');
Route::get('master/template_master/edit/{id}', [TemplateMasterListController::class, 'edit'])->middleware('custom_auth:10')->name('master.templatemaster.edit');
Route::post('master/template_master/edit/{id}', [TemplateMasterListController::class, 'postUpdate'])->middleware('custom_auth:10')->name('master.templatemaster.edit.update');

Route::get('order/noshi/list', [App\Http\Controllers\Order\NoshiController::class, 'list'])->middleware('custom_auth:10')->name('order.noshi.list');
Route::post('order/noshi/list', [App\Http\Controllers\Order\NoshiController::class, 'search'])->middleware('custom_auth:10')->name('order.noshi.search');
Route::post('order/api/create-noshi/check-linkage', [App\Http\Controllers\Order\Api\CreateNoshiController::class, 'checkLinkage'])->middleware('custom_auth:10')->name('order.create.noshi.check-linkage');
Route::post('order/api/create-noshi/check-shared', [App\Http\Controllers\Order\Api\CreateNoshiController::class, 'checkShared'])->middleware('custom_auth:10')->name('order.create.noshi.check-shared');
Route::post('order/api/create-noshi/check-create', [App\Http\Controllers\Order\Api\CreateNoshiController::class, 'checkCreate'])->middleware('custom_auth:10')->name('order.create.noshi.check-create');
Route::post('order/api/create-noshi/create', [App\Http\Controllers\Order\Api\CreateNoshiController::class, 'create'])->middleware('custom_auth:10')->name('order.create.noshi.create');
Route::post('order/api/create-noshi/clear', [App\Http\Controllers\Order\Api\CreateNoshiController::class, 'clear'])->middleware('custom_auth:10')->name('order.create.noshi.clear');

Route::get('master/warehouses/list', [App\Http\Controllers\Master\WarehousesController::class, 'list'])->name('master.warehouses.list')->middleware('custom_auth:10');
Route::post('master/warehouses/list', [App\Http\Controllers\Master\WarehousesController::class, 'postList'])->name('master.warehouses.post-list')->middleware('custom_auth:10');
Route::get('master/warehouses/new', [App\Http\Controllers\Master\WarehousesController::class, 'new'])->name('master.warehouses.new')->middleware('custom_auth:10');
Route::post('master/warehouses/new', [App\Http\Controllers\Master\WarehousesController::class, 'postNew'])->name('master.warehouses.post-new')->middleware('custom_auth:10');
Route::get('master/warehouses/edit/{id}', [App\Http\Controllers\Master\WarehousesController::class, 'edit'])->name('master.warehouses.edit')->middleware('custom_auth:10');
Route::post('master/warehouses/edit/{id}', [App\Http\Controllers\Master\WarehousesController::class, 'postEdit'])->name('master.warehouses.post-edit')->middleware('custom_auth:10');
Route::get('master/warehouses/notify', [App\Http\Controllers\Master\WarehousesController::class, 'notify'])->name('master.warehouses.notify')->middleware('custom_auth:10');
Route::post('master/warehouses/notify', [App\Http\Controllers\Master\WarehousesController::class, 'postNotify'])->name('master.warehouses.post-notify')->middleware('custom_auth:10');
Route::put('master/warehouses/notify', [App\Http\Controllers\Master\WarehousesController::class, 'putNotify'])->name('master.warehouses.put-notify')->middleware('custom_auth:10');
Route::post('master/warehouses/{id}/getCalendar/{year}/{notify?}', [App\Http\Controllers\Master\WarehousesController::class, 'getCalendar']);

Route::get('cc/customer-history/new', [CustomerHistoryController::class, 'new'])->middleware('custom_auth:10')->name('cc.customer-history.new');

// Route::post('order/order-delivery/info/{id?}', 'Order\OrderDeliveryController@info')->middleware('custom_auth:10');

// 受注系その他画面
Route::get('order/bulk_order/list', [App\Http\Controllers\Order\BulkOrderController::class, 'list'])->name('order.bulk_order.list')->middleware('custom_auth:10');
Route::post('order/bulk_order/list', [App\Http\Controllers\Order\BulkOrderController::class, 'postList'])->name('order.bulk_order.post-list')->middleware('custom_auth:10');

Route::get('order/dm_analytics/new', [App\Http\Controllers\Order\DmAnalyticsController::class, 'new'])->name('order.dm_analytics.new')->middleware('custom_auth:10');
Route::post('order/dm_analytics/output', [App\Http\Controllers\Order\DmAnalyticsController::class, 'output'])->name('order.dm_analytics.output')->middleware('custom_auth:10');

Route::get('order/ecbeing/list', [App\Http\Controllers\Order\EcBeingListController::class, 'info'])->name('order.ecbeing.list')->middleware('custom_auth:10');
Route::post('order/ecbeing/list', [App\Http\Controllers\Order\EcBeingListController::class, 'postInfo'])->name('order.ecbeing.post-list')->middleware('custom_auth:10');

// 入金検索
Route::get('order/payment/list', [App\Http\Controllers\Order\PaymentListController::class, 'list'])->name('order.payment.list')->middleware('custom_auth:10');
Route::post('order/payment/list', [App\Http\Controllers\Order\PaymentListController::class, 'postList'])->name('order.payment.post-list')->middleware('custom_auth:10');

// 顧客受付
Route::get('cc/cc-customer/list', [CcCustomerListController::class, 'list'])->middleware('custom_auth:10')->name('cc.cc-customer.list');
Route::post('cc/cc-customer/list', [CcCustomerListController::class, 'postList'])->middleware('custom_auth:10')->name('cc.cc-customer.list');
Route::get('cc/cc-customer/list/{phone?}', [CcCustomerListController::class, 'list'])->middleware('custom_auth:10')->where('phone', '.*')->name('cc.cc-customer.list');
Route::post('cc/cc-customer/list/{phone?}', [CcCustomerListController::class, 'postList'])->middleware('custom_auth:10')->where('phone', '.*')->name('cc.cc-customer.list');
Route::get('cc/cc-customer/new', [CcCustomerListController::class, 'new'])->middleware('custom_auth:10')->name('cc.cc-customer.new');
Route::post('cc/cc-customer/new', [CcCustomerListController::class, 'new'])->middleware('custom_auth:10')->name('cc.cc-customer.new');

/*
// 顧客照会対応履歴一覧
Route::get('cc-customer-history/list', [CcCustomerHistoryController::class, 'list'])->middleware('custom_auth:10');
Route::post('cc-customer-history/list', [CcCustomerHistoryController::class, 'list'])->middleware('custom_auth:10');

// 顧客照会メール送信履歴一覧
Route::get('cc-customer-mail/list', [CcCustomerMailController::class, 'list'])->middleware('custom_auth:10');
Route::post('cc-customer-mail/list', [CcCustomerMailController::class, 'list'])->middleware('custom_auth:10');
*/

// 顧客照会メール送信履歴照会(受注照会のroute設定用にここだけ復帰)
//Route::get('cc/cc-customer-mail/info/{id?}', [CcCustomerMailController::class, 'info'])->middleware('custom_auth:10')->name('cc.cc-customer-mail.info');
/*
Route::post('cc-customer-mail/info/{id?}', [CcCustomerMailController::class, 'info'])->middleware('custom_auth:10');
*/

// 顧客対応履歴登録・修正
Route::get('cc/customer-history/new', [CustomerHistoryController::class, 'new'])->middleware('custom_auth:10')->name('cc.customer-history.new');
Route::post('cc/customer-history/new', [CustomerHistoryController::class, 'postNew'])->middleware('custom_auth:10')->name('cc.customer-history.post-new');
Route::get('cc/customer-history/edit/{id?}', [CustomerHistoryController::class, 'edit'])->middleware('custom_auth:10')->name('cc.customer-history.edit');
Route::post('cc/customer-history/edit/{id?}', [CustomerHistoryController::class, 'postEdit'])->middleware('custom_auth:10')->name('cc.customer-history.post-edit');

// 顧客対応履歴登録・修正確認
Route::get('cc/customer-history/notify', [CustomerHistoryController::class, 'notify'])->middleware('custom_auth:10')->name('cc.customer-history.notify');
Route::post('cc/customer-history/notify', [CustomerHistoryController::class, 'postNotify'])->middleware('custom_auth:10')->name('cc.customer-history.post-notify');
Route::put('cc/customer-history/notify', [CustomerHistoryController::class, 'putNotify'])->middleware('custom_auth:10')->name('cc.customer-history.put-notify');

// 確認画面からupdate
Route::post('cc/customer-history/reportOutput', [CustomerHistoryController::class, 'postReportOutput'])->middleware('custom_auth:10')->name('cc.customer-history.post-report-output');
Route::post('cc/customer-history-detail/delete', [CustomerHistoryController::class, 'postDelete'])->middleware('custom_auth:10')->name('cc.customer-history-dtl.post-delete');
/*
// 顧客対応履歴照会
Route::get('customer-history/info/{id?}', [CustomerHistoryController::class, 'info'])->middleware('custom_auth:10');
Route::post('customer-history/info/{id?}', [CustomerHistoryController::class, 'info'])->middleware('custom_auth:10');
*/
// 顧客対応履歴検索
Route::get('cc/customer-history/list', [CustomerHistoryController::class, 'index'])->middleware('custom_auth:10')->name('cc.customer-history.index');
Route::post('cc/customer-history/list', [CustomerHistoryController::class, 'list'])->middleware('custom_auth:10')->name('cc.customer-history.list');
Route::get('cc/customer-history/output', [CustomerHistoryController::class, 'csvOutput'])->middleware('custom_auth:10')->name('cc.customer-history.output');
Route::post('cc/customer-history/output', [CustomerHistoryController::class, 'csvOutput'])->middleware('custom_auth:10')->name('cc.customer-history.output');
//Route::post('cc-customer/info/{id?}', [CcCustomerController::class, 'info'])->middleware('custom_auth:10');

/*
// 顧客照会受注一覧
Route::get('cc-customer-order/list', [CcCustomerOrderController::class, 'list'])->middleware('custom_auth:10');
Route::post('cc-customer-order/list', [CcCustomerOrderController::class, 'list'])->middleware('custom_auth:10');
*/
Route::get('master/noshi-naming-patterns/list', [App\Http\Controllers\Master\NoshiNamingPatternController::class, 'list'])->middleware('custom_auth:10')->name('noshi.namingpattern.list');
Route::post('master/noshi-naming-patterns/list', [App\Http\Controllers\Master\NoshiNamingPatternController::class, 'list'])->middleware('custom_auth:10')->name('noshi.namingpattern.search');
Route::get('master/noshi-naming-patterns/new', [App\Http\Controllers\Master\NoshiNamingPatternController::class, 'new'])->middleware('custom_auth:10');
Route::post('master/noshi-naming-patterns/new', [App\Http\Controllers\Master\NoshiNamingPatternController::class, 'postNew'])->middleware('custom_auth:10');
Route::get('master/noshi-naming-patterns/edit/{id}', [App\Http\Controllers\Master\NoshiNamingPatternController::class, 'edit'])->middleware('custom_auth:10')->name('noshi.namingpattern.edit');
Route::post('master/noshi-naming-patterns/edit/{id}', [App\Http\Controllers\Master\NoshiNamingPatternController::class, 'postEdit'])->middleware('custom_auth:10');

// 熨斗マスタ
Route::get('master/noshi/list', [App\Http\Controllers\Master\NoshiController::class, 'list'])->middleware('custom_auth:10')->name('master.noshi.list');
Route::post('master/noshi/list', [App\Http\Controllers\Master\NoshiController::class, 'list'])->middleware('custom_auth:10')->name('master.noshi.search');
Route::get('master/noshi/new', [App\Http\Controllers\Master\NoshiController::class, 'new'])->middleware('custom_auth:10')->name('master.noshi.new');
Route::post('master/noshi/new', [App\Http\Controllers\Master\NoshiController::class, 'postNew'])->middleware('custom_auth:10');
Route::get('master/noshi/edit/{id}', [App\Http\Controllers\Master\NoshiController::class, 'edit'])->middleware('custom_auth:10')->name('master.noshi.edit');
Route::post('master/noshi/edit/{id}', [App\Http\Controllers\Master\NoshiController::class, 'postEdit'])->middleware('custom_auth:10');

// 熨斗テンプレートマスタ
Route::get('master/noshi-templates/{id}', [App\Http\Controllers\Master\NoshiTemplateController::class, 'edit'])->middleware('custom_auth:10')->name('master.noshi.template'); //熨斗テンプレート
Route::get('master/api/noshi-templates/list', [App\Http\Controllers\Master\Api\NoshiTemplateController::class, 'list'])->middleware('custom_auth:10')->name('master.noshi-templates.list'); //熨斗詳細のリスト
Route::post('master/api/noshi-templates/update', [App\Http\Controllers\Master\Api\NoshiTemplateController::class, 'update'])->middleware('custom_auth:10')->name('master.noshi-templates.update');
Route::get('master/api/noshi-templates/download', [App\Http\Controllers\Master\Api\NoshiTemplateController::class, 'download'])->middleware('custom_auth:10')->name('master.noshi-templates.download');

// 出荷一覧検索
Route::get('order/shipping_order/list', [App\Http\Controllers\Order\ShippingOrderController::class, 'list'])->name('order.shipping_order.list')->middleware('custom_auth:10');
Route::post('order/shipping_order/list', [App\Http\Controllers\Order\ShippingOrderController::class, 'postList'])->name('order.shipping_order.post-list')->middleware('custom_auth:10');

/**
 * サンプルプログラム
 * 同じURLを持ちつつ、処理が異なる場合は、コントローラー内で切り分けずに、HTTPメソッドで切り分けるのが理想
 */
Route::get('sample/sample/list', [App\Http\Controllers\Sample\SampleController::class, 'list'])->name('sample.sample.list')->middleware('custom_auth:10');
Route::post('sample/sample/list', [App\Http\Controllers\Sample\SampleController::class, 'postList'])->name('sample.sample.post-list')->middleware('custom_auth:10');
Route::get('sample/sample/new', [App\Http\Controllers\Sample\SampleController::class, 'new'])->name('sample.sample.new')->middleware('custom_auth:10');
Route::post('sample/sample/new', [App\Http\Controllers\Sample\SampleController::class, 'postNew'])->name('sample.sample.post-new')->middleware('custom_auth:10');
Route::get('sample/sample/edit/{id}', [App\Http\Controllers\Sample\SampleController::class, 'edit'])->name('sample.sample.edit')->middleware('custom_auth:10');
Route::post('sample/sample/edit/{id}', [App\Http\Controllers\Sample\SampleController::class, 'postEdit'])->name('sample.sample.post-edit')->middleware('custom_auth:10');
Route::get('sample/sample/notify', [App\Http\Controllers\Sample\SampleController::class, 'notify'])->name('sample.sample.notify')->middleware('custom_auth:10');
Route::post('sample/sample/notify', [App\Http\Controllers\Sample\SampleController::class, 'postNotify'])->name('sample.sample.post-notify')->middleware('custom_auth:10');
Route::put('sample/sample/notify', [App\Http\Controllers\Sample\SampleController::class, 'putNotify'])->name('sample.sample.put-notify')->middleware('custom_auth:10');
Route::delete('sample/sample/notify', [App\Http\Controllers\Sample\SampleController::class, 'deleteNotify'])->name('sample.sample.delete-notify')->middleware('custom_auth:10');
// 他のサンプル画面からは遷移しないので、遷移する場合は、ブラウザで直接URLを入力すること
Route::get('sample/sample/info/{id}', [App\Http\Controllers\Sample\SampleController::class, 'info'])->name('sample.sample.info')->middleware('custom_auth:10');


Route::get('/middleware-list', function (\Illuminate\Routing\Router $router) {
    $globalMiddleware = $router->getMiddleware();
    $routeMiddleware = $router->getMiddlewareGroups();
    $namedMiddleware = $router->getMiddleware();

    return response()->json([
        'global_middleware' => $globalMiddleware,
        'route_middleware' => $routeMiddleware,
        'named_middleware' => $namedMiddleware,
    ]);
});

//キャンペーン画面一覧について
Route::get('master/campaign/list', [App\Http\Controllers\Master\CampaignListController::class, 'list'])
->middleware('custom_auth:10')
->name('campaign.list');
Route::post('master/campaign/list', [App\Http\Controllers\Master\CampaignListController::class, 'list'])->middleware('custom_auth:10');
// キャンペーン新規登録
Route::get('master/campaign/new', [App\Http\Controllers\Master\CampaignListController::class, 'new'])
->middleware('custom_auth:10')
->name('campaign.new'); // ← ここで `name` メソッドを使用してルートに名前を付ける
Route::post('master/campaign/new', [App\Http\Controllers\Master\CampaignListController::class, 'postNew'])
->middleware('custom_auth:10');
// キャンペーン修正(編集)
Route::get('master/campaign/edit/{id?}', [App\Http\Controllers\Master\CampaignListController::class, 'edit'])->middleware('custom_auth:10')->name('campaign.edit');
Route::post('campaign/edit/{id?}', [App\Http\Controllers\Master\CampaignListController::class, 'postEdit'])->middleware('custom_auth:10');
// キャンペーン確認画面
Route::get('master/campaign/notify', [App\Http\Controllers\Master\CampaignListController::class, 'notify'])
    ->middleware('custom_auth:10')
    ->name('campaign.notify');

// 確認画面からのPOSTリクエストを処理
Route::post('master/campaign/notify', [App\Http\Controllers\Master\CampaignListController::class, 'postNotify'])
    ->middleware('custom_auth:10')
    ->name('campaign.postNotify');

Route::get('master/operators/list', [App\Http\Controllers\Master\OperatorsController::class, 'list'])->name('master.operators.list')->middleware('custom_auth:10');
Route::post('master/operators/list', [App\Http\Controllers\Master\OperatorsController::class, 'postList'])->name('master.operators.post-list')->middleware('custom_auth:10');
Route::get('master/operators/new', [App\Http\Controllers\Master\OperatorsController::class, 'new'])->name('master.operators.new')->middleware('custom_auth:10');
Route::post('master/operators/new', [App\Http\Controllers\Master\OperatorsController::class, 'postNew'])->name('master.operators.post-new')->middleware('custom_auth:10');
Route::get('master/operators/edit/{id}', [App\Http\Controllers\Master\OperatorsController::class, 'edit'])->name('master.operators.edit')->middleware('custom_auth:10');
Route::post('master/operators/edit/{id}', [App\Http\Controllers\Master\OperatorsController::class, 'postEdit'])->name('master.operators.post-edit')->middleware('custom_auth:10');
Route::get('master/operators/notify', [App\Http\Controllers\Master\OperatorsController::class, 'notify'])->name('master.operators.notify')->middleware('custom_auth:10');
Route::post('master/operators/notify', [App\Http\Controllers\Master\OperatorsController::class, 'postNotify'])->name('master.operators.post-notify')->middleware('custom_auth:10');
Route::put('master/operators/notify', [App\Http\Controllers\Master\OperatorsController::class, 'putNotify'])->name('master.operators.put-notify')->middleware('custom_auth:10');

// 確認画面からupdate
Route::post('master/campaign/update', [App\Http\Controllers\Master\CampaignListController::class, 'update'])
    ->middleware('custom_auth:10')
    ->name('campaign.update');

Route::get('master/payment_types/list', [App\Http\Controllers\Master\PaymentTypesController::class, 'list'])->name('master.payment_types.list')->middleware('custom_auth:10');
Route::post('master/payment_types/list', [App\Http\Controllers\Master\PaymentTypesController::class, 'postList'])->name('master.payment_types.post-list')->middleware('custom_auth:10');
Route::get('master/payment_types/new', [App\Http\Controllers\Master\PaymentTypesController::class, 'new'])->name('master.payment_types.new')->middleware('custom_auth:10');
Route::post('master/payment_types/new', [App\Http\Controllers\Master\PaymentTypesController::class, 'postNew'])->name('master.payment_types.post-new')->middleware('custom_auth:10');
Route::get('master/payment_types/edit/{id}', [App\Http\Controllers\Master\PaymentTypesController::class, 'edit'])->name('master.payment_types.edit')->middleware('custom_auth:10');
Route::post('master/payment_types/edit/{id}', [App\Http\Controllers\Master\PaymentTypesController::class, 'postEdit'])->name('master.payment_types.post-edit')->middleware('custom_auth:10');
Route::get('master/payment_types/notify', [App\Http\Controllers\Master\PaymentTypesController::class, 'notify'])->name('master.payment_types.notify')->middleware('custom_auth:10');
Route::post('master/payment_types/notify', [App\Http\Controllers\Master\PaymentTypesController::class, 'postNotify'])->name('master.payment_types.post-notify')->middleware('custom_auth:10');
Route::put('master/payment_types/notify', [App\Http\Controllers\Master\PaymentTypesController::class, 'putNotify'])->name('master.payment_types.put-notify')->middleware('custom_auth:10');

//付属品マスタ画面一覧について
Route::get('ami/attachment_item/list', [App\Http\Controllers\Ami\AttachmentitemListController::class, 'list'])
->middleware('custom_auth:10')
->name('attachment_item.list');
Route::post('ami/attachment_item/list', [App\Http\Controllers\Ami\AttachmentitemListController::class, 'postList'])->middleware('custom_auth:10');

// 付属品マスタ新規登録
Route::get('ami/attachment_item/new', [App\Http\Controllers\Ami\AttachmentitemListController::class, 'new'])
->middleware('custom_auth:10')
->name('attachment_item.new'); // ← ここで `name` メソッドを使用してルートに名前を付ける
Route::post('ami/attachment_item/new', [App\Http\Controllers\Ami\AttachmentitemListController::class, 'postNew'])
->middleware('custom_auth:10');

// 付属品マスタ修正(編集)
Route::get('ami/attachment_item/edit/{id?}', [App\Http\Controllers\Ami\AttachmentitemListController::class, 'edit'])->middleware('custom_auth:10')->name('attachment_item.edit');
Route::post('attachment_item/edit/{id?}', [App\Http\Controllers\Ami\AttachmentitemListController::class, 'postEdit'])->middleware('custom_auth:10');

// 付属品マスタ確認画面
Route::get('ami/attachment_item/notify', [App\Http\Controllers\Ami\AttachmentitemListController::class, 'notify'])
    ->middleware('custom_auth:10')
    ->name('attachment_item.notify');

// 付属品マスタ確認画面からのPOSTリクエストを処理
Route::post('ami/attachment_item/notify', [App\Http\Controllers\Ami\AttachmentitemListController::class, 'postNotify'])
    ->middleware('custom_auth:10')
    ->name('attachment_item.postNotify');


// 付属品マスタ確認画面からupdate
Route::post('ami/attachment_item/update', [App\Http\Controllers\Ami\AttachmentitemListController::class, 'update'])
    ->middleware('custom_auth:10')
    ->name('attachment_item.update');

    // 見積書・納品書・請求書
    Route::get('billing/excel-report/list', [App\Http\Controllers\Billing\ExcelReportController::class, 'list'])->name('billing.excel-report.list')->middleware('custom_auth:10');
    Route::post('billing/excel-report/list', [App\Http\Controllers\Billing\ExcelReportController::class, 'search'])->name('billing.excel-report.search')->middleware('custom_auth:10');

// });
