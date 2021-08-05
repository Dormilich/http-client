<?php

namespace Dormilich\HttpClient\Transformer;

use Dormilich\HttpClient\Exception\DecoderException;
use Dormilich\HttpClient\Exception\EncoderException;

interface TransformerInterface
{
    /**
     * Whether this transformer should be used to transform the given data.
     *
     * @param mixed $data
     * @return bool
     */
    public function supports($data): bool;

    /**
     * Transform data into the body content of the request.
     *
     * @param mixed $data
     * @return string
     * @throws EncoderException
     */
    public function encode($data): string;

    /**
     * Transform the response body into the desired data structure.
     *
     * @param string $content
     * @return mixed
     * @throws DecoderException
     */
    public function decode(string $content);

    /**
     * The MIME type of the data.
     *
     * @return string
     */
    public function contentType(): string;
}
