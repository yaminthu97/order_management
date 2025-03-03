<?php


if (!function_exists('esm_external_route')) {
    function esm_external_route(string $url, array $params, string $method="GET"): string
    {
        // paramsのチェック
        // 関数の内容
        $baseUrl = config('esm.app_base_url');

        foreach ($params as $key => $value) {
            if (strpos($url, '{' . $key . '}') !== false) {
                // urLのセクションに{}で括られたurlパラメータがあれば、それを置換する
                $url = str_replace('{' . $key . '}', $value, $url);
            }else{
                // ない場合かつHttpメソッドがGETの場合は、クエリパラメータとして追加する。最初のパラメータの場合は?、それ以降は&でつなぎ、URLに追加する
                if($method === 'GET'){
                    $url .= strpos($url, '?') === false ? '?' : '&';
                    $url .= $key . '=' . $value;
                }else{
                    // それ以外の場合は、無視する
                }

            }
        }

        return $baseUrl . $url;
    }
}

if(!function_exists('account_view')){
    function account_view($view = null, $data = [], $mergeData = []):\Illuminate\Contracts\View\View|\Illuminate\Contracts\View\Factory
    {
        /**
         * @var \App\Services\AccountViewFactory $accountViewFactory
         */
        $accountViewFactory = app()->make('App\Services\AccountViewFactory');
        return $accountViewFactory->account_view($view, $data, $mergeData);
    }
}

if (!function_exists('esm_internal_asset')) {
    function esm_internal_asset(string $url): string
    {
        // ゼグメントの取得が出来なくなったため直接パスを指定
        $segment = 'gfh';

        if ($segment) {
            $assetUrl = asset("/{$segment}/{$url}");
        } else {
            $assetUrl = asset($url);
        }
        // 環境により gfh が重複する場合は削除
        $assetUrl = str_replace('/gfh/gfh/', '/gfh/', $assetUrl);

        // プロトコル相対 URL に変換
        return '//'.str_replace(['http://', 'https://'], '', $assetUrl);
    }
}