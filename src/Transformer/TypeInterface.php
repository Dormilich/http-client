<?php

namespace Dormilich\HttpClient\Transformer;

interface TypeInterface
{
    /**
     * The MIME type of the data.
     *
     * @return string
     */
    public function contentType(): string;
}
