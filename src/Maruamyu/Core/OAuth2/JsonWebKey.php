<?php

namespace Maruamyu\Core\OAuth2;

use Maruamyu\Core\Base64Url;
use Maruamyu\Core\Cipher\Aes;
use Maruamyu\Core\Cipher\Ecdsa;
use Maruamyu\Core\Cipher\EncryptionInterface;
use Maruamyu\Core\Cipher\Hmac;
use Maruamyu\Core\Cipher\Rsa;
use Maruamyu\Core\Cipher\SignatureInterface;

/**
 * JSON Web Key (RFC7517)
 */
class JsonWebKey
{
    const MEDIA_TYPE = 'application/jwk+json';

    const KEY_TYPES_PARAMETERS = [
        'EC' => ['crv', 'x', 'y'],
        'RSA' => ['n', 'e'],
        'oct' => ['k'],
    ];

    const DEFAULT_HASH_ALGORITHM = [
        'EC' => 'ES256',
        'RSA' => 'RS256',
        'oct' => 'HS256',
    ];

    const KEY_OPS = [
        'sign',
        'verify',
        'encrypt',
        'decrypt',
        'wrapKey',
        'unwrapKey',
        'deriveKey',
        'deriveBits',
    ];

    /** @var array [ OpenSSL => JWK ] */
    const OPENSSL_RSA_PARAMETERS_MAPPING = [
        'n' => 'n',
        'e' => 'e',
        'd' => 'd',
        'p' => 'p',
        'q' => 'q',
        'dmp1' => 'dp',
        'dmq1' => 'dq',
        'iqmp' => 'qi',
    ];

    const JSON_ENCODE_OPTIONS = JSON_UNESCAPED_SLASHES;

    /** @var array */
    protected $data;

    /**
     * @param string|array $initValue JSON or Array
     * @throws \Exception if invalid data
     */
    public function __construct($initValue = null)
    {
        if (is_array($initValue)) {
            $this->setFromArray($initValue);
        } elseif (is_string($initValue)) {
            $this->setFromArray(json_decode($initValue, true));
        } elseif (isset($initValue)) {
            throw new \DomainException('invalid init value (expects JSON or Array)');
        } else {
            $this->data = [];
        }
    }

    /**
     * @param array $data
     * @throws \Exception if invalid data
     */
    protected function setFromArray(array $data)
    {
        # key type and required parameters
        if (isset($data['kty']) == false) {
            $errorMsg = 'invalid data: kty is required';
            throw new \DomainException($errorMsg);
        }
        $keyType = strval($data['kty']);
        $keyTypesParameters = static::KEY_TYPES_PARAMETERS;
        if (isset($keyTypesParameters[$keyType]) == false) {
            $errorMsg = 'invalid data: kty=' . $keyType . ' is not supported';
            throw new \DomainException($errorMsg);
        }
        foreach ($keyTypesParameters[$keyType] as $field) {
            if (isset($data[$field]) == false) {
                $errorMsg = 'invalid data: ' . $field . ' is required';
                throw new \DomainException($errorMsg);
            }
        }

        # check EC curve_name
        if ($data['kty'] === 'EC') {
            $ecdsaCurveNames = JsonWebAlgorithms::ECDSA_CURVE_NAME;
            if (isset($ecdsaCurveNames[$data['crv']]) == false) {
                $errorMsg = 'invalid data: crv=' . $data['crv'] . ' is not supported';
                throw new \DomainException($errorMsg);
            }
        }

        $this->data = $data;
    }

    /**
     * @param string|resource $ecdsaPublicKey
     * @param string $keyId
     * @param string $algorithm
     * @return static
     * @throws \Exception if invalid keys
     */
    public static function createFromEcdsaPublicKey($ecdsaPublicKey, $keyId = null, $algorithm = null)
    {
        $ecdsaPublicKey = Ecdsa::fetchPublicKey($ecdsaPublicKey);

        if (is_null($keyId)) {
            $keyId = Base64Url::encode(static::makeEcdsaKeyThumbprint($ecdsaPublicKey, 'sha256'));
        }

        if (is_null($algorithm)) {
            $algorithm = static::DEFAULT_HASH_ALGORITHM['EC'];
        }

        $detail = openssl_pkey_get_details($ecdsaPublicKey);
        $crvValue = self::getCrvValueFromCurveName($detail['ec']['curve_name']);
        $initValue = [
            'kty' => 'EC',
            'crv' => $crvValue,
            'x' => Base64Url::encode($detail['ec']['x']),
            'y' => Base64Url::encode($detail['ec']['y']),
            'kid' => $keyId,
            'alg' => $algorithm,
            'key_ops' => 'verify',
        ];
        return new static($initValue);
    }

    /**
     * @param string|resource $ecdsaPrivateKey
     * @param string $passphrase
     * @param string $keyId
     * @param string $algorithm
     * @return static
     * @throws \Exception if invalid keys
     */
    public static function createFromEcdsaPrivateKey($ecdsaPrivateKey, $passphrase = null, $keyId = null, $algorithm = null)
    {
        $ecdsaPrivateKey = Ecdsa::fetchPrivateKey($ecdsaPrivateKey, $passphrase);

        if (is_null($keyId)) {
            $keyId = Base64Url::encode(static::makeEcdsaKeyThumbprint($ecdsaPrivateKey, 'sha256'));
        }

        if (is_null($algorithm)) {
            $algorithm = static::DEFAULT_HASH_ALGORITHM['EC'];
        }

        $detail = openssl_pkey_get_details($ecdsaPrivateKey);
        $crvValue = self::getCrvValueFromCurveName($detail['ec']['curve_name']);
        $initValue = [
            'kty' => 'EC',
            'crv' => $crvValue,
            'x' => Base64Url::encode($detail['ec']['x']),
            'y' => Base64Url::encode($detail['ec']['y']),
            'd' => Base64Url::encode($detail['ec']['d']),
            'kid' => $keyId,
            'alg' => $algorithm,
            'key_ops' => 'sign',
        ];
        return new static($initValue);
    }

    /**
     * @param string|resource $rsaPublicKey
     * @param string $keyId
     * @param string $algorithm
     * @return static
     * @throws \Exception if invalid keys
     */
    public static function createFromRsaPublicKey($rsaPublicKey, $keyId = null, $algorithm = null)
    {
        $rsaPublicKey = Rsa::fetchPublicKey($rsaPublicKey);

        if (is_null($keyId)) {
            $keyId = Base64Url::encode(static::makeRsaKeyThumbprint($rsaPublicKey, 'sha256'));
        }

        if (is_null($algorithm)) {
            $algorithm = static::DEFAULT_HASH_ALGORITHM['RSA'];
        }

        $detail = openssl_pkey_get_details($rsaPublicKey);
        $initValue = [
            'kty' => 'RSA',
            'n' => Base64Url::encode($detail['rsa']['n']),
            'e' => Base64Url::encode($detail['rsa']['e']),
            'kid' => $keyId,
            'alg' => $algorithm,
            'key_ops' => 'verify',
        ];
        return new static($initValue);
    }

    /**
     * @param string|resource $rsaPrivateKey
     * @param string $passphrase
     * @param string $keyId
     * @param string $algorithm
     * @return static
     * @throws \Exception if invalid keys
     */
    public static function createFromRsaPrivateKey($rsaPrivateKey, $passphrase = null, $keyId = null, $algorithm = null)
    {
        $rsaPrivateKey = Rsa::fetchPrivateKey($rsaPrivateKey, $passphrase);

        if (is_null($keyId)) {
            $keyId = Base64Url::encode(static::makeRsaKeyThumbprint($rsaPrivateKey, 'sha256'));
        }

        if (is_null($algorithm)) {
            $algorithm = static::DEFAULT_HASH_ALGORITHM['RSA'];
        }

        $detail = openssl_pkey_get_details($rsaPrivateKey);
        $initValue = [
            'kty' => 'RSA',
            'kid' => $keyId,
            'alg' => $algorithm,
            'key_ops' => 'sign',
        ];
        foreach (static::OPENSSL_RSA_PARAMETERS_MAPPING as $openssl => $jwk) {
            if (isset($detail['rsa'][$openssl]) == false) {
                throw new \DomainException('invalid private key: ' . $openssl . ' is empty');
            }
            $initValue[$jwk] = Base64Url::encode($detail['rsa'][$openssl]);
        }
        return new static($initValue);
    }

    /**
     * @param string $commonKey
     * @param string $keyId
     * @param string $algorithm
     * @return static
     * @throws \Exception if invalid keys
     */
    public static function createFromCommonKey($commonKey, $keyId = null, $algorithm = null)
    {
        if (is_null($keyId)) {
            $keyId = Base64Url::encode(static::makeCommonKeyThumbprint($commonKey));
        }

        if (is_null($algorithm)) {
            $algorithm = static::DEFAULT_HASH_ALGORITHM['oct'];
        }

        $initValue = [
            'kty' => 'oct',
            'k' => $commonKey,
            'kid' => $keyId,
            'alg' => $algorithm,
        ];
        return new static($initValue);
    }

    /**
     * WARNING!! has private key values if created from private key
     *
     * @return string JWK JSON
     */
    public function __toString()
    {
        return json_encode($this->toArray(), static::JSON_ENCODE_OPTIONS);
    }

    /**
     * WARNING!! has private key values if created from private key
     *
     * @return array JWK data
     */
    public function toArray()
    {
        return $this->data;
    }

    /**
     * @return string `kty`
     */
    public function getKeyType()
    {
        return $this->data['kty'];
    }

    /**
     * @return string `use`
     */
    public function getUse()
    {
        return $this->data['use'];
    }

    /**
     * @return string `key_ops`
     */
    public function getkeyOps()
    {
        return $this->data['key_ops'];
    }

    /**
     * @return string `alg`
     */
    public function getAlgorithm()
    {
        return $this->data['alg'];
    }

    /**
     * @return string `kid`
     */
    public function getKeyId()
    {
        return $this->data['kid'];
    }

    /**
     * @return string `x5u` (X.509 URL)
     */
    public function getX509Url()
    {
        return $this->data['x5u'];
    }

    /**
     * @return string `x5c` (X.509 Certificate Chain)
     */
    public function getX509CertChain()
    {
        return $this->data['x5c'];
    }

    /**
     * @return string `x5t` (X.509 Certificate SHA-1 Thumbprint)
     */
    public function getX509CertSha1()
    {
        return $this->data['x5t'];
    }

    /**
     * @return string `x5t#S256` (X.509 Certificate SHA-256 Thumbprint)
     */
    public function getX509CertSha256()
    {
        return $this->data['x5t#S256'];
    }

    /**
     * @return boolean true if can make signature
     */
    public function hasPrivateKey()
    {
        switch ($this->getKeyType()) {
            case 'EC':
            case 'RSA':
                return (isset($this->data['d']) && (strlen($this->data['d']) > 0));
            case 'oct':
                return (strlen($this->data['k']) > 0);
            default:
                return false;
        }
    }

    /**
     * @return SignatureInterface
     * @throws \Exception if invalid key data
     */
    public function getSignatureInterface()
    {
        switch ($this->getKeyType()) {
            case 'EC':
                if (isset($this->data['d']) && (strlen($this->data['d']) > 0)) {
                    return new Ecdsa(null, $this->getEcdsaPrivateKey());
                } else {
                    return new Ecdsa($this->getEcdsaPublicKey());
                }
            case 'RSA':
                if (isset($this->data['d']) && (strlen($this->data['d']) > 0)) {
                    return new Rsa(null, $this->getRsaPrivateKey());
                } else {
                    return new Rsa($this->getRsaPublicKey());
                }
            case 'oct':
                return new Hmac($this->getCommonKey());
            default:
                throw new \RuntimeException('kty=' . $this->getKeyType() . ' is not supported');
        }
    }

    /**
     * @return EncryptionInterface
     * @throws \Exception if invalid key data
     */
    public function getEncryptionInterface()
    {
        switch ($this->getKeyType()) {
            case 'EC':
                if (isset($this->data['d']) && (strlen($this->data['d']) > 0)) {
                    return new Ecdsa(null, $this->getEcdsaPrivateKey());
                } else {
                    return new Ecdsa($this->getEcdsaPublicKey());
                }
            case 'RSA':
                if (isset($this->data['d']) && (strlen($this->data['d']) > 0)) {
                    return new Rsa(null, $this->getRsaPrivateKey());
                } else {
                    return new Rsa($this->getRsaPublicKey());
                }
            case 'oct':
                return new Aes($this->getCommonKey());
            default:
                throw new \RuntimeException('kty=' . $this->getKeyType() . ' is not supported');
        }
    }

    /**
     * @param string $message
     * @param string $signature
     * @return boolean true if valid signature
     * @throws \Exception if invalid keys
     */
    public function verifySignature($message, $signature)
    {
        $alg = $this->getAlgorithm();
        $hashAlgorithms = JsonWebAlgorithms::HASH_ALGORITHM;
        if (isset($hashAlgorithms[$alg]) == false) {
            throw new \RuntimeException('alg=' . $alg . ' is not supported');
        }
        list(, $hashAlgorithm) = $hashAlgorithms[$alg];

        $signatureInterface = $this->getSignatureInterface();
        return $signatureInterface->verifySignature($message, $signature, $hashAlgorithm);
    }

    /**
     * @param string $message
     * @return string $signature
     * @throws \Exception if failed or not has private key
     */
    public function makeSignature($message)
    {
        if (!($this->hasPrivateKey())) {
            throw new \RuntimeException('not has private key');
        }
        $alg = $this->getAlgorithm();
        $hashAlgorithms = JsonWebAlgorithms::HASH_ALGORITHM;
        if (isset($hashAlgorithms[$alg]) == false) {
            throw new \RuntimeException('alg=' . $alg . ' is not supported');
        }
        list(, $hashAlgorithm) = $hashAlgorithms[$alg];

        $signatureInterface = $this->getSignatureInterface();
        return $signatureInterface->makeSignature($message, $hashAlgorithm);
    }

    /**
     * @return resource ECDSA public key resource
     * @throws \RuntimeException if not kty=EC
     */
    protected function getEcdsaPublicKey()
    {
        if ($this->getKeyType() !== 'EC') {
            throw new \RuntimeException('not has ECDSA public key');
        }
        $curveName = JsonWebAlgorithms::ECDSA_CURVE_NAME[$this->data['crv']];
        $xCoordinate = Base64Url::decode($this->data['x']);
        $yCoordinate = Base64Url::decode($this->data['y']);
        return Ecdsa::publicKeyFromCurveXY($curveName, $xCoordinate, $yCoordinate);
    }

    /**
     * @return resource ECDSA private key resource
     * @throws \RuntimeException if not kty=EC or not has d
     */
    protected function getEcdsaPrivateKey()
    {
        if ($this->getKeyType() !== 'EC' || isset($this->data['d']) == false) {
            throw new \RuntimeException('not has ECDSA private key');
        }
        $curveName = JsonWebAlgorithms::ECDSA_CURVE_NAME[$this->data['crv']];
        $xCoordinate = Base64Url::decode($this->data['x']);
        $yCoordinate = Base64Url::decode($this->data['y']);
        $eccPrivate = Base64Url::decode($this->data['d']);
        return Ecdsa::privateKeyFromCurveXYD($curveName, $xCoordinate, $yCoordinate, $eccPrivate);
    }

    /**
     * @return resource RSA public key resource
     * @throws \RuntimeException if not kty=RSA
     */
    protected function getRsaPublicKey()
    {
        if ($this->getKeyType() !== 'RSA') {
            throw new \RuntimeException('not has RSA public key');
        }
        $modulus = Base64Url::decode($this->data['n']);
        $exponent = Base64Url::decode($this->data['e']);
        return Rsa::publicKeyFromModulusAndExponent($modulus, $exponent);
    }

    /**
     * @return resource RSA private key resource
     * @throws \RuntimeException if not kty=RSA or not has private values
     */
    protected function getRsaPrivateKey()
    {
        if ($this->getKeyType() !== 'RSA') {
            throw new \RuntimeException('not has RSA private key');
        }

        $parameters = [];
        foreach (static::OPENSSL_RSA_PARAMETERS_MAPPING as $openssl => $jwk) {
            if (isset($this->data[$jwk]) == false) {
                throw new \RuntimeException('not has RSA private key (' . $jwk . ')');
            }
            $parameters[$openssl] = Base64Url::decode($this->data[$jwk]);
        }
        return Rsa::privateKeyFromParameters($parameters);
    }

    /**
     * @return string raw value
     * @throws \RuntimeException if not kty=oct
     */
    protected function getCommonKey()
    {
        if ($this->getKeyType() !== 'oct') {
            throw new \RuntimeException('not kty=oct');
        }
        return Base64Url::decode($this->data['k']);
    }

    /**
     * @param string $curveName (example: "secp256r1")
     * @return string `crv` value (example: "P-256") or empty (if not found)
     */
    public static function getCrvValueFromCurveName($curveName)
    {
        $curveNameToCrvValue = array_flip(JsonWebAlgorithms::ECDSA_CURVE_NAME);
        if (isset($curveNameToCrvValue[$curveName])) {
            return $curveNameToCrvValue[$curveName];
        } else {
            return '';
        }
    }

    /**
     * @param resource $ecdsaKeyResource
     * @param string $hashAlgorithm
     * @return string thumbprint
     * @throws \DomainException if invalid keys
     */
    public static function makeEcdsaKeyThumbprint($ecdsaKeyResource, $hashAlgorithm = 'sha256')
    {
        $detail = openssl_pkey_get_details($ecdsaKeyResource);
        if (!($detail) || (isset($detail['ec']) == false)) {
            throw new \DomainException('invalid ECDSA key');
        }
        $crv = static::getCrvValueFromCurveName($detail['ec']['curve_name']);
        if (strlen($crv) < 1) {
            $errorMsg = 'curve_name=' . $detail['ec']['curve_name'] . ' is not supported.';
            throw new \DomainException($errorMsg);
        }
        $message = '{"crv":"' . Base64Url::encode($crv) . '","kty":"EC","x":"' . Base64Url::encode($detail['ec']['x']) . '","y":"' . Base64Url::encode($detail['ec']['y']) . '"}';
        return hash($hashAlgorithm, $message, true);
    }

    /**
     * @param resource $rsaKeyResource
     * @param string $hashAlgorithm
     * @return string thumbprint
     * @throws \DomainException if invalid keys
     */
    public static function makeRsaKeyThumbprint($rsaKeyResource, $hashAlgorithm = 'sha256')
    {
        $detail = openssl_pkey_get_details($rsaKeyResource);
        if (!($detail) || (isset($detail['rsa']) == false)) {
            throw new \DomainException('invalid RSA key');
        }
        $message = '{"e":"' . Base64Url::encode($detail['rsa']['e']) . '","kty":"RSA","n":"' . Base64Url::encode($detail['rsa']['n']) . '"}';
        return hash($hashAlgorithm, $message, true);
    }

    /**
     * @param string $commonKey
     * @param string $hashAlgorithm
     * @return string thumbprint
     * @throws \DomainException if invalid keys
     */
    public static function makeCommonKeyThumbprint($commonKey, $hashAlgorithm = 'sha256')
    {
        if (strlen($commonKey) < 1) {
            throw new \DomainException('key is empty');
        }
        $message = '{"k":"' . Base64Url::encode($commonKey) . '","kty":"oct"}';
        return hash($hashAlgorithm, $message, true);
    }
}
