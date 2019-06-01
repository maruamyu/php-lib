<?php

namespace Maruamyu\Core\Salmon;

use Maruamyu\Core\Base64Url;
use Maruamyu\Core\Cipher\Hmac;
use Maruamyu\Core\Cipher\Rsa;
use Maruamyu\Core\Cipher\SignatureInterface;

/**
 * Salmon MagicEnvelope
 */
class MagicEnvelope
{
    const XML_NAMESPACE = 'http://salmon-protocol.org/ns/magic-env';

    const CONTENT_TYPE = 'application/magic-envelope';

    const CONTENT_TYPE_XML = 'application/magic-envelope+xml';

    const CONTENT_TYPE_JSON = 'application/magic-envelope+json';

    const TEMPLATE = '<?xml version="1.0" encoding="UTF-8"?><me:env xmlns:me="http://salmon-protocol.org/ns/magic-env"/>';

    const SUPPORTED_ENCODINGS = ['base64url'];

    const HASH_ALGORITHMS = [
        'HMAC-SHA256' => ['hash_hmac', 'sha256'],
        'RSA-SHA256' => ['openssl', OPENSSL_ALGO_SHA256],
    ];

    const DEFAULT_HASH_ALGORITHM = 'HMAC-SHA256';

    /**
     * @var string
     */
    protected $data;

    /**
     * @var string
     */
    protected $dataType;

    /**
     * @var string
     */
    protected $encoding;

    /**
     * @var string
     */
    protected $alg;

    /**
     * @var array[]
     */
    protected $signatures;

    /**
     * @param string $serialized
     * @return static
     * @throws \UnexpectedValueException if invalid string
     */
    public static function fromCompactSerialization($serialized)
    {
        $magicEnvelope = new static();
        $magicEnvelope->setFromCompactSerialization($serialized);
        return $magicEnvelope;
    }

    /**
     * @param string $xmlString
     * @return static
     * @throws \UnexpectedValueException if invalid XML
     */
    public static function fromXml($xmlString)
    {
        $magicEnvelope = new static();
        $magicEnvelope->setFromXml($xmlString);
        return $magicEnvelope;
    }

    /**
     * @param string $jsonString
     * @return static
     * @throws \UnexpectedValueException if invalid JSON
     */
    public static function fromJson($jsonString)
    {
        $magicEnvelope = new static();
        $magicEnvelope->setFromJson($jsonString);
        return $magicEnvelope;
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return $this->toCompactSerialization();
    }

    /**
     * initialize
     */
    public function __construct()
    {
        $this->data = '<?xml version="1.0" encoding="utf-8"?><entry xmlns="http://www.w3.org/2005/Atom"/>';
        $this->dataType = 'application/atom+xml';
        $this->encoding = static::SUPPORTED_ENCODINGS[0];
        $this->alg = static::DEFAULT_HASH_ALGORITHM;
        $this->signatures = [];
    }

    /**
     * @return string
     */
    public function getData()
    {
        return $this->data;
    }

    /**
     * @return string data_type
     */
    public function getDataType()
    {
        return $this->dataType;
    }

    /**
     * @param string $data
     * @param string $dataType
     */
    public function setData($data, $dataType = '')
    {
        $this->data = $data;
        if (strlen($dataType) > 0) {
            $this->dataType = $dataType;
        }
    }

    /**
     * @param string $encoding
     * @throws \DomainException if not supported encoding
     */
    public function setEncoding($encoding)
    {
        $encoding = strtolower($encoding);
        if (in_array($encoding, static::SUPPORTED_ENCODINGS) == false) {
            throw new \DomainException('encoding=' . $encoding . ' is not supported.');
        }
        $this->encoding = $encoding;
    }

    /**
     * @return string
     */
    public function getAlg()
    {
        return $this->alg;
    }

    /**
     * @param string $alg
     * @throws \DomainException if not supported alg
     */
    public function setAlg($alg)
    {
        $hashAlgorithms = static::HASH_ALGORITHMS;
        if (isset($hashAlgorithms[$alg]) == false) {
            throw new \DomainException('alg=' . $alg . ' is not supported.');
        }
        $this->alg = $alg;
    }

    /**
     * @return string[]
     */
    public function getKeyIds()
    {
        $keyIds = [];
        foreach (array_keys($this->signatures) as $sigKeyId) {
            if (is_string($sigKeyId)) {
                $keyIds[] = $sigKeyId;
            }
        }
        return $keyIds;
    }

    /**
     * @return string base string
     */
    protected function createBaseString()
    {
        return join('.', [
            $this->encodeValue($this->data),
            Base64Url::encode($this->dataType, true),
            Base64Url::encode($this->encoding, true),
            Base64Url::encode($this->alg, true),
        ]);
    }

    /**
     * @param SignatureInterface $signatureInterface
     * @param string $targetKeyId
     * @return boolean true if verified
     */
    public function verify(SignatureInterface $signatureInterface, $targetKeyId = '')
    {
        # alg auto detection
        $alg = static::detectAlgBySignatureInterface($signatureInterface);
        if (strlen($alg) > 0) {
            $this->setAlg($alg);
        }

        list(, $hashAlgorithm) = static::HASH_ALGORITHMS[$this->alg];

        $message = $this->createBaseString();
        foreach ($this->signatures as $keyId => $signature) {
            if (is_string($keyId)) {
                # if include `key_id` attributes, do key_id matching
                if ((strlen($targetKeyId) > 0) && $keyId !== $targetKeyId) {
                    continue;
                }
            }
            $verified = $signatureInterface->verifySignature($message, $signature, $hashAlgorithm);
            if ($verified) {
                return true;
            }
        }
        return false;
    }

    /**
     * @param SignatureInterface $signatureInterface
     * @param string $keyId
     * @return boolean true if succeeded
     * @throws \Exception if invalid private key or sign failed
     */
    public function sign(SignatureInterface $signatureInterface, $keyId = '')
    {
        # alg auto detection
        $alg = static::detectAlgBySignatureInterface($signatureInterface);
        if (strlen($alg) > 0) {
            $this->setAlg($alg);
        }

        list(, $hashAlgorithm) = static::HASH_ALGORITHMS[$this->alg];
        $signature = $signatureInterface->makeSignature($this->createBaseString(), $hashAlgorithm);
        if (strlen($keyId) > 0) {
            $this->signatures[$keyId] = $signature;
        } else {
            $this->signatures[] = $signature;
        }
        return true;
    }

    /**
     * @return string XML String
     */
    public function toXml()
    {
        $xml = simplexml_load_string(static::TEMPLATE);

        $encodedData = $this->encodeValue($this->data);
        $dataNode = $xml->addChild('me:data', $encodedData, static::XML_NAMESPACE);
        $dataNode->addAttribute('type', $this->dataType);

        $xml->addChild('me:encoding', $this->encoding, static::XML_NAMESPACE);

        $xml->addChild('me:alg', $this->alg, static::XML_NAMESPACE);

        foreach ($this->signatures as $sigKeyHash => $sigValue) {
            $encodedSignature = $this->encodeValue($sigValue);
            $sigNode = $xml->addChild('me:sig', $encodedSignature, static::XML_NAMESPACE);
            if (is_string($sigKeyHash)) {
                $sigNode->addAttribute('key_id', $sigKeyHash);
            }
        }

        return $xml->asXML();
    }

    /**
     * @return string JSON String
     */
    public function toJson()
    {
        $data = [
            'data' => $this->encodeValue($this->data),
            'data_type' => $this->dataType,
            'encoding' => $this->encoding,
            'alg' => $this->alg,
            'sigs' => [],
        ];
        foreach ($this->signatures as $sigKeyId => $sigValue) {
            $sigNode = ['value' => $this->encodeValue($sigValue)];
            if (is_string($sigKeyId)) {
                $sigNode['key_id'] = $sigKeyId;
            }
            $data['sigs'][] = $sigNode;
        }
        return json_encode($data, JSON_UNESCAPED_SLASHES);
    }

    /**
     * @return string Compact Serialization string
     */
    public function toCompactSerialization()
    {
        if (empty($this->signatures)) {
            $signaturePart = '.';
        } else {
            $sigKeyId = array_keys($this->signatures)[0];
            $sigValue = array_values($this->signatures)[0];
            $signaturePart = rtrim($sigKeyId . '.' . $this->encodeValue($sigValue));
        }
        return $signaturePart . '.' . $this->createBaseString();
    }

    /**
     * @param string $serialized MagicEnvelope Compact Serialization
     * @throws \UnexpectedValueException if invalid value
     */
    protected function setFromCompactSerialization($serialized)
    {
        $parts = explode('.', $serialized);
        if ($parts < 3) {
            throw new \UnexpectedValueException('invalid Compact Serialization value.');
        }

        # alg
        if (isset($parts[5])) {
            $alg = Base64Url::decode($parts[5]);
            $this->setAlg($alg);
        }

        # encoding
        if (isset($parts[4])) {
            $encoding = Base64Url::decode($parts[4]);
            $this->setEncoding($encoding);
        }

        # data and data_type
        $this->data = $this->decodeValue($parts[2]);
        if (isset($parts[3])) {
            $this->dataType = Base64Url::decode($parts[3]);
        }

        # key_id, sig (set to protected propaty)
        $this->signatures = [
            $parts[0] => $this->decodeValue($parts[1]),
        ];
    }

    /**
     * @param string $xmlString MagicEnvelope Atom
     * @throws \UnexpectedValueException if invalid XML
     */
    protected function setFromXml($xmlString)
    {
        $xml = simplexml_load_string($xmlString);
        if ($xml === false) {
            throw new \UnexpectedValueException('invalid XML.');
        }
        if ($xml->getName() !== 'env') {
            throw new \UnexpectedValueException('invalid MagicEnvelope.');
        }
        $nodes = $xml->children(static::XML_NAMESPACE);

        if (!isset($nodes->encoding)) {
            throw new \UnexpectedValueException('invalid MagicEnvelope. (required `encoding` element)');
        }
        $this->setEncoding(strval($nodes->encoding));

        if (!isset($nodes->data)) {
            throw new \UnexpectedValueException('invalid MagicEnvelope. (required `data` element)');
        }
        $this->data = $this->decodeValue(strval($nodes->data));
        $dataAttributes = $nodes->data->attributes();
        if (empty($dataAttributes)) {
            $this->dataType = 'text/plain';
        } else {
            $this->dataType = strval($dataAttributes->type);
        }

        if (!isset($nodes->alg)) {
            throw new \UnexpectedValueException('invalid MagicEnvelope. (required `alg` element)');
        }
        $this->setAlg(strval($nodes->alg));

        if (!isset($nodes->sig)) {
            throw new \UnexpectedValueException('invalid MagicEnvelope. (required `sig` element)');
        }
        $this->signatures = [];
        foreach ($nodes->sig as $sigNode) {
            $sigAttributes = $sigNode->attributes();
            $signature = $this->decodeValue(strval($sigNode));
            if ((empty($sigAttributes) == false) && isset($sigAttributes->key_id)) {
                $sigKeyId = strval($sigAttributes->key_id);
                $this->signatures[$sigKeyId] = $signature;
            } else {
                $this->signatures[] = $signature;
            }
        }
    }

    /**
     * @param string $jsonString MagicEnvelope JSON String
     * @throws \UnexpectedValueException if invalid JSON
     */
    protected function setFromJson($jsonString)
    {
        $data = json_decode($jsonString, true);
        if (is_null($data)) {
            throw new \UnexpectedValueException('invalid JSON.');
        }
        $this->setFromArray($data);
    }

    /**
     * @param array $data decoded MagicEnvelope JSON data
     */
    protected function setFromArray($data)
    {
        if (!isset($data['encoding'])) {
            throw new \UnexpectedValueException('invalid MagicEnvelope. (required `encoding`)');
        }
        $this->setEncoding(strval($data['encoding']));

        if (!isset($data['data'])) {
            throw new \UnexpectedValueException('invalid MagicEnvelope. (required `data`)');
        }
        $this->data = $this->decodeValue(strval($data['data']));

        if (!isset($data['data_type'])) {
            throw new \UnexpectedValueException('invalid MagicEnvelope. (required `data_type`)');
        }
        $this->dataType = strval($data['data_type']);

        if (!isset($data['alg'])) {
            throw new \UnexpectedValueException('invalid MagicEnvelope. (required `alg`)');
        }
        $this->setAlg(strval($data['alg']));

        if (!isset($data['sigs'])) {
            throw new \UnexpectedValueException('invalid MagicEnvelope. (required `sigs`)');
        }
        $this->signatures = [];
        foreach ($data['sigs'] as $sigNode) {
            $signature = $this->decodeValue(strval($sigNode['value']));
            if (isset($sigNode['key_id'])) {
                $sigKeyId = strval($sigNode['key_id']);
                $this->signatures[$sigKeyId] = $signature;
            } else {
                $this->signatures[] = $signature;
            }
        }
    }

    /**
     * @param string $value encoded data
     * @return string decoded data
     */
    protected function decodeValue($value)
    {
        switch ($this->encoding) {
            case 'base64url':
                return Base64Url::decode($value);
            case 'base64':
                return base64_decode($value);
            default:
                return $value;
        }
    }

    /**
     * @param string $value raw value
     * @return string encoded data by `encoding`
     */
    protected function encodeValue($value)
    {
        switch ($this->encoding) {
            case 'base64url':
                return Base64Url::encode($value, true);
            case 'base64':
                return base64_encode($value);
            default:
                return $value;
        }
    }

    /**
     * @param SignatureInterface $signatureInterface
     * @return string 'HMAC-SHA256' or 'RSA-SHA256' or '' (if not detected)
     */
    protected static function detectAlgBySignatureInterface(SignatureInterface $signatureInterface)
    {
        if ($signatureInterface instanceof MagicPublicKey) {
            return 'RSA-SHA256';
        }
        if ($signatureInterface instanceof Rsa) {
            return 'RSA-SHA256';
        }
        if ($signatureInterface instanceof Hmac) {
            return 'HMAC-SHA256';
        }
        return '';
    }
}
