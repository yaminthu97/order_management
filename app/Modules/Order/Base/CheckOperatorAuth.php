<?php

namespace App\Modules\Order\Base;

use App\Services\Esm2ApiManager;
use App\Services\EsmSessionManager;

class CheckOperatorAuth implements CheckOperatorAuthInterface
{
    /**
     * ESM2.0 APIマネージャー
     */
    protected $esm2ApiManager;

    /**
     * ESMセッション管理クラス
     */
    protected $esmSessionManager;
    public function __construct(Esm2ApiManager $esm2ApiManager, EsmSessionManager $esmSessionManager)
    {
        $this->esm2ApiManager = $esm2ApiManager;
        $this->esmSessionManager = $esmSessionManager;
    }

    public function execute($menuType)
    {
        // some logic here
        $authInfo = $this->esmSessionManager->getLoginSessionInfo('operation_authority_detail');
        $isExist = collect($authInfo)->contains(function ($auth) use ($menuType) {
            return ($auth['menu_type'] == $menuType && $auth['available_flg'] == '1');
        });
        return ($isExist) ? 1 : 0;
    }
}
