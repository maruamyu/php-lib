<?php

namespace Maruamyu\Core\OAuth2;

use Maruamyu\Core\Base64Url;

/**
 * JSON Web Token (RFC 7519)
 */
class JsonWebToken
{
    const MEDIA_TYPE = 'application/jwt';

    const JSON_ENCODE_OPTIONS = JSON_UNESCAPED_SLASHES;

    /**
     * @param string $jwtString
     * @param JsonWebKey[] $jwks [ kid => JsonWebKey, ... ]  (ignore signature if empty)
     * @return array payload
     * @throws \DomainException JWE is not supported
     * @throws \Exception if invalid JWT
     */
    public static function parse($jwtString, array $jwks = [])
    {
        $parts = explode('.', $jwtString);
        if (count($parts) < 2) {
            throw new \DomainException('invalid JWT');
        }

        # header
        $headerJson = Base64Url::decode($parts[0]);
        $header = json_decode($headerJson, true);
        if (isset($header['alg']) == false) {
            throw new \DomainException('invalid JWT : alg is empty');
        }
        if (isset($header['enc']) || (isset($header['typ']) && ($header['typ'] === 'JWE'))) {
            # TODO JWE is not supported
            throw new \DomainException('JWE is not supported');
        }

        # payload
        $payloadJson = Base64Url::decode($parts[1]);
        $payload = json_decode($payloadJson, true);

        # signature
        if (isset($parts[2]) && (empty($jwks) == false)) {
            if (isset($header['kid']) == false) {
                throw new \DomainException('invalid JWT : kid is empty');
            }
            $keyId = strval($header['kid']);
            if (isset($jwks[$keyId]) == false) {
                throw new \DomainException('key not found. (kid=' . $keyId . ')');
            }
            $jsonWebkey = $jwks[$keyId];

            $alg = $jsonWebkey->getAlgorithm();
            if (JsonWebAlgorithms::isSupportedHashAlgorithm($alg) == false) {
                throw new \DomainException('alg=' . $alg . ' is not supported');
            }
            if (isset($header['alg'])) {
                if ($header['alg'] !== $alg) {
                    $errorMsg = 'Algorithm not match. (jwk.alg=' . $alg . ', jwt.alg=' . $header['alg'] . ')';
                    throw new \DomainException($errorMsg);
                }
            }

            $message = $parts[0] . '.' . $parts[1];
            $signature = Base64Url::decode($parts[2]);
            $verified = $jsonWebkey->verifySignature($message, $signature);
            if (!$verified) {
                throw new \RuntimeException('invalid JWT : signature not match');
            }
        }

        return $payload;
    }

    /**
     * @param array $payload
     * @param JsonWebKey $jsonWebKey (exclude signature if null)
     * @return string
     * @throws \Exception if invalid payload or JsonWebKey
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
     * @param array $payload
     * @param string $issuer
     * @param string $clientId
     * @return boolean
     */
    public static function validatePayload(array $payload, $issuer, $clientId)
    {
        if ((isset($payload['iss']) == false) || (strcmp($payload['iss'], $issuer) != 0)) {
            return false;
        }
        if ((isset($payload['aud']) == false) || (strcmp($payload['aud'], $clientId) != 0)) {
            return false;
        }
        if ((isset($payload['exp']) == false) || ($payload['exp'] < time())) {
            return false;
        }
        if ((isset($payload['sub']) == false) || (strlen($payload['sub']) < 1)) {
            return false;
        }
        return true;
    }

    /**
     * constructor (private)
     */
    private function __construct()
    {
    }
}
