<?php

namespace Dormilich\HttpClient\Decoder;

use Dormilich\HttpClient\Utility\QueryParser;
use Psr\Http\Message\ResponseInterface;

/**
 * Parse form data.
 */
class UrlDecoder implements DecoderInterface
{
    private QueryParser $decoder;

    use ContentTypeTrait;
    use StatusCodeTrait;

    /**
     * @param QueryParser $decoder
     */
    public function __construct(QueryParser $decoder)
    {
        $this->decoder = $decoder;
    }

    /**
     * @inheritDoc
     */
    public function getContentType(): string
    {
        return 'application/x-www-form-urlencoded';
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
        return $this->decoder->decode($content);
    }
}
