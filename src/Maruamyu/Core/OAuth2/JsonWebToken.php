<?php

namespace Maruamyu\Core\OAuth2;

use Maruamyu\Core\Base64Url;

/**
 * JSON Web Token
 */
class JsonWebToken
{
    const MEDIA_TYPE = 'application/jwt';

    const JSON_ENCODE_OPTIONS = JSON_UNESCAPED_SLASHES;

    /**
     * @param string $jwtString
     * @param JsonWebKey[] $jwks [ kid => JsonWebKey, ... ]  (ignore signature if empty)
     * @return array payload
     */
    public static function parse($jwtString, array $jwks = [])
    {
        $parts = explode('.', $jwtString);
        if (count($parts) < 2) {
            throw new \InvalidArgumentException('invalid JWT');
        }

        # header
        $headerJson = Base64Url::decode($parts[0]);
        $header = json_decode($headerJson, true);
        if (isset($header['alg']) == false) {
            throw new \InvalidArgumentException('invalid JWT : alg is empty');
        }
        if (isset($header['enc']) || (isset($header['typ']) && ($header['typ'] === 'JWE'))) {
            throw new \RuntimeException('JWE is not supported');
        }

        # payload
        $payloadJson = Base64Url::decode($parts[1]);
        $payload = json_decode($payloadJson, true);

        # signature
        if (isset($parts[2]) && (empty($jwks) == false)) {
            if (isset($header['kid']) == false) {
                throw new \InvalidArgumentException('invalid JWT : kid is empty');
            }
            $keyId = strval($header['kid']);
            if (isset($jwks[$keyId]) == false) {
                throw new \RuntimeException('key not found. (kid=' . $keyId . ')');
            }
            $jsonWebkey = $jwks[$keyId];

            $alg = $jsonWebkey->getAlgorithm();
            $hashAlgorithms = JsonWebAlgorithms::HASH_ALGORITHM;
            if (isset($hashAlgorithms[$alg]) == false) {
                throw new \RuntimeException('alg=' . $alg . ' is not supported');
            }
            if (isset($header['alg'])) {
                if ($header['alg'] !== $alg) {
                    $errorMsg = 'Algorithm not match. (jwk.alg=' . $alg . ', jwt.alg=' . $header['alg'] . ')';
                    throw new \RuntimeException($errorMsg);
                }
            }

            $message = $parts[0] . '.' . $parts[1];
            $signature = Base64Url::decode($parts[2]);
            $verified = $jsonWebkey->verifySignature($message, $signature);
            if (!$verified) {
                throw new \InvalidArgumentException('invalid JWT : signature not match');
            }
        }

        return $payload;
    }

    /**
     * @param array $payload
     * @param JsonWebKey $jsonWebKey (exclude signature if null)
     * @return string
     */
    public static function build(array $payload, JsonWebKey $jsonWebKey = null)
    {
        # header and payload
        $header = ['typ' => 'JWT'];
        if ($jsonWebKey) {
            $header['alg'] = $jsonWebKey->getAlgorithm();
            $header['kid'] = $jsonWebKey->getKeyId();
        } else {
            $header['alg'] = 'none';
        }
        $parts = [
            Base64Url::encode(json_encode($header, static::JSON_ENCODE_OPTIONS)),
            Base64Url::encode(json_encode($payload, static::JSON_ENCODE_OPTIONS)),
        ];

        # signature
        if ($jsonWebKey) {
            $message = join('.', $parts);
            $signature = $jsonWebKey->makeSignature($message);
            $parts[] = Base64Url::encode($signature);
        } else {
            $parts[] = '';
        }

        return join('.', $parts);
    }

    /**
     * constructor (private)
     */
    private function __construct()
    {
    }
}
