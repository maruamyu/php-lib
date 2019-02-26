<?php

namespace Maruamyu\Core\Http\Message;

use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\UploadedFileInterface;

/**
 * Webアプリから利用するリクエストコンテキストデータ
 */
class ServerRequest extends Request implements ServerRequestInterface
{
    /**
     * @var array
     */
    protected $serverParams;

    /**
     * @var array
     */
    protected $cookieParams;

    /**
     * @var array
     */
    protected $queryParams;

    /**
     * @var array
     */
    protected $parsedBody;

    /**
     * @var UploadedFileInterface[]
     */
    protected $uploadedFiles;

    /**
     * @var array
     */
    protected $attributes;

    /**
     * サーバーのパラメータを返す.
     *
     * @return array $_SERVER
     */
    public function getServerParams()
    {
        return $this->serverParams;
    }

    /**
     * Cookieを返す.
     *
     * @return array $_COOKIE
     */
    public function getCookieParams()
    {
        return $this->cookieParams;
    }

    /**
     * 指定されたCookieデータを設定した新しいインスタンスを返す.
     *
     * @param array $cookieParams パラメータ
     * @return static パラメータを設定した新しいインスタンス
     */
    public function withCookieParams(array $cookieParams)
    {
        $newInstance = clone $this;
        $newInstance->cookieParams = $cookieParams;
        return $newInstance;
    }

    /**
     * QUERY_STRINGの内容をパースして返す.
     * ({parse_str()}を使ったPHP互換形式)
     *
     * @return array QUERY_STRINGの内容
     */
    public function getQueryParams()
    {
        return $this->queryParams;
    }

    /**
     * 指定されたQUERY_STRINGデータを設定した新しいインスタンスを返す.
     * ({parse_str()}を使ったPHP互換形式)
     *
     * @param array $queryParams パラメータ
     * @return static パラメータを設定した新しいインスタンス
     */
    public function withQueryParams(array $queryParams)
    {
        $this->queryParams = $queryParams;
    }

    /**
     * アップロードされたファイルのリストを取得する.
     *
     * @return UploadedFileInterface[] アップロードファイルのリスト
     */
    public function getUploadedFiles()
    {
        return $this->uploadedFiles;
    }

    /**
     * アップロードされたファイルのリストを設定した新しいインスタンスを返す.
     *
     * @param UploadedFileInterface[] $uploadedFiles アップロードファイルのリスト
     * @return static パラメータを設定した新しいインスタンス
     * @throws \InvalidArgumentException 入力データが正しくないとき
     */
    public function withUploadedFiles(array $uploadedFiles)
    {
        $newInstance = clone $this;
        $newInstance->uploadedFiles = [];
        foreach ($uploadedFiles as $uploadedFile) {
            if (!($uploadedFile instanceof UploadedFileInterface)) {
                throw new \InvalidArgumentException('invalid uploaded files.');
            }
            $newInstance->uploadedFiles[] = clone $uploadedFile;
        }
        return $newInstance;
    }

    /**
     * POSTパラメータの内容をパースして返す.
     * ({parse_str()}または{json_decode()}を使ったPHP互換形式)
     *
     * @return null|array|object POSTパラメータの内容
     */
    public function getParsedBody()
    {
        return $this->parsedBody;
    }

    /**
     * POSTパラメータの内容を設定した新しいインスタンスを返す.
     * ({parse_str()}または{json_decode()}を使ったPHP互換形式)
     *
     * @param null|array|object $parsedBody POSTデータ
     * @return static POSTデータを設定した新しいインスタンス
     * @throws \InvalidArgumentException POSTデータが正しくないとき
     */
    public function withParsedBody($parsedBody)
    {
        if (!(is_null($parsedBody)) && !(is_array($parsedBody)) && !(is_object($parsedBody))) {
            throw new \InvalidArgumentException('invalid params: ' . gettype($parsedBody));
        }
        $newInstance = clone $this;
        $newInstance->parsedBody = $parsedBody;
        return $newInstance;
    }

    /**
     * リクエストの属性情報を取得する.
     *
     * @return array Attributes derived from the request.
     */
    public function getAttributes()
    {
        return $this->attributes;
    }

    /**
     * リクエストの属性値を取得する.
     *
     * @param string $name 属性名
     * @param mixed $default デフォルト値
     * @return mixed 属性値
     * @see getAttributes()
     */
    public function getAttribute($name, $default = null)
    {
        if (isset($this->attributes[$name])) {
            return $this->attributes[$name];
        } else {
            return $default;
        }
    }

    /**
     * リクエストの属性情報を設定した新しいインスタンスを返す.
     *
     * @param string $name 属性名
     * @param mixed $value 属性値
     * @return static 属性情報を設定した新しいインスタンス
     * @see getAttributes()
     */
    public function withAttribute($name, $value)
    {
        $newInstance = clone $this;
        $newInstance->attributes[$name] = $value;
        return $newInstance;
    }

    /**
     * リクエストの属性情報を削除した新しいインスタンスを返す.
     *
     * @param string $name 属性名
     * @return static 属性情報から属性名を削除した新しいインスタンス
     * @see getAttributes()
     */
    public function withoutAttribute($name)
    {
        $newInstance = clone $this;
        if (isset($newInstance->attributes[$name])) {
            unset($newInstance->attributes[$name]);
        }
        return $newInstance;
    }
}
