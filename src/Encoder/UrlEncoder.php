<?php

namespace Dormilich\HttpClient\Encoder;

use Dormilich\HttpClient\Utility\QueryParser;
use Psr\Http\Message\RequestInterface;

use Psr\Http\Message\StreamFactoryInterface;

use function is_array;

/**
 * Encode an array as name-value pairs.
 */
class UrlEncoder implements EncoderInterface
{
    private QueryParser $encoder;

    use ContentTrait;

    /**
     * @param StreamFactoryInterface $factory
     * @param QueryParser $encoder
     */
    public function __construct(StreamFactoryInterface $factory, QueryParser $encoder)
    {
        $this->setFactory($factory);
        $this->encoder = $encoder;
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
    public function getContentType(): ?string
    {
        return 'application/x-www-form-urlencoded';
    }

    /**
     * @inheritDoc
     * @param array<string,null|scalar|scalar[]> $data
     */
    public function serialize(RequestInterface $request, $data): RequestInterface
    {
        $query = $this->encoder->encode($data);

        if ($request->getMethod() === 'GET') {
            $uri = $request->getUri()->withQuery($query);
            return $request->withUri($uri);
        } else {
            return $this->setContent($request, $query);
        }
    }
}
