<?php

namespace Dormilich\HttpClient\Utility;

use const PHP_QUERY_RFC3986;

use function http_build_query;
use function is_array;
use function iterator_to_array;
use function parse_str;

/**
 * Encode and decode URL parameters in PHP style (using square brackets for nested data).
 */
class PhpQueryParser implements QueryParser
{
    /**
     * @inheritDoc
     */
    public function encode(iterable $data): string
    {
        return http_build_query($this->toArray($data), '', '&', PHP_QUERY_RFC3986);
    }

    /**
     * @inheritDoc
     */
    public function decode(string $query)
    {
        parse_str($query, $data);
        return $data;
    }

    /**
     * @param array|\Traversable $data
     * @return array
     */
    private function toArray(iterable $data): array
    {
        if (is_array($data)) {
            return $data;
        }
        return iterator_to_array($data);
    }
}
