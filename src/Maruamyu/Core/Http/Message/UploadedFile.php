<?php

namespace Maruamyu\Core\Http\Message;

use Psr\Http\Message\StreamInterface;
use Psr\Http\Message\UploadedFileInterface;

/**
 * アップロードされたファイルのハンドラ.
 */
class UploadedFile implements UploadedFileInterface
{
    /**
     * @var StreamInterface
     */
    protected $stream;

    /**
     * @var string
     */
    protected $tmpName;

    /**
     * @var int
     */
    protected $error;

    /**
     * @var int
     */
    protected $size;

    /**
     * @var string
     */
    protected $name;

    /**
     * @var string
     */
    protected $type;

    /**
     * インスタンスを初期化する.
     *
     * @param array $fileEntry $_FILESのうち1ファイル分
     */
    public function __construct(array $fileEntry)
    {
        $this->stream = null;
        $this->tmpName = $fileEntry['tmp_name'];
        $this->error = $fileEntry['error'];
        $this->size = $fileEntry['size'];
        $this->name = $fileEntry['name'];
        $this->type = $fileEntry['type'];
    }

    /**
     * ファイルのストリームハンドラを取得する.
     *
     * @return StreamInterface ストリームハンドラ
     * @throws \RuntimeException 何らかのエラーが発生したとき
     */
    public function getStream()
    {
        if (!$this->stream) {
            $stream = Stream::fromFilePath($this->tmpName, 'rb');
            if (!$stream) {
                throw new \RuntimeException('upload file read error.');
            }
            $this->stream = $stream;
        }
        return $this->stream;
    }

    /**
     * ファイルを指定した場所へ保存する.
     * ({move_uploaded_file()}に相当するもの.)
     *
     * @param string $targetPath 保存場所
     * @return bool 成功したらtrue
     * @throws \InvalidArgumentException 保存場所が正しくないとき
     * @throws \RuntimeException 保存時にエラーが発生したとき
     */
    public function moveTo($targetPath)
    {
        if (strlen($targetPath) < 1) {
            throw new \InvalidArgumentException('invalid target path.');
        }
        $succeeded = move_uploaded_file($this->tmpName, $targetPath);
        if (!$succeeded) {
            throw new \RuntimeException('upload file write error.');
        }
        return $succeeded;
    }

    /**
     * ファイルのサイズを取得する.
     *
     * @return int|null ファイルサイズ
     */
    public function getSize()
    {
        return $this->size;
    }

    /**
     * アップロード時のエラー情報を取得する.
     *
     * @return int エラー番号(UPLOAD_ERR_XXX)
     */
    public function getError()
    {
        return $this->error;
    }

    /**
     * 送られてきた元のファイル名を取得する.
     *
     * @return string|null ファイル名
     */
    public function getClientFilename()
    {
        return $this->name;
    }

    /**
     * 送られてきたMIMEタイプを取得する.
     *
     * @return string|null MIMEタイプ
     */
    public function getClientMediaType()
    {
        return $this->type;
    }
}
