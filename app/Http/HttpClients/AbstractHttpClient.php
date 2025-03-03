<?php

namespace App\Http\HttpClients;

use GuzzleHttp;
use GuzzleHttp\Client;
use Psr\Http\Message\ResponseInterface;

/**
 *
 * @author 10205494
 *
 */
class AbstractHttpClient extends Client
{
    /**
     * baseUrl
     */
    protected $_baseUri = '';

    public function __construct(array $config = [])
    {
        if (isset($config['base_uri'])) {
            $this->_baseUri = GuzzleHttp\Psr7\uri_for($config['base_uri']);
        }

        parent::__construct($config);
    }


    // requestメソッドのシグネチャをGuzzleHttp\Clientと一致させる
    public function request(string $method, $uri = '', array $options = []): ResponseInterface
    {
        // 親クラスのメソッドを呼び出す
        return parent::request($method, $uri, $options);
    }

    /**
     * プロキシの設定をする
     */
    protected function _setProxy()
    {
        $proxyAddress = '';

        $proxyFlag = env('USE_PROXY', '0');

        if($proxyFlag) {
            $proxyAddress = env('HTTP_PROXY');

            $noProxyData = explode(',', env('NO_PROXY_SERVER'));

            foreach($noProxyData as $noProxyHost) {
                if(strpos($this->_baseUri, $noProxyHost) !== false) {
                    $proxyAddress = '';
                    break;
                }
            }
        }

        return $proxyAddress;
    }
}
