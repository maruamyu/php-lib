<?php

namespace Maruamyu\Core\OAuth2;

use Maruamyu\Core\Http\Client as HttpClient;

/**
 * OpenID Connect Core 1.0 extention for OAuth2 client class
 */
trait OpenIDExtendsTrait
{
    /**
     * @return boolean true if initialized by OpenIDProviderMetadata
     */
    public function hasOpenIDMetadata()
    {
        return ($this->metadata instanceof OpenIDProviderMetadata);
    }

    /**
     * @return JsonWebKey[]
     * @throws \Exception if failed
     */
    public function fetchJwks()
    {
        $openIDMetadata = $this->getOpenIDMetadata();
        if (strlen($openIDMetadata->jwksUri) < 1) {
            throw new \RuntimeException('jwksUri not set yet.');
        }

        $response = $this->getHttpClient()->request('GET', $openIDMetadata->jwksUri);
        if ($response->statusCodeIsOk() == false) {
            throw new \RuntimeException('jwks fetch failed.');
        }
        $jwksJson = strval($response->getBody());
        if (strlen($jwksJson) < 1) {
            throw new \RuntimeException('jwks is empty.');
        }
        $jwksRawData = json_decode($jwksJson, true);
        if (!($jwksRawData) || !(isset($jwksRawData['keys'])) || empty($jwksRawData['keys'])) {
            throw new \RuntimeException('jwks is empty.');
        }

        $jwks = [];
        foreach ($jwksRawData['keys'] as $entry) {
            $jsonWebKey = new JsonWebKey($entry);
            $keyId = $jsonWebKey->getKeyId();
            $jwks[$keyId] = $jsonWebKey;
        }
        return $jwks;
    }

    /**
     * @param array $idTokenPayload
     * @return boolean
     * @throws \Exception if invalid settings
     * @see JsonWebToken::validatePayload()
     */
    public function validateIdTokenPayload($idTokenPayload)
    {
        return JsonWebToken::validatePayload($idTokenPayload, $this->metadata->issuer, $this->clientId);
    }

    /**
     * userinfo request
     *
     * @return array
     * @throws \Exception if invalid settings or access_token
     */
    public function requestGetUserinfo()
    {
        $openIDMetadata = $this->getOpenIDMetadata();
        if (strlen($openIDMetadata->userinfoEndpoint) < 1) {
            throw new \RuntimeException('userinfoEndpoint not set yet.');
        }
        if (!($this->hasValidAccessToken())) {
            throw new \RuntimeException('not has valid access_token.');
        }
        $response = $this->request('GET', $openIDMetadata->userinfoEndpoint);
        $responseBody = strval($response->getBody());
        return json_decode($responseBody, true);
    }

    /**
     * @param string $issuer
     * @return OpenIDProviderMetadata
     * @throws \Exception if failed
     */
    public static function fetchOpenIDProviderMetadata($issuer)
    {
        $configurationUrl = $issuer . '/.well-known/openid-configuration';
        $httpClient = static::createHttpClientInstance();
        $response = $httpClient->request('GET', $configurationUrl);
        if ($response->statusCodeIsOk() == false) {
            throw new \RuntimeException('openid-configuration fetch failed. (HTTP ' . $response->getStatusCode() . ')');
        }
        $configurationJson = strval($response->getBody());
        if (strlen($configurationJson) < 1) {
            throw new \RuntimeException('openid-configuration fetch failed.');
        }
        $configuration = json_decode($configurationJson, true);
        if (empty($configuration) || (isset($configuration['issuer']) == false)) {
            throw new \RuntimeException('openid-configuration fetch failed.');
        }
        if ($configuration['issuer'] !== $issuer) {
            $errorMsg = 'issuer not match. (args=' . $issuer . ', configuration=' . $configuration['issuer'] . ')';
            throw new \RuntimeException($errorMsg);
        }
        return new OpenIDProviderMetadata($configuration);
    }

    /**
     * @return OpenIDProviderMetadata
     * @throws \Exception if invalid settings
     */
    protected function getOpenIDMetadata()
    {
        if (!($this->metadata instanceof OpenIDProviderMetadata)) {
            throw new \RuntimeException('required OpenIDProviderMetadata');
        }
        return $this->metadata;
    }

    /**
     * @return HttpClient
     */
    protected function getHttpClient()
    {
        if (isset($this->httpClient) == false) {
            $this->httpClient = static::createHttpClientInstance();
        }
        return $this->httpClient;
    }

    /**
     * @return HttpClient
     * @internal
     */
    protected static function createHttpClientInstance()
    {
        return new HttpClient();
    }
}
