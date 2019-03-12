<?php

namespace Maruamyu\Core\OAuth2;

/**
 * OAuth 2.0 (and OpenID Connect Core 1.0) settings object
 */
class Settings
{
    /** @var string */
    public $clientId;

    /** @var string */
    public $clientSecret;

    /** @var string */
    public $authorizationEndpoint;

    /** @var string */
    public $tokenEndpoint;

    /** @var string */
    public $revocationEndpoint;

    /** @var string */
    public $tokenIntrospectionEndpoint;

    /** @var boolean true if is required client_id and client_secret on token revocation */
    public $isRequiredClientCredentialsOnRevocationRequest = false;

    /** @var boolean true if using Basic Authorization on client credentials request */
    public $isUseBasicAuthorizationOnClientCredentialsRequest = false;

    /**
     * @param array $metadata
     * @return static
     */
    public static function createFromOpenIDProviderMetadata(array $metadata)
    {
        $settings = new static();

        # authorization_endpoint (REQUIRED)
        $settings->authorizationEndpoint = $metadata['authorization_endpoint'];

        # token_endpoint (REQUIRED unless only the Implicit Flow is used)
        if (isset($metadata['token_endpoint'])) {
            $settings->tokenEndpoint = $metadata['token_endpoint'];
        }

        # revocation_endpoint (without specs, but included Google's metadata)
        if (isset($metadata['revocation_endpoint'])) {
            $settings->revocationEndpoint = $metadata['revocation_endpoint'];
        }

        # token_endpoint_auth_methods_supported
        if (isset($metadata['token_endpoint_auth_methods_supported'])) {
            if (in_array('client_secret_post', $metadata['token_endpoint_auth_methods_supported'])) {
                # use POST parameters if enable client_secret_post
                $settings->isUseBasicAuthorizationOnClientCredentialsRequest = false;
            } elseif (in_array('client_secret_basic', $metadata['token_endpoint_auth_methods_supported'])) {
                # use Basic Authorization if enable client_secret_basic and disable client_secret_post
                $settings->isUseBasicAuthorizationOnClientCredentialsRequest = true;
            } else {
                # default use POST parameters
                $settings->isUseBasicAuthorizationOnClientCredentialsRequest = false;
            }
        }

        return $settings;
    }
}
