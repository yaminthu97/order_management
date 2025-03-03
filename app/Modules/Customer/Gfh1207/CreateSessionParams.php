<?php
namespace App\Modules\Customer\Gfh1207;

use App\Events\ModuleCompleted;
use App\Events\ModuleFailed;
use App\Events\ModuleStarted;
use App\Modules\Customer\Base\CreateSessionParamsInterface;
use PhpParser\Node\Expr\AssignOp\Mod;

class CreateSessionParams implements CreateSessionParamsInterface
{
    public function execute(array $params): array
    {
        ModuleStarted::dispatch(__CLASS__, compact('params'));
        try {
            $params['m_cust_id'] = $params['m_cust_id'] ?? null;
            $result = [
                'tel' => $params['tel1'],
                'name_kanji' => $params['name_kanji'],
                'name_kana' => $params['name_kana'],
                'postal' => $params['postal'],
                'address1' => $params['address1'],
                'address2' => $params['address2'],
                'address3' => $params['address3'],
                'address4' => $params['address4'],
                'email' => $params['email1'],
                'previous_subsys' => 'cc',
                'previous_url' => url()->current(),
                'm_cust_id' => $params['m_cust_id'],
            ];
            ModuleCompleted::dispatch(__CLASS__, compact('result'));
            return $result;
        } catch (\Exception $e) {
            ModuleFailed::dispatch(__CLASS__, [$params], $e);
            throw $e;
        }
    }
}
