<?php

namespace Dormilich\HttpClient\Transformer;

use Dormilich\HttpClient\Utility\PhpQuery;
use Dormilich\HttpClient\Utility\QueryParser;

class UrlDecoder implements DataDecoderInterface
{
    private QueryParser $parser;

    /**
     * @param QueryParser|null $parser
     */
    public function __construct(QueryParser $parser = null)
    {
        $this->parser = $parser ?: new PhpQuery();
    }

    /**
     * @inheritDoc
     */
    public function decode(string $content)
    {
        return $this->parser->decode($content);
    }

    /**
     * @inheritDoc
     */
    public function contentType(): string
    {
        return 'application/x-www-form-urlencoded';
    }
}
