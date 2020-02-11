<?php

namespace Maruamyu\Core\OAuth1;

use Maruamyu\Core\Http\Message\NormalizeMessageTrait;
use Maruamyu\Core\Http\Message\QueryString;
use Maruamyu\Core\Http\Message\UriInterface;

/**
 * OAuth 1.0 PLAINTEXT signature operations
 */
class PlaintextSigner implements SignerInterface
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
     * initialize instance
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
     * @return string oauth_signature_method
     */
    public function getSignatureMethod()
    {
        return 'PLAINTEXT';
    }

    /**
     * @param string $method HTTP method (void)
     * @param string|UriInterface $uri URL (void)
     * @param array|QueryString $params form params (void)
     * @param array $headerParams Authorization header params (void)
     * @return string signature
     */
    public function sign($method, $uri, $params, $headerParams = null)
    {
        $salt = rawurlencode($this->consumerKey->getSecret()) . '&';
        if ($this->accessToken) {
            $salt .= rawurlencode($this->accessToken->getTokenSecret());
        }
        return $salt;
    }

    /**
     * @param string $method HTTP method
     * @param string|UriInterface $uri URL
     * @param array|QueryString $params form params
     * @param array $headerParams Authorization header params
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

        $signature = $this->sign($method, $uri, $params, $headerParams);
        return (strcmp($signature, $origSignature) == 0);
    }
}
