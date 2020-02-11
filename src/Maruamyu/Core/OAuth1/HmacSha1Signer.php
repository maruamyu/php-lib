<?php

namespace Maruamyu\Core\OAuth1;

use Maruamyu\Core\Http\Message\NormalizeMessageTrait;
use Maruamyu\Core\Http\Message\QueryString;
use Maruamyu\Core\Http\Message\UriInterface;

/**
 * OAuth1 HMAC-SHA1 signature operations
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
     * @param ConsumerKey $consumerKey ConsumerKey
     * @param AccessToken $accessToken AccessToken
     */
    public function __construct(ConsumerKey $consumerKey, AccessToken $accessToken = null)
    {
        $this->consumerKey = $consumerKey;
        $this->accessToken = $accessToken;
    }

    /**
     * @return string oauth_signature_method
     */
    public function getSignatureMethod()
    {
        return 'HMAC-SHA1';
    }

    /**
     * @param string $method HTTP Method
     * @param string|UriInterface $uri URL
     * @param array|QueryString $params request parameters
     * @param array $headerParams Authorization header parameters
     * @return string signature
     */
    public function sign($method, $uri, $params, $headerParams = null)
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
     * @param string $method HTTP Method
     * @param string|UriInterface $uri URL
     * @param array|QueryString $params request parameters
     * @param array $headerParams Authorization header parameters
     * @return bool true if valid signature in params, else false
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

        $salt = rawurlencode($this->consumerKey->getSecret()) . '&';
        if ($this->accessToken) {
            $salt .= rawurlencode($this->accessToken->getTokenSecret());
        }

        return base64_encode(hash_hmac('sha1', $baseString, $salt, true));
    }
}
