<?php
namespace App\Services;

class AccountViewFactory
{
    public function __construct(
        protected EsmSessionManager $esmSessionManager
    ){}

    public function account_view($view = null, $data = [], $mergeData = []):\Illuminate\Contracts\View\View|\Illuminate\Contracts\View\Factory
    {
        $accountCode = $this->esmSessionManager->getAccountCode();
        // $viewのbaseを$accountCodeに変更する
        $accountView = str_replace('base', $accountCode, $view);
        // 存在確認
        if(view()->exists($accountView)){
            return view($accountView, $data, $mergeData);
        }else{
            return view($view, $data, $mergeData);
        }
    }
}
