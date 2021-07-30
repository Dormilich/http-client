<?php

namespace Dormilich\HttpClient\Exception;

use Dormilich\HttpClient\ExceptionInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

/**
 * Base exception thrown when a problem arises in processing a request or response.
 */
class RequestException extends \RuntimeException implements ExceptionInterface
{
    private ?RequestInterface $request = null;

    private ?ResponseInterface $response = null;

    /**
     * @return RequestInterface|null
     */
    public function getRequest(): ?RequestInterface
    {
        return $this->request;
    }

    /**
     * @param RequestInterface $request
     */
    public function setRequest(RequestInterface $request): void
    {
        $this->request = $request;
    }

    /**
     * @return ResponseInterface|null
     */
    public function getResponse(): ?ResponseInterface
    {
        return $this->response;
    }

    /**
     * @param ResponseInterface $response
     */
    public function setResponse(ResponseInterface $response): void
    {
        $this->response = $response;
    }
}
