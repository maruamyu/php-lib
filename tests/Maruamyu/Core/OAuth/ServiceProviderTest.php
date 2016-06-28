<?php

namespace Maruamyu\Core\OAuth;

use Maruamyu\Core\Http\Message\ServerRequest;
use Maruamyu\Core\Http\Message\Uri;

class ServiceProviderTest extends \PHPUnit_Framework_TestCase
{
    const PRIVATE_KEY = <<<__EOS__
-----BEGIN RSA PRIVATE KEY-----
Proc-Type: 4,ENCRYPTED
DEK-Info: DES-EDE3-CBC,233BA5ADFF1FA838

OXgEcIlT7FBWeZtVsq+xx2ymZnLvyr3Qskhv1DZzompQ2UinkzmHxZQ3lOOolIrE
Nb63hqf5VlLeqdBNuWF0+M5Y4VBCeKuXL+lu+Qamw0o6I4VlfIdTzGmrWMgln8/W
ub+3PexMuAJRET/ZV5qsjpmujc+n9fMbco/PFqFlcrSrawfwStoJVcijfH3h+7YL
GmM+CyOHGPLU4MBPbCdZNsgsMgQKLSvhAZT/MNWbzQ5AlU6QaVQJ/laX1M8na6aa
uvBYW5ywL4nypL24AHFgNEHkMyAZAiDEGTdjN7tOkn9e0RjDhnldDo3uRfuoB0oS
FILHXKK61vydUifFJf20C7JA/23LPFmlbRYfX0OxLZAXKoxwizLd4lXc1sOkpVRb
fMCOIZaeO5CYL7jdHck50g865xkI48pT646KYTk1SIx8X0tOCOH+sn47wRTFzG7J
bSLJyBnwaD7o48NX+8wWQeah3wb9enYP1gHNHgX70SD8kCQmbx7gL1gzZU/1CISu
5jKuI1fUbutMbvvxKiP1x2h1P5/uYnnaRTrpKwyCqMnQCvBo4o2tPiL5sC19kJW4
TXAldW4Z1fBABnddjmuecBtTZI4rpBhihhpnekxFHQ8c05WnVgeDgRstKIHm2iJc
ziIPp1JnSBF+CwpzNrQ5B0lOs1i813T0PyaLwMWsPVICY064cuL7XguZi5NCDWE2
v5TEj7ymBDmSQFjjN8sPANWwZoGGINxXWP/dQyhfz9SDYDW5o3If7uNEIxX8b/wd
xRwPymjheDcTBMCXwTScDak/1JOtmTb/KLP2B4SK0RbA8Kw6N2Xf+32Xgymt5HkT
xmayyU7LNDCG3gQ65UikPuB8gwwwFlKC9ck2idN1hyx4pmy8LO8OmfNeBU91y1Nf
RtNRml26rgMneuJkdjtbW9RUfJDoQ0VSpGjHc3ZJbUvrXV7wn5HmK3HdKOr2j2Wq
Hdu9mlQf5xwDIYjvZBTpnnlaZaYkRCgsHCBBnJWnW84RufCiciS7UhH1AxgIrDev
9C+vV065OOUEufWEbo2Q/yDQe7wsBGMVPqwEy5CldmDAz0xSyjOl/3MAO4M8Io0F
2mL7PRYtCHK5z6RPi0X6xP6tgcb1eKmZ2Yh9EUljAx1EUkftLLaHJirodTEGIg7q
7zOgwk+MWG1GjdNKj2jeWdxAePvp60ycDoUvuIVxfVSHBfA6DDUXNb+UptWl8xwg
4HRiFbQBvynftYe9kHyjzcPsFwJXo1dDEeStVrq7Pl7LzihGWlBRqg7itoULrQtc
ukMf78psEDITPFmYgqwsgJXjWvhDF7+BU9IAWH9zf3msTIHbQwvZTpob8fO16nI+
BmFsFAVKr3S85+JUGr346GcJE7XB89pjth302okDf12RMIZCxRb9HJ+AQq7WUZZk
47dwn1okMOaPoBR7rzJiu5rw4F2j/Qg5LY3R5g1cbVEdT8Lg1ZMrvtfd69vaMd2X
Sc+Bx76y48DlFABYkmGXBeb/8KpNKSrtYTOmrhreO3NzlZIxugo89G9ylVNnk125
cfICTQxGi0YCYK09d1hj/B/P73h4srAS/KC4gnkVeHKcoDmnAlEDTA==
-----END RSA PRIVATE KEY-----
__EOS__;

    const PUBLIC_KEY = <<<__EOS__
-----BEGIN PUBLIC KEY-----
MIIBIDANBgkqhkiG9w0BAQEFAAOCAQ0AMIIBCAKCAQEAh3F2L2AVUQaSXhda5gGb
i3Z/z37yJ/VWGvr71/yyYFk4brlQqNgMAkJIkWqm6o7QoyISwilRnLnrA5SoM6fe
yVwh3AndvbM/myrv7QRl3m3rye1sVP6SHbnW+0iRCIyk7/382CHNQTGBhb25oMuM
GnJMTJWUQb7+2zT7fhNZmo66kZBBMIYggNAZGS68r5r5N0apR1/tjxRBUNh23OJ/
HKw7GtR1vwp2AkRWgNQiIEoTW4iMaJcqFqpL5gLVthmBuFSY+1JGoEOT1vh2LRbx
Syj8sM+LMvpEw+sLhS+Z46JbJ2Bv6K+GESZMWhib3ZejK2lj+9T9Cadhjmmwybf1
oQIBJQ==
-----END PUBLIC KEY-----
__EOS__;

    const PASSPHRASE = 'passphrase';

    public function test_setAccessToken()
    {
        $consumerKey = $this->getConsumerKey();
        $accessToken = $this->getAccessToken();
        $serviceProvider = new ServiceProvider($consumerKey);
        $serviceProvider->setAccessToken($accessToken);
        $this->assertTrue($serviceProvider->hasAccessToken());
    }

    public function test_setNullAccessToken()
    {
        $consumerKey = $this->getConsumerKey();
        $accessToken = $this->getAccessToken();
        $serviceProvider = new ServiceProvider($consumerKey);
        $serviceProvider->setAccessToken($accessToken);
        $serviceProvider->setNullAccessToken();
        $this->assertFalse($serviceProvider->hasAccessToken());
    }

    public function test_getSignatureMethod_default()
    {
        $consumerKey = $this->getConsumerKey();
        $serviceProvider = new ServiceProvider($consumerKey);
        $this->assertEquals('HMAC-SHA1', $serviceProvider->getSignatureMethod());
    }

    public function test_setSignatureMethod()
    {
        $consumerKey = $this->getConsumerKey();
        $serviceProvider = new ServiceProvider($consumerKey);
        $serviceProvider->setSignatureMethod('RSA-SHA1');
        $this->assertEquals('RSA-SHA1', $serviceProvider->getSignatureMethod());
    }

    public function test_signatureMethod_initialize()
    {
        $consumerKey = $this->getConsumerKey();
        $serviceProvider = new ServiceProvider($consumerKey, 'PLAINTEXT');
        $this->assertEquals('PLAINTEXT', $serviceProvider->getSignatureMethod());
    }

    public function test_verifySignature_hmac_sha1()
    {
        $consumerKey = $this->getConsumerKey();
        $accessToken = $this->getAccessToken();
        $serviceProvider = new ServiceProvider($consumerKey);
        $serviceProvider->setAccessToken($accessToken);

        $serverRequest = $this->getHmacSha1ServerRequest();
        $this->assertTrue($serviceProvider->verifySignature($serverRequest));
    }

    public function test_verifySignature_rsa_sha1()
    {
        $consumerKey = $this->getConsumerKey();
        $accessToken = $this->getAccessToken();
        $serviceProvider = new ServiceProvider($consumerKey);
        $serviceProvider->setAccessToken($accessToken);
        $serviceProvider->setRsaKeyPair(self::PUBLIC_KEY);

        $serverRequest = $this->getRsaSha1ServerRequest();
        $this->assertTrue($serviceProvider->verifySignature($serverRequest));
    }

    public function test_verifySignature_plainText()
    {
        $consumerKey = $this->getConsumerKey();
        $accessToken = $this->getAccessToken();
        $serviceProvider = new ServiceProvider($consumerKey);
        $serviceProvider->setAccessToken($accessToken);

        $serverRequest = $this->getPlainTextServerRequest();
        $this->assertTrue($serviceProvider->verifySignature($serverRequest));
    }

    public function test_makeAuthorization()
    {
        $consumerKey = $this->getConsumerKey();
        $accessToken = $this->getAccessToken();
        $serviceProvider = new ServiceProvider($consumerKey, 'RSA-SHA1');
        $serviceProvider->setRsaKeyPair(self::PUBLIC_KEY, self::PRIVATE_KEY, self::PASSPHRASE);
        $serviceProvider->setAccessToken($accessToken);

        $method = 'POST';
        $url = 'https://example.jp/';
        $params = [
            'hoge' => 'ほげほげ',
            'fuga' => 'ふがふが',
            'piyo' => 'ぴよぴよ',
        ];
        $authParams = $serviceProvider->makeAuthorization($method, $url, $params);

        $this->assertEquals('1.0', $authParams['oauth_version']);
        $this->assertEquals('RSA-SHA1', $authParams['oauth_signature_method']);
        $this->assertEquals($consumerKey->getToken(), $authParams['oauth_consumer_key']);
        $this->assertEquals($accessToken->getToken(), $authParams['oauth_token']);
        $this->assertFalse(empty($authParams['oauth_nonce']));
        $this->assertFalse(empty($authParams['oauth_timestamp']));
        $this->assertFalse(empty($authParams['oauth_signature']));

        $serverRequest = (new ServerRequest())
            ->withUri(new Uri($url))
            ->withMethod($method)
            ->withBodyContents(http_build_query($params))
            ->withHeader('Content-Type', 'application/x-www-form-urlencoded')
            ->withHeader('Authorization', AuthorizationHeader::build($authParams));

        $this->assertTrue($serviceProvider->verifySignature($serverRequest));
    }

    public function test_makeRequest()
    {
        $consumerKey = $this->getConsumerKey();
        $accessToken = $this->getAccessToken();
        $serviceProvider = new ServiceProvider($consumerKey);
        $serviceProvider->setAccessToken($accessToken);

        $method = 'POST';
        $url = 'https://example.jp/?use_iframe=false';
        $params = [
            'opensocial_app_id' => '58737',
            'opensocial_viewer_id' => '8765',
            'opensocial_owner_id' => '8765',
        ];
        $request = $serviceProvider->makeRequest($method, $url, $params, true);
        $this->assertTrue($serviceProvider->verifySignature($request));
    }

    private function getConsumerKey()
    {
        return new ConsumerKey('consumer_key', 'consumer_secret');
    }

    private function getAccessToken()
    {
        return new AccessToken('oauth_token', 'oauth_token_secret');
    }

    private function getHmacSha1ServerRequest()
    {
        $consumerKey = $this->getConsumerKey();
        $accessToken = $this->getAccessToken();

        $url = 'http://example.jp/';
        $params = [
            'hoge' => 'ほげほげ',
            'fuga' => 'ふがふが',
        ];
        $url .= '?' . http_build_query($params);

        $authorizationValue = 'OAuth realm="http://example.jp/"';
        $authorizationValue .= ' , oauth_version="1.0"';
        $authorizationValue .= ' , oauth_signature_method="HMAC-SHA1"';
        $authorizationValue .= ' , oauth_consumer_key="' . rawurlencode($consumerKey->getToken()) . '"';
        $authorizationValue .= ' , oauth_token="' . rawurlencode($accessToken->getToken()) . '"';
        $authorizationValue .= ' , oauth_nonce="nonce"';
        $authorizationValue .= ' , oauth_timestamp="1234567890"';
        $authorizationValue .= ' , oauth_signature="qBoVJCjYWRHEmXh5VRqzItHuA50="';

        return (new ServerRequest())
            ->withUri(new Uri($url))
            ->withMethod('GET')
            ->withHeader('Authorization', $authorizationValue);
    }

    private function getRsaSha1ServerRequest()
    {
        $consumerKey = $this->getConsumerKey();
        $accessToken = $this->getAccessToken();

        $url = 'http://example.jp/';
        $params = [
            'hoge' => 'ほげほげ',
            'fuga' => 'ふがふが',
        ];
        $url .= '?' . http_build_query($params);

        $authorizationValue = 'OAuth realm="http://example.jp/"';
        $authorizationValue .= ' , oauth_version="1.0"';
        $authorizationValue .= ' , oauth_signature_method="RSA-SHA1"';
        $authorizationValue .= ' , oauth_consumer_key="' . rawurlencode($consumerKey->getToken()) . '"';
        $authorizationValue .= ' , oauth_token="' . rawurlencode($accessToken->getToken()) . '"';
        $authorizationValue .= ' , oauth_nonce="nonce"';
        $authorizationValue .= ' , oauth_timestamp="1234567890"';
        $authorizationValue .= ' , oauth_signature="CIfhEbtNGmK5GD5Ny3W5pKbJX55gQwy8qUXl5rx6USpbOzwAOELGu+YCVCK5RjZHGBWajAdd/k2J5gVTpWS/uyk+UpFhEnhrbs9CczNP+yJFHmG16QY0DPep+Rs3h3wX5HHRQteHW3a2CzjohyiqCOMM7wUZFwzADIIugkvuZ3NKcd++Tp2PgDA6ZUejARfCXcHztMFcJWPPziCWjtArpCi07qSp/ips4v6F0mtuvx/VrkGfMLWMq4ZQ0q+QF9mooM5THvrSJ2tEkzoQe+MJm/ioDG7JFQ9NYNfJtXycQ4mL790SiZvs68DHd91sR2EF2cK1RBZ4QdCTerjlzyJVXg=="';

        return (new ServerRequest())
            ->withUri(new Uri($url))
            ->withMethod('GET')
            ->withHeader('Authorization', $authorizationValue);
    }

    private function getPlainTextServerRequest()
    {
        $consumerKey = $this->getConsumerKey();
        $accessToken = $this->getAccessToken();

        $url = 'https://example.jp/';
        $params = [
            'hoge' => 'ほげほげ',
            'fuga' => 'ふがふが',
        ];

        $plainTextSignature = rawurlencode($consumerKey->getTokenSecret())
            . '&' . rawurlencode($accessToken->getTokenSecret());

        $authorizationValue = 'OAuth realm="https://example.jp/"';
        $authorizationValue .= ' , oauth_version="1.0"';
        $authorizationValue .= ' , oauth_signature_method="PLAINTEXT"';
        $authorizationValue .= ' , oauth_consumer_key="' . rawurlencode($consumerKey->getToken()) . '"';
        $authorizationValue .= ' , oauth_token="' . rawurlencode($accessToken->getToken()) . '"';
        $authorizationValue .= ' , oauth_nonce="nonce"';
        $authorizationValue .= ' , oauth_timestamp="1234567890"';
        $authorizationValue .= ' , oauth_signature="' . $plainTextSignature . '"';

        return (new ServerRequest())
            ->withUri(new Uri($url))
            ->withMethod('POST')
            ->withBodyContents(http_build_query($params))
            ->withHeader('Content-Type', 'application/x-www-form-urlencoded')
            ->withHeader('Authorization', $authorizationValue);
    }
}
