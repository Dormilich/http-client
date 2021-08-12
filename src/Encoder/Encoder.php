<?php

namespace Dormilich\HttpClient\Encoder;

use Dormilich\HttpClient\Transformer\DataEncoderInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\StreamFactoryInterface;

use function strlen;
use function strtoupper;

/**
 * Wrapper class for data encoders.
 */
class Encoder implements EncoderInterface
{
    use ContentTrait;

    private DataEncoderInterface $transformer;

    /**
     * @param StreamFactoryInterface $factory
     * @param DataEncoderInterface $transformer
     */
    public function __construct(StreamFactoryInterface $factory, DataEncoderInterface $transformer)
    {
        $this->setFactory($factory);
        $this->transformer = $transformer;
    }

    /**
     * @inheritDoc
     */
    public function supports($data): bool
    {
        return $this->transformer->supports($data);
    }

    /**
     * @inheritDoc
     */
    public function getContentType(): ?string
    {
        return $this->transformer->contentType();
    }

    /**
     * @inheritDoc
     */
    public function serialize(RequestInterface $request, $data): RequestInterface
    {
        $content = $this->transformer->encode($data);

        if (strlen($content) === 0) {
            return $request;
        }

        $method = $request->getMethod();
        switch (strtoupper($method)) {
            case 'POST':
            case 'PUT':
            case 'PATCH':
            case 'DELETE':
                return $this->setContent($request, $content);
            case 'GET':
            case 'HEAD':
            case 'TRACE':
                return $this->setQuery($request, $content);
            default:
                return $request;
        }
    }
}
