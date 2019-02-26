<?php

namespace Maruamyu\Core\OAuth2;

class JsonWebAlgorithms
{
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

    const ECDSA_CURVE_NAME = [
        'P-256' => 'secp256r1',
        'P-384' => 'secp384r1',
        'P-521' => 'secp521r1',
    ];
}
