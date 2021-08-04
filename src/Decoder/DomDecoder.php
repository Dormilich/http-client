<?php

namespace Dormilich\HttpClient\Decoder;

use Dormilich\HttpClient\Exception\DecoderException;
use Psr\Http\Message\ResponseInterface;

use function restore_error_handler;
use function set_error_handler;

/**
 * Parse an XML response into a DOMDocument instance.
 */
class DomDecoder implements DecoderInterface
{
    private int $options;

    use ContentTypeTrait;
    use StatusCodeTrait;

    /**
     * @param integer $options Bitmask of libxml constants.
     */
    public function __construct(int $options = 0)
    {
        $this->options = $options;
    }

    /**
     * @inheritDoc
     */
    public function getContentType(): string
    {
        return 'application/xml';
    }

    /**
     * @inheritDoc
     */
    public function supports(ResponseInterface $response): bool
    {
        return $this->hasStatusCode($response) and $this->hasContentType($response);
    }

    /**
     * @inheritDoc
     */
    public function unserialize(ResponseInterface $response)
    {
        $content = (string) $response->getBody();

        $doc = new \DOMDocument();
        $this->loadDocument($doc, $content);

        return $doc;
    }

    /**
     * Creates an XML document from the response.
     *
     * @param \DOMDocument $doc
     * @param string $content
     * @return bool
     * @throws DecoderException
     */
    private function loadDocument(\DOMDocument $doc, string $content): bool
    {
        try {
            set_error_handler(function (int $code, string $message) {
                throw new DecoderException($message, $code);
            });
            return $doc->loadXML($content, $this->options);
        } finally {
            restore_error_handler();
        }
    }
}
