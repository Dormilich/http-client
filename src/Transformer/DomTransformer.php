<?php

namespace Dormilich\HttpClient\Transformer;

use Dormilich\HttpClient\Exception\DecoderException;

class DomTransformer implements TransformerInterface
{
    /**
     * @inheritDoc
     */
    public function supports($data): bool
    {
        return $data instanceof \DOMDocument;
    }

    /**
     * @inheritDoc
     */
    public function encode($data): string
    {
        return $data->saveXML();
    }

    /**
     * @inheritDoc
     */
    public function decode(string $content)
    {
        try {
            set_error_handler(function (int $code, string $message) {
                throw new DecoderException($message, $code);
            });
            $doc = new \DOMDocument();
            $doc->loadXML($content);
            return $doc;
        } finally {
            restore_error_handler();
        }
    }

    /**
     * @inheritDoc
     */
    public function contentType(): string
    {
        return 'application/xml';
    }
}
