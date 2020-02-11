<?php

namespace Maruamyu\Core\OAuth1;

use Maruamyu\Core\Http\Message\Headers;
use Maruamyu\Core\Http\Message\NormalizeMessageTrait;
use Maruamyu\Core\Http\Message\QueryString;
use Maruamyu\Core\Http\Message\Request;
use Maruamyu\Core\Http\Message\UriInterface;

/**
 * OAuth 1.0 Core Logic
 */
class CoreLogic
{
    use NormalizeMessageTrait;

    const DEFAULT_SIGNATURE_METHOD = 'HMAC-SHA1';

    const SUPPORTED_SIGNATURE_METHODS = [
        'HMAC-SHA1',
        'RSA-SHA1',
        'PLAINTEXT',
    ];

    /**
     * @var ConsumerKey
     */
    protected $consumerKey;

    /**
     * @var AccessToken
     */
    protected $accessToken;

    /**
     * @var RsaSha1Signer
     */
    protected $rsaSha1Signer;

    /**
     * @var string
     */
    protected $signatureMethod;

    /**
     * initialize instance
     *
     * @param ConsumerKey $consumerKey ConsumerKey
     * @param string $signatureMethod signature method
     *   (if null, then {DEFAULT_SIGNATURE_METHOD})
     * @throws \InvalidArgumentException if invalid args
     */
    public function __construct(ConsumerKey $consumerKey, $signatureMethod = null)
    {
        static::checkLoadedExtension();
        if ($consumerKey instanceof ConsumerKey) {
            $this->consumerKey = clone $consumerKey;
        } else {
            throw new \InvalidArgumentException('invalid consumer key.');
        }
        if ($signatureMethod) {
            $this->setSignatureMethod($signatureMethod);
        } else {
            $this->signatureMethod = static::DEFAULT_SIGNATURE_METHOD;
        }
    }

    /**
     * clone data
     */
    public function __clone()
    {
        $this->consumerKey = clone $this->consumerKey;
        if ($this->accessToken) {
            $this->accessToken = clone $this->accessToken;
        }
    }

    /**
     * set AccessToken
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
     * remove AccessToken
     */
    public function setNullAccessToken()
    {
        $this->accessToken = null;
    }

    /**
     * return true if have AccessToken
     *
     * @return bool true if have AccessToken, else false
     */
    public function hasAccessToken()
    {
        return !!($this->accessToken);
    }

    /**
     * set RSA key pair
     *
     * @param string|resource $publicKey public key
     * @param string|resource $privateKey private key
     * @param string $passphrase passphrase of private key
     * @throws \InvalidArgumentException if invalid key data
     */
    public function setRsaKey($publicKey, $privateKey = null, $passphrase = null)
    {
        $this->rsaSha1Signer = new RsaSha1Signer($publicKey, $privateKey, $passphrase);
    }

    /**
     * return signature method
     *
     * @return string signature method
     */
    public function getSignatureMethod()
    {
        return $this->signatureMethod;
    }

    /**
     * set signature method
     *
     * @param string $signatureMethod signature method
     * @throws \InvalidArgumentException if invalid signature method
     */
    public function setSignatureMethod($signatureMethod)
    {
        if (!in_array($signatureMethod, static::SUPPORTED_SIGNATURE_METHODS)) {
            throw new \InvalidArgumentException('invalid signature method: ' . $signatureMethod);
        }
        $this->signatureMethod = $signatureMethod;
    }

    /**
     * create request message with auth-params and signature
     *
     * @param string $method HTTP method
     * @param string|UriInterface $uri URL
     * @param array|QueryString $params form data
     * @param bool $notUseAuthorizationHeader if true, then auth-params into QUERY_STRING or form data
     * @return Request request message
     * @throws \InvalidArgumentException if invalid args
     */
    public function makeRequest($method, $uri, $params = null, $notUseAuthorizationHeader = false)
    {
        $method = static::normalizeMethod($method);
        $uri = static::normalizeUri($uri);
        $params = static::normalizeQueryString($params);

        $isQueryStringOnly = ($method === 'GET' || $method === 'HEAD');

        if ($isQueryStringOnly) {
            $uriQueryString = $uri->getQueryString();
            if ($uriQueryString->hasAny()) {
                $uri = $uri->withQuery('');
                $params->append($uriQueryString);
            }
        }

        $authorization = $this->makeAuthorization($method, $uri, $params);

        $headers = new Headers();
        if ($notUseAuthorizationHeader) {
            foreach ($authorization as $key => $value) {
                $params->set($key, $value);
            }
        } else {
            $authorization['realm'] = $uri->getScheme() . '://' . $uri->getHost() . '/';
            $headers->set('Authorization', AuthorizationHeader::build($authorization));
        }

        if ($isQueryStringOnly) {
            $requestBody = '';
            $uri = $uri->withQueryString($params);
        } else {
            $requestBody = $params->toString();
            $headers->set('Content-Type', 'application/x-www-form-urlencoded');
        }

        return new Request($method, $uri, $requestBody, $headers);
    }

    /**
     * create auth-params for Authorization header (with signature)
     *
     * @param string $method HTTP methods
     * @param string|UriInterface $uri URL
     * @param array|QueryString $params form data
     * @return array auth-params for Authorization header
     * @throws \InvalidArgumentException if invalid args
     */
    public function makeAuthorization($method, $uri, $params = null)
    {
        $signer = $this->getSigner($this->getSignatureMethod());
        $authParams = $this->createAuthParams();
        $authParams['oauth_signature_method'] = $signer->getSignatureMethod();
        $authParams['oauth_signature'] = $signer->sign($method, $uri, $params, $authParams);
        return $authParams;
    }

    /**
     * create auth-params (without signature)
     *
     * @return array part of auth-params
     */
    protected function createAuthParams()
    {
        $authParams = $this->createOneTimeAuthParams();
        $authParams['oauth_version'] = $this->getVersion();
        $authParams['oauth_consumer_key'] = $this->consumerKey->getKey();
        if ($this->accessToken) {
            $authParams['oauth_token'] = $this->accessToken->getToken();
        }
        return $authParams;
    }

    /**
     * create onetime params (nonce and timestamp)
     *
     * @return array params
     */
    protected function createOneTimeAuthParams()
    {
        return [
            'oauth_timestamp' => strval(time()),
            'oauth_nonce' => bin2hex(openssl_random_pseudo_bytes(32)),
        ];
    }

    /**
     * @return string OAuth version
     */
    protected function getVersion()
    {
        return '1.0';
    }

    /**
     * get signature generator instance
     *
     * @param string $signatureMethod signature method
     * @return SignerInterface signature generator instance
     * @throws \InvalidArgumentException if unsupported method
     * @throws \RuntimeException if without required params
     */
    protected function getSigner($signatureMethod)
    {
        switch ($signatureMethod) {
            case 'HMAC-SHA1':
                return new HmacSha1Signer($this->consumerKey, $this->accessToken);
            case 'RSA-SHA1':
                if (!$this->rsaSha1Signer) {
                    throw new \RuntimeException('RSA key not set yet.');
                }
                return clone $this->rsaSha1Signer;
            case 'PLAINTEXT':
                return new PlaintextSigner($this->consumerKey, $this->accessToken);
            default:
                throw new \InvalidArgumentException('unsupported: ' . $signatureMethod);
        }
    }

    /**
     * throw exception if not loaded required extension
     *
     * @throws \RuntimeException if not loaded required extension
     */
    protected static function checkLoadedExtension()
    {
        if (!extension_loaded('openssl')) {
            throw new \RuntimeException('OpenSSL module not found');
        }
    }
}