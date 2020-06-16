<?php

namespace Maruamyu\Core\OAuth2;

/**
 * OAuth 2.0 Authorization Server Metadata (RFC 8414)
 */
class AuthorizationServerMetadata
{
    const JSON_ENCODE_OPTIONS = JSON_UNESCAPED_SLASHES;

    /** @var string issuer */
    public $issuer;

    /** @var string authorization_endpoint */
    public $authorizationEndpoint;

    /** @var string device_authorization_endpoint */
    public $deviceAuthorizationEndpoint;

    /** @var string token_endpoint */
    public $tokenEndpoint;

    /** @var string jwks_uri */
    public $jwksUri;

    /** @var null|string registration_endpoint */
    public $registrationEndpoint;

    /** @var null|string[] scopes_supported */
    public $supportedScopes = [];

    /** @var string[] response_types_supported */
    public $supportedResponseTypes = ['code', 'id_token', 'token id_token'];

    /**
     * @var null|string[] response_modes_supported
     *   default value is ["query", "fragment"]
     */
    public $supportedResponseModes = ['query', 'fragment'];

    /**
     * @var string[] grant_types_supported
     *   default value is ["authorization_code", "implicit"]
     */
    public $supportedGrantTypes = ['authorization_code', 'implicit'];

    /** @var null|string[] token_endpoint_auth_methods_supported */
    public $supportedTokenEndpointAuthMethods;

    /** @var null|string[] token_endpoint_auth_signing_alg_values_supported */
    public $supportedTokenEndpointAuthSigningAlgValues;

    /** @var null|string service_documentation */
    public $serviceDocumentationUri;

    /** @var null|string[] ui_locales_supported */
    public $supportedUiLocales;

    /** @var null|string op_policy_uri */
    public $opPolicyUri;

    /** @var null|string op_tos_uri */
    public $opTosUri;

    /** @var string revocation_endpoint */
    public $revocationEndpoint;

    /** @var null|string[] revocation_endpoint_auth_methods_supported */
    public $supportedRevocationEndpointAuthMethods;

    /** @var null|string[] revocation_endpoint_auth_signing_alg_values_supported */
    public $supportedRevocationEndpointAuthSigningAlgValues;

    /** @var string introspection_endpoint */
    public $tokenIntrospectionEndpoint;

    /** @var null|string[] introspection_endpoint_auth_methods_supported */
    public $supportedTokenIntrospectionEndpointAuthMethods;

    /** @var null|string[] introspection_endpoint_auth_signing_alg_values_supported */
    public $supportedTokenIntrospectionEndpointAuthSigningAlgValues;

    /** @var null|string[] code_challenge_methods_supported */
    public $supportedCodeChallengeMethods;

    /** @var string signed_metadata */
    public $signedMetadata;

    /**
     * @param string|array|null $initialMetadata
     */
    public function __construct($initialMetadata = null)
    {
        if (is_array($initialMetadata)) {
            $this->setMetadata($initialMetadata);
        } elseif (is_string($initialMetadata)) {
            $metadata = json_decode($initialMetadata, true);
            $this->setMetadata($metadata);
        }
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return json_encode($this->toArray(), static::JSON_ENCODE_OPTIONS);
    }

    /**
     * @param array $metadata
     */
    public function setMetadata(array $metadata)
    {
        # issuer (REQUIRED)
        $this->issuer = strval($metadata['issuer']);

        # authorization_endpoint (REQUIRED unless no grant types are supported that use the authorization endpoint)
        if (isset($metadata['authorization_endpoint'])) {
            $this->authorizationEndpoint = strval($metadata['authorization_endpoint']);
        }

        # device_authorization_endpoint (OPTIONAL)
        if (isset($metadata['device_authorization_endpoint'])) {
            $this->deviceAuthorizationEndpoint = strval($metadata['device_authorization_endpoint']);
        }

        # token_endpoint (REQUIRED unless only the Implicit Flow is used)
        if (isset($metadata['token_endpoint'])) {
            $this->tokenEndpoint = strval($metadata['token_endpoint']);
        }

        # jwks_uri (OPTIONAL)
        if (isset($metadata['jwks_uri'])) {
            $this->jwksUri = strval($metadata['jwks_uri']);
        }

        # registration_endpoint (OPTIONAL)
        if (isset($metadata['registration_endpoint'])) {
            $this->registrationEndpoint = strval($metadata['registration_endpoint']);
        }

        # scopes_supported (RECOMMENDED)
        if (isset($metadata['scopes_supported'])) {
            $this->supportedScopes = $metadata['scopes_supported'];
        }

        # response_types_supported (REQUIRED)
        if (isset($metadata['response_types_supported'])) {
            $this->supportedResponseTypes = $metadata['response_types_supported'];
        } else {
            $this->supportedResponseTypes = [];
        }

        # response_modes_supported (OPTIONAL)
        if (isset($metadata['response_modes_supported'])) {
            $this->supportedResponseModes = $metadata['response_modes_supported'];
        }

        # grant_types_supported (OPTIONAL)
        if (isset($metadata['grant_types_supported'])) {
            $this->supportedGrantTypes = $metadata['grant_types_supported'];
        }

        # token_endpoint_auth_methods_supported (OPTIONAL)
        if (isset($metadata['token_endpoint_auth_methods_supported'])) {
            $this->supportedTokenEndpointAuthMethods = $metadata['token_endpoint_auth_methods_supported'];
        }

        # token_endpoint_auth_signing_alg_values_supported (OPTIONAL)
        if (isset($metadata['token_endpoint_auth_signing_alg_values_supported'])) {
            $this->supportedTokenEndpointAuthSigningAlgValues = $metadata['token_endpoint_auth_signing_alg_values_supported'];
        }

        # service_documentation (OPTIONAL)
        if (isset($metadata['service_documentation'])) {
            $this->serviceDocumentationUri = strval($metadata['service_documentation']);
        }

        # ui_locales_supported (OPTIONAL)
        if (isset($metadata['ui_locales_supported'])) {
            $this->supportedUiLocales = $metadata['ui_locales_supported'];
        }

        # op_policy_uri (OPTIONAL)
        if (isset($metadata['op_policy_uri'])) {
            $this->opPolicyUri = strval($metadata['op_policy_uri']);
        }

        # op_tos_uri (OPTIONAL)
        if (isset($metadata['op_tos_uri'])) {
            $this->opTosUri = strval($metadata['op_tos_uri']);
        }

        # revocation_endpoint (OPTIONAL)
        if (isset($metadata['revocation_endpoint'])) {
            $this->revocationEndpoint = strval($metadata['revocation_endpoint']);
        }

        # revocation_endpoint_auth_methods_supported (OPTIONAL)
        if (isset($metadata['revocation_endpoint_auth_methods_supported'])) {
            $this->supportedRevocationEndpointAuthMethods = $metadata['revocation_endpoint_auth_methods_supported'];
        }

        # revocation_endpoint_auth_signing_alg_values_supported (OPTIONAL)
        if (isset($metadata['revocation_endpoint_auth_signing_alg_values_supported'])) {
            $this->supportedRevocationEndpointAuthSigningAlgValues = $metadata['revocation_endpoint_auth_signing_alg_values_supported'];
        }

        # introspection_endpoint (OPTIONAL)
        if (isset($metadata['introspection_endpoint'])) {
            $this->tokenIntrospectionEndpoint = strval($metadata['introspection_endpoint']);
        }

        # introspection_endpoint_auth_methods_supported (OPTIONAL)
        if (isset($metadata['introspection_endpoint_auth_methods_supported'])) {
            $this->supportedTokenIntrospectionEndpointAuthMethods = $metadata['introspection_endpoint_auth_methods_supported'];
        }

        # introspection_endpoint_auth_signing_alg_values_supported (OPTIONAL)
        if (isset($metadata['introspection_endpoint_auth_signing_alg_values_supported'])) {
            $this->supportedTokenIntrospectionEndpointAuthSigningAlgValues = $metadata['introspection_endpoint_auth_signing_alg_values_supported'];
        }

        # code_challenge_methods_supported (OPTIONAL)
        if (isset($metadata['code_challenge_methods_supported'])) {
            $this->supportedCodeChallengeMethods = $metadata['code_challenge_methods_supported'];
        }

        # signed_metadata (OPTIONAL)
        if (isset($metadata['signed_metadata'])) {
            $this->signedMetadata = $metadata['signed_metadata'];
        }
    }

    /**
     * @return array
     */
    public function toArray()
    {
        $metadata = [];

        # issuer (REQUIRED)
        $metadata['issuer'] = strval($this->issuer);

        # authorization_endpoint (REQUIRED unless no grant types are supported that use the authorization endpoint)
        if (strlen($this->authorizationEndpoint) > 0) {
            $metadata['authorization_endpoint'] = strval($this->authorizationEndpoint);
        }

        # device_authorization_endpoint (OPTIONAL)
        if (strlen($this->deviceAuthorizationEndpoint) > 0) {
            $metadata['device_authorization_endpoint'] = strval($this->deviceAuthorizationEndpoint);
        }

        # token_endpoint (REQUIRED unless only the Implicit Flow is used)
        if (strlen($this->tokenEndpoint) > 0) {
            $metadata['token_endpoint'] = strval($this->tokenEndpoint);
        }

        # jwks_uri (OPTIONAL)
        if (strlen($this->jwksUri) > 0) {
            $metadata['jwks_uri'] = strval($this->jwksUri);
        }

        # registration_endpoint (OPTIONAL)
        if (strlen($this->registrationEndpoint) > 0) {
            $metadata['registration_endpoint'] = strval($this->registrationEndpoint);
        }

        # scopes_supported (RECOMMENDED)
        if (isset($this->supportedScopes) && !(empty($this->supportedScopes))) {
            $metadata['scopes_supported'] = $this->supportedScopes;
        }

        # response_types_supported (REQUIRED)
        $metadata['response_types_supported'] = $this->supportedResponseTypes;

        # response_modes_supported (OPTIONAL)
        if (isset($this->supportedResponseModes) && !(empty($this->supportedResponseModes))) {
            $metadata['response_modes_supported'] = $this->supportedResponseModes;
        }

        # grant_types_supported (OPTIONAL)
        if (isset($this->supportedGrantTypes) && !(empty($this->supportedGrantTypes))) {
            $metadata['grant_types_supported'] = $this->supportedGrantTypes;
        }

        # token_endpoint_auth_methods_supported (OPTIONAL)
        if (isset($this->supportedTokenEndpointAuthMethods) && !(empty($this->supportedTokenEndpointAuthMethods))) {
            $metadata['token_endpoint_auth_methods_supported'] = $this->supportedTokenEndpointAuthMethods;
        }

        # token_endpoint_auth_signing_alg_values_supported (OPTIONAL)
        if (isset($this->supportedTokenEndpointAuthSigningAlgValues) && !(empty($this->supportedTokenEndpointAuthSigningAlgValues))) {
            $metadata['token_endpoint_auth_signing_alg_values_supported'] = $this->supportedTokenEndpointAuthSigningAlgValues;
        }

        # service_documentation (OPTIONAL)
        if (strlen($this->serviceDocumentationUri) > 0) {
            $metadata['service_documentation'] = strval($this->serviceDocumentationUri);
        }

        # ui_locales_supported (OPTIONAL)
        if (isset($this->supportedUiLocales) && !(empty($this->supportedUiLocales))) {
            $metadata['ui_locales_supported'] = $this->supportedUiLocales;
        }

        # op_policy_uri (OPTIONAL)
        if (strlen($this->opPolicyUri) > 0) {
            $metadata['op_policy_uri'] = strval($this->opPolicyUri);
        }

        # op_tos_uri (OPTIONAL)
        if (strlen($this->opTosUri) > 0) {
            $metadata['op_tos_uri'] = strval($this->opTosUri);
        }

        # revocation_endpoint (OPTIONAL)
        if (strlen($this->revocationEndpoint) > 0) {
            $metadata['revocation_endpoint'] = strval($this->revocationEndpoint);
        }

        # revocation_endpoint_auth_methods_supported (OPTIONAL)
        if (isset($this->supportedRevocationEndpointAuthMethods) && !(empty($this->supportedRevocationEndpointAuthMethods))) {
            $metadata['revocation_endpoint_auth_methods_supported'] = $this->supportedRevocationEndpointAuthMethods;
        }

        # revocation_endpoint_auth_signing_alg_values_supported (OPTIONAL)
        if (isset($this->supportedRevocationEndpointAuthSigningAlgValues) && !(empty($this->supportedRevocationEndpointAuthSigningAlgValues))) {
            $metadata['revocation_endpoint_auth_signing_alg_values_supported'] = $this->supportedRevocationEndpointAuthSigningAlgValues;
        }

        # introspection_endpoint (OPTIONAL)
        if (strlen($this->tokenIntrospectionEndpoint) > 0) {
            $metadata['introspection_endpoint'] = strval($this->tokenIntrospectionEndpoint);
        }

        # introspection_endpoint_auth_methods_supported (OPTIONAL)
        if (isset($this->supportedTokenIntrospectionEndpointAuthMethods) && !(empty($this->supportedTokenIntrospectionEndpointAuthMethods))) {
            $metadata['introspection_endpoint_auth_methods_supported'] = $this->supportedTokenIntrospectionEndpointAuthMethods;
        }

        # introspection_endpoint_auth_signing_alg_values_supported (OPTIONAL)
        if (isset($this->supportedTokenIntrospectionEndpointAuthSigningAlgValues) && !(empty($this->supportedTokenIntrospectionEndpointAuthSigningAlgValues))) {
            $metadata['introspection_endpoint_auth_signing_alg_values_supported'] = $this->supportedTokenIntrospectionEndpointAuthSigningAlgValues;
        }

        # code_challenge_methods_supported (OPTIONAL)
        if (isset($this->supportedCodeChallengeMethods) && !(empty($this->supportedCodeChallengeMethods))) {
            $metadata['code_challenge_methods_supported'] = $this->supportedCodeChallengeMethods;
        }

        # signed_metadata (OPTIONAL)
        if (isset($this->signedMetadata) && !(empty($this->signedMetadata))) {
            $metadata['signed_metadata'] = $this->signedMetadata;
        }

        return $metadata;
    }
}
