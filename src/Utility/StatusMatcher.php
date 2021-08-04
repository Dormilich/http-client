<?php

namespace Dormilich\HttpClient\Utility;

use function is_int;

class StatusMatcher
{
    private ?int $min;

    private ?int $max;

    /**
     * @param int|null $min Least allowed status code.
     * @param int|null $max Highest allowed status code.
     */
    public function __construct(?int $min = null, ?int $max = null)
    {
        $this->min = $min;
        $this->max = $max;
    }

    /**
     * test if a status code matches the expectation.
     *
     * @param int $status
     * @return bool
     */
    public function matches(int $status): bool
    {
        if ($this->isInverseMatch()) {
            return ($status > $this->min) xor ($status < $this->max);
        }

        $min = $this->matchMin($status);
        $max = $this->matchMax($status);

        return $min and $max;
    }

    /**
     * Check if the standard or inverse strategy needs to be used.
     *
     * @return bool
     */
    private function isInverseMatch(): bool
    {
        return is_int($this->min) and is_int($this->max) and ($this->min > $this->max);
    }

    /**
     * Test if the lower limit matches.
     *
     * @param int $status
     * @return bool
     */
    private function matchMin(int $status): bool
    {
        return null === $this->min || $status >= $this->min;
    }

    /**
     * Test if the upper limit matches.
     *
     * @param int $status
     * @return bool
     */
    private function matchMax(int $status): bool
    {
        return null === $this->max || $status <= $this->max;
    }

    /**
     * Match an exact status code.
     *
     * @param int $status
     * @return self
     */
    public static function exact(int $status): self
    {
        return new self($status, $status);
    }

    /**
     * Match all status codes.
     *
     * @return self
     */
    public static function any(): self
    {
        return new self;
    }

    /**
     * Match 2xx status codes.
     *
     * @return self
     */
    public static function success(): self
    {
        return new self(200, 299);
    }

    /**
     * Match 4xx status codes.
     *
     * @return self
     */
    public static function clientError(): self
    {
        return new self(400, 499);
    }

    /**
     * Match 5xx status codes.
     *
     * @return self
     */
    public static function serverError(): self
    {
        return new self(500, 599);
    }

    /**
     * Match status codes ≥ 400.
     *
     * @return self
     */
    public static function error(): self
    {
        return new self(400);
    }

    /**
     * Match status codes except 2xx.
     *
     * @return self
     */
    public static function unexpected(): self
    {
        return new self(299, 200);
    }
}
