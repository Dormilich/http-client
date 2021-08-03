<?php

namespace Dormilich\HttpClient\Encoder;

use Psr\Http\Message\RequestInterface;

class ContentLength implements EncoderInterface
{
    /**
     * @inheritDoc
     */
    public function supports($data): bool
    {
        return ($data instanceof RequestInterface)
           and ($data->getMethod() !== 'GET')
           and !$data->hasHeader('Transfer-Encoding');
    }

    /**
     * @inheritDoc
     */
    public function getContentType(): ?string
    {
        return null;
    }

    /**
     * @inheritDoc
     */
    public function serialize(RequestInterface $request, $data): RequestInterface
    {
        $size = $request->getBody()->getSize();

        if ($size > 0) {
            return $request->withHeader('Content-Length', $size);
        }

        return $request;
    }
}
