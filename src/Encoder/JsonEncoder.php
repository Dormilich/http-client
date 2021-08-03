<?php

namespace Dormilich\HttpClient\Encoder;

use Dormilich\HttpClient\Exception\EncoderException;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\StreamFactoryInterface;

use const JSON_THROW_ON_ERROR;

use function json_encode;

/**
 * Encode a serializable object to JSON.
 *
 * Some useful encoding constants
 *  - JSON_FORCE_OBJECT
 *  - JSON_NUMERIC_CHECK
 *  - JSON_PRESERVE_ZERO_FRACTION
 *  - JSON_UNESCAPED_SLASHES
 *  - JSON_UNESCAPED_UNICODE
 */
class JsonEncoder implements EncoderInterface
{
    private int $options = 0;

    use ContentTrait;

    /**
     * @link https://www.php.net/manual/json.constants.php
     * @param StreamFactoryInterface $factory
     * @param int|null $options JSON encoding options.
     */
    public function __construct(StreamFactoryInterface $factory, int $options = 0)
    {
        $this->setFactory($factory);
        $this->setOptions($options);
    }

    /**
     * @param int $options
     */
    private function setOptions(int $options): void
    {
        $this->options = $options;
    }

    /**
     * @inheritDoc
     */
    public function supports($data): bool
    {
        return ($data instanceof \JsonSerializable) or ($data instanceof \stdClass);
    }

    /**
     * @inheritDoc
     */
    public function getContentType(): ?string
    {
        return 'application/json';
    }

    /**
     * @inheritDoc
     */
    public function serialize(RequestInterface $request, $data): RequestInterface
    {
        try {
            $content = json_encode($data, JSON_THROW_ON_ERROR|$this->options);
            return $this->setContent($request, $content);
        } catch (\JsonException $e) {
            throw new EncoderException($e->getMessage(), $e->getCode(), $e);
        }
    }
}
