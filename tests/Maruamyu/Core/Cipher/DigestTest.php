<?php

namespace Maruamyu\Core\Cipher;

class DigestTest extends \PHPUnit\Framework\TestCase
{
    public function test_sha1()
    {
        $message = 'スノウレター / 木下ひなた';
        $digest = Digest::sha1($message);
        $expects = base64_decode('O7OndVAwfRgaPSFh2Ivkf6GCQK0=');
        $this->assertEquals($expects, $digest);
    }

    public function test_sha256()
    {
        $message = 'スノウレター / 木下ひなた';
        $digest = Digest::sha256($message);
        $expects = base64_decode('EFDwbJ+bwD9z/hh7pGDaVtraQz900gEDYz98WiEPnVk=');
        $this->assertEquals($expects, $digest);
    }

    public function test_sha512()
    {
        $message = 'スノウレター / 木下ひなた';
        $digest = Digest::sha512($message);
        $expects = base64_decode('IaLYpc5uGUipmbnTWqfT/dudTEWlGpbWlcsUHePTPJphOQsofob9YxnHYuVdM94IxOYHLsAqOhFhTASqclAMOw==');
        $this->assertEquals($expects, $digest);
    }
}
