<?php

namespace Dormilich\HttpClient\Encoder;

use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\StreamFactoryInterface;

/**
 * Encode a DOM object as XML.
 */
class DomEncoder implements EncoderInterface
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
        return $data instanceof \DOMDocument;
    }

    /**
     * @inheritDoc
     */
    public function getContentType(): ?string
    {
        return 'application/xml';
    }

    /**
     * @inheritDoc
     */
    public function serialize(RequestInterface $request, $data): RequestInterface
    {
        return $this->setContent($request, $data->saveXML());
    }
}
