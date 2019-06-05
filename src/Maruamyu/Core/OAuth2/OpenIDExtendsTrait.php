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
    public function hasOpenIDSettings()
    {
        return ($this->settings instanceof OpenIDProviderMetadata);
    }

    /**
     * @return JsonWebKey[]
     * @throws \Exception if invalid settings
     */
    public function fetchJwks()
    {
        $openIDSettings = $this->getOpenIDSettings();
        if (strlen($openIDSettings->jwksUri) < 1) {
            throw new \RuntimeException('jwksUri not set yet.');
        }

        $response = $this->getHttpClient()->request('GET', $openIDSettings->jwksUri);
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
     * @internal
     */
    protected function getOpenIDSettings()
    {
        if (!($this->settings instanceof OpenIDProviderMetadata)) {
            throw new \RuntimeException('required OpenIDProviderMetadata');
        }
        return $this->settings;
    }

    /**
     * @return HttpClient
     * @internal
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
