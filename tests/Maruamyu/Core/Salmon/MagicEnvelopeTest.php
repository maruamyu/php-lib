<?php

namespace Maruamyu\Core\Salmon;

use Maruamyu\Core\Cipher\Hmac;
use Maruamyu\Core\Cipher\Rsa;

class MagicEnvelopeTest extends \PHPUnit\Framework\TestCase
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

    public function test_fromXml()
    {
        $xmlString = '<?xml version="1.0" encoding="UTF-8"?>' . "\n"
            . '<me:env xmlns:me="http://salmon-protocol.org/ns/magic-env">'
            . '<me:data type="application/atom+xml">'
            . 'Tm90IHJlYWxseSBBdG9t'
            . '</me:data>'
            . '<me:encoding>base64url</me:encoding>'
            . '<me:alg>RSA-SHA256</me:alg>'
            . '<me:sig key_id="4k8ikoyC2Xh+8BiIeQ+ob7Hcd2J7/Vj3uM61dy9iRMI=">'
            . 'EvGSD2vi8qYcveHnb-rrlok07qnCXjn8YSeCDDXlbhILSabgvNsPpbe76up8w63i2f'
            . 'WHvLKJzeGLKfyHg8ZomQ'
            . '</me:sig>'
            . '</me:env>' . "\n";
        $magicEnvelope = MagicEnvelope::fromXml($xmlString);
        $this->assertNotNull($magicEnvelope);
        $this->assertEquals('Not really Atom', $magicEnvelope->getData());
        $this->assertEquals('application/atom+xml', $magicEnvelope->getDataType());
        $this->assertEquals('RSA-SHA256', $magicEnvelope->getAlg());
        $this->assertEquals(['4k8ikoyC2Xh+8BiIeQ+ob7Hcd2J7/Vj3uM61dy9iRMI='], $magicEnvelope->getKeyIds());
    }

    public function test_fromJson()
    {
        $jsonString = '{"data":"Tm90IHJlYWxseSBBdG9t","data_type":"application/atom+xml","encoding":"base64url","alg":"RSA-SHA256","sigs":[{"value":"EvGSD2vi8qYcveHnb-rrlok07qnCXjn8YSeCDDXlbhILSabgvNsPpbe76up8w63i2fWHvLKJzeGLKfyHg8ZomQ","key_id":"4k8ikoyC2Xh+8BiIeQ+ob7Hcd2J7/Vj3uM61dy9iRMI="}]}';
        $magicEnvelope = MagicEnvelope::fromJson($jsonString);
        $this->assertNotNull($magicEnvelope);
        $this->assertEquals('Not really Atom', $magicEnvelope->getData());
        $this->assertEquals('application/atom+xml', $magicEnvelope->getDataType());
        $this->assertEquals('RSA-SHA256', $magicEnvelope->getAlg());
        $this->assertEquals(['4k8ikoyC2Xh+8BiIeQ+ob7Hcd2J7/Vj3uM61dy9iRMI='], $magicEnvelope->getKeyIds());
    }

    public function test_fromCompactSerialization()
    {
        $serialized = '4k8ikoyC2Xh+8BiIeQ+ob7Hcd2J7/Vj3uM61dy9iRMI=.EvGSD2vi8qYcveHnb-rrlok07qnCXjn8YSeCDDXlbhILSabgvNsPpbe76up8w63i2fWHvLKJzeGLKfyHg8ZomQ.Tm90IHJlYWxseSBBdG9t.YXBwbGljYXRpb24vYXRvbSt4bWw=.YmFzZTY0dXJs.UlNBLVNIQTI1Ng';
        $magicEnvelope = MagicEnvelope::fromCompactSerialization($serialized);
        $this->assertNotNull($magicEnvelope);
        $this->assertEquals('Not really Atom', $magicEnvelope->getData());
        $this->assertEquals('application/atom+xml', $magicEnvelope->getDataType());
        $this->assertEquals('RSA-SHA256', $magicEnvelope->getAlg());
        $this->assertEquals(['4k8ikoyC2Xh+8BiIeQ+ob7Hcd2J7/Vj3uM61dy9iRMI='], $magicEnvelope->getKeyIds());
    }

    public function test_create_hmac()
    {
        $privateKey = 'common_key';

        $hmac = new Hmac($privateKey);

        $magicEnvelope = new MagicEnvelope();
        $magicEnvelope->setData('Not really Atom');
        $magicEnvelope->sign($hmac);
        $xml = $magicEnvelope->toXml();

        $result = MagicEnvelope::fromXml($xml);
        $this->assertNotEmpty($result);
        $this->assertEquals('Not really Atom', $result->getData());
        $this->assertEquals('HMAC-SHA256', $result->getAlg());
        $this->assertTrue($result->verify($hmac));
    }

    public function test_create_rsa()
    {
        $privateKey = openssl_pkey_get_private(self::PRIVATE_KEY);
        $rsa = new Rsa(null, $privateKey);

        $magicEnvelope = new MagicEnvelope();
        $magicEnvelope->setData('Not really Atom');
        # $magicEnvelope->setAlg('RSA-SHA256');  # auto detection
        $magicEnvelope->sign($rsa, 'hoge_key_id');
        $xml = $magicEnvelope->toXml();

        $result = MagicEnvelope::fromXml($xml);
        $this->assertNotEmpty($result);
        $this->assertEquals('Not really Atom', $result->getData());
        $this->assertEquals('RSA-SHA256', $result->getAlg());

        $this->assertTrue($result->verify($rsa));

        $magicPublicKey = new MagicPublicKey($rsa->exportPublicKey());
        $this->assertTrue($result->verify($magicPublicKey, 'hoge_key_id'));
    }

    public function test_getKeyIds()
    {
        $privateKey = openssl_pkey_get_private(self::PRIVATE_KEY);
        $rsa = new Rsa(null, $privateKey);

        $magicEnvelope = new MagicEnvelope();
        $magicEnvelope->setData('Not really Atom');
        $magicEnvelope->sign($rsa, 'hoge_key_id');

        $this->assertEquals(['hoge_key_id'], $magicEnvelope->getKeyIds());
    }
}
