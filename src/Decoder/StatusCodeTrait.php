<?php

namespace Dormilich\HttpClient\Decoder;

use Dormilich\HttpClient\Utility\StatusMatcher;
use Psr\Http\Message\ResponseInterface;

trait StatusCodeTrait
{
    private ?StatusMatcher $matcher = null;

    /**
     * @param StatusMatcher $matcher
     */
    public function setStatusMatcher(StatusMatcher $matcher): void
    {
        $this->matcher = $matcher;
    }

    /**
     * Test if the status code constraint matches.
     *
     * @param ResponseInterface $response
     * @return bool
     */
    protected function hasStatusCode(ResponseInterface $response): bool
    {
        if (!$this->matcher) {
            return true;
        }
        $code = $response->getStatusCode();
        return $this->matcher->matches($code);
    }
}
