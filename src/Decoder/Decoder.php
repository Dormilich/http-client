<?php

namespace Dormilich\HttpClient\Decoder;

use Dormilich\HttpClient\Exception\DecoderException;
use Dormilich\HttpClient\Transformer\TransformerInterface;
use Psr\Http\Message\ResponseInterface;

class Decoder implements DecoderInterface
{
    private TransformerInterface $transformer;

    use ContentTypeTrait;
    use StatusCodeTrait;

    /**
     * @param TransformerInterface $transformer
     */
    public function __construct(TransformerInterface $transformer)
    {
        $this->transformer = $transformer;
    }

    /**
     * @inheritDoc
     */
    public function getContentType(): string
    {
        return $this->transformer->contentType();
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
        $content = (string) $response->getBody();
        return $this->transformer->decode($content);
    }
}
