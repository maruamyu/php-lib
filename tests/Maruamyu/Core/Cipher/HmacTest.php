<?php

namespace Maruamyu\Core\Cipher;

class HmacTest extends \PHPUnit\Framework\TestCase
{
    public function test_canMakeSignature()
    {
        $hmac = new Hmac('思い出の鍵');
        $this->assertTrue($hmac->canMakeSignature());
    }

    public function test_sign_verify()
    {
        $hmac = new Hmac('思い出の鍵');
        $cleartext = 'ラブレター / ピンクチェックスクール';
        $signature = $hmac->makeSignature($cleartext);
        $this->assertTrue($hmac->verifySignature($cleartext, $signature));
    }

    public function test_sign_verify_algo()
    {
        $hmac = new Hmac('思い出の鍵');
        $cleartext = 'ラブレター / ピンクチェックスクール';
        $signature = $hmac->makeSignature($cleartext, 'sha512');
        $this->assertTrue($hmac->verifySignature($cleartext, $signature, 'sha512'));
    }

    public function test_hmac_sha1()
    {
        $message = 'メッセージ';
        $salt = 'CINDERELLA GIRLS for BEST5!';
        $signature = Hmac::sha1($message, $salt);
        $expects = base64_decode('fJPWr0T6GYtuSi1I//3OKmRIOPA=');
        $this->assertEquals($expects, $signature);
    }

    public function test_hmac_sha256()
    {
        $message = 'メッセージ';
        $salt = 'CINDERELLA GIRLS for BEST5!';
        $signature = Hmac::sha256($message, $salt);
        $expects = base64_decode('fFaYDUqzBut+Zs3mj5YUVrNvGolzVoAw49smoOZo8CU=');
        $this->assertEquals($expects, $signature);
    }

    public function test_hmac_sha512()
    {
        $message = 'メッセージ';
        $salt = 'CINDERELLA GIRLS for BEST5!';
        $signature = Hmac::sha512($message, $salt);
        $expects = base64_decode('CyKbhxwTC9W61NbbEJ735mCwiycfu7IZslrmLdem1h1CUnwCLLXlBAyl1cJCcIFC4aN1nFg1/ZBpTxouZ5B2vQ==');
        $this->assertEquals($expects, $signature);
    }
}
