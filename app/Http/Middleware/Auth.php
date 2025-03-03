<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class Auth
{
    private $_loginUrl = '/common/login';

    public function handle(Request $request, Closure $next, $menuType)
    {
        $req = $request->all();


        $authInfo = session()->get('OperatorInfo');

        session()->put('OperatorInfo', $authInfo);

        $this->_loginUrl = esm_external_route('common/login', []);

        // 認証情報が無い
        if (empty($authInfo)) {
            if(!empty($req)) {
                $req['redirect_url'] = url()->current();
                session()->put('outside_post_redirect', $req);
            }
            return redirect($this->_loginUrl);
        }

        // メニュー区分より権限を判定する
        $menuTypeArry = [];
        foreach ($authInfo['operation_authority_detail'] as $key => $value) {
            $menuTypeArry = $menuTypeArry + [ $value['menu_type'] => $value['available_flg'] ];
        }
        if (!array_key_exists($menuType, $menuTypeArry)) {
            throw new \App\Exceptions\AccessPermissionException();
        }

        // 社員IDまたは企業IDが設定されていない
        if (empty($authInfo['m_account_id']) || empty($authInfo['m_operators_id'])) {

            return redirect($this->_loginUrl);
        }

        return $next($request);
    }
}
