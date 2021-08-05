<?php

namespace Dormilich\HttpClient\Transformer;

use Dormilich\HttpClient\Exception\DecoderException;
use Dormilich\HttpClient\Exception\EncoderException;

use const JSON_OBJECT_AS_ARRAY;
use const JSON_THROW_ON_ERROR;

use function json_decode;
use function json_encode;

/**
 * Some useful encoding constants
 *  - JSON_FORCE_OBJECT
 *  - JSON_NUMERIC_CHECK
 *  - JSON_PRESERVE_ZERO_FRACTION
 *  - JSON_UNESCAPED_SLASHES
 *  - JSON_UNESCAPED_UNICODE
 *
 * Some useful decoding constants
 *  - JSON_BIGINT_AS_STRING
 *  - JSON_OBJECT_AS_ARRAY
 */
class JsonTransformer implements TransformerInterface
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
    public function supports($data): bool
    {
        return ($data instanceof \JsonSerializable)
            or ($data instanceof \stdClass);
    }

    /**
     * @inheritDoc
     */
    public function encode($data): string
    {
        try {
            return json_encode($data, JSON_THROW_ON_ERROR|$this->options);
        } catch (\JsonException $e) {
            throw new EncoderException($e->getMessage(), $e->getCode(), $e);
        }
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
