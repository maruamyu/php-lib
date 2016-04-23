<?php

namespace Maruamyu\Core\OAuth;

use Maruamyu\Core\Http\Message\NormalizeMessageTrait;
use Maruamyu\Core\Http\Message\QueryString;
use Maruamyu\Core\Http\Message\UriInterface;

/**
 * OAuth RSA-SHA1署名 処理クラス
 */
class RsaSha1Signer implements SignerInterface
{
    use NormalizeMessageTrait;

    /**
     * @var string
     */
    private $publicKey;

    /***
     * @var string
     */
    private $privateKey;

    /**
     * インスタンスを初期化する.
     *
     * 鍵は string または resource
     * string - PEM形式の値
     * resource - openssl_pkey_get_* で取得できる値
     *
     * @param string|resource $publicKey 公開鍵
     * @param string|resource $privateKey 秘密鍵
     * @param string $passphrase 鍵のパスフレーズ
     * @throws \InvalidArgumentException 鍵が正しくない場合
     */
    public function __construct($publicKey, $privateKey = null, $passphrase = null)
    {
        if (is_resource($publicKey)) {
            $this->publicKey = $publicKey;
        } elseif (is_string($publicKey)) {
            $this->publicKey = openssl_pkey_get_public($publicKey);
        } else {
            throw new \InvalidArgumentException('invalid public key.');
        }

        if (is_null($privateKey)) {
            $this->privateKey = null;
        } elseif (is_resource($privateKey)) {
            $this->privateKey = $privateKey;
        } elseif (is_string($privateKey)) {
            $this->privateKey = openssl_pkey_get_private($privateKey, $passphrase);
        } else {
            throw new \InvalidArgumentException('invalid private key.');
        }
    }

    /**
     * @return string 署名生成方式
     */
    public function getSignatureMethod()
    {
        return 'RSA-SHA1';
    }

    /**
     * @param string $method メソッド
     * @param string|UriInterface $uri URL
     * @param array|QueryString $params リクエストパラメータ
     * @param array $headerParams Authorizationヘッダのパラメータ
     * @return string 署名(バイナリデータ)
     * @throws \RuntimeException 秘密鍵が指定されていない場合
     */
    public function makeSignature($method, $uri, $params, $headerParams = null)
    {
        if (!$this->privateKey) {
            throw new \RuntimeException('private key required.');
        }

        $message = static::normalizeQueryString($params);
        if ($headerParams) {
            $message->merge(static::normalizeQueryString($headerParams));
        }
        $message->delete('oauth_signature');

        $baseString = $this->makeBaseString($method, $uri, $message);

        $signature = null;
        $succeeded = openssl_sign($baseString, $signature, $this->privateKey, OPENSSL_ALGO_SHA1);
        if ($succeeded) {
            return $signature;
        } else {
            return null;
        }
    }

    /**
     * @param string $method メソッド
     * @param string|UriInterface $uri URL
     * @param array|QueryString $params リクエストパラメータ
     * @param array $headerParams Authorizationヘッダのパラメータ
     * @return boolean パラメータ内の署名が正しければtrue, それ以外はfalse
     */
    public function verify($method, $uri, $params, $headerParams = null)
    {
        $message = static::normalizeQueryString($params);
        if ($headerParams) {
            $message->merge(static::normalizeQueryString($headerParams));
        }

        list($signatureMethod) = $message->get('oauth_signature_method');
        if (strcasecmp($signatureMethod, $this->getSignatureMethod()) != 0) {
            return false;
        }

        list($signatureString) = $message->delete('oauth_signature');
        $signature = base64_decode($signatureString);

        $baseString = $this->makeBaseString($method, $uri, $message);

        $verified = openssl_verify($baseString, $signature, $this->publicKey, OPENSSL_ALGO_SHA1);
        return ($verified == 1);
    }

    /**
     * @param string $method メソッド
     * @param UriInterface $uri URL
     * @param QueryString $message パラメータ
     * @return string base_string
     */
    private function makeBaseString($method, UriInterface $uri, QueryString $message)
    {
        $method = static::normalizeMethod($method);
        $uri = static::normalizeUri($uri);

        $workMessage = clone $message;
        $workMessage->delete('oauth_signature');

        $baseString = rawurlencode($method)
            . '&' . rawurlencode(strval($uri))
            . '&' . rawurlencode($workMessage->toOAuthQueryString());

        return $baseString;
    }
}
