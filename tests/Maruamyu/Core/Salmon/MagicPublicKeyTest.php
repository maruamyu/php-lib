<?php

namespace Maruamyu\Core\Salmon;

class MagicPublicKeyTest extends \PHPUnit\Framework\TestCase
{
    private $publicKeyPem = <<<__EOS__
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

    private $magicPublicKey = 'RSA.h3F2L2AVUQaSXhda5gGbi3Z_z37yJ_VWGvr71_yyYFk4brlQqNgMAkJIkWqm6o7QoyISwilRnLnrA5SoM6feyVwh3AndvbM_myrv7QRl3m3rye1sVP6SHbnW-0iRCIyk7_382CHNQTGBhb25oMuMGnJMTJWUQb7-2zT7fhNZmo66kZBBMIYggNAZGS68r5r5N0apR1_tjxRBUNh23OJ_HKw7GtR1vwp2AkRWgNQiIEoTW4iMaJcqFqpL5gLVthmBuFSY-1JGoEOT1vh2LRbxSyj8sM-LMvpEw-sLhS-Z46JbJ2Bv6K-GESZMWhib3ZejK2lj-9T9Cadhjmmwybf1oQ==.JQ==';

    public function test_initialize_pem()
    {
        $magicPublicKey = new MagicPublicKey($this->publicKeyPem);
        $this->assertInstanceOf(__NAMESPACE__ . '\MagicPublicKey', $magicPublicKey);
    }

    public function test_initialize_salmon()
    {
        $magicPublicKey = new MagicPublicKey($this->magicPublicKey);
        $this->assertInstanceOf(__NAMESPACE__ . '\MagicPublicKey', $magicPublicKey);
    }

    public function test_toString()
    {
        $magicPublicKey = new MagicPublicKey($this->magicPublicKey);
        $this->assertEquals($this->magicPublicKey, strval($magicPublicKey));
    }

    public function test_keyId()
    {
        $magicPublicKey = new MagicPublicKey($this->magicPublicKey);
        $this->assertEquals('YQv+cVyHyK7PX/ReLHw74sfsJIcrb8EgWrBd+tKMKcU=', $magicPublicKey->getKeyId());

        $magicPublicKey = new MagicPublicKey($this->magicPublicKey, 'hoge_key_id');
        $this->assertEquals('hoge_key_id', $magicPublicKey->getKeyId());
    }
}
