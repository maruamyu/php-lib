<?php

namespace Maruamyu\Core\Http\Message;

use Psr\Http\Message\MessageInterface;
use Psr\Http\Message\StreamInterface;

/**
 * PSR-7準拠 HTTPメッセージ 基底クラス
 */
abstract class MessageAbstract implements MessageInterface
{
    const DEFAULT_HTTP_VERSION = '1.1';

    /** @var string */
    protected $protocolVersion;

    /** @var Headers */
    protected $headers;

    /** @var StreamInterface */
    protected $body;

    /**
     * インスタンスを初期化する.
     */
    public function __construct()
    {
        $this->protocolVersion = static::DEFAULT_HTTP_VERSION;
        $this->headers = new Headers();
    }

    /**
     * clone時のデータコピー.
     */
    public function __clone()
    {
        $this->headers = clone $this->headers;
    }

    /**
     * @return string HTTPプロトコルバージョン
     */
    public function getProtocolVersion()
    {
        return $this->protocolVersion;
    }

    /**
     * @param string $version HTTPプロトコルバージョン
     * @return static 指定のHTTPプロトコルバージョンを設定した新しいインスタンス
     */
    public function withProtocolVersion($version)
    {
        $newInstance = clone $this;
        $newInstance->protocolVersion = $version;
        return $newInstance;
    }

    /**
     * メッセージヘッダの内容を連想配列で返す.
     *
     * @return array ヘッダの内容
     */
    public function getHeaders()
    {
        return $this->headers->toArray();
    }

    /**
     * メッセージが設定されているかどうか確認する.
     *
     * @param string $name ヘッダ名
     * @return bool ヘッダが存在するならtrue, それ以外はfalse
     */
    public function hasHeader($name)
    {
        return $this->headers->has($name);
    }

    /**
     * 指定されたメッセージヘッダの値を配列で返す.
     *
     * @param string $name ヘッダ名
     * @return string[] 値のリスト
     */
    public function getHeader($name)
    {
        return $this->headers->get($name);
    }

    /**
     * 指定されたメッセージヘッダの値を, カンマ区切りの文字列で返す.
     *
     * @param string $name ヘッダ名
     * @return string 値の文字列
     */
    public function getHeaderLine($name)
    {
        return join(', ', $this->getHeader($name));
    }

    /**
     * メッセージヘッダを(以前の値を削除して)上書き設定する.
     *
     * @param string $name 設定するヘッダ名
     * @param string $value 設定するヘッダ値
     * @return static 指定のヘッダを設定した新しいインスタンス
     * @throws \InvalidArgumentException ヘッダ名または値が正しくないとき
     */
    public function withHeader($name, $value)
    {
        $newInstance = clone $this;
        $newInstance->headers->set($name, $value);
        return $newInstance;
    }

    /**
     * ヘッダを追加する.
     * (指定されたヘッダが存在する場合は保持され, 指定した値が追加される.)
     *
     * @param string $name 追加するヘッダ名
     * @param string $value 追加するヘッダ値
     * @return static 指定のヘッダを追加した新しいインスタンス
     * @throws \InvalidArgumentException ヘッダ名または値が正しくないとき
     */
    public function withAddedHeader($name, $value)
    {
        $newInstance = clone $this;
        $newInstance->headers->add($name, $value);
        return $newInstance;
    }

    /**
     * メッセージヘッダを削除する.
     *
     * @param string $name 削除するヘッダ名
     * @return static 指定のヘッダを削除した新しいインスタンス
     */
    public function withoutHeader($name)
    {
        $newInstance = clone $this;
        $newInstance->headers->delete($name);
        return $newInstance;
    }

    /**
     * @return StreamInterface 内容を保持したストリーム
     */
    public function getBody()
    {
        if (!$this->body) {
            $this->body = Stream::fromTemp();
        }
        return $this->body;
    }

    /**
     * @param StreamInterface $body 内容を保持したストリーム
     * @return static 指定の内容を設定した新しいインスタンス
     * @throws \InvalidArgumentException 内容が正しくないとき
     */
    public function withBody(StreamInterface $body)
    {
        if ($body === $this->body) {
            return $this;
        }
        $newInstance = clone $this;
        $newInstance->body = $body;
        return $newInstance;
    }

    /**
     * 内容からデータストリームを生成して設定する.
     * (PSR-7規定にない独自実装メソッド)
     *
     * @param string $contents 内容
     * @return static 指定の内容を設定した新しいインスタンス
     */
    public function withBodyContents($contents)
    {
        $newInstance = clone $this;
        $newInstance->body = Stream::fromTemp($contents);
        return $newInstance;
    }

    /**
     * Content-Typeの取得.
     * (PSR-7規定にない独自実装メソッド)
     *
     * @return string Content-Type
     */
    public function getContentType()
    {
        $contentType = '';
        $values = $this->getHeader('Content-Type');
        if (!empty($values)) {
            # 最後に設定されたものを取り出す
            $contentType = $values[(count($values) - 1)];
        }
        return $contentType;
    }

    /**
     * メッセージヘッダのフィールド一覧を取得する.
     * ("ヘッダ名: 値" の配列が返る)
     * (PSR-7規定にない独自実装メソッド)
     *
     * @return string[] ヘッダのフィールド一覧
     */
    public function getHeaderFields()
    {
        return $this->headers->fields();
    }

    /**
     * return `Date` header value
     * (PSR-7規定にない独自実装メソッド)
     *
     * @return \DateTimeImmutable|null
     */
    public function getDate()
    {
        try {
            $dateValues = $this->getHeader('Date');
            $dateValue = array_pop($dateValues);
            if ($dateValue) {
                return new \DateTimeImmutable($dateValue);
            }
        } catch (\Exception $exception) {
            ;
        }
        return null;
    }
}
