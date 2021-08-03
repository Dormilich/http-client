<?php

namespace Dormilich\HttpClient\Encoder;

use Psr\Http\Message\RequestInterface;

use Psr\Http\Message\StreamFactoryInterface;

use function is_numeric;
use function is_object;
use function is_string;
use function method_exists;

/**
 * Encode text or a string-convertable object as plain text.
 */
class TextEncoder implements EncoderInterface
{
    use ContentTrait;

    /**
     * @param StreamFactoryInterface $factory
     */
    public function __construct(StreamFactoryInterface $factory)
    {
        $this->setFactory($factory);
    }

    /**
     * @inheritDoc
     */
    public function supports($data): bool
    {
        return is_string($data) or is_numeric($data)
            or (is_object($data) and method_exists($data, '__toString'));
    }

    /**
     * @inheritDoc
     */
    public function getContentType(): ?string
    {
        return 'text/plain';
    }

    /**
     * @inheritDoc
     */
    public function serialize(RequestInterface $request, $data): RequestInterface
    {
        return $this->setContent($request, (string) $data);
    }
}
