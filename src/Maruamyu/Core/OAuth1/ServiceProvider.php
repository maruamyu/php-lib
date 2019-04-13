<?php

namespace Maruamyu\Core\OAuth1;

use Maruamyu\Core\Http\Message\QueryString;
use Maruamyu\Core\Http\Message\Request;

/**
 * OAuth 1.0 Service Provider
 */
class ServiceProvider extends CoreLogic
{
    /**
     * verify signature in request
     *
     * @param Request $request HTTP request message
     * @return boolean true if valid signature, else false
     */
    public function verifySignature(Request $request)
    {
        $authorization = $request->getHeaderLine('Authorization');
        if (strlen($authorization) > 0) {
            $authParams = AuthorizationHeader::parse($authorization);
        } else {
            $authParams = [];
        }
        $method = $request->getMethod();
        $uri = $request->getUri();
        $params = static::fetchFormDataFromRequest($request);

        $signatureMethod = $this->getSignatureMethod();
        if (isset($authParams['oauth_signature_method'])) {
            $signatureMethod = $authParams['oauth_signature_method'];
        } elseif ($params && $params->has('oauth_signature_method')) {
            list($signatureMethod) = $params->get('oauth_signature_method');
        }

        $signer = $this->getSigner($signatureMethod);
        return $signer->verify($method, $uri, $params, $authParams);
    }

    /**
     * fetch application/x-www-form-urlencoded data from request
     *
     * @param Request $request HTTP request message
     * @return QueryString|null form data (null if invalid Content-Type)
     */
    protected static function fetchFormDataFromRequest(Request $request)
    {
        list($mimeType) = explode(';', $request->getContentType());
        if (strcasecmp($mimeType, 'application/x-www-form-urlencoded') !== 0) {
            return null;
        }
        $requestBody = strval($request->getBody());
        return new QueryString($requestBody);
    }
}
