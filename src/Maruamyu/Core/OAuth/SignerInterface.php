<?php

namespace Maruamyu\Core\OAuth;

use Maruamyu\Core\Http\Message\QueryString;
use Maruamyu\Core\Http\Message\UriInterface;

/**
 * OAuth 署名 処理クラス インタフェース
 */
interface SignerInterface
{
    /**
     * @return string 署名生成方式
     */
    public function getSignatureMethod();

    /**
     * @param string $method メソッド
     * @param string|UriInterface $uri URL
     * @param array|QueryString $params リクエストパラメータ
     * @param array $headerParams Authorizationヘッダのパラメータ
     * @return string 署名
     */
    public function makeSignature($method, $uri, $params, $headerParams = null);

    /**
     * @param string $method メソッド
     * @param string|UriInterface $uri URL
     * @param array|QueryString $params リクエストパラメータ
     * @param array $headerParams Authorizationヘッダのパラメータ
     * @return boolean パラメータ内の署名が正しければtrue, それ以外はfalse
     */
    public function verify($method, $uri, $params, $headerParams = null);
}
