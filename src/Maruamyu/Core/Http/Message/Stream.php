<?php

namespace Maruamyu\Core\Http\Message;

use Psr\Http\Message\StreamInterface as PsrStreamInterface;

/**
 * 独自実装を含む PSR-7準拠 データストリーム オブジェクト
 */
class Stream implements PsrStreamInterface
{
    /**
     * @var resource
     */
    protected $handler;

    /**
     * 指定されたファイルからインスタンスを生成する.
     *
     * @param string $path ファイルパス
     * @param string $mode モード
     * @return self
     * @throws \RuntimeException ファイルオープンが失敗したとき
     */
    public static function fromFilePath($path, $mode = 'c+b')
    {
        $handler = fopen($path, $mode);
        if (!$handler) {
            throw new \RuntimeException('file error: ' . $path);
        }
        return new static($handler);
    }

    /**
     * 一時データのインスタンスを生成する.
     *
     * @param string $initData 初期データ
     * @return self
     */
    public static function fromTemp($initData = '')
    {
        $handler = fopen('php://temp', 'c+b');
        $stream = new static($handler);
        if (strlen($initData) > 0) {
            $stream->write($initData);
            $stream->rewind();
        }
        return $stream;
    }

    /**
     * インスタンスを初期化する.
     *
     * @param resource $resourceHandler リソースのハンドラ
     */
    public function __construct($resourceHandler)
    {
        if (!is_resource($resourceHandler)) {
            throw new \InvalidArgumentException('invalid resource.');
        }
        $this->handler = $resourceHandler;
    }

    /**
     * 後処理
     */
    public function __destruct()
    {
        $this->close();
    }

    /**
     * ストリームのデータを全て読み込んで返す.
     *
     * @return string ストリームのデータ
     */
    public function __toString()
    {
        if (!$this->isReadable()) {
            return '';
        }
        try {
            $this->rewind();
            return $this->getContents();

        } catch (\Exception $exception) {
            return '';
        }
    }

    /**
     * ストリームを閉じる.
     *
     * @return void
     */
    public function close()
    {
        if ($this->handler) {
            fclose($this->handler);
        }
        $this->handler = null;
    }

    /**
     * ストリームのハンドラ(PHPのresource型の値)を返す.
     *
     * @return resource|null ハンドラ
     */
    public function detach()
    {
        return $this->handler;
    }

    /**
     * ストリームデータのサイズを返す.
     *
     * @return int|null サイズ(不明な場合はnull)
     */
    public function getSize()
    {
        if (!$this->handler) {
            return null;
        }
        $stats = fstat($this->handler);
        if (isset($stats['size'])) {
            return $stats['size'];
        } else {
            return null;
        }
    }

    /**
     * ストリームのファイルポインタ位置を返す.
     * ({ftell()}に相当するもの.)
     *
     * @return int ハンドラの位置
     * @throws \RuntimeException なんらかのエラーが発生したとき
     */
    public function tell()
    {
        $handler = $this->detach();
        if (!$handler) {
            throw new \RuntimeException('stream handler is null.');
        }
        return ftell($handler);
    }

    /**
     * ストリームのファイルポインタ位置がファイル末尾かどうか判定する.
     * ({feof()}に相当するもの.)
     *
     * @return boolean 末尾ならtrue, それ以外はfalse
     */
    public function eof()
    {
        $handler = $this->detach();
        if (!$handler) {
            return true;
        }
        return feof($handler);
    }

    /**
     * ストリームがseek可能かどうか判定する.
     *
     * @return boolean seek可能ならtrue, それ以外はfalse
     */
    public function isSeekable()
    {
        return $this->getMetadata('seekable');
    }

    /**
     * ストリームのファイルポインタ位置を移動する.
     * ({fseek()}に相当するもの.)
     *
     * @param int $offset 位置
     * @param int $whence 基準点(SEEK_SET等)
     * @throws \RuntimeException なんらかのエラーが発生したとき
     */
    public function seek($offset, $whence = SEEK_SET)
    {
        $handler = $this->detach();
        if (!$handler) {
            throw new \RuntimeException('stream handler is null.');
        }
        fseek($handler, $offset, $whence);
    }

    /**
     * ストリームのファイルポインタ位置を先頭へ移動する.
     *
     * @throws \RuntimeException なんらかのエラーが発生したとき
     */
    public function rewind()
    {
        $this->seek(0, SEEK_SET);
    }

    /**
     * ストリームが書き込み可能かどうか判定する.
     *
     * @return boolean 書き込み可能ならtrue, それ以外はfalse
     */
    public function isWritable()
    {
        $mode = $this->getMetadata('mode');
        if (strpos($mode, '+') !== false) {
            return true;
        }
        if (strpos($mode, 'w') !== false) {
            return true;
        }
        if (strpos($mode, 'a') !== false) {
            return true;
        }
        if (strpos($mode, 'x') !== false) {
            return true;
        }
        if (strpos($mode, 'c') !== false) {
            return true;
        }
        return false;
    }

    /**
     * ストリームに書き込みする.
     *
     * @param string $string 書き込みするデータ
     * @return int 書き込みできたデータサイズ
     * @throws \RuntimeException 失敗したとき
     */
    public function write($string)
    {
        $string = strval($string);
        if (strlen($string) < 1) {
            return 0;
        }
        $handler = $this->detach();
        if (!$handler) {
            throw new \RuntimeException('stream handler is null.');
        }
        $writtenSize = fwrite($handler, $string);
        if (!$writtenSize) {
            throw new \RuntimeException('write error');

        }
        fflush($handler);
        return $writtenSize;
    }

    /**
     * ストリームが読み込み可能かどうか判定する.
     *
     * @return boolean 読み込み可能ならtrue, それ以外はfalse
     */
    public function isReadable()
    {
        $mode = $this->getMetadata('mode');
        if (strpos($mode, '+') !== false) {
            return true;
        }
        if (strpos($mode, 'r') !== false) {
            return true;
        }
        return false;
    }

    /**
     * ストリームからデータを読み込みする.
     *
     * @param int $length データサイズ
     * @return string 読み込んだデータ
     * @throws \RuntimeException 失敗したとき
     */
    public function read($length)
    {
        $handler = $this->detach();
        if (!$handler) {
            throw new \RuntimeException('stream handler is null.');
        }
        return fread($handler, $length);
    }

    /**
     * 残りのデータを全て読み込みする.
     * ({stream_get_contents()}と同じ動作.)
     *
     * @return string 読み込んだデータ
     * @throws \RuntimeException 失敗したとき
     */
    public function getContents()
    {
        $handler = $this->detach();
        if (!$handler) {
            throw new \RuntimeException('stream handler is null.');
        }
        return stream_get_contents($handler);
    }

    /**
     * ストリームのメタデータを取得する.
     * ({stream_get_meta_data()}が返すものと同じもの.)
     *
     * @param string $key 特定のデータだけを取り出すとき指定
     * @return array|mixed|null データ
     */
    public function getMetadata($key = null)
    {
        $handler = $this->detach();
        if (!$handler) {
            return null;
        }
        $meta = stream_get_meta_data($handler);
        if (is_null($key)) {
            return $meta;
        } else {
            return $meta[$key];
        }
    }
}
