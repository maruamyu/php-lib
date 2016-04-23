<?php

namespace Maruamyu\Core\Http\Message;

use Psr\Http\Message\StreamInterface as PsrStreamInterface;

/**
 * multipart/form-data形式データの一片を保持するクラス
 */
class MultipartData
{
    const DEFAULT_MIME_TYPE = 'application/octet-stream';

    /**
     * パラメータ名
     * @var string
     */
    private $name;

    /**
     * データストリーム
     * @var PsrStreamInterface
     */
    private $stream;

    /**
     * Content-Type
     * @var string
     */
    private $contentType;

    /**
     * ファイル名
     * @var string
     */
    private $fileName;

    /**
     * @param string $name パラメータ名
     * @param PsrStreamInterface $stream データストリーム
     * @param string $contentType MIMEタイプ
     * @param string $fileName ファイル名
     * @throws \InvalidArgumentException パラメータが正しくないとき
     */
    public function __construct($name, PsrStreamInterface $stream, $contentType = '', $fileName = '')
    {
        $name = strval($name);
        if (strlen($name) < 1) {
            throw new \InvalidArgumentException('name is empty.');
        }

        if (!$stream) {
            throw new \InvalidArgumentException('stream not found.');
        }

        $contentType = strval($contentType);
        if (strlen($contentType) < 1) {
            $contentType = static::DEFAULT_MIME_TYPE;
        }

        $fileName = strval($fileName);
        if (strlen($fileName) < 1) {
            $fileName = $name;
        }

        $this->name = $name;
        $this->stream = $stream;
        $this->contentType = $contentType;
        $this->fileName = $fileName;
    }

    /**
     * @return string $name パラメータ名
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @return PsrStreamInterface ストリームデータ
     */
    public function getStream()
    {
        return $this->stream;
    }

    /**
     * @return string $contentType MIMEタイプ
     */
    public function getContentType()
    {
        return $this->contentType;
    }

    /**
     * @return string ファイル名
     */
    public function getFileName()
    {
        return $this->fileName;
    }
}
