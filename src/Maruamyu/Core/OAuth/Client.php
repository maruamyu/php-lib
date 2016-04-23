<?php

namespace Maruamyu\Core\OAuth;

use Maruamyu\Core\Http\Driver\DriverFactory;
use Maruamyu\Core\Http\Driver\DriverInterface;
use Maruamyu\Core\Http\Message\Header;
use Maruamyu\Core\Http\Message\NormalizeMessageTrait;
use Maruamyu\Core\Http\Message\QueryString;
use Maruamyu\Core\Http\Message\Request;
use Maruamyu\Core\Http\Message\Response;
use Maruamyu\Core\Http\Message\UriInterface;

/**
 * OAuth1.0クライアント
 */
class Client
{
    use NormalizeMessageTrait;

    /**
     * @var ConsumerKey
     */
    protected $consumerKey;

    /**
     * @var AccessToken
     */
    protected $accessToken;

    /**
     * @var DriverFactory
     */
    protected $httpDriverFactory = null;

    /**
     * インスタンスを初期化する.
     *
     * @param ConsumerKey $consumerKey ConsumerKey
     * @throws \InvalidArgumentException 指定されたパラメータが正しくないとき
     */
    public function __construct(ConsumerKey $consumerKey)
    {
        static::checkLoadedExtension();
        if ($consumerKey instanceof ConsumerKey) {
            $this->consumerKey = clone $consumerKey;
        } else {
            throw new \InvalidArgumentException('invalid consumer key.');
        }
    }

    /**
     * clone時のデータコピー.
     */
    public function __clone()
    {
        $this->consumerKey = clone $this->consumerKey;
        if ($this->accessToken) {
            $this->accessToken = clone $this->accessToken;
        }
    }

    /**
     * AccessTokenを設定する.
     *
     * @param AccessToken $accessToken AccessToken
     */
    public function setAccessToken(AccessToken $accessToken)
    {
        if ($accessToken instanceof AccessToken) {
            $this->accessToken = clone $accessToken;
        } else {
            throw new \InvalidArgumentException('invalid access token.');
        }
    }

    /**
     * AccessTokenを削除する.
     */
    public function setNullAccessToken()
    {
        $this->accessToken = null;
    }

    /**
     * 内部にAccessTokenを持っているかどうか確認する.
     *
     * @return boolean AccessTokenを持っているならtrue, それ以外はfalse
     */
    public function hasAccessToken()
    {
        return !!($this->accessToken);
    }

    /**
     * OAuthの署名付きリクエストを実行する.
     *
     * @param string $method メソッド
     * @param string|UriInterface $uri URL
     * @param array|QueryString $params パラメータ
     * @return Response レスポンス
     * @throws \InvalidArgumentException 指定されたパラメータが正しくないとき
     */
    public function doRequest($method, $uri, $params = null)
    {
        $httpRequest = $this->makeRequest($method, $uri, $params);
        $httpDriver = $this->getHttpDriver($httpRequest);
        return $httpDriver->execute();
    }

    /**
     * OAuthの署名付きリクエストメッセージを生成する.
     *
     * @param string $method メソッド
     * @param string|UriInterface $uri URL
     * @param array|QueryString $params パラメータ
     * @param boolean $notUseAuthorizationHeader Authorizationヘッダを使わないときtrueを指定
     *   (trueの場合, QUERY_STRINGまたはPOSTデータに含める)
     * @return Request リクエストメッセージ
     * @throws \InvalidArgumentException 指定されたパラメータが正しくないとき
     */
    public function makeRequest($method, $uri, $params = null, $notUseAuthorizationHeader = false)
    {
        $method = static::normalizeMethod($method);
        $uri = static::normalizeUri($uri);
        $params = static::normalizeQueryString($params);

        # パラメータをQUERY_STRINGで処理するメソッド
        $isQueryStringOnly = ($method === 'GET' || $method === 'HEAD');

        if ($isQueryStringOnly) {
            $uriQueryString = $uri->getQueryString();
            if ($uriQueryString->hasAny()) {
                $uri = $uri->withQuery('');
                $params->merge($uriQueryString);
            }
        }

        $authorization = $this->makeAuthorization($method, $uri, $params);

        $header = new Header();
        if ($notUseAuthorizationHeader) {
            foreach ($authorization as $key => $value) {
                $params->set($key, $value, true);
            }
        } else {
            $authorization['realm'] = $uri->getScheme() . '://' . $uri->getHost() . '/';
            $header->set('Authorization', AuthorizationHeader::build($authorization));
        }

        if ($isQueryStringOnly) {
            $requestBody = '';
            $uri = $uri->withQueryString($params);
        } else {
            $requestBody = $params->toString();
        }

        return new Request($method, $uri, $requestBody, $header);
    }

    /**
     * Authorizationヘッダのauth-paramsを生成する.
     *
     * @param string $method メソッド
     * @param string|UriInterface $uri URL
     * @param array|QueryString $params パラメータ
     * @return array Authorizationヘッダの値
     * @throws \InvalidArgumentException 指定されたパラメータが正しくないとき
     */
    public function makeAuthorization($method, $uri, $params = null)
    {
        $signer = $this->getSigner();
        $authParams = $this->createAuthParams();
        $authParams['oauth_signature_method'] = $signer->getSignatureMethod();
        $authParams['oauth_signature'] = $signer->makeSignature($method, $uri, $params, $authParams);
        return $authParams;
    }

    /**
     * @return string OAuthのバージョン
     */
    protected function getVersion()
    {
        return '1.0';
    }

    /**
     * @return SignerInterface 署名生成クラスのインスタンス
     */
    protected function getSigner()
    {
        return $this->getHmacSha1Signer();
    }

    /**
     * @return HmacSha1Signer HMAC-SHA1署名生成クラスのインスタンス
     */
    protected function getHmacSha1Signer()
    {
        return new HmacSha1Signer($this->consumerKey, $this->accessToken);
    }

    /**
     * 認証パラメータの生成
     *
     * @return array 認証パラメータ
     */
    protected function createAuthParams()
    {
        $authParams = $this->createOneTimeAuthParams();
        $authParams['oauth_version'] = $this->getVersion();
        $authParams['oauth_consumer_key'] = $this->consumerKey->getToken();
        if ($this->accessToken) {
            $authParams['oauth_token'] = $this->accessToken->getToken();
        }
        return $authParams;
    }

    /**
     * nonceおよびtimestampの生成
     *
     * @return array 認証パラメータ
     */
    protected function createOneTimeAuthParams()
    {
        return [
            'oauth_timestamp' => strval(time()),
            'oauth_nonce' => bin2hex(openssl_random_pseudo_bytes(32)),
        ];
    }

    /**
     * @param Request $request リクエスト
     * @return DriverInterface HTTP処理クラス
     */
    protected function getHttpDriver(Request $request = null)
    {
        if (!$this->httpDriverFactory) {
            $this->httpDriverFactory = new DriverFactory();
        }
        return $this->httpDriverFactory->getDriver($request);
    }

    /**
     * 必須モジュールのチェック.
     *
     * @throws \RuntimeException 必要なモジュールがロードされていないとき
     */
    protected static function checkLoadedExtension()
    {
        if (!extension_loaded('openssl')) {
            throw new \RuntimeException('OpenSSL module not found');
        }
    }
}
