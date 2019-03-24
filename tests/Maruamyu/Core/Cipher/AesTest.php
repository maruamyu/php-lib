<?php

namespace Maruamyu\Core\Cipher;

class AesTest extends \PHPUnit\Framework\TestCase
{
    public function test_encrypt_decrypt_128_cbc()
    {
        $key = '7657657657657657';
        $aes = new Aes($key, 'CBC');

        $cleartext = 'アイドルマスターミリオンライブ！シアターデイズ';
        $iv = $aes->makeIV();
        $encrypted = $aes->encrypt($cleartext, $iv);

        $this->assertEquals($aes->decrypt($encrypted, $iv), $cleartext);
    }

    public function test_encrypt_decrypt_192_cbc()
    {
        $key = '346346346346346346346346';
        $aes = new Aes($key, 'CBC');

        $cleartext = 'アイドルマスターシンデレラガールズ スターライトステージ';
        $iv = $aes->makeIV();
        $encrypted = $aes->encrypt($cleartext, $iv);

        $this->assertEquals($aes->decrypt($encrypted, $iv), $cleartext);
    }

    public function test_encrypt_decrypt_256_cbc()
    {
        $key = '31531531531531531531531531531531';
        $aes = new Aes($key, 'CBC');

        $cleartext = 'アイドルマスターSideM ライブオンステージ！';
        $iv = $aes->makeIV();
        $encrypted = $aes->encrypt($cleartext, $iv);

        $this->assertEquals($aes->decrypt($encrypted, $iv), $cleartext);
    }

    public function test_encrypt_decrypt_128_ctr()
    {
        $key = '2832832832832832';
        $aes = new Aes($key, 'CTR');

        $cleartext = 'アイドルマスターシャイニーカラーズ';
        $iv = $aes->makeIV();
        $encrypted = $aes->encrypt($cleartext, $iv);

        $this->assertEquals($aes->decrypt($encrypted, $iv), $cleartext);
    }
}
