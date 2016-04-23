<?php

namespace Maruamyu\Core\OAuth;

use Maruamyu\Core\Http\Message\NormalizeMessageTrait;
use Maruamyu\Core\Http\Message\QueryString;
use Maruamyu\Core\Http\Message\UriInterface;

/**
 * OAuth HMAC-SHA1署名 処理クラス
 */
class HmacSha1Signer implements SignerInterface
{
    use NormalizeMessageTrait;

    /**
     * @var ConsumerKey
     */
    private $consumerKey;

    /**
     * @var AccessToken
     */
    private $accessToken;

    /**
     * インスタンスを初期化する.
     *
     * @param ConsumerKey $consumerKey ConsumerKey
     * @param AccessToken $accessToken AccessToken
     */
    public function __construct(ConsumerKey $consumerKey, AccessToken $accessToken = null)
    {
        $this->consumerKey = $consumerKey;
        $this->accessToken = $accessToken;
    }

    /**
     * @return string 署名生成方式
     */
    public function getSignatureMethod()
    {
        return 'HMAC-SHA1';
    }

    /**
     * @param string $method メソッド
     * @param string|UriInterface $uri URL
     * @param array|QueryString $params リクエストパラメータ
     * @param array $headerParams Authorizationヘッダのパラメータ
     * @return string 署名
     */
    public function makeSignature($method, $uri, $params, $headerParams = null)
    {
        $method = static::normalizeMethod($method);
        $uri = static::normalizeUri($uri);

        $message = static::normalizeQueryString($params);
        if ($headerParams) {
            $message->merge(static::normalizeQueryString($headerParams));
        }

        # パラメータをQUERY_STRINGで処理するメソッド
        if ($method === 'GET' || $method === 'HEAD') {
            $uriQueryString = $uri->getQueryString();
            if ($uriQueryString->hasAny()) {
                $uri = $uri->withQuery('');
                $message->merge($uriQueryString);
            }
        }

        $message->delete('oauth_signature');
        $message->delete('realm');

        $baseString = rawurlencode($method)
            . '&' . rawurlencode(strval($uri))
            . '&' . rawurlencode($message->toOAuthQueryString());

        $salt = rawurlencode($this->consumerKey->getTokenSecret()) . '&';
        if ($this->accessToken) {
            $salt .= rawurlencode($this->accessToken->getTokenSecret());
        }

        return base64_encode(hash_hmac('sha1', $baseString, $salt, true));
    }

    /**
     * @param string $method メソッド
     * @param string|UriInterface $uri URL
     * @param array|QueryString $params リクエストパラメータ
     * @param array $headerParams Authorizationヘッダのパラメータ
     * @return boolean パラメータ内の署名が正しければtrue, それ以外はfalse
     */
    public function verify($method, $uri, $params, $headerParams = null)
    {
        $method = static::normalizeMethod($method);
        $uri = static::normalizeUri($uri);

        $message = static::normalizeQueryString($params);
        if ($headerParams) {
            if (isset($headerParams['realm'])) {
                unset($headerParams['realm']);
            }
            $message->merge(static::normalizeQueryString($headerParams));
        }

        list($signatureMethod) = $message->get('oauth_signature_method');
        if (strcasecmp($signatureMethod, $this->getSignatureMethod()) != 0) {
            return false;
        }

        list($origSignature) = $message->delete('oauth_signature');

        $signature = $this->makeSignature($method, $uri, $message);
        return (strcmp($signature, $origSignature) == 0);
    }
}
