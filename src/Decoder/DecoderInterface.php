<?php

namespace Dormilich\HttpClient\Decoder;

use Dormilich\HttpClient\Exception\DecoderException;
use Psr\Http\Message\ResponseInterface;

/**
 * Plugin interface to parse the response.
 */
interface DecoderInterface
{
    /**
     * Returns the response content type supported by this decoder.
     *
     * @return string
     */
    public function getContentType(): string;

    /**
     * Check that this content parser can handle the response.
     *
     * @param ResponseInterface $response
     * @return bool
     */
    public function supports(ResponseInterface $response): bool;

    /**
     * Parse the response’s content.
     *
     * @param ResponseInterface $response
     * @return mixed
     * @throws DecoderException
     */
    public function unserialize(ResponseInterface $response);
}
