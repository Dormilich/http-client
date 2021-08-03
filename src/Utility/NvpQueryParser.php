<?php

namespace Dormilich\HttpClient\Utility;

use Dormilich\HttpClient\Exception\EncoderException;

use function array_map;
use function explode;
use function is_array;
use function is_int;
use function is_iterable;
use function is_scalar;
use function ltrim;
use function property_exists;
use function rawurlencode;
use function str_replace;
use function strlen;

/**
 * Encode/decode query parameters as strict name-value pairs. This does not support
 * nested structures (except for plain arrays).
 */
class NvpQueryParser implements QueryParser
{
    /**
     * @inheritDoc
     */
    public function encode(iterable $data): string
    {
        $query  = $this->createQueryString($data);

        return ltrim($query, '&');
    }

    /**
     * @inheritDoc
     */
    public function decode(string $query)
    {
        $query = str_replace('+', ' ', $query);
        $params = strlen($query) ? explode('&', $query) : [];

        return $this->parseQueryParams($params);
    }

    /**
     * Convert an array into an urlencoded query string.
     *
     * @param iterable $data
     * @return string
     * @throws EncoderException
     */
    private function createQueryString(iterable $data): string
    {
        $query = '';

        foreach ($data as $name => $value) {
            $query .= $this->createQueryParam($name, $value);
        }

        return $query;
    }

    /**
     * Encode value(s) for a single parameter name.
     *
     * @param string $name
     * @param mixed $value
     * @return string
     * @throws EncoderException
     */
    private function createQueryParam(string $name, $value): string
    {
        if (!is_iterable($value)) {
            return $this->encodeParameter($name, $value);
        }

        $query = '';

        foreach ($value as $index => $item) {
            if (is_int($index)) {
                $query .= $this->encodeParameter($name, $item);
            } else {
                $message = "Value for parameter '{$name}' cannot be encoded, a non-hash value is expected.";
                throw new EncoderException($message);
            }
        }

        return $query;
    }

    /**
     * Encode a single key-value pair.
     *
     * @param string $name
     * @param scalar|null $value
     * @return string
     * @throws EncoderException
     */
    private function encodeParameter(string $name, $value): string
    {
        $query = '&' . rawurlencode($name);

        if (true === $value) {
            $query .= '=true';
        } elseif (false === $value) {
            $query .= '=false';
        } elseif (is_scalar($value)) {
            $query .= '=' . rawurlencode($value);
        } elseif (null !== $value) {
            $message = "Value for parameter '{$name}' cannot be encoded, a scalar value is expected.";
            throw new EncoderException($message);
        }

        return $query;
    }

    /**
     * Decode query parameters.
     *
     * @param array $data
     * @return \stdClass
     */
    private function parseQueryParams(array $data): \stdClass
    {
        $nvp = new \stdClass();

        foreach ($data as $item) {
            [ $key, $value ] = $this->getDataItem($item);
            if (property_exists($nvp, $key)) {
                $value = $this->getDataValue($nvp->{$key}, $value);
            }
            $nvp->{$key} = $value;
        }

        return $nvp;
    }

    /**
     * Decode a query part into a key-value pair.
     *
     * @param string $part
     * @return string[]
     */
    private function getDataItem(string $part): array
    {
        $item = explode('=', $part, 2);
        $item = array_map('rawurldecode', $item);
        $item += [null, null];

        return $item;
    }

    /**
     * @param array|string $data
     * @param string|null $value
     * @return array
     */
    private function getDataValue($data, ?string $value)
    {
        if (!is_array($data)) {
            return [$data, $value];
        }
        $data[] = $value;
        return $data;
    }
}
