<?php

namespace Dormilich\HttpClient\Decoder;

use Dormilich\HttpClient\Exception\DecoderException;
use Psr\Http\Message\ResponseInterface;

use const JSON_OBJECT_AS_ARRAY;
use const JSON_THROW_ON_ERROR;

use function json_decode;

/**
 * Parse response as JSON.
 *
 * Some useful decoding constants
 *  - JSON_BIGINT_AS_STRING
 *  - JSON_OBJECT_AS_ARRAY
 */
class JsonDecoder implements DecoderInterface
{
    private int $options = 0;

    use ContentTypeTrait;
    use StatusCodeTrait;

    /**
     * @param int|null $options JSON decoding options.
     */
    public function __construct(int $options = 0)
    {
        $this->options = $options;
    }

    /**
     * @inheritDoc
     */
    public function getContentType(): string
    {
        return 'application/json';
    }

    /**
     * @inheritDoc
     */
    public function supports(ResponseInterface $response): bool
    {
        return $this->hasStatusCode($response) and $this->hasContentType($response);
    }

    /**
     * @inheritDoc
     */
    public function unserialize(ResponseInterface $response)
    {
        try {
            $content = (string) $response->getBody();
            $assoc = ($this->options & JSON_OBJECT_AS_ARRAY) === JSON_OBJECT_AS_ARRAY;
            return json_decode($content, $assoc, 512, JSON_THROW_ON_ERROR|$this->options);
        } catch (\JsonException $e) {
            throw new DecoderException($e->getMessage(), $e->getCode(), $e);
        }
    }
}
