<?php

namespace Maruamyu\Core\Http\Message;

class StreamTest extends \PHPUnit\Framework\TestCase
{
    /**
     * ストリームを閉じる.
     */
    public function test_close()
    {
        $stream = Stream::fromTemp();
        $stream->close();
        $this->assertNull($stream->detach());
    }

    /**
     * ストリームがseek可能かどうか判定する.
     */
    public function test_isSeekable()
    {
        $stream = Stream::fromTemp();
        $this->assertTrue($stream->isSeekable());
    }

    /**
     * ストリームが読み込み可能かどうか判定する.
     */
    public function test_isReadable()
    {
        $stream = Stream::fromTemp();
        $this->assertTrue($stream->isReadable());

        $writeOnly = fopen(__FILE__, 'c');
        $writeOnlyStream = new Stream($writeOnly);
        $this->assertFalse($writeOnlyStream->isReadable());
    }

    /**
     * ストリームが書き込み可能かどうか判定する.
     */
    public function test_isWritable()
    {
        $stream = Stream::fromTemp();
        $this->assertTrue($stream->isWritable());

        $readOnly = fopen(__FILE__, 'r');
        $readOnlyStream = new Stream($readOnly);
        $this->assertFalse($readOnlyStream->isWritable());
    }

    /**
     * ストリームのハンドラ(PHPのresource型の値)を返す.
     */
    public function test_detach()
    {
        $stream = Stream::fromTemp();
        $this->assertEquals('resource', gettype($stream->detach()));
        $this->assertNull($stream->detach());
    }

    /**
     * ストリームデータのサイズを返す.
     */
    public function test_getSize()
    {
        $stream = Stream::fromTemp();
        $this->assertEquals(0, $stream->getSize());

        $str = 'ほげふがぴよ';
        $stream->write($str);
        $this->assertEquals(strlen($str), $stream->getSize());
    }

    /**
     * ストリームのファイルポインタ位置を返す.
     */
    public function test_tell()
    {
        $stream = Stream::fromTemp();
        $this->assertEquals(0, $stream->tell());

        $str = 'ほげふがぴよ';
        $stream->write($str);
        $this->assertEquals(strlen($str), $stream->tell());
    }

    /**
     * ストリームのファイルポインタ位置がファイル末尾かどうか判定する.
     */
    public function test_eof()
    {
        $stream = Stream::fromTemp();

        # 直観的ではないが仕様上こうなる
        $this->assertFalse($stream->eof());

        # 全部読み込んで末尾へ移動させる
        $stream->getContents();
        $this->assertTrue($stream->eof());
    }

    /**
     * ストリームのファイルポインタ位置を移動する.
     */
    public function test_seek()
    {
        $stream = Stream::fromTemp();

        $str = 'ほげふがぴよ';
        $stream->write($str);
        $this->assertEquals(strlen($str), $stream->tell());

        $back = 6;
        $stream->seek((0 - $back), SEEK_CUR);
        $this->assertEquals((strlen($str) - $back), $stream->tell());

        $setPos = 9;
        $stream->seek($setPos, SEEK_SET);
        $this->assertEquals($setPos, $stream->tell());

        $stream->seek(0, SEEK_END);
        $this->assertEquals(strlen($str), $stream->tell());
    }

    /**
     * ストリームのファイルポインタ位置を先頭へ移動する.
     *
     * @throws \RuntimeException なんらかのエラーが発生したとき
     */
    public function test_rewind()
    {
        $stream = Stream::fromTemp();

        $str = 'ほげふがぴよ';
        $stream->write($str);

        $stream->rewind();
        $this->assertEquals($str, $stream->getContents());
    }

    /**
     * ストリームのデータを全て読み込んで返す.
     */
    public function test_toString()
    {
        $stream = Stream::fromTemp();
        $str = 'ほげふがぴよ';
        $stream->write($str);
        $this->assertEquals($str, strval($stream));
    }

    /**
     * ストリームからデータを読み込みする.
     */
    public function test_read()
    {
        $stream = Stream::fromTemp();

        $str = 'ほげふがぴよ';
        $stream->write($str);

        $stream->rewind();
        $buffer = $stream->read(strlen($str));
        $this->assertEquals($str, $buffer);

        $stream->rewind();
        $this->assertEquals(substr($str, 0, 10), $stream->read(10));

        $stream->rewind();
        $stream->seek(10, SEEK_SET);
        $this->assertEquals(substr($str, 10, 10), $stream->read(10));
    }

    /**
     * ストリームのメタデータを取得する.
     */
    public function test_getMetadata()
    {
        $stream = Stream::fromTemp();
        $this->assertEquals('array', gettype($stream->getMetadata()));
    }
}
