<?php

namespace Maruamyu\Core\OAuth2;

/**
 * JSON Web Algorithms (RFC 7518)
 */
class JsonWebAlgorithms
{
    # [ alg => [extention, hash_algorithm] ]
    const HASH_ALGORITHM = [
        'HS256' => ['hash_hmac', 'sha256'],
        'HS384' => ['hash_hmac', 'sha384'],
        'HS512' => ['hash_hmac', 'sha512'],
        'RS256' => ['openssl', OPENSSL_ALGO_SHA256],
        'RS384' => ['openssl', OPENSSL_ALGO_SHA384],
        'RS512' => ['openssl', OPENSSL_ALGO_SHA512],
        'ES256' => ['openssl', OPENSSL_ALGO_SHA256],
        'ES384' => ['openssl', OPENSSL_ALGO_SHA384],
        'ES512' => ['openssl', OPENSSL_ALGO_SHA512],
        # 'PS256' => ['openssl', OPENSSL_ALGO_SHA256],  # RSASSA-PSS is not supported
        # 'PS384' => ['openssl', OPENSSL_ALGO_SHA384],  # RSASSA-PSS is not supported
        # 'PS512' => ['openssl', OPENSSL_ALGO_SHA512],  # RSASSA-PSS is not supported
    ];

    # [ crv => curve_name ]
    const ECDSA_CURVE_NAME = [
        'P-256' => 'secp256r1',
        'P-384' => 'secp384r1',
        'P-521' => 'secp521r1',
    ];

    /**
     * @param string $alg `alg`
     * @return boolean
     */
    public static function isSupportedHashAlgorithm($alg)
    {
        $supportedAlgorithms = static::HASH_ALGORITHM;
        return isset($supportedAlgorithms[$alg]);
    }

    /**
     * @param string $curveName OpenSSL `curve_name` (example: "secp256r1")
     * @return string `crv` value (example: "P-256") or empty (if not found)
     */
    public static function getCrvValueFromCurveName($curveName)
    {
        $curveNameToCrvValue = array_flip(static::ECDSA_CURVE_NAME);
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
        $curveNames = JsonWebAlgorithms::ECDSA_CURVE_NAME;
        if (isset($curveNames[$crv])) {
            return $curveNames[$crv];
        } else {
            return '';
        }
    }
}
