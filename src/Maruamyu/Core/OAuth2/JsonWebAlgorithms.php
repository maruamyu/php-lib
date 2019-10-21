<?php

namespace Maruamyu\Core\OAuth2;

/**
 * JSON Web Algorithms (RFC 7518)
 */
class JsonWebAlgorithms
{
    # [ alg => [extention, hash_algorithm, kty] ]
    const HASH_ALGORITHM = [
        'HS256' => ['hash_hmac', 'sha256', 'oct'],
        'HS384' => ['hash_hmac', 'sha384', 'oct'],
        'HS512' => ['hash_hmac', 'sha512', 'oct'],
        'RS256' => ['openssl', OPENSSL_ALGO_SHA256, 'RSA'],
        'RS384' => ['openssl', OPENSSL_ALGO_SHA384, 'RSA'],
        'RS512' => ['openssl', OPENSSL_ALGO_SHA512, 'RSA'],
        'ES256' => ['openssl', OPENSSL_ALGO_SHA256, 'EC'],
        'ES384' => ['openssl', OPENSSL_ALGO_SHA384, 'EC'],
        'ES512' => ['openssl', OPENSSL_ALGO_SHA512, 'EC'],
        # 'PS256' => ['openssl', OPENSSL_ALGO_SHA256, 'RSA'],  # RSASSA-PSS is not supported
        # 'PS384' => ['openssl', OPENSSL_ALGO_SHA384, 'RSA'],  # RSASSA-PSS is not supported
        # 'PS512' => ['openssl', OPENSSL_ALGO_SHA512, 'RSA'],  # RSASSA-PSS is not supported
    ];

    # [ crv => curve_name ]
    const EC_CURVE_NAME = [
        'P-256' => 'secp256r1',  # or 'prime256v1'
        'P-384' => 'secp384r1',
        'P-521' => 'secp521r1',
    ];

    # [ curve_name => crv ]
    const EC_CURVE_NAME_TO_CRV = [
        'prime256v1' => 'P-256',
        'secp256r1' => 'P-256',
        'secp384r1' => 'P-384',
        'secp521r1' => 'P-521',
    ];

    /**
     * @param string $alg `alg`
     * @param string $kty `kty`
     * @return boolean
     */
    public static function isSupportedHashAlgorithm($alg, $kty = null)
    {
        $supportedAlgorithms = static::HASH_ALGORITHM;
        if (isset($supportedAlgorithms[$alg]) == false) {
            return false;
        }
        if ($kty && ($kty !== $supportedAlgorithms[$alg][2])) {
            return false;
        }
        return true;
    }

    /**
     * @param string $kty `kty`
     * @return string[]
     */
    public static function getSupportedAlgsByKty($kty)
    {
        $supportedAlgs = [];
        foreach (static::HASH_ALGORITHM as $alg => $elem) {
            if ($elem[2] === $kty) {
                $supportedAlgs[] = $alg;
            }
        }
        return $supportedAlgs;
    }

    /**
     * @param string $curveName OpenSSL `curve_name` (example: "secp256r1")
     * @return string `crv` value (example: "P-256") or empty (if not found)
     */
    public static function getCrvValueFromCurveName($curveName)
    {
        $curveNameToCrvValue = static::EC_CURVE_NAME_TO_CRV;
        if (isset($curveNameToCrvValue[$curveName])) {
            return $curveNameToCrvValue[$curveName];
        } else {
            return '';
        }
    }

    /**
     * @param string $crv `crv` value (example: "P-256")
     * @return string OpenSSL `curve_name` (example: "secp256r1") or empty (if not found)
     */
    public static function getCurveNameFromCrvValue($crv)
    {
        $curveNames = JsonWebAlgorithms::EC_CURVE_NAME;
        if (isset($curveNames[$crv])) {
            return $curveNames[$crv];
        } else {
            return '';
        }
    }
}
