<?php

namespace Maruamyu\Core\Cipher;

/**
 * DSA (not EC) cryptography
 */
class Dsa extends PublicKeyCryptography
{
    /**
     * @param string|resource $publicKey
     * @return resource|null
     */
    public static function fetchPublicKey($publicKey)
    {
        $publicKey = parent::fetchPublicKey($publicKey);
        if (!$publicKey) {
            return null;
        }
        $detail = @openssl_pkey_get_details($publicKey);
        if (
            $detail
            && ($detail['type'] === OPENSSL_KEYTYPE_DSA)
            && isset($detail['dsa'])
            && (isset($detail['dsa']['priv_key']) == false)
        ) {
            return $publicKey;
        } else {
            return null;
        }
    }

    /**
     * @param string|resource $privateKey
     * @param string $passphrase
     * @return resource|null
     */
    public static function fetchPrivateKey($privateKey, $passphrase = null)
    {
        $privateKey = parent::fetchPrivateKey($privateKey, $passphrase);
        if (!$privateKey) {
            return null;
        }
        $detail = @openssl_pkey_get_details($privateKey);
        if (
            $detail
            && ($detail['type'] === OPENSSL_KEYTYPE_DSA)
            && isset($detail['dsa'])
            && isset($detail['dsa']['priv_key'])
        ) {
            return $privateKey;
        } else {
            return null;
        }
    }
}
