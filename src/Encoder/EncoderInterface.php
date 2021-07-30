<?php

namespace Dormilich\HttpClient\Encoder;

use Dormilich\HttpClient\Exception\EncoderException;
use Psr\Http\Message\ServerRequestInterface;

/**
 * Plugin interface to set the request data.
 */
interface EncoderInterface
{
    /**
     * Check that this content encoder can handle the data type.
     *
     * @param mixed $data
     * @return bool
     */
    public function supports($data): bool;

    /**
     * Returns the request content type for this encoder.
     *
     * @return string|null
     */
    public function getContentType(): ?string;

    /**
     * Add data to the request.
     *
     * @param ServerRequestInterface $request
     * @param mixed $data
     * @return ServerRequestInterface
     * @throws EncoderException
     */
    public function serialize(ServerRequestInterface $request, $data): ServerRequestInterface;
}
