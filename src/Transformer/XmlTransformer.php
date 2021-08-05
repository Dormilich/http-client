<?php

namespace Dormilich\HttpClient\Transformer;

use Dormilich\HttpClient\Exception\DecoderException;

use function restore_error_handler;
use function set_error_handler;
use function simplexml_load_string;

class XmlTransformer implements TransformerInterface
{

    /**
     * @inheritDoc
     */
    public function supports($data): bool
    {
        return $data instanceof \SimpleXMLElement;
    }

    /**
     * @inheritDoc
     */
    public function encode($data): string
    {
        return $data->asXML();
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
            return simplexml_load_string($content);
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
