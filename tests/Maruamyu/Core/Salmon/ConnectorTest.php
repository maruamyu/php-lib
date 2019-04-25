<?php

namespace Maruamyu\Core\Salmon;

use Maruamyu\Core\Cipher\Rsa;

class ConnectorText extends \PHPUnit\Framework\TestCase
{
    const PRIVATE_KEY = <<<__EOS__
-----BEGIN RSA PRIVATE KEY-----
MIICXgIBAAKBgQDOJsE0EinetRH4pVDDDRzhoNt2RTggG3M+MlQFCnM8gm5Nijyr
2VHR6pMRPNrAmgh2Uw2FtFvHoTaZgP+wiog1xCcvvqKYFAyVZKKXfb1EB3cNFz0v
6dEFiMnaY77y8hCpihnUrg9U2Lt1okevXxguk5rqakBQSHcdkvk8OAz/LQIDAQAB
AoGBAKxLq7JPc/sUrt53nJZIwVi6TnH8zDnZd2oGOj60SzfJc1MPNEIUsdOWEDCa
AeJzWps6MtUKtqKUVMPbEtiED+4OZ8UKh2Nq6R6CWMgbiKcw5nw429kQqMWtREcP
vJUtWNzfvkEffw9vH2KbRknAvHcl6TsEQbDEKH1Q953BTbOpAkEA+yykUs6y+zXR
n0jAXUEporO7yZbX+yVC3nbN9IFSrdSDhWzJYn0fZu3dGDIiLO/2XUTmP+IP80K0
B8/dRb+LuwJBANIcrMfyAOLAdHgdqLSqylzFuVgUzaI37xgEfZrK2miH9w24FFhB
PIKphAmIfjKfmzzn0HK7s+y7L98/UyVJTjcCQGPiVt6PUGHR/zCGr+jl1vba3tzF
3dID+VmaiUCohQaXsk3G+zbtZyV5hijvFuQj8ScaFS5mac1lQ06v/OCV0a0CQQCf
Ah5sLqQm9ljuMhvjpkEBFo2esBezBTuHZJad15iUdRkto7qZ07z0cU9AW7CNpSY5
YIHq4kxXAo5HGEhXNnPfAkEAyDwR4ebtiluHBE8dsRiibsZcecPEhn3D4wUYPpWY
qOpYgzAjCdzkNBdmCro1vvd1CgBR2GxQLEStbg1S8hYTsA==
-----END RSA PRIVATE KEY-----
__EOS__;

    const PUBLIC_KEY = <<<__EOS__
-----BEGIN PUBLIC KEY-----
MIGfMA0GCSqGSIb3DQEBAQUAA4GNADCBiQKBgQDOJsE0EinetRH4pVDDDRzhoNt2
RTggG3M+MlQFCnM8gm5Nijyr2VHR6pMRPNrAmgh2Uw2FtFvHoTaZgP+wiog1xCcv
vqKYFAyVZKKXfb1EB3cNFz0v6dEFiMnaY77y8hCpihnUrg9U2Lt1okevXxguk5rq
akBQSHcdkvk8OAz/LQIDAQAB
-----END PUBLIC KEY-----
__EOS__;

    public function test_makeRequest()
    {
        $private = new Rsa(null, self::PRIVATE_KEY);
        $connector = new Connector($private);
        $request = $connector->makeRequest('https://example.jp/', 'Not really Atom');

        $this->assertEquals('POST', strval($request->getMethod()));
        $this->assertEquals('https://example.jp/', strval($request->getUri()));
        $this->assertEquals('application/magic-envelope+xml', strval($request->getContentType()));

        $requestBody = $request->getBody()->getContents();
        $magicEnvelope = MagicEnvelope::fromXml($requestBody);
        $this->assertEquals('Not really Atom', $magicEnvelope->getData());
    }

    public function test_verify()
    {
        $private = new Rsa(null, self::PRIVATE_KEY);

        $magicEnvelope = new MagicEnvelope();
        $magicEnvelope->setData('Not really Atom');
        $magicEnvelope->sign($private);

        $public = new Rsa(self::PUBLIC_KEY);
        $connector = new Connector($public);
        $this->assertTrue($connector->verify($magicEnvelope));

        $mpk = new MagicPublicKey(self::PUBLIC_KEY);
        $connector2 = new Connector($mpk);
        $this->assertTrue($connector2->verify($magicEnvelope));
    }

    public function test_parseRequest_xml()
    {
        $private = new Rsa(null, self::PRIVATE_KEY);
        $connector = new Connector($private);
        $xmlRequest = $connector->makeRequest('https://example.jp/', 'Not really Atom');
        $this->assertEquals('application/magic-envelope+xml', strval($xmlRequest->getContentType()));

        $magicEnvelope = Connector::parseRequest($xmlRequest);
        $this->assertNotNull($magicEnvelope);
        $this->assertEquals('Not really Atom', $magicEnvelope->getData());
    }

    public function test_parseRequest_json()
    {
        $private = new Rsa(null, self::PRIVATE_KEY);
        $connector = new Connector($private);
        $jsonRequest = $connector->makeRequest('https://example.jp/', 'Not really Atom', 'json');
        $this->assertEquals('application/magic-envelope+json', strval($jsonRequest->getContentType()));

        $magicEnvelope = Connector::parseRequest($jsonRequest);
        $this->assertNotNull($magicEnvelope);
        $this->assertEquals('Not really Atom', $magicEnvelope->getData());
    }

    public function test_parseRequest_serialization()
    {
        $private = new Rsa(null, self::PRIVATE_KEY);
        $connector = new Connector($private);
        $serializationRequest = $connector->makeRequest('https://example.jp/', 'Not really Atom', 'serialization');
        $this->assertEquals('application/magic-envelope', strval($serializationRequest->getContentType()));

        $magicEnvelope = Connector::parseRequest($serializationRequest);
        $this->assertNotNull($magicEnvelope);
        $this->assertEquals('Not really Atom', $magicEnvelope->getData());
    }

    public function test_buildXmlRequest()
    {
        $magicEnvelope = new MagicEnvelope();
        $magicEnvelope->setData('Not really Atom');

        $request = Connector::buildXmlRequest('https://example.jp/', $magicEnvelope);
        $this->assertEquals('POST', strval($request->getMethod()));
        $this->assertEquals('https://example.jp/', strval($request->getUri()));
        $this->assertEquals('application/magic-envelope+xml', strval($request->getContentType()));
        $this->assertEquals($magicEnvelope->toXml(), strval($request->getBody()));
    }

    public function test_buildJsonRequest()
    {
        $magicEnvelope = new MagicEnvelope();
        $magicEnvelope->setData('Not really Atom');

        $jsonRequest = Connector::buildJsonRequest('https://example.jp/', $magicEnvelope);
        $this->assertEquals('POST', strval($jsonRequest->getMethod()));
        $this->assertEquals('https://example.jp/', strval($jsonRequest->getUri()));
        $this->assertEquals('application/magic-envelope+json', strval($jsonRequest->getContentType()));
        $this->assertJsonStringEqualsJsonString($magicEnvelope->toJson(), strval($jsonRequest->getBody()));
    }

    public function test_buildSerializationRequest()
    {
        $magicEnvelope = new MagicEnvelope();
        $magicEnvelope->setData('Not really Atom');

        $serializationRequest = Connector::buildSerializationRequest('https://example.jp/', $magicEnvelope);
        $this->assertEquals('POST', strval($serializationRequest->getMethod()));
        $this->assertEquals('https://example.jp/', strval($serializationRequest->getUri()));
        $this->assertEquals('application/magic-envelope', strval($serializationRequest->getContentType()));
        $this->assertEquals($magicEnvelope->toCompactSerialization(), strval($serializationRequest->getBody()));
    }
}
