<?php

namespace Dormilich\HttpClient\Utility;

use function array_key_exists;
use function count;
use function current;
use function is_iterable;
use function key;
use function next;
use function reset;
use function str_replace;
use function strtr;
use function ucwords;

/**
 * Store HTTP headers in a consistent way. This allows to add or remove headers
 * even if the letter case of the header name is inconsistent.
 */
class Header implements \ArrayAccess, \Countable, \Iterator
{
    private array $header = [];

    /**
     * @param iterable $header HTTP headers.
     */
    public function __construct(iterable $header = [])
    {
        foreach ($header as $name => $value) {
            $this->offsetSet($name, $value);
        }
    }

    /**
     * Test if a header was defined.
     *
     * @param string $name HTTP header name.
     * @return bool
     */
    public function has(string $name): bool
    {
        return $this->offsetExists($name);
    }

    /**
     * Get the contents of a header. Returns an empty array if the header is
     * not defined.
     *
     * @param string $name HTTP header name.
     * @return string[]
     */
    public function get(string $name): array
    {
        return $this->offsetGet($name);
    }

    /**
     * Add a header value.
     *
     * @param string $name HTTP header name.
     * @param string $value HTTP header value.
     * @return self
     */
    public function add(string $name, string $value): self
    {
        $this->offsetSet($name, $value);
        return $this;
    }

    /**
     * Replace existing header content with a new value.
     *
     * @param string $name HTTP header name.
     * @param string $value HTTP header value.
     * @return self
     */
    public function replace(string $name, string $value): self
    {
        $this->offsetUnset($name);
        $this->offsetSet($name, $value);
        return $this;
    }

    /**
     * Remove an existing header.
     *
     * @param string $name HTTP header name.
     * @return self
     */
    public function remove(string $name): self
    {
        $this->offsetUnset($name);
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function current()
    {
        return current($this->header);
    }

    /**
     * @inheritDoc
     */
    public function next(): void
    {
        next($this->header);
    }

    /**
     * @inheritDoc
     */
    public function key(): string
    {
        return $this->canonicalize(key($this->header));
    }

    /**
     * @inheritDoc
     */
    public function valid(): bool
    {
        return null !== key($this->header);
    }

    /**
     * @inheritDoc
     */
    public function rewind(): void
    {
        reset($this->header);
    }

    /**
     * @inheritDoc
     */
    public function offsetExists($offset): bool
    {
        $key = $this->normalise($offset);
        return array_key_exists($key, $this->header);
    }

    /**
     * @inheritDoc
     */
    public function offsetGet($offset): array
    {
        if (!$this->offsetExists($offset)) {
            return [];
        }
        $key = $this->normalise($offset);
        return $this->header[$key];
    }

    /**
     * @inheritDoc
     */
    public function offsetSet($offset, $value): void
    {
        if (null === $offset) {
            throw new \LogicException('Missing required header name!');
        }
        $key = $this->normalise($offset);
        $values = $this->getValues($value);
        $this->setValues($key, $values);
    }

    /**
     * @inheritDoc
     */
    public function offsetUnset($offset): void
    {
        $key = $this->normalise($offset);
        unset($this->header[$key]);
    }

    /**
     * @inheritDoc
     */
    public function count(): int
    {
        return count($this->header);
    }

    /**
     * Normalise a header name so it can be used for comparison.
     *
     * @param string $name HTTP header name.
     * @return string
     */
    private function normalise(string $name): string
    {
        return strtr($name, 'ABCDEFGHIJKLMNOPQRSTUVWXYZ-', 'abcdefghijklmnopqrstuvwxyz_');
    }

    /**
     * Convert a normalised header name into the IANA-type format.
     *
     * @param string $key HTTP header storage key.
     * @return string
     */
    private function canonicalize(string $key): string
    {
        return str_replace('_', '-', ucwords($key, '_'));
    }

    /**
     * Convert a parameter into an iterable form.
     *
     * @param mixed $value HTTP header content.
     * @return iterable
     */
    private function getValues($value): iterable
    {
        if (is_iterable($value)) {
            return $value;
        } else {
            return [$value];
        }
    }

    /**
     * Store HTTP header.
     *
     * @param string $key HTTP header name.
     * @param iterable $values HTTP header content.
     */
    private function setValues(string $key, iterable $values): void
    {
        foreach ($values as $value) {
            $this->header[$key][] = (string) $value;
        }
    }
}
