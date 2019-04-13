<?php

namespace Maruamyu\Core;

class Asn1Test extends \PHPUnit\Framework\TestCase
{
    public function test_encodeBoolean()
    {
        $expects1 = pack('C*', 0x01, 0x01, 0x01);
        $actual1 = Asn1::encodeBoolean(true);
        $this->assertEquals($expects1, $actual1);

        $expects2 = pack('C*', 0x01, 0x01, 0x00);
        $actual2 = Asn1::encodeBoolean(false);
        $this->assertEquals($expects2, $actual2);
    }

    public function test_encodeIntegerBinary()
    {
        $expects1 = pack('C*', 0x02, 0x01, 0x80);
        $actual1 = Asn1::encodeIntegerBinary(chr(0x80));
        $this->assertEquals($expects1, $actual1);

        $expects2 = pack('C*', 0x02, 0x02, 0x00, 0x80);
        $actual2 = Asn1::encodeIntegerBinary(chr(0x80), true);
        $this->assertEquals($expects2, $actual2);
    }

    public function test_encodeNull()
    {
        $expects = pack('C*', 0x05, 0x00);
        $actual = Asn1::encodeNull();
        $this->assertEquals($expects, $actual);
    }

    public function test_encodeBitString()
    {
        $string = 'なんか適当な文字列';
        $expects = chr(0x03) . Asn1::toLengthBinary(strlen($string) + 1) . chr(0x00) . $string;
        $actual = Asn1::encodeBitString($string);
        $this->assertEquals($expects, $actual);
    }

    public function test_encodeOctetString()
    {
        $string = 'なんか適当な文字列';
        $expects = chr(0x04) . Asn1::toLengthBinary(strlen($string)) . $string;
        $actual = Asn1::encodeOctetString($string);
        $this->assertEquals($expects, $actual);
    }

    public function test_encodeObjectIdentifier()
    {
        $expects = pack('C*', 0x06, 0x09, 0x2A, 0x86, 0x48, 0x86, 0xF7, 0x0D, 0x01, 0x01, 0x01);
        $actual = Asn1::encodeObjectIdentifier('1.2.840.113549.1.1.1');
        $this->assertEquals($expects, $actual);
    }

    public function test_toObjectIdentifierBinary()
    {
        $expects = pack('C*', 0x2A, 0x86, 0x48, 0x86, 0xF7, 0x0D, 0x01, 0x01, 0x01);
        $actual = Asn1::toObjectIdentifierBinary('1.2.840.113549.1.1.1');
        $this->assertEquals($expects, $actual);
    }

    public function test_toLengthBinary()
    {
        $expects1 = pack('C*', 0x01);
        $actual1 = Asn1::toLengthBinary(1);
        $this->assertEquals($expects1, $actual1);

        $expects = pack('C*', 0x82, 0x01, 0x0F);
        $actual = Asn1::toLengthBinary(271);
        $this->assertEquals($expects, $actual);
    }
}
