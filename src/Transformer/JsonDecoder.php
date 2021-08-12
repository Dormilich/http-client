<?php

namespace Dormilich\HttpClient\Transformer;

use Dormilich\HttpClient\Exception\DecoderException;

use const JSON_OBJECT_AS_ARRAY;
use const JSON_THROW_ON_ERROR;

use function json_decode;

/**
 * Some useful decoding constants
 *  - JSON_BIGINT_AS_STRING
 *  - JSON_OBJECT_AS_ARRAY
 */
class JsonDecoder implements DataDecoderInterface
{
    private int $options = 0;

    /**
     * @param int $options
     */
    public function __construct(int $options = 0)
    {
        $this->options = $options;
    }

    /**
     * @inheritDoc
     */
    public function decode(string $content)
    {
        try {
            $assoc = ($this->options & JSON_OBJECT_AS_ARRAY) === JSON_OBJECT_AS_ARRAY;
            return json_decode($content, $assoc, 512, JSON_THROW_ON_ERROR|$this->options);
        } catch (\JsonException $e) {
            throw new DecoderException($e->getMessage(), $e->getCode(), $e);
        }
    }

    /**
     * @inheritDoc
     */
    public function contentType(): string
    {
        return 'application/json';
    }
}
