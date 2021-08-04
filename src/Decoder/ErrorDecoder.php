<?php

namespace Dormilich\HttpClient\Decoder;

use Dormilich\HttpClient\Exception\RequestException;
use Psr\Http\Message\ResponseInterface;

/**
 * Convert an error response into an exception.
 */
class ErrorDecoder implements DecoderInterface
{
    /**
     * @inheritDoc
     */
    public function getContentType(): string
    {
        return '*/*';
    }

    /**
     * @inheritDoc
     */
    public function supports(ResponseInterface $response): bool
    {
        return 400 <= $response->getStatusCode();
    }

    /**
     * @inheritDoc
     * @throws RequestException
     */
    public function unserialize(ResponseInterface $response)
    {
        $message = (string) $response->getBody();
        $code = $response->getStatusCode();

        throw new RequestException($message, $code);
    }
}
