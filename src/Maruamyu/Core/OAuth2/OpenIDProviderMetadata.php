<?php

namespace Maruamyu\Core\OAuth2;

/**
 * OpenID Connect Core 1.0 Provider Metadata
 */
class OpenIDProviderMetadata extends Settings
{
    const JSON_ENCODE_OPTIONS = JSON_UNESCAPED_SLASHES;

    /** @var string (REQUIRED) */
    public $issuer;

    /** @var null|string */
    public $userinfoEndpoint;

    /** @var string (REQUIRED) */
    public $jwksUri;

    /** @var null|string */
    public $registrationEndpoint;

    /** @var null|string[] */
    public $supportedScopes = [];

    /** @var string[] (REQUIRED) */
    public $supportedResponseTypes = ['code', 'id_token', 'token id_token'];

    /**
     * @var null|string[]
     *   default for Dynamic OpenID Providers is ["query", "fragment"]
     */
    public $supportedResponseModes = ['query', 'fragment'];

    /**
     * @var string[]
     *   default value is ["authorization_code", "implicit"]
     */
    public $supportedGrantTypes = ['authorization_code', 'implicit'];

    /** @var null|string[] */
    public $supportedAcrValues;

    /** @var string[] (REQUIRED) */
    public $supportedSubjectTypes = [];

    /** @var string[] (REQUIRED) */
    public $supportedIdTokenSigningAlgValues = [];

    /** @var null|string[] */
    public $supportedIdTokenEncryptionAlgValues;

    /** @var null|string[] */
    public $supportedIdTokenEncryptionEncValues;

    /** @var null|string[] */
    public $supportedUserinfoSigningAlgValues;

    /** @var null|string[] */
    public $supportedUserinfoEncryptionAlgValues;

    /** @var null|string[] */
    public $supportedUserinfoEncryptionEncValues;

    /** @var null|string[] */
    public $supportedRequestObjectSigningAlgValues;

    /** @var null|string[] */
    public $supportedRequestObjectEncryptionAlgValues;

    /** @var null|string[] */
    public $supportedRequestObjectEncryptionEncValues;

    /** @var null|string[] */
    public $supportedTokenEndpointAuthMethods;

    /** @var null|string[] */
    public $supportedTokenEndpointAuthSigningAlgValues;

    /** @var null|string[] */
    public $supportedDisplayValues;

    /** @var null|string[] */
    public $supportedClaimTypes;

    /** @var null|string[] */
    public $supportedClaims;

    /** @var null|string */
    public $serviceDocumentationUri;

    /** @var null|string[] */
    public $supportedClaimsLocales;

    /** @var boolean (OPTIONAL, default=false) */
    public $isSupportedClaimsParameter = false;

    /** @var boolean (OPTIONAL, default=false) */
    public $isSupportedRequestParameter = false;

    /** @var boolean (OPTIONAL, default=true) */
    public $isSupportedRequestUriParameter = true;

    /** @var boolean (OPTIONAL, default=false) */
    public $isRequiredRequestUriRegistration = false;

    /** @var null|string */
    public $opPolicyUri;

    /** @var null|string */
    public $opTosUri;

    /** @var null|string[] */
    public $supportedCodeChallengeMethods;

    /**
     * @param array $initialMetadata
     */
    public function __construct(array $initialMetadata = null)
    {
        if ($initialMetadata) {
            $this->setMetadata($initialMetadata);
        }
    }

    /**
     * @param array $metadata
     * @return static
     */
    public static function createFromOpenIDProviderMetadata(array $metadata)
    {
        return new static($metadata);
    }

    /**
     * @param array $metadata
     */
    public function setMetadata(array $metadata)
    {
        # issuer (REQUIRED)
        $this->issuer = strval($metadata['issuer']);

        # authorization_endpoint (REQUIRED)
        $this->authorizationEndpoint = strval($metadata['authorization_endpoint']);

        # token_endpoint (REQUIRED unless only the Implicit Flow is used)
        if (isset($metadata['token_endpoint'])) {
            $this->tokenEndpoint = strval($metadata['token_endpoint']);
        }

        # revocation_endpoint (without specs, but included Google's metadata)
        if (isset($metadata['revocation_endpoint'])) {
            $this->revocationEndpoint = strval($metadata['revocation_endpoint']);
        }

        # userinfo_endpoint (RECOMMENDED)
        if (isset($metadata['userinfo_endpoint'])) {
            $this->userinfoEndpoint = strval($metadata['userinfo_endpoint']);
        }

        # jwks_uri (REQUIRED)
        $this->jwksUri = strval($metadata['jwks_uri']);

        # registration_endpoint (RECOMMENDED)
        if (isset($metadata['registration_endpoint'])) {
            $this->registrationEndpoint = strval($metadata['registration_endpoint']);
        }

        # scopes_supported (RECOMMENDED)
        if (isset($metadata['scopes_supported'])) {
            $this->supportedScopes = $metadata['scopes_supported'];
        }

        # response_types_supported (REQUIRED)
        $this->supportedResponseTypes = $metadata['response_types_supported'];

        # response_modes_supported (OPTIONAL)
        if (isset($metadata['response_modes_supported'])) {
            $this->supportedResponseModes = $metadata['response_modes_supported'];
        }

        # grant_types_supported (OPTIONAL)
        if (isset($metadata['grant_types_supported'])) {
            $this->supportedGrantTypes = $metadata['grant_types_supported'];
        }

        # acr_values_supported (OPTIONAL)
        if (isset($metadata['acr_values_supported'])) {
            $this->supportedAcrValues = $metadata['acr_values_supported'];
        }

        # subject_types_supported (REQUIRED)
        $this->supportedSubjectTypes = $metadata['subject_types_supported'];

        # id_token_signing_alg_values_supported (REQUIRED)
        $this->supportedIdTokenSigningAlgValues = $metadata['id_token_signing_alg_values_supported'];
        # id_token_encryption_alg_values_supported (OPTIONAL)
        if (isset($metadata['id_token_encryption_alg_values_supported'])) {
            $this->supportedIdTokenEncryptionAlgValues = $metadata['id_token_encryption_alg_values_supported'];
        }
        # id_token_encryption_enc_values_supported (OPTIONAL)
        if (isset($metadata['id_token_encryption_enc_values_supported'])) {
            $this->supportedIdTokenEncryptionEncValues = $metadata['id_token_encryption_enc_values_supported'];
        }

        # userinfo_signing_alg_values_supported (OPTIONAL)
        if (isset($metadata['userinfo_signing_alg_values_supported'])) {
            $this->supportedUserinfoSigningAlgValues = $metadata['userinfo_signing_alg_values_supported'];
        }
        # userinfo_encryption_alg_values_supported (OPTIONAL)
        if (isset($metadata['userinfo_encryption_alg_values_supported'])) {
            $this->supportedUserinfoEncryptionAlgValues = $metadata['userinfo_encryption_alg_values_supported'];
        }
        # userinfo_encryption_enc_values_supported (OPTIONAL)
        if (isset($metadata['userinfo_encryption_enc_values_supported'])) {
            $this->supportedUserinfoEncryptionEncValues = $metadata['userinfo_encryption_enc_values_supported'];
        }

        # request_object_signing_alg_values_supported (OPTIONAL)
        if (isset($metadata['request_object_signing_alg_values_supported'])) {
            $this->supportedRequestObjectSigningAlgValues = $metadata['request_object_signing_alg_values_supported'];
        }
        # request_object_encryption_alg_values_supported (OPTIONAL)
        if (isset($metadata['request_object_encryption_alg_values_supported'])) {
            $this->supportedRequestObjectEncryptionAlgValues = $metadata['request_object_encryption_alg_values_supported'];
        }
        # request_object_encryption_enc_values_supported (OPTIONAL)
        if (isset($metadata['request_object_encryption_enc_values_supported'])) {
            $this->supportedRequestObjectEncryptionEncValues = $metadata['request_object_encryption_enc_values_supported'];
        }

        # token_endpoint_auth_methods_supported (OPTIONAL)
        if (isset($metadata['token_endpoint_auth_methods_supported'])) {
            $this->supportedTokenEndpointAuthMethods = $metadata['token_endpoint_auth_methods_supported'];
            $this->setClientCredentialsRequestSettings($metadata['token_endpoint_auth_methods_supported']);
        }
        # token_endpoint_auth_signing_alg_values_supported (OPTIONAL)
        if (isset($metadata['token_endpoint_auth_signing_alg_values_supported'])) {
            $this->supportedTokenEndpointAuthSigningAlgValues = $metadata['token_endpoint_auth_signing_alg_values_supported'];
        }

        # display_values_supported (OPTIONAL)
        if (isset($metadata['display_values_supported'])) {
            $this->supportedDisplayValues = $metadata['display_values_supported'];
        }

        # claim_types_supported (OPTIONAL)
        if (isset($metadata['claim_types_supported'])) {
            $this->supportedClaimTypes = $metadata['claim_types_supported'];
        }
        # claims_supported (RECOMMENDED)
        if (isset($metadata['claims_supported'])) {
            $this->supportedClaims = $metadata['claims_supported'];
        }

        # service_documentation (OPTIONAL)
        if (isset($metadata['service_documentation'])) {
            $this->serviceDocumentationUri = strval($metadata['service_documentation']);
        }

        # claims_locales_supported (OPTIONAL)
        if (isset($metadata['claims_locales_supported'])) {
            $this->supportedClaimsLocales = $metadata['claims_locales_supported'];
        }

        # claims_parameter_supported (OPTIONAL)
        if (isset($metadata['claims_parameter_supported'])) {
            $this->isSupportedClaimsParameter = !!($metadata['claims_parameter_supported']);
        }

        # request_parameter_supported (OPTIONAL)
        if (isset($metadata['request_parameter_supported'])) {
            $this->isSupportedRequestParameter = !!($metadata['request_parameter_supported']);
        }

        # request_uri_parameter_supported (OPTIONAL)
        if (isset($metadata['request_uri_parameter_supported'])) {
            $this->isSupportedRequestUriParameter = !!($metadata['request_uri_parameter_supported']);
        }

        # require_request_uri_registration (OPTIONAL)
        if (isset($metadata['require_request_uri_registration'])) {
            $this->isRequiredRequestUriRegistration = !!($metadata['require_request_uri_registration']);
        }

        # op_policy_uri (OPTIONAL)
        if (isset($metadata['op_policy_uri'])) {
            $this->opPolicyUri = strval($metadata['op_policy_uri']);
        }

        # op_tos_uri (OPTIONAL)
        if (isset($metadata['op_tos_uri'])) {
            $this->opTosUri = strval($metadata['op_tos_uri']);
        }

        # code_challenge_methods_supported (without specs, but included Google's metadata)
        if (isset($metadata['code_challenge_methods_supported'])) {
            $this->supportedCodeChallengeMethods = $metadata['code_challenge_methods_supported'];
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
     * @return array
     */
    public function toArray()
    {
        $metadata = [];

        # issuer (REQUIRED)
        $metadata['issuer'] = strval($this->issuer);

        # authorization_endpoint (REQUIRED)
        $metadata['authorization_endpoint'] = strval($this->authorizationEndpoint);

        # token_endpoint (REQUIRED unless only the Implicit Flow is used)
        if (strlen($this->tokenEndpoint) > 0) {
            $metadata['token_endpoint'] = strval($this->tokenEndpoint);
        }

        # revocation_endpoint (without specs, but included Google's metadata)
        if (strlen($this->revocationEndpoint) > 0) {
            $metadata['revocation_endpoint'] = strval($this->revocationEndpoint);
        }

        # userinfo_endpoint (RECOMMENDED)
        if (strlen($this->userinfoEndpoint) > 0) {
            $metadata['userinfo_endpoint'] = strval($this->userinfoEndpoint);
        }

        # jwks_uri (REQUIRED)
        $metadata['jwks_uri'] = strval($this->jwksUri);

        # registration_endpoint (RECOMMENDED)
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

        # acr_values_supported (OPTIONAL)
        if (isset($this->supportedAcrValues) && !(empty($this->supportedAcrValues))) {
            $metadata['acr_values_supported'] = $this->supportedAcrValues;
        }

        # subject_types_supported (REQUIRED)
        $metadata['subject_types_supported'] = $this->supportedSubjectTypes;

        # id_token_signing_alg_values_supported (REQUIRED)
        $metadata['id_token_signing_alg_values_supported'] = $this->supportedIdTokenSigningAlgValues;
        # id_token_encryption_alg_values_supported (OPTIONAL)
        if (isset($this->supportedIdTokenEncryptionAlgValues) && !(empty($this->supportedIdTokenEncryptionAlgValues))) {
            $metadata['id_token_encryption_alg_values_supported'] = $this->supportedIdTokenEncryptionAlgValues;
        }
        # id_token_encryption_enc_values_supported (OPTIONAL)
        if (isset($this->supportedIdTokenEncryptionEncValues) && !(empty($this->supportedIdTokenEncryptionEncValues))) {
            $metadata['id_token_encryption_enc_values_supported'] = $this->supportedIdTokenEncryptionEncValues;
        }

        # userinfo_signing_alg_values_supported (OPTIONAL)
        if (isset($this->supportedUserinfoSigningAlgValues) && !(empty($this->supportedUserinfoSigningAlgValues))) {
            $metadata['userinfo_signing_alg_values_supported'] = $this->supportedUserinfoSigningAlgValues;
        }
        # userinfo_encryption_alg_values_supported (OPTIONAL)
        if (isset($this->supportedUserinfoEncryptionAlgValues) && !(empty($this->supportedUserinfoEncryptionAlgValues))) {
            $metadata['userinfo_encryption_alg_values_supported'] = $this->supportedUserinfoEncryptionAlgValues;
        }
        # userinfo_encryption_enc_values_supported (OPTIONAL)
        if (isset($this->supportedUserinfoEncryptionEncValues) && !(empty($this->supportedUserinfoEncryptionEncValues))) {
            $metadata['userinfo_encryption_enc_values_supported'] = $this->supportedUserinfoEncryptionEncValues;
        }

        # request_object_signing_alg_values_supported (OPTIONAL)
        if (isset($this->supportedRequestObjectSigningAlgValues) && !(empty($this->supportedRequestObjectSigningAlgValues))) {
            $metadata['request_object_signing_alg_values_supported'] = $this->supportedRequestObjectSigningAlgValues;
        }
        # request_object_encryption_alg_values_supported (OPTIONAL)
        if (isset($this->supportedRequestObjectEncryptionAlgValues) && !(empty($this->supportedRequestObjectEncryptionAlgValues))) {
            $metadata['request_object_encryption_alg_values_supported'] = $this->supportedRequestObjectEncryptionAlgValues;
        }
        # request_object_encryption_enc_values_supported (OPTIONAL)
        if (isset($this->supportedRequestObjectEncryptionEncValues) && !(empty($this->supportedRequestObjectEncryptionEncValues))) {
            $metadata['request_object_encryption_enc_values_supported'] = $this->supportedRequestObjectEncryptionEncValues;
        }

        # token_endpoint_auth_methods_supported (OPTIONAL)
        if (isset($this->supportedTokenEndpointAuthMethods) && !(empty($this->supportedTokenEndpointAuthMethods))) {
            $metadata['token_endpoint_auth_methods_supported'] = $this->supportedTokenEndpointAuthMethods;
            $this->setClientCredentialsRequestSettings($metadata['token_endpoint_auth_methods_supported']);
        }
        # token_endpoint_auth_signing_alg_values_supported (OPTIONAL)
        if (isset($this->supportedTokenEndpointAuthSigningAlgValues) && !(empty($this->supportedTokenEndpointAuthSigningAlgValues))) {
            $metadata['token_endpoint_auth_signing_alg_values_supported'] = $this->supportedTokenEndpointAuthSigningAlgValues;
        }

        # display_values_supported (OPTIONAL)
        if (isset($this->supportedDisplayValues) && !(empty($this->supportedDisplayValues))) {
            $metadata['display_values_supported'] = $this->supportedDisplayValues;
        }

        # claim_types_supported (OPTIONAL)
        if (isset($this->supportedClaimTypes) && !(empty($this->supportedClaimTypes))) {
            $metadata['claim_types_supported'] = $this->supportedClaimTypes;
        }
        # claims_supported (RECOMMENDED)
        if (isset($this->supportedClaims) && !(empty($this->supportedClaims))) {
            $metadata['claims_supported'] = $this->supportedClaims;
        }

        # service_documentation (OPTIONAL)
        if (strlen($this->serviceDocumentationUri) > 0) {
            $metadata['service_documentation'] = strval($this->serviceDocumentationUri);
        }

        # claims_locales_supported (OPTIONAL)
        if (isset($this->supportedClaimsLocales) && !(empty($this->supportedClaimsLocales))) {
            $metadata['claims_locales_supported'] = $this->supportedClaimsLocales;
        }

        # claims_parameter_supported (OPTIONAL)
        if (isset($this->isSupportedClaimsParameter)) {
            $metadata['claims_parameter_supported'] = !!($this->isSupportedClaimsParameter);
        }

        # request_parameter_supported (OPTIONAL)
        if (isset($this->isSupportedRequestParameter)) {
            $metadata['request_parameter_supported'] = !!($this->isSupportedRequestParameter);
        }

        # request_uri_parameter_supported (OPTIONAL)
        if (isset($this->isSupportedRequestUriParameter)) {
            $metadata['request_uri_parameter_supported'] = !!($this->isSupportedRequestUriParameter);
        }

        # require_request_uri_registration (OPTIONAL)
        if (isset($this->isRequiredRequestUriRegistration)) {
            $metadata['require_request_uri_registration'] = !!($this->isRequiredRequestUriRegistration);
        }

        # op_policy_uri (OPTIONAL)
        if (strlen($this->opPolicyUri) > 0) {
            $metadata['op_policy_uri'] = strval($this->opPolicyUri);
        }

        # op_tos_uri (OPTIONAL)
        if (strlen($this->opTosUri) > 0) {
            $metadata['op_tos_uri'] = strval($this->opTosUri);
        }

        # code_challenge_methods_supported (without specs, but included Google's metadata)
        if (isset($this->supportedCodeChallengeMethods) && !(empty($this->supportedCodeChallengeMethods))) {
            $metadata['code_challenge_methods_supported'] = $this->supportedCodeChallengeMethods;
        }

        return $metadata;
    }
}
