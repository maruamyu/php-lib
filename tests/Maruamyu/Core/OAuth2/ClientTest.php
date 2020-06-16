<?php

namespace Maruamyu\Core\OAuth2;

use Maruamyu\Core\Base64Url;
use Maruamyu\Core\Http\Message\QueryString;
use Maruamyu\Core\Http\Message\Uri;

class ClientTest extends \PHPUnit\Framework\TestCase
{
    public function test_getClientId()
    {
        $metadata = $this->getDefaultMetadata();
        $clientId = 'client_id';
        $clientSecret = 'client_secret';
        $client = new Client($metadata, $clientId, $clientSecret);
        $this->assertEquals($clientId, $client->getClientId());
    }

    public function test_startAuthorizationCodeGrant()
    {
        $metadata = $this->getDefaultMetadata();
        $clientId = 'client_id';
        $clientSecret = 'client_secret';
        $client = new Client($metadata, $clientId, $clientSecret);

        $callbackUrl = 'https://example.jp/oauth2/callback';
        $state = sha1(uniqid());
        $uri = new Uri($client->startAuthorizationCodeGrant(['openid'], $callbackUrl, $state));

        $this->assertEquals('example.jp', $uri->getHost());
        $this->assertEquals('/oauth2/authorization', $uri->getPath());
        $this->assertEquals('code', $uri->getQueryString()->getString('response_type'));
        $this->assertEquals('client_id', $uri->getQueryString()->getString('client_id'));
        $this->assertEquals('openid', $uri->getQueryString()->getString('scope'));
        $this->assertEquals($callbackUrl, $uri->getQueryString()->getString('redirect_uri'));
        $this->assertEquals($state, $uri->getQueryString()->getString('state'));
    }

    public function test_startAuthorizationCodeGrantWithPkce()
    {
        $metadata = $this->getDefaultMetadata();
        $clientId = 'client_id';
        $clientSecret = 'client_secret';
        $client = new Client($metadata, $clientId, $clientSecret);

        $callbackUrl = 'https://example.jp/oauth2/callback';
        $codeVerifier = openssl_random_pseudo_bytes(128);
        $uri = new Uri($client->startAuthorizationCodeGrantWithPkce(['openid'], $callbackUrl, $codeVerifier));

        $this->assertEquals('example.jp', $uri->getHost());
        $this->assertEquals('/oauth2/authorization', $uri->getPath());
        $this->assertEquals('code', $uri->getQueryString()->getString('response_type'));
        $this->assertEquals('client_id', $uri->getQueryString()->getString('client_id'));
        $this->assertEquals('openid', $uri->getQueryString()->getString('scope'));
        $this->assertEquals($callbackUrl, $uri->getQueryString()->getString('redirect_uri'));
        $this->assertEquals('S256', $uri->getQueryString()->getString('code_challenge_method'));
        $this->assertEquals(Base64Url::encode(hash('sha256', $codeVerifier, true)), $uri->getQueryString()->getString('code_challenge'));
    }

    public function test_startImplicitGrant()
    {
        $metadata = $this->getDefaultMetadata();
        $clientId = 'client_id';
        $clientSecret = 'client_secret';
        $client = new Client($metadata, $clientId, $clientSecret);

        $callbackUrl = 'https://example.jp/oauth2/callback';
        $state = sha1(uniqid());
        $uri = new Uri($client->startImplicitGrant(['openid'], $callbackUrl, $state));

        $this->assertEquals('example.jp', $uri->getHost());
        $this->assertEquals('/oauth2/authorization', $uri->getPath());
        $this->assertEquals('token', $uri->getQueryString()->getString('response_type'));
        $this->assertEquals('client_id', $uri->getQueryString()->getString('client_id'));
        $this->assertEquals('openid', $uri->getQueryString()->getString('scope'));
        $this->assertEquals($callbackUrl, $uri->getQueryString()->getString('redirect_uri'));
        $this->assertEquals($state, $uri->getQueryString()->getString('state'));
    }

    public function test_makeRequest()
    {
        $metadata = $this->getDefaultMetadata();
        $clientId = 'client_id';
        $clientSecret = 'client_secret';

        $accessToken = new AccessToken([
            'access_token' => 'access_token',
            'token_type' => 'Bearer',
            'expires_in' => 3600,
        ]);

        $client = new Client($metadata, $clientId, $clientSecret, $accessToken);

        $request = $client->makeRequest('POST', 'https://example.jp/oauth2/endpoint');
        $this->assertEquals('POST', $request->getMethod());
        $this->assertEquals('https://example.jp/oauth2/endpoint', strval($request->getUri()));
        $this->assertEquals(['Bearer access_token'], $request->getHeader('Authorization'));
    }

    public function test_makeJwtBearerGrantRequest()
    {
        $metadata = $this->getDefaultMetadata();
        $clientId = 'client_id';
        $clientSecret = 'client_secret';
        $client = new Client($metadata, $clientId, $clientSecret);

        $jsonWebKey = $this->getJsonWebKey();

        $nowTimestamp = time();
        $expireAtTimestamp = $nowTimestamp + 3600;
        $scopes = ['hoge', 'fuga', 'piyo'];

        $request = $client->makeJwtBearerGrantRequest($jsonWebKey, 'issuer@example.jp',
            'subject@example.jp', $expireAtTimestamp, $scopes, ['iat' => $nowTimestamp]);
        $this->assertEquals('POST', $request->getMethod());
        $this->assertEquals('https://example.jp/oauth2/token', strval($request->getUri()));

        $requestBody = $request->getBody()->getContents();
        $parameters = new QueryString($requestBody);
        $grantType = $parameters->getString('grant_type');
        $assertion = $parameters->getString('assertion');

        $this->assertEquals('urn:ietf:params:oauth:grant-type:jwt-bearer', $grantType);

        $jwtPayload = JsonWebToken::parse($assertion, [$jsonWebKey->getKeyId() => $jsonWebKey]);
        $this->assertEquals('issuer@example.jp', $jwtPayload['iss']);
        $this->assertEquals('subject@example.jp', $jwtPayload['sub']);
        $this->assertEquals('https://example.jp/oauth2/token', $jwtPayload['aud']);
        $this->assertEquals($expireAtTimestamp, $jwtPayload['exp']);
        $this->assertEquals($nowTimestamp, $jwtPayload['iat']);
        $this->assertEquals('hoge fuga piyo', $jwtPayload['scope']);
    }

    public function test_hasOpenIDSettings()
    {
        $clientId = 'client_id';
        $clientSecret = 'client_secret';

        $oauth2metadata = $this->getDefaultMetadata();
        $oauth2Client = new Client($oauth2metadata, $clientId, $clientSecret);
        $this->assertFalse($oauth2Client->hasOpenIDMetadata());

        # Google's OpenID metadata
        $openIDMetadata = [
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
        $openIDSettings = new OpenIDProviderMetadata($openIDMetadata);
        $openIDClient = new Client($openIDSettings, $clientId, $clientSecret);
        $this->assertTrue($openIDClient->hasOpenIDMetadata());
    }

    /**
     * @return AuthorizationServerMetadata
     */
    private function getDefaultMetadata()
    {
        $metadata = new AuthorizationServerMetadata();
        $metadata->authorizationEndpoint = 'https://example.jp/oauth2/authorization';
        $metadata->tokenEndpoint = 'https://example.jp/oauth2/token';
        return $metadata;
    }

    /**
     * @return JsonWebKey
     * @throws \Exception
     */
    private function getJsonWebKey()
    {
        $privateKey = <<<__EOS__
-----BEGIN RSA PRIVATE KEY-----
Proc-Type: 4,ENCRYPTED
DEK-Info: DES-CBC,801A15CFA3CA5D28

AF+y0kJOMEcQJefHMGvwds8Wgy5a3eLDY4FAxEG4d7uWGkVRaPPSqATBhBFMrvId
43fAv0BDUHq/DpEq5HIjqalPr+xKGpcigCzeiZMRnpK984FFSAYVOH6B0z/QdU2a
BiPv/PuRG1/tLF6xQufTzoM09E7F1a3Paei1oTJkPVZ04paekgoJBBVTAfVAc1Vt
rLNOKcV6UiHtQVob43c+AzzBYxrPqGi6m6Tk+LaR78WCRq81PC0ib6t85VjtdbDX
qRZqEjozsBYwITuyYu64qjWebEwPszIISCl2yzsPE4w6V0CQ3PSAZnXpJ/NdDDXD
SPkDssKVisGtXTIOtatjpDbRFNDy69MT9C3BVQJiBWoWUPeI3ze68j0EOwXQOlng
Yr6AzLN+YoSYoU/RJbMGpXHW0Cht4XSZ19gRKIkg/WJqBhslReWASik1obBRJBTL
Jfar3+IG1usUX40e4/s7VfiAH3+BsfBmiId/esbVdwMSMnRYikNWWl6ALdqRFDU9
C54NpoM5xMGqCmxZ0JccNS7/+dCQvfHtk382KzX376eOunuzPwA0hDRn4KZblR3Z
Zzrlu1/za21wmbQKzXsGWHsOX7GecQq32enjWytQ7N9ElYYe1fws7vli0t+DopqS
WCWgjFr1aRCavQjFGnfIQ5XQgaHmwQm4VYGIYK7jjACOTdKF3cg9RcBYdg12OF2X
N06MitxISY6Sxr++51fEvln/XookTrOXf24wiop5kEURJtk1PF4sUePL0pzKmRbJ
ipxUU8aGKxAt87cFeyVCncE+nRj6lUkddkLAe/c0dLftOddYh2vAdAq5M2nWrg4f
EJzRAFl3M6JUGmK3HqoHofoeccDg5/+T9TzfTFKtChFbinYWQs2LrEjNyR4mVSNg
vC/1xRmQzVJbRT6io3lkGpGxu6doxUKn15fVZhNjlo5UnbmQrh95n5f3fh46Ix5B
rR7U65VpXRuxuutVbpHfHvOl6zhAoVsE2+rVPbXnGLSDNz/3qea/0jN33K7Oea1y
AiNryf1A4wgAl9VU1aPMaLjHN2WTrXpAm0Na9pcQxN6fez6Dw9zrVhgiGnPsuCC2
u/jKWLcZt6e7/pEPr5oMvwBb85GBBUn78oRdU5F1zyvQR2Mx3qsow628XBe/5Qxt
Cm6bpUu2nr91ZSZANscXOrr42X0F71f7GohbjjrVWd6C8edHD8Ir13IhS7dhX+Ao
bcbRVP43fCUhWb7fhjfYRLoCuDTVjKuKSRJ36alOj8uTpd2jYgQj9ca/2S46/vhO
zcdyj7Ud7Zxpn7jXhGnRkC4iJZWBVxCtrTOZQVBj6R70UpWqh66XYqhdUDro+ywZ
If6lATgFujeqzpW4nJAbgjC2I9Fn/mSW0EsE1AQwy5XM627ygOHWBaS/MPX/Z6o9
T6iKJjyzqB5blC3iI1TflQhU2dHekkeGc55NJJNoGQScYugItg0RZ3gMvhQrHzFk
ZNw9va2tSMixmUf3bqCIrBqpDDcuMZUDoDT4fwdjhe0P50Rsluvu6aDxNmdslm+C
NwoQy95fYmijKnII6223djt2rf/Re0Ty23695kn/tsBaOW0ymR3KJi1QvDHHFquP
-----END RSA PRIVATE KEY-----
__EOS__;
        return JsonWebKey::createFromRsaPrivateKey($privateKey, 'passphrase');
    }
}
