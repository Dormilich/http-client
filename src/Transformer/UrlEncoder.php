<?php

namespace Dormilich\HttpClient\Transformer;

use Dormilich\HttpClient\Utility\PhpQuery;
use Dormilich\HttpClient\Utility\QueryParser;

class UrlEncoder implements DataEncoderInterface
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
    public function supports($data): bool
    {
        return is_array($data);
    }

    /**
     * @inheritDoc
     */
    public function encode($data): string
    {
        return $this->parser->encode($data);
    }

    /**
     * @inheritDoc
     */
    public function contentType(): string
    {
        return 'application/x-www-form-urlencoded';
    }
}
