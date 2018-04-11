<?php

namespace Maruamyu\Core\OAuth1;

use Maruamyu\Core\Http\Message\NormalizeMessageTrait;
use Maruamyu\Core\Http\Message\QueryString;
use Maruamyu\Core\Http\Message\UriInterface;

/**
 * OAuth 1.0 RSA-SHA1 signature operations
 */
class RsaSha1Signer implements SignerInterface
{
    use NormalizeMessageTrait;

    /**
     * @var string
     */
    private $publicKey;

    /***
     * @var string
     */
    private $privateKey;

    /**
     * key format: string or resource
     * string - PEM format
     * resource - return from openssl_pkey_get_*
     *
     * @param string|resource $publicKey
     * @param string|resource $privateKey
     * @param string $passphrase
     * @throws \InvalidArgumentException if invalid keys
     */
    public function __construct($publicKey, $privateKey = null, $passphrase = null)
    {
        $publicKeyResource = static::fetchPublicKey($publicKey);
        if (!$publicKeyResource) {
            throw new \InvalidArgumentException('invalid public key.');
        }
        $this->publicKey = $publicKeyResource;

        if ($privateKey) {
            $privateKeyResource = static::fetchPrivateKey($privateKey, $passphrase);
            if (!$privateKeyResource) {
                throw new \InvalidArgumentException('invalid private key.');
            }
            $this->privateKey = $privateKeyResource;
        }
    }

    /**
     * @return string oauth_signature_method
     */
    public function getSignatureMethod()
    {
        return 'RSA-SHA1';
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
        if (!$this->privateKey) {
            throw new \RuntimeException('private key required.');
        }

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

        $baseString = rawurlencode($method)
            . '&' . rawurlencode(strval($uri))
            . '&' . rawurlencode($message->toOAuthQueryString());

        $signature = null;
        $succeeded = openssl_sign($baseString, $signature, $this->privateKey, OPENSSL_ALGO_SHA1);
        if ($succeeded) {
            return base64_encode($signature);
        } else {
            return null;
        }
    }

    /**
     * @param string $method HTTP Method
     * @param string|UriInterface $uri URL
     * @param array|QueryString $params request parameters
     * @param array $headerParams Authorization header parameters
     * @return boolean true if valid signature in params, else false
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

        list($signatureString) = $message->delete('oauth_signature');
        $signature = base64_decode($signatureString);

        $baseString = rawurlencode($method)
            . '&' . rawurlencode(strval($uri))
            . '&' . rawurlencode($message->toOAuthQueryString());

        $verified = openssl_verify($baseString, $signature, $this->publicKey, OPENSSL_ALGO_SHA1);
        return ($verified == 1);
    }

    /**
     * @param string|resource $publicKey
     * @return resource|null resource of public key, null if invalid
     */
    private static function fetchPublicKey($publicKey)
    {
        if (is_resource($publicKey)) {
            $detail = @openssl_pkey_get_details($publicKey);
            if ($detail && $detail['type'] === OPENSSL_KEYTYPE_RSA
                && isset($detail['rsa']) && !(isset($detail['rsa']['d']))
            ) {
                return $publicKey;
            } else {
                return null;
            }
        } elseif (is_string($publicKey)) {
            $resource = openssl_pkey_get_public($publicKey);
            if ($resource) {
                return $resource;
            } else {
                return null;
            }
        } else {
            return null;
        }
    }

    /**
     * @param string|resource $privateKey
     * @param string $passphrase
     * @return resource|null resource of private key, null if invalid
     */
    private static function fetchPrivateKey($privateKey, $passphrase = null)
    {
        if (is_resource($privateKey)) {
            $detail = @openssl_pkey_get_details($privateKey);
            if ($detail && $detail['type'] === OPENSSL_KEYTYPE_RSA
                && isset($detail['rsa']) && isset($detail['rsa']['d'])
            ) {
                return $privateKey;
            } else {
                return null;
            }
        } elseif (is_string($privateKey)) {
            $resource = openssl_pkey_get_private($privateKey, $passphrase);
            if ($resource) {
                return $resource;
            } else {
                return null;
            }
        } else {
            return null;
        }
    }
}
