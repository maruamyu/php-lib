<?php

namespace Maruamyu\Core\Http\Message;

use Psr\Http\Message\UriInterface as PsrUriInterface;

trait NormalizeMessageTrait
{
    /**
     * @param string $method リクエストメソッド
     * @return string リクエストメソッド(大文字)
     *   (指定が空だったときは"GET"が返る)
     */
    protected static function normalizeMethod($method)
    {
        $method = strtoupper($method);
        if (strlen($method) < 1) {
            $method = 'GET';
        }
        return $method;
    }

    /**
     * @param null|string|array|PsrUriInterface $uri URLデータ
     * @return UriInterface Uriインスタンス
     * @throws \InvalidArgumentException 指定されたURLが正しくないとき
     */
    protected static function normalizeUri($uri)
    {
        if ($uri instanceof UriInterface) {
            return clone $uri;
        } else {
            return new Uri($uri);
        }
    }

    /**
     * @param string|array|QueryString $params パラメータ
     * @return QueryString QueryStringインスタンス
     */
    protected static function normalizeQueryString($params)
    {
        if ($params instanceof QueryString) {
            return clone $params;
        } else {
            return new QueryString($params);
        }
    }
}
