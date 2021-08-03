<?php

namespace Dormilich\HttpClient\Encoder;

use Dormilich\HttpClient\Exception\EncoderException;
use Psr\Http\Message\RequestInterface;

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
     * @param RequestInterface $request
     * @param mixed $data
     * @return RequestInterface
     * @throws EncoderException
     */
    public function serialize(RequestInterface $request, $data): RequestInterface;
}
