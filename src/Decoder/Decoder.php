<?php

namespace Dormilich\HttpClient\Decoder;

use Dormilich\HttpClient\Transformer\DataDecoderInterface;
use Psr\Http\Message\ResponseInterface;

/**
 * Wrapper class for data decoders.
 */
class Decoder implements DecoderInterface
{
    use ContentTypeTrait;
    use StatusCodeTrait;

    private DataDecoderInterface $transformer;

    /**
     * @param DataDecoderInterface $transformer
     */
    public function __construct(DataDecoderInterface $transformer)
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
