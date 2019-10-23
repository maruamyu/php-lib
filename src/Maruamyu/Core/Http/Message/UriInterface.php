<?php

namespace Maruamyu\Core\Http\Message;

use Psr\Http\Message\UriInterface as PsrUriInterface;

/**
 * 独自実装を含む PSR-7準拠 URLオブジェクト インタフェース
 */
interface UriInterface extends PsrUriInterface
{
    /**
     * @param null|string|UriInterface $url URL
     * @throws \InvalidArgumentException 指定されたURLが正しくないとき
     */
    public function __construct($url = null);

    /**
     * @return string URL
     */
    public function toString();

    /**
     * @param Uri|string $uri 比較対象
     * @return bool 比較対象と同じときtrue, それ以外はfalse
     */
    public function equals($uri);

    /**
     * @return QueryString QUERY_STRINGデータ
     */
    public function getQueryString();

    /**
     * @param string|array|QueryString $queryString QUERY_STRINGデータ
     * @return static 指定のQUERY_STRINGデータを設定した新しいインスタンス
     * @throws \InvalidArgumentException QUERY_STRINGデータが正しくないとき
     */
    public function withQueryString($queryString);

    /**
     * @param string|array|QueryString $queryString QUERY_STRINGデータ
     * @return static 指定のQUERY_STRINGデータを追加した新しいインスタンス
     * @throws \InvalidArgumentException QUERY_STRINGデータが正しくないとき
     */
    public function withAddedQueryString($queryString);
}
