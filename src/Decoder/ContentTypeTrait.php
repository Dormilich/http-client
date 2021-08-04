<?php

namespace Dormilich\HttpClient\Decoder;

use Psr\Http\Message\ResponseInterface;

use function preg_replace;
use function stripos;

trait ContentTypeTrait
{
    abstract public function getContentType(): string;

    /**
     * Test if the content-type of the response matches the one from the decoder.
     *
     * @param ResponseInterface $response
     * @return bool
     */
    protected function hasContentType(ResponseInterface $response): bool
    {
        $type = $this->getContentType();

        if ('*/*' === $type) {
            return true;
        }

        $header = $response->getHeaderLine('Content-Type');
        // direct match
        if (stripos($header, $type) !== false) {
            return true;
        }
        // "application/xhtml+xml" => "application/xml"
        $short = preg_replace('#([a-z]+)/(?:\S+\+)?(\S+)#i', '$1/$2', $header);
        return stripos($short, $type) !== false;
    }
}
