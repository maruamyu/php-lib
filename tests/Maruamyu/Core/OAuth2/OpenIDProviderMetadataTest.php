<?php

namespace Maruamyu\Core\OAuth2;

class OpenIDProviderMetadataTest extends \PHPUnit\Framework\TestCase
{
    public function test_construct()
    {
        $sourceMetadata = $this->getFixture();
        $metadata = new OpenIDProviderMetadata($sourceMetadata);

        $this->assertEquals('https://accounts.google.com', $metadata->issuer);
        $this->assertEquals('https://accounts.google.com/o/oauth2/v2/auth', $metadata->authorizationEndpoint);
        $this->assertEquals('https://www.googleapis.com/oauth2/v4/token', $metadata->tokenEndpoint);
        $this->assertNull($metadata->tokenIntrospectionEndpoint);
        $this->assertEquals('https://www.googleapis.com/oauth2/v3/userinfo', $metadata->userinfoEndpoint);
        $this->assertEquals('https://accounts.google.com/o/oauth2/revoke', $metadata->revocationEndpoint);
        $this->assertEquals('https://www.googleapis.com/oauth2/v3/certs', $metadata->jwksUri);
        $this->assertEquals(['code', 'token', 'id_token', 'code token', 'code id_token', 'token id_token', 'code token id_token', 'none'], $metadata->supportedResponseTypes);
        $this->assertEquals(['public'], $metadata->supportedSubjectTypes);
        $this->assertEquals(['RS256'], $metadata->supportedIdTokenSigningAlgValues);
        $this->assertEquals(['openid', 'email', 'profile'], $metadata->supportedScopes);
        $this->assertEquals(['client_secret_post', 'client_secret_basic'], $metadata->supportedTokenEndpointAuthMethods);
        $this->assertFalse($metadata->isRequiredClientCredentialsOnRevocationRequest);
        $this->assertFalse($metadata->isUseBasicAuthorizationOnClientCredentialsRequest);
        $this->assertEquals(['aud', 'email', 'email_verified', 'exp', 'family_name', 'given_name', 'iat', 'iss', 'locale', 'name', 'picture', 'sub'], $metadata->supportedClaims);
        $this->assertEquals(['plain', 'S256'], $metadata->supportedCodeChallengeMethods);
    }

    public function test_createFromOpenIDProviderMetadata()
    {
        $sourceMetadata = $this->getFixture();
        $metadata = OpenIDProviderMetadata::createFromOpenIDProviderMetadata($sourceMetadata);

        $this->assertInstanceOf(OpenIDProviderMetadata::class, $metadata);
        $this->assertEquals('https://accounts.google.com', $metadata->issuer);
        $this->assertEquals('https://accounts.google.com/o/oauth2/v2/auth', $metadata->authorizationEndpoint);
        $this->assertEquals('https://www.googleapis.com/oauth2/v4/token', $metadata->tokenEndpoint);
        $this->assertNull($metadata->tokenIntrospectionEndpoint);
        $this->assertEquals('https://www.googleapis.com/oauth2/v3/userinfo', $metadata->userinfoEndpoint);
        $this->assertEquals('https://accounts.google.com/o/oauth2/revoke', $metadata->revocationEndpoint);
        $this->assertEquals('https://www.googleapis.com/oauth2/v3/certs', $metadata->jwksUri);
        $this->assertEquals(['code', 'token', 'id_token', 'code token', 'code id_token', 'token id_token', 'code token id_token', 'none'], $metadata->supportedResponseTypes);
        $this->assertEquals(['public'], $metadata->supportedSubjectTypes);
        $this->assertEquals(['RS256'], $metadata->supportedIdTokenSigningAlgValues);
        $this->assertEquals(['openid', 'email', 'profile'], $metadata->supportedScopes);
        $this->assertEquals(['client_secret_post', 'client_secret_basic'], $metadata->supportedTokenEndpointAuthMethods);
        $this->assertFalse($metadata->isRequiredClientCredentialsOnRevocationRequest);
        $this->assertFalse($metadata->isUseBasicAuthorizationOnClientCredentialsRequest);
        $this->assertEquals(['aud', 'email', 'email_verified', 'exp', 'family_name', 'given_name', 'iat', 'iss', 'locale', 'name', 'picture', 'sub'], $metadata->supportedClaims);
        $this->assertEquals(['plain', 'S256'], $metadata->supportedCodeChallengeMethods);
    }

    public function test_toArray()
    {
        $sourceMetadata = $this->getFixture();
        $metadata = new OpenIDProviderMetadata($sourceMetadata);
        $actual = $metadata->toArray();
        # fixture was ommited optional values
        foreach ($sourceMetadata as $key => $value) {
            $this->assertEquals($value, $actual[$key]);
        }
    }

    /**
     * @return array
     */
    private function getFixture()
    {
        # Google's OpenID metadata
        return [
            'issuer' => 'https://accounts.google.com',
            'authorization_endpoint' => 'https://accounts.google.com/o/oauth2/v2/auth',
            'token_endpoint' => 'https://www.googleapis.com/oauth2/v4/token',
            'userinfo_endpoint' => 'https://www.googleapis.com/oauth2/v3/userinfo',
            'revocation_endpoint' => 'https://accounts.google.com/o/oauth2/revoke',
            'jwks_uri' => 'https://www.googleapis.com/oauth2/v3/certs',
            'response_types_supported' => ['code', 'token', 'id_token', 'code token', 'code id_token', 'token id_token', 'code token id_token', 'none'],
            'subject_types_supported' => ['public'],
            'id_token_signing_alg_values_supported' => ['RS256'],
            'scopes_supported' => ['openid', 'email', 'profile'],
            'token_endpoint_auth_methods_supported' => ['client_secret_post', 'client_secret_basic'],
            'claims_supported' => ['aud', 'email', 'email_verified', 'exp', 'family_name', 'given_name', 'iat', 'iss', 'locale', 'name', 'picture', 'sub'],
            'code_challenge_methods_supported' => ['plain', 'S256']
        ];
    }
}
