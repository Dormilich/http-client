<?php

namespace Dormilich\HttpClient\Transformer;

use Dormilich\HttpClient\Exception\EncoderException;

interface DataEncoderInterface extends TypeInterface
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
}
