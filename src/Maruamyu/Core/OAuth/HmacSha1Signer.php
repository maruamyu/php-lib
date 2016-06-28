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

        $message = static::normalizeQueryString($headerParams);
        $message->delete('realm');

        $message->append(static::normalizeQueryString($params));

        $uriQueryString = $uri->getQueryString();
        if ($uriQueryString->hasAny()) {
            $uri = $uri->withQuery('');
            $message->append($uriQueryString);
        }

        $message->delete('oauth_signature');

        return $this->makeSignatureWithoutNormalize($method, $uri, $message);
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

        $message = static::normalizeQueryString($headerParams);
        $message->delete('realm');

        $message->append(static::normalizeQueryString($params));

        $uriQueryString = $uri->getQueryString();
        if ($uriQueryString->hasAny()) {
            $uri = $uri->withQuery('');
            $message->append($uriQueryString);
        }

        list($signatureMethod) = $message->get('oauth_signature_method');
        if (strcasecmp($signatureMethod, $this->getSignatureMethod()) != 0) {
            return false;
        }

        list($origSignature) = $message->delete('oauth_signature');

        $signature = $this->makeSignatureWithoutNormalize($method, $uri, $message);
        return (strcmp($signature, $origSignature) == 0);
    }

    /**
     * @param string $method HTTP method
     * @param UriInterface $uri URL
     * @param QueryString $message normalized request parameters
     * @return string signature
     */
    private function makeSignatureWithoutNormalize($method, UriInterface $uri, QueryString $message)
    {
        $baseString = rawurlencode($method)
            . '&' . rawurlencode(strval($uri))
            . '&' . rawurlencode($message->toOAuthQueryString());

        $salt = rawurlencode($this->consumerKey->getTokenSecret()) . '&';
        if ($this->accessToken) {
            $salt .= rawurlencode($this->accessToken->getTokenSecret());
        }

        return base64_encode(hash_hmac('sha1', $baseString, $salt, true));
    }
}
