<?php

namespace Maruamyu\Core\OAuth2;

class OpenIDProviderMetadataTest extends \PHPUnit\Framework\TestCase
{
    public function test_construct()
    {
        $metadata = new OpenIDProviderMetadata();

        # check default values
        $this->assertEquals(['code', 'id_token', 'token id_token'], $metadata->supportedResponseTypes);
        $this->assertEquals(['query', 'fragment'], $metadata->supportedResponseModes);
        $this->assertEquals(['authorization_code', 'implicit'], $metadata->supportedGrantTypes);
        $this->assertEquals([], $metadata->supportedSubjectTypes);
        $this->assertEquals([], $metadata->supportedIdTokenSigningAlgValues);
    }

    public function test_initializeFromArray()
    {
        $sourceMetadata = $this->getFixture();
        $metadata = new OpenIDProviderMetadata($sourceMetadata);

        $this->assertEquals('https://accounts.google.com', $metadata->issuer);
        $this->assertEquals('https://accounts.google.com/o/oauth2/v2/auth', $metadata->authorizationEndpoint);
        $this->assertEquals('https://oauth2.googleapis.com/token', $metadata->tokenEndpoint);
        $this->assertNull($metadata->tokenIntrospectionEndpoint);
        $this->assertEquals('https://openidconnect.googleapis.com/v1/userinfo', $metadata->userinfoEndpoint);
        $this->assertEquals('https://oauth2.googleapis.com/revoke', $metadata->revocationEndpoint);
        $this->assertEquals('https://www.googleapis.com/oauth2/v3/certs', $metadata->jwksUri);
        $this->assertEquals(['code', 'token', 'id_token', 'code token', 'code id_token', 'token id_token', 'code token id_token', 'none'], $metadata->supportedResponseTypes);
        $this->assertEquals(['public'], $metadata->supportedSubjectTypes);
        $this->assertEquals(['RS256'], $metadata->supportedIdTokenSigningAlgValues);
        $this->assertEquals(['openid', 'email', 'profile'], $metadata->supportedScopes);
        $this->assertEquals(['client_secret_post', 'client_secret_basic'], $metadata->supportedTokenEndpointAuthMethods);
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
            'device_authorization_endpoint' => 'https://oauth2.googleapis.com/device/code',
            'token_endpoint' => 'https://oauth2.googleapis.com/token',
            'userinfo_endpoint' => 'https://openidconnect.googleapis.com/v1/userinfo',
            'revocation_endpoint' => 'https://oauth2.googleapis.com/revoke',
            'jwks_uri' => 'https://www.googleapis.com/oauth2/v3/certs',
            'response_types_supported' => ['code', 'token', 'id_token', 'code token', 'code id_token', 'token id_token', 'code token id_token', 'none'],
            'subject_types_supported' => ['public'],
            'id_token_signing_alg_values_supported' => ['RS256'],
            'scopes_supported' => ['openid', 'email', 'profile'],
            'token_endpoint_auth_methods_supported' => ['client_secret_post', 'client_secret_basic'],
            'claims_supported' => ['aud', 'email', 'email_verified', 'exp', 'family_name', 'given_name', 'iat', 'iss', 'locale', 'name', 'picture', 'sub'],
            'code_challenge_methods_supported' => ['plain', 'S256'],
            'grant_types_supported' => ['authorization_code', 'refresh_token', 'urn:ietf:params:oauth:grant-type:device_code', 'urn:ietf:params:oauth:grant-type:jwt-bearer'],
        ];
    }
}
