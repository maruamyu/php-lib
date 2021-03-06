<?php

namespace Maruamyu\Core\Http\Message;

class UploadedFileTest extends \PHPUnit\Framework\TestCase
{
    /**
     * ファイルのサイズ
     */
    public function test_getSize()
    {
        $uploadedFile = $this->getInstance();
        $this->assertEquals(765, $uploadedFile->getSize());
    }

    /**
     * アップロード時のエラー情報
     */
    public function test_getError()
    {
        $uploadedFile = $this->getInstance();
        $this->assertEquals(0, $uploadedFile->getError());
    }

    /**
     * 送られてきた元のファイル名
     */
    public function test_getClientFilename()
    {
        $uploadedFile = $this->getInstance();
        $this->assertEquals('43210.bin', $uploadedFile->getClientFilename());
    }

    /**
     * 送られてきたMIMEタイプ
     */
    public function test_getClientMediaType()
    {
        $uploadedFile = $this->getInstance();
        $this->assertEquals('application/octet-stream', $uploadedFile->getClientMediaType());
    }

    /**
     * ファイルのストリームハンドラ
     */
    public function test_getStream()
    {
        $uploadedFile = $this->getInstance();
        $this->assertNotNull($uploadedFile->getStream());
    }

    /**
     * initialized by file resouce
     */
    public function test_initByResouce()
    {
        $handler = fopen('php://temp', 'c+b');
        fwrite($handler, 'hogehoge');

        $fileEntry = [
            'tmp_name' => '',
            'error' => 0,
            'size' => 765,
            'name' => '43210.bin',
            'type' => 'application/octet-stream',
        ];
        $uploadedFile = new UploadedFile($fileEntry, $handler);
        $stream = $uploadedFile->getStream();
        $stream->rewind();
        $this->assertEquals('hogehoge', $stream->getContents());
    }

    /**
     * initialized by StreamInterface
     */
    public function test_initByStreamInterface()
    {
        $stream = Stream::fromTemp('hogehoge');
        $fileEntry = [
            'tmp_name' => '',
            'error' => 0,
            'size' => 765,
            'name' => '43210.bin',
            'type' => 'application/octet-stream',
        ];
        $uploadedFile = new UploadedFile($fileEntry, $stream);
        $stream = $uploadedFile->getStream();
        $stream->rewind();
        $this->assertEquals('hogehoge', $stream->getContents());
    }

    /**
     * @return UploadedFile
     */
    private function getInstance()
    {
        $handler = fopen('php://temp', 'c+b');
        $meta = stream_get_meta_data($handler);
        $fileEntry = [
            'tmp_name' => $meta['uri'],
            'error' => 0,
            'size' => 765,
            'name' => '43210.bin',
            'type' => 'application/octet-stream',
        ];
        return new UploadedFile($fileEntry);
    }
}
