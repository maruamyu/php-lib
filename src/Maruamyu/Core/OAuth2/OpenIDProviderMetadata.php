<?php

namespace Maruamyu\Core\OAuth2;

/**
 * OpenID Connect Core 1.0 Provider Metadata
 *
 * "authorization_endpoint" and "jwks_uri" is REQUIRED
 * @see https://openid.net/specs/openid-connect-discovery-1_0.html
 */
class OpenIDProviderMetadata extends AuthorizationServerMetadata
{
    /** @var null|string */
    public $userinfoEndpoint;

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
    public $supportedDisplayValues;

    /** @var null|string[] */
    public $supportedClaimTypes;

    /** @var null|string[] */
    public $supportedClaims;

    /** @var null|string[] */
    public $supportedClaimsLocales;

    /** @var bool (OPTIONAL, default=false) */
    public $isSupportedClaimsParameter = false;

    /** @var bool (OPTIONAL, default=false) */
    public $isSupportedRequestParameter = false;

    /** @var bool (OPTIONAL, default=true) */
    public $isSupportedRequestUriParameter = true;

    /** @var bool (OPTIONAL, default=false) */
    public $isRequiredRequestUriRegistration = false;

    /**
     * @param array $metadata
     * @return static
     * @deprecated
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
        parent::setMetadata($metadata);

        # "authorization_endpoint" and "jwks_uri" is REQUIRED
        $this->authorizationEndpoint = strval($this->authorizationEndpoint);
        $this->jwksUri = strval($this->jwksUri);

        # userinfo_endpoint (RECOMMENDED)
        if (isset($metadata['userinfo_endpoint'])) {
            $this->userinfoEndpoint = strval($metadata['userinfo_endpoint']);
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
    }

    /**
     * @return array
     */
    public function toArray()
    {
        $metadata = parent::toArray();

        # "authorization_endpoint" and "jwks_uri" is REQUIRED
        $metadata['authorization_endpoint'] = strval($this->authorizationEndpoint);
        $metadata['jwks_uri'] = strval($this->jwksUri);

        # userinfo_endpoint (RECOMMENDED)
        if (strlen($this->userinfoEndpoint) > 0) {
            $metadata['userinfo_endpoint'] = strval($this->userinfoEndpoint);
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

        return $metadata;
    }
}
