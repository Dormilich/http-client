<?php

namespace Dormilich\HttpClient\Transformer;

use Dormilich\HttpClient\Exception\EncoderException;

use const JSON_THROW_ON_ERROR;

use function json_encode;

/**
 * Some useful encoding constants
 *  - JSON_FORCE_OBJECT
 *  - JSON_NUMERIC_CHECK
 *  - JSON_PRESERVE_ZERO_FRACTION
 *  - JSON_UNESCAPED_SLASHES
 *  - JSON_UNESCAPED_UNICODE
 */
class JsonEncoder implements DataEncoderInterface
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
    public function contentType(): string
    {
        return 'application/json';
    }
}
