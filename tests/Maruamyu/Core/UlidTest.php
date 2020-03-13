<?php

namespace Maruamyu\Core;

class UlidTest extends \PHPUnit\Framework\TestCase
{
    const EXPECTS_REGEXP = '/^[0123456789ABCDEFGHJKMNPQRSTVWXYZ]{26}$/u';

    public function test_generate()
    {
        $generated = [];

        Ulid::$configForceBcmath = false;
        for ($i = 0; $i < 100; $i++) {
            $ulid = Ulid::generate();
            $this->assertRegExp(self::EXPECTS_REGEXP, $ulid);
            $this->assertArrayNotHasKey($ulid, $generated);
            $generated[$ulid] = $i;
        }

        Ulid::$configForceBcmath = true;
        for ($i = 0; $i < 100; $i++) {
            $ulid = Ulid::generate();
            $this->assertRegExp(self::EXPECTS_REGEXP, $ulid);
            $this->assertArrayNotHasKey($ulid, $generated);
            $generated[$ulid] = $i;
        }
    }

    public function test_generate_fixed_timestamp()
    {
        $timestamp = time();  # for 32bit system...
        Ulid::$configForceBcmath = false;

        $ulid1st = Ulid::generate($timestamp);
        $ulid2nd = Ulid::generate($timestamp);

        # same timestamp
        $ulid1stTimePart = substr($ulid1st, 0, 10);
        $ulid2ndTimePart = substr($ulid2nd, 0, 10);
        $this->assertEquals($ulid1stTimePart, $ulid2ndTimePart);

        # incremental random
        $ulid1stRandomPart = substr($ulid1st, 10);
        $ulid2ndRandomPart = substr($ulid2nd, 10);

        $ulid1stRandomPartLastChar = substr($ulid1stRandomPart, -1, 1);
        $ulid2ndRandomPartLastChar = substr($ulid2ndRandomPart, -1, 1);
        $ulid1stRandomPartLastCharIdx = array_search($ulid1stRandomPartLastChar, Ulid::CHARS, true);
        $ulid2ndRandomPartLastCharIdx = array_search($ulid2ndRandomPartLastChar, Ulid::CHARS, true);
        if ($ulid2ndRandomPartLastCharIdx == 0) {
            $charsLastIdx = count(Ulid::CHARS) - 1;
            $this->assertEquals($charsLastIdx, $ulid1stRandomPartLastCharIdx);
        } else {
            $this->assertEquals(1, ($ulid2ndRandomPartLastCharIdx - $ulid1stRandomPartLastCharIdx));

            $ulid1stRandomPartCommon = substr($ulid1stRandomPart, 0, (strlen($ulid1stRandomPart) - 1));
            $ulid2ndRandomPartCommon = substr($ulid2ndRandomPart, 0, (strlen($ulid2ndRandomPart) - 1));
            $this->assertEquals($ulid1stRandomPartCommon, $ulid2ndRandomPartCommon);
        }
    }

    public function test_generate_fixed_timestamp_force_bcmath()
    {
        $timestamp = time();  # for 32bit system...
        Ulid::$configForceBcmath = true;

        $ulid1st = Ulid::generate($timestamp);
        $ulid2nd = Ulid::generate($timestamp);

        # same timestamp
        $ulid1stTimePart = substr($ulid1st, 0, 10);
        $ulid2ndTimePart = substr($ulid2nd, 0, 10);
        $this->assertEquals($ulid1stTimePart, $ulid2ndTimePart);

        # incremental random
        $ulid1stRandomPart = substr($ulid1st, 10);
        $ulid2ndRandomPart = substr($ulid2nd, 10);

        $ulid1stRandomPartLastChar = substr($ulid1stRandomPart, -1, 1);
        $ulid2ndRandomPartLastChar = substr($ulid2ndRandomPart, -1, 1);
        $ulid1stRandomPartLastCharIdx = array_search($ulid1stRandomPartLastChar, Ulid::CHARS, true);
        $ulid2ndRandomPartLastCharIdx = array_search($ulid2ndRandomPartLastChar, Ulid::CHARS, true);
        if ($ulid2ndRandomPartLastCharIdx == 0) {
            $charsLastIdx = count(Ulid::CHARS) - 1;
            $this->assertEquals($charsLastIdx, $ulid1stRandomPartLastCharIdx);
        } else {
            $this->assertEquals(1, ($ulid2ndRandomPartLastCharIdx - $ulid1stRandomPartLastCharIdx));

            $ulid1stRandomPartCommon = substr($ulid1stRandomPart, 0, (strlen($ulid1stRandomPart) - 1));
            $ulid2ndRandomPartCommon = substr($ulid2ndRandomPart, 0, (strlen($ulid2ndRandomPart) - 1));
            $this->assertEquals($ulid1stRandomPartCommon, $ulid2ndRandomPartCommon);
        }
    }

    public function test_getCurrentTimestamp()
    {
        $timestamp = Ulid::getCurrentTimestamp();
        $this->assertTrue(is_string($timestamp));
        $this->assertRegExp('/^\d+$/u', $timestamp);
    }

    public function test_extractTimestamp()
    {
        $timestamp = Ulid::getCurrentTimestamp();
        $ulid = Ulid::generate($timestamp);

        Ulid::$configForceBcmath = false;
        $this->assertEquals($timestamp, Ulid::extractTimestamp($ulid));

        Ulid::$configForceBcmath = true;
        $this->assertEquals($timestamp, Ulid::extractTimestamp($ulid));
    }
}
