<?php

namespace App\Http\Controllers\Customer;

use App\Modules\Customer\Base\CreateSessionParamsInterface;
use App\Modules\Customer\Base\Enums\CustomerInfoSubmitType;
use App\Modules\Customer\Base\FindCustomerInfoInterface;
use App\Modules\Customer\Base\SearchCustCommunicationInterface;
use App\Modules\Order\Base\SearchInterface;
use App\Modules\Order\Base\SerchMailSendHistoryInterface;
use App\Services\EsmSessionManager;
use Illuminate\Http\Request;
use Symfony\Component\Routing\Exception\InvalidParameterException;

class CustmerInfoController
{
    //
    public function __construct(
        protected EsmSessionManager $esmSessionManager
    ){}

    public function info(
        Request $request,
        FindCustomerInfoInterface $findCustomerInfo,
        SearchInterface $searchOrder,
        SearchCustCommunicationInterface $searchCustCommunication,
        SerchMailSendHistoryInterface $serchMailSendHistory,
        CreateSessionParamsInterface $createSessionParams
    )
    {
        $customer = $findCustomerInfo->execute($request->route('id'));
        $orders = $searchOrder->execute([
            'm_cust_id' => $customer->m_cust_id,
        ], [
            'should_paginate' => true,
            'limit' => 10,
            'page' => 1,
            'sorts' => [
                't_order_hdr_id' => 'desc',
            ],
        ]);
        $custCommunications = $searchCustCommunication->execute([
            'm_cust_id' => $customer->m_cust_id,
        ], [
            'should_paginate' => true,
            'limit' => 10,
            'page' => 1,
            'sorts' => [
                't_cust_communication_id' => 'desc',
            ],
        ]);
        $mailSendHistories = $serchMailSendHistory->execute([
            'm_account_id' => $this->esmSessionManager->getAccountId(),
            'm_cust_id' => $customer->m_cust_id,
        ],[
            'should_paginate' => true,
            'limit' => 10,
            'page' => 1,
            'sorts' => [
                't_mail_send_history_id' => 'desc',
            ],
            'with' => [
                'emailTemplates',
                'entryOperator',
            ]
        ]);

        $params = $createSessionParams->execute($customer->toArray());

        $encodedParams = $this->esmSessionManager->setSessionKeyName(
            config('define.cc.send_mail_parameter_session'),
            config('define.cc.session_key_id'),
            $params
        );
        return account_view('customer.base.info', [
            'customer' => $customer,
            'orders' => $orders,
            'custCommunications' => $custCommunications,
            'mailSendHistories' => $mailSendHistories,
            'params' => $encodedParams,
        ]);
    }

    public function postInfo(
        Request $request,
        FindCustomerInfoInterface $findCustomerInfo,
        CreateSessionParamsInterface $createSessionParams,
    )
    {
        $customer = $findCustomerInfo->execute($request->route('id'));
        $params = $createSessionParams->execute($customer->toArray());
        $encodedParams = $this->esmSessionManager->setSessionKeyName(
            config('define.cc.send_mail_parameter_session'),
            config('define.cc.session_key_id'),
            $params
        );
        $submit = $request->input('submit');
        switch ($submit) {
            case CustomerInfoSubmitType::MAILNEW->value:
                return redirect(esm_external_route('order/mail-send/new', ['params' => $encodedParams]));
            case CustomerInfoSubmitType::CUSTOMERHISTORYNEW->value:
                return redirect()->route('cc.customer-history.new')->withInput($params);
            case CustomerInfoSubmitType::ORDERNEW->value:
                return redirect()->route('order.order.new', ['params' => base64_encode(json_encode($params))]);
            case CustomerInfoSubmitType::CCCUSTOMERMAIL->value:
                return redirect(esm_external_route('cc-customer-mail/list', []))->withInput($params);
            case CustomerInfoSubmitType::CCCUSTOMERORDER->value:
                return redirect(esm_external_route('cc/cc-customer-order/list', []))->withInput($params);
            case CustomerInfoSubmitType::CUSTOMERHISTORYLIST->value:
                return redirect()->route('cc.customer-history.list')->withInput($params);
            case CustomerInfoSubmitType::CUSTOMEREDIT->value:
                return redirect()->route('cc.customer.edit', ['id' => $customer->m_cust_id]);
            case CustomerInfoSubmitType::CCCUSTOMERLIST->value:
                return redirect()->route('cc.customer.list');
            default:
                throw new InvalidParameterException('不正なリクエストです');
        }
    }

}
