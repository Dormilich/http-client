<?php

namespace Dormilich\HttpClient\Encoder;

use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\StreamFactoryInterface;

use function strlen;

/**
 * Add body content to the request.
 */
trait ContentTrait
{
    protected StreamFactoryInterface $factory;

    /**
     * @see EncoderInterface
     */
    abstract public function getContentType(): ?string;

    /**
     * @param StreamFactoryInterface $factory
     */
    protected function setFactory(StreamFactoryInterface $factory)
    {
        $this->factory = $factory;
    }

    /**
     * Add the body to the request and set common headers.
     *
     * @param RequestInterface $request
     * @param string $content
     * @return RequestInterface
     */
    protected function setContent(RequestInterface $request, string $content): RequestInterface
    {
        if (strlen($content)) {
            $stream = $this->factory->createStream($content);
            return $this->setContentType($request)->withBody($stream);
        }
        return $request;
    }

    /**
     * Set the content type for JSON data.
     *
     * @param RequestInterface $request
     * @return RequestInterface
     */
    private function setContentType(RequestInterface $request): RequestInterface
    {
        if ($type = $this->getContentType()) {
            return $request->withHeader('Content-Type', $type);
        }
        return $request;
    }
}
