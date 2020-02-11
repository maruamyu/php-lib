<?php

namespace Maruamyu\Core\Salmon;

use Maruamyu\Core\Cipher\Hmac;
use Maruamyu\Core\Cipher\Rsa;
use Maruamyu\Core\Cipher\SignatureInterface;
use Maruamyu\Core\Http\Client as HttpClient;
use Maruamyu\Core\Http\Message\Request;
use Maruamyu\Core\Http\Message\Response;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\UriInterface;

/**
 * Salmon client
 */
class Connector
{
    /** @var SignatureInterface */
    protected $signatureInterface;

    /** @var string */
    protected $keyId;

    /** @var HttpClient */
    protected $httpClient;

    /**
     * @param SignatureInterface $signatureInterface
     * @param string $keyId
     */
    public function __construct(SignatureInterface $signatureInterface = null, $keyId = '')
    {
        $this->signatureInterface = $signatureInterface;
        $this->keyId = $keyId;
    }

    /**
     * @param MagicEnvelope $magicEnvelope
     * @return bool
     */
    public function verify(MagicEnvelope $magicEnvelope)
    {
        return $magicEnvelope->verify($this->signatureInterface, $this->keyId);
    }

    /**
     * @param string|UriInterface $url Salmon endpoint URL
     * @param string $envelope Magical envelope body
     * @param string $format 'xml' or 'json' or 'serialized'
     * @return Response
     * @throws \Exception if failed
     */
    public function post($url, $envelope, $format = 'xml')
    {
        $request = $this->makeRequest($url, $envelope, $format);
        $httpClient = $this->getHttpClient();
        return $httpClient->send($request);
    }

    /**
     * @param string|UriInterface $url Salmon endpoint URL
     * @param string $envelope Magical envelope
     * @param string $format 'xml' or 'json' or 'serialization'
     * @return Request
     * @throws \Exception if failed
     */
    public function makeRequest($url, $envelope, $format = 'xml')
    {
        if (
            !($this->signatureInterface)
            || !($this->signatureInterface->canMakeSignature())
        ) {
            throw new \RuntimeException('required private key');
        }

        $magicEnvelope = new MagicEnvelope();
        $magicEnvelope->setData($envelope);
        $magicEnvelope->sign($this->signatureInterface, $this->keyId);

        switch (strtolower($format)) {
            case 'serialization':
            case 'serialized':
            case 'serialize':
                return static::buildSerializationRequest($url, $magicEnvelope);

            case 'json':
                return static::buildJsonRequest($url, $magicEnvelope);

            case 'xml':
            default:
                return static::buildXmlRequest($url, $magicEnvelope);
        }
    }

    /**
     * @return HttpClient
     */
    protected function getHttpClient()
    {
        if (isset($this->httpClient) == false) {
            $this->httpClient = new HttpClient();
        }
        return $this->httpClient;
    }

    /**
     * @param string $commonKey
     * @return static
     */
    public static function fromCommonKey($commonKey)
    {
        $hmac = new Hmac($commonKey);
        return new static($hmac);
    }

    /**
     * @param string|resource $publicKey
     * @param string|resource $privateKey
     * @param string $passphrase
     * @return static
     * @throws \Exception if invalid keys
     */
    public static function fromRsaKey($publicKey, $privateKey = null, $passphrase = null)
    {
        $rsa = new Rsa($publicKey, $privateKey, $passphrase);
        return new static($rsa);
    }

    /**
     * @param RequestInterface $request
     * @return MagicEnvelope|null (return null if not MagicEnvelope)
     */
    public static function parseRequest(RequestInterface $request)
    {
        $requestBody = $request->getBody()->getContents();

        $contentTypes = $request->getHeader('Content-Type');
        if (isset($contentTypes) && (empty($contentTypes) == false)) {
            list($contentType) = explode(';', strval($contentTypes[0]));
        } else {
            $contentType = '';
        }
        if (strlen($contentType) < 1) {
            # auto detection
            if (strpos($requestBody, '<?xml') === 0) {
                $contentType = MagicEnvelope::CONTENT_TYPE_XML;
            } elseif (strpos($requestBody, '{') === 0) {
                $contentType = MagicEnvelope::CONTENT_TYPE_JSON;
            }
        }

        try {
            switch ($contentType) {
                case MagicEnvelope::CONTENT_TYPE_XML:
                case 'text/xml':
                    return MagicEnvelope::fromXml($requestBody);

                case MagicEnvelope::CONTENT_TYPE_JSON:
                case 'application/json':
                    return MagicEnvelope::fromJson($requestBody);

                default:
                    return MagicEnvelope::fromCompactSerialization($requestBody);
            }
        } catch (\Exception $exception) {
            # if invalid MagicEnvelope
            return null;
        }
    }

    /**
     * @param string|UriInterface $url Salmon endpoint URL
     * @param MagicEnvelope $magicEnvelope
     * @return Request
     * @throws \Exception if failed
     */
    public static function buildXmlRequest($url, MagicEnvelope $magicEnvelope)
    {
        $requestBody = $magicEnvelope->toXml();
        $requestHeaders = [
            'Content-Type' => MagicEnvelope::CONTENT_TYPE_XML,
        ];
        return new Request('POST', $url, $requestBody, $requestHeaders);
    }

    /**
     * @param string|UriInterface $url Salmon endpoint URL
     * @param MagicEnvelope $magicEnvelope
     * @return Request
     * @throws \Exception if failed
     */
    public static function buildJsonRequest($url, MagicEnvelope $magicEnvelope)
    {
        $requestBody = $magicEnvelope->toJson();
        $requestHeaders = [
            'Content-Type' => MagicEnvelope::CONTENT_TYPE_JSON,
        ];
        return new Request('POST', $url, $requestBody, $requestHeaders);
    }

    /**
     * @param string|UriInterface $url Salmon endpoint URL
     * @param MagicEnvelope $magicEnvelope
     * @return Request
     * @throws \Exception if failed
     */
    public static function buildSerializationRequest($url, MagicEnvelope $magicEnvelope)
    {
        $requestBody = $magicEnvelope->toCompactSerialization();
        $requestHeaders = [
            'Content-Type' => MagicEnvelope::CONTENT_TYPE,
        ];
        return new Request('POST', $url, $requestBody, $requestHeaders);
    }
}
