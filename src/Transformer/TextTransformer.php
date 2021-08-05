<?php

namespace Dormilich\HttpClient\Transformer;

use function is_numeric;
use function is_object;
use function is_string;
use function method_exists;

class TextTransformer implements TransformerInterface
{
    /**
     * @inheritDoc
     */
    public function supports($data): bool
    {
        return is_string($data)
            or is_numeric($data)
            or (is_object($data) and method_exists($data, '__toString'));
    }

    /**
     * @inheritDoc
     */
    public function encode($data): string
    {
        return (string) $data;
    }

    /**
     * @inheritDoc
     */
    public function decode(string $content)
    {
        return $content;
    }

    /**
     * @inheritDoc
     */
    public function contentType(): string
    {
        return 'text/plain';
    }
}
