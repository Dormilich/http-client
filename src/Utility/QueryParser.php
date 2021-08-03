<?php

namespace Dormilich\HttpClient\Utility;

use Dormilich\HttpClient\Exception\DecoderException;
use Dormilich\HttpClient\Exception\EncoderException;

interface QueryParser
{
    /**
     * Encode an array into a URI query string.
     *
     * @param iterable $data
     * @return string
     * @throws EncoderException
     */
    public function encode(iterable $data): string;

    /**
     * Parse a query string into the desired data structure.
     *
     * @param string $query
     * @return array|object
     * @throws DecoderException
     */
    public function decode(string $query);
}
