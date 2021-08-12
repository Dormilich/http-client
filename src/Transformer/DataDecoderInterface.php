<?php

namespace Dormilich\HttpClient\Transformer;

use Dormilich\HttpClient\Exception\DecoderException;

interface DataDecoderInterface extends TypeInterface
{
    /**
     * Transform the response body into the desired data structure.
     *
     * @param string $content
     * @return mixed
     * @throws DecoderException
     */
    public function decode(string $content);
}
