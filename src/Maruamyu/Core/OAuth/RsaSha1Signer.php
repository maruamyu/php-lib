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
        $publicKeyResource = static::fetchPublicKey($publicKey);
        if (!$publicKeyResource) {
            throw new \InvalidArgumentException('invalid public key.');
        }
        $this->publicKey = $publicKeyResource;

        if ($privateKey) {
            $privateKeyResource = static::fetchPrivateKey($privateKey, $passphrase);
            if (!$privateKeyResource) {
                throw new \InvalidArgumentException('invalid private key.');
            }
            $this->privateKey = $privateKeyResource;
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

        $method = static::normalizeMethod($method);
        $uri = static::normalizeUri($uri);

        $message = static::normalizeQueryString($params);
        if ($headerParams) {
            $message->merge(static::normalizeQueryString($headerParams));
        }
        $message->delete('oauth_signature');

        $baseString = rawurlencode($method)
            . '&' . rawurlencode(strval($uri))
            . '&' . rawurlencode($message->toOAuthQueryString());

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
        $method = static::normalizeMethod($method);
        $uri = static::normalizeUri($uri);

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

        $baseString = rawurlencode($method)
            . '&' . rawurlencode(strval($uri))
            . '&' . rawurlencode($message->toOAuthQueryString());

        $verified = openssl_verify($baseString, $signature, $this->publicKey, OPENSSL_ALGO_SHA1);
        return ($verified == 1);
    }

    /**
     * @param string|resource $publicKey 公開鍵
     * @return resource|null 公開鍵リソース (入力が正しくないときnull)
     */
    private static function fetchPublicKey($publicKey)
    {
        if (is_resource($publicKey)) {
            $detail = @openssl_pkey_get_details($publicKey);
            if ($detail && $detail['type'] === OPENSSL_KEYTYPE_RSA
                && isset($detail['rsa']) && !(isset($detail['rsa']['d']))
            ) {
                return $publicKey;
            } else {
                return null;
            }
        } elseif (is_string($publicKey)) {
            $resource = openssl_pkey_get_public($publicKey);
            if ($resource) {
                return $resource;
            } else {
                return null;
            }
        } else {
            return null;
        }
    }

    /**
     * @param string|resource $privateKey 秘密鍵
     * @param string $passphrase 鍵のパスフレーズ
     * @return resource|null 秘密鍵リソース (入力が正しくないときnull)
     */
    private static function fetchPrivateKey($privateKey, $passphrase = null)
    {
        if (is_resource($privateKey)) {
            $detail = @openssl_pkey_get_details($privateKey);
            if ($detail && $detail['type'] === OPENSSL_KEYTYPE_RSA
                && isset($detail['rsa']) && isset($detail['rsa']['d'])
            ) {
                return $privateKey;
            } else {
                return null;
            }
        } elseif (is_string($privateKey)) {
            $resource = openssl_pkey_get_private($privateKey, $passphrase);
            if ($resource) {
                return $resource;
            } else {
                return null;
            }
        } else {
            return null;
        }
    }
}
