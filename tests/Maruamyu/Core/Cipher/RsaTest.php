<?php

namespace Maruamyu\Core\Cipher;

class RsaTest extends \PHPUnit\Framework\TestCase
{
    const PRIVATE_KEY = <<<__EOS__
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

    const PUBLIC_KEY = <<<__EOS__
-----BEGIN PUBLIC KEY-----
MIIBIjANBgkqhkiG9w0BAQEFAAOCAQ8AMIIBCgKCAQEA6nMlrRFuT7fjTe0k6sIn
iS8aJHpdk6S5zXnKEnyKnf0kuhuJcB+ta935SmpZ0m7CxfLOqnSYfX8ahsd6iVZm
POxUR0cbTAPRrlFYiSwRFNNINhTww/OtnNJXGDD/wG2pd2vkbIYFSDNSakWH1mE3
l5Cbbl1TXmQRJEAp9bkDf3pA8tQpkDPkJzcru1ddUuY+A6r7lnvhIKIAXiqUpQgl
wuM6yy5UAZ5DEMBkfuM2HJxCEdmIZ/dpiiCpFnW80j663vhasnAdeNj2cn8v6izz
PekZ7sFtpgS7N9w1FlZ1p9fTKlJKLC+5mSC6cpuvHuhM81tcCnKT6H2JWecuezby
LwIDAQAB
-----END PUBLIC KEY-----
__EOS__;

    const PASSPHRASE = 'passphrase';

    public function test_encrypt_decrypt()
    {
        $public = new Rsa(self::PUBLIC_KEY);
        $private = new Rsa(null, self::PRIVATE_KEY, self::PASSPHRASE);

        $cleartext = '秘密のメモリーズ / 四条貴音, 豊川風花';
        $encrypted = $public->encrypt($cleartext);
        $this->assertEquals($cleartext, $private->decrypt($encrypted));
    }

    public function test_sign_verify()
    {
        $public = new Rsa(self::PUBLIC_KEY);
        $private = new Rsa(null, self::PRIVATE_KEY, self::PASSPHRASE);

        $message = '虹色letters / Cleasky';
        $signature = $private->makeSignature($message);
        $this->assertTrue($public->verifySignature($message, $signature));
    }

    public function test_sign_verify_algo()
    {
        $public = new Rsa(self::PUBLIC_KEY);
        $private = new Rsa(null, self::PRIVATE_KEY, self::PASSPHRASE);

        $message = '虹色letters / Cleasky';
        $signature = $private->makeSignature($message, OPENSSL_ALGO_SHA512);
        $this->assertTrue($public->verifySignature($message, $signature, OPENSSL_ALGO_SHA512));
    }
}
