<?php

namespace Maruamyu\Core\OAuth1;

use Maruamyu\Core\Http\Message\QueryString;
use Maruamyu\Core\Http\Message\UriInterface;

/**
 * OAuth 1.0 signature operations
 */
interface SignerInterface
{
    /**
     * @return string oauth_signature_method
     */
    public function getSignatureMethod();

    /**
     * @param string $method HTTP Method
     * @param string|UriInterface $uri URL
     * @param array|QueryString $params request parameters
     * @param array $headerParams Authorization header parameters
     * @return string signature
     */
    public function sign($method, $uri, $params, $headerParams = null);

    /**
     * @param string $method HTTP Method
     * @param string|UriInterface $uri URL
     * @param array|QueryString $params request parameters
     * @param array $headerParams Authorization header parameters
     * @return bool true if valid signature in params, else false
     */
    public function verify($method, $uri, $params, $headerParams = null);
}
