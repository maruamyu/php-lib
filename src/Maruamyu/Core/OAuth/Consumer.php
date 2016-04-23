<?php

namespace Maruamyu\Core\OAuth;

use Maruamyu\Core\Http\Message\NormalizeMessageTrait;
use Maruamyu\Core\Http\Message\QueryString;
use Maruamyu\Core\Http\Message\UriInterface;
use Psr\Http\Message\ServerRequestInterface;

/**
 * OAuth1.0コンシューマ(サーバー)
 */
class Consumer
{
    use NormalizeMessageTrait;

    /**
     * @var ConsumerKey
     */
    protected $consumerKey;

    /**
     * @var AccessToken
     */
    private $accessToken;

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
     * リクエストの署名を検証する.
     *
     * @param ServerRequestInterface $serverRequest リクエスト情報
     * @return boolean 署名が正しければtrue, それ以外はfalse
     */
    public function verifySignature(ServerRequestInterface $serverRequest)
    {
        $authorization = $serverRequest->getHeaderLine('Authorization');
        if (strlen($authorization) > 0) {
            $authParams = AuthorizationHeader::parse($authorization);
        } else {
            $authParams = [];
        }
        $method = $serverRequest->getMethod();
        $uri = $serverRequest->getUri();
        $params = $serverRequest->getParsedBody();

        $signer = $this->getSigner();
        return $signer->verify($method, $uri, $params, $authParams);
    }

    /**
     * Authorizationヘッダのauth-paramsを生成する.
     *
     * @param string $method メソッド
     * @param string|UriInterface $uri URL
     * @param array|QueryString $params パラメータ
     * @param boolean $includeTokenSecret oauth_token_secretを含めるときtrue
     * @return array Authorizationヘッダのauth-params
     * @throws \InvalidArgumentException 指定されたパラメータが正しくないとき
     */
    public function makeAuthorization($method, $uri, $params = null, $includeTokenSecret = false)
    {
        $signer = $this->getSigner();
        $authParams = $this->createAuthParams();
        $authParams['oauth_signature_method'] = $signer->getSignatureMethod();
        if ($includeTokenSecret && $this->accessToken) {
            $authParams['oauth_token_secret'] = $this->accessToken->getTokenSecret();
        }
        $authParams['oauth_signature'] = $signer->makeSignature($method, $uri, $params, $authParams);
        return $authParams;
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
