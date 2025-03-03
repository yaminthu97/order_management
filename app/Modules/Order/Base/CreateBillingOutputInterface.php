<?php

namespace App\Modules\Order\Base;

interface CreateBillingOutputInterface
{
    public function execute($billing_id, $template_id, $output_at,$account_id,$operator_id);
}
