<?php

namespace Dormilich\HttpClient;

use Dormilich\HttpClient\Decoder\Decoder;
use Dormilich\HttpClient\Decoder\DecoderInterface;
use Dormilich\HttpClient\Encoder\Encoder;
use Dormilich\HttpClient\Encoder\EncoderInterface;
use Dormilich\HttpClient\Exception\RequestException;
use Dormilich\HttpClient\Exception\UnsupportedDataTypeException;
use Dormilich\HttpClient\Transformer\TransformerInterface;
use Dormilich\HttpClient\Utility\Header;
use Dormilich\HttpClient\Utility\StatusMatcher;
use Psr\Http\Client\ClientExceptionInterface;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamFactoryInterface;
use Psr\Http\Message\UriInterface;

use const DATE_RFC7231;

use function array_map;
use function array_unique;
use function date;
use function strtoupper;

class Client
{
    private ClientInterface $client;

    private RequestFactoryInterface $requestFactory;

    private StreamFactoryInterface $streamFactory;

    /**
     * @var Header HTTP headers to send with every request.
     */
    private Header $header;

    /**
     * @var DecoderInterface[]
     */
    private array $decoder = [];

    /**
     * @var EncoderInterface[]
     */
    private array $encoder = [];

    /**
     * @param ClientInterface $client
     * @param RequestFactoryInterface $requestFactory
     * @param StreamFactoryInterface $streamFactory
     */
    public function __construct(
        ClientInterface $client,
        RequestFactoryInterface $requestFactory,
        StreamFactoryInterface $streamFactory
    ) {
        $this->header = new Header();
        $this->client = $client;
        $this->requestFactory = $requestFactory;
        $this->streamFactory = $streamFactory;
    }

    /**
     * @return Header
     */
    public function getHeaders(): Header
    {
        return $this->header;
    }

    /**
     * Add encoder and decoder for a transformer.
     *
     * @param TransformerInterface $transformer
     * @param StatusMatcher|null $matcher
     * @return self
     */
    public function addTransformer(TransformerInterface $transformer, StatusMatcher $matcher = null): self
    {
        $encoder = new Encoder($this->streamFactory, $transformer);
        $decoder = new Decoder($transformer);
        if ($matcher) {
            $decoder->setStatusMatcher($matcher);
        }
        return $this->addDecoder($decoder)->addEncoder($encoder);
    }

    /**
     * Add response parser. When the HTTP response is received, use a matching
     * parser to return processable data.
     *
     * @param DecoderInterface $decoder
     * @return self
     */
    public function addDecoder(DecoderInterface $decoder): self
    {
        $this->decoder[] = $decoder;
        return $this;
    }

    /**
     * Add encoder for input conversion.
     *
     * @param EncoderInterface $encoder
     * @return self
     */
    public function addEncoder(EncoderInterface $encoder): self
    {
        $this->encoder[] = $encoder;
        return $this;
    }

    /**
     * Submit a preconfigured request and return the parsed response.
     *
     * @param RequestInterface $request
     * @return mixed
     * @throws RequestException
     */
    public function request(RequestInterface $request)
    {
        $request = $this->addRequestHeaders($request, $this->header);
        $request = $this->setAcceptHeader($request);
        $request = $this->postProcessRequest($request);

        $response = $this->doRequest($request);

        try {
            return $this->parseResponse($response);
        } catch (RequestException $e) {
            $e->setRequest($request);
            $e->setResponse($response);
            throw $e;
        }
    }

    /**
     * Request a resource.
     *
     * @param string $method HTTP method.
     * @param string|UriInterface $uri Target URI.
     * @param mixed $data Data payload.
     * @param iterable<string,string|string[]> $header Additional request headers.
     * @return mixed
     * @throws RequestException
     * @throws UnsupportedDataTypeException
     */
    public function fetch(string $method, $uri, $data = null, iterable $header = [])
    {
        $request = $this->createRequest($method, $uri);
        $request = $this->addRequestHeaders($request, new Header($header));
        $request = $this->setRequestData($request, $data);

        return $this->request($request);
    }

    /**
     * Shorthand method for GET requests.
     *
     * @param string|UriInterface $uri Target URI.
     * @return mixed
     * @throws RequestException
     */
    public function get($uri)
    {
        return $this->fetch('GET', $uri);
    }

    /**
     * Shorthand method for POST requests.
     *
     * @param string|UriInterface $uri Target URI.
     * @param mixed $data
     * @return mixed
     * @throws RequestException
     * @throws UnsupportedDataTypeException
     */
    public function post($uri, $data)
    {
        return $this->fetch('POST', $uri, $data);
    }

    /**
     * Shorthand method for PUT requests.
     *
     * @param string|UriInterface $uri Target URI.
     * @param mixed $data
     * @return mixed
     * @throws RequestException
     * @throws UnsupportedDataTypeException
     */
    public function put($uri, $data)
    {
        return $this->fetch('PUT', $uri, $data);
    }

    /**
     * Shorthand method for PATCH requests.
     *
     * @param string|UriInterface $uri Target URI.
     * @param mixed $data
     * @return mixed
     * @throws RequestException
     * @throws UnsupportedDataTypeException
     */
    public function patch($uri, $data)
    {
        return $this->fetch('PATCH', $uri, $data);
    }

    /**
     * Shorthand method for DELETE requests.
     *
     * @param string|UriInterface $uri Target URI.
     * @param mixed $data
     * @return mixed
     * @throws RequestException
     * @throws UnsupportedDataTypeException
     */
    public function delete($uri, $data = null)
    {
        return $this->fetch('DELETE', $uri, $data);
    }

    /**
     * @param string $method
     * @param UriInterface|string $uri
     * @return RequestInterface
     */
    public function createRequest(string $method, $uri): RequestInterface
    {
        return $this->requestFactory->createRequest(strtoupper($method), $uri, []);
    }

    /**
     * Add payload data to the request.
     *
     * @param RequestInterface $request
     * @param mixed $data
     * @return RequestInterface
     * @throws UnsupportedDataTypeException
     */
    private function setRequestData(RequestInterface $request, $data): RequestInterface
    {
        if (null === $data) {
            return $request;
        }

        foreach ($this->encoder as $encoder) {
            if ($encoder->supports($data)) {
                return $this->encode($request, $encoder, $data);
            }
        }

        throw new UnsupportedDataTypeException($data);
    }

    /**
     * Run all encoders that may modify the prepared request.
     *
     * @param RequestInterface $request
     * @return RequestInterface
     * @throws RequestException
     */
    private function postProcessRequest(RequestInterface $request): RequestInterface
    {
        foreach ($this->encoder as $encoder) {
            if ($encoder->supports($request)) {
                $request = $this->encode($request, $encoder, null);
            }
        }

        return $request;
    }

    /**
     * Execute encoder with the request data.
     *
     * @param RequestInterface $request
     * @param EncoderInterface $encoder
     * @param mixed $data
     * @return RequestInterface
     * @throws RequestException
     */
    private function encode(RequestInterface $request, EncoderInterface $encoder, $data): RequestInterface
    {
        try {
            return $encoder->serialize($request, $data);
        } catch (RequestException $e) {
            $e->setRequest($request);
            throw $e;
        }
    }

    /**
     * Add the default headers to the request.
     *
     * @param RequestInterface $request
     * @param Header $headers
     * @return RequestInterface
     */
    private function addRequestHeaders(RequestInterface $request, Header $headers): RequestInterface
    {
        foreach ($headers as $name => $content) {
            $request = $request->withAddedHeader($name, $content);
        }
        return $request;
    }

    /**
     * Define accept headers.
     *
     * @param RequestInterface $request
     * @return RequestInterface
     */
    private function setAcceptHeader(RequestInterface $request): RequestInterface
    {
        return $request->withHeader('Accept', $this->getAcceptHeader());
    }

    /**
     * Get the `Accept` header values using the configured decoders
     * (content negotiation).
     *
     * @see https://tools.ietf.org/html/rfc7231#section-5.3
     * @return string[]
     */
    private function getAcceptHeader(): array
    {
        $accept = array_map(function (DecoderInterface $decoder) {
            return $decoder->getContentType();
        }, $this->decoder);

        $accept = array_unique($accept);

        return $accept ?: ['*/*'];
    }

    /**
     * Run HTTP request in non-PSR-18 mode.
     *
     * @param RequestInterface $request
     * @return ResponseInterface
     * @throws RequestException
     */
    private function doRequest(RequestInterface $request): ResponseInterface
    {
        try {
            $request = $request->withHeader('Date', date(DATE_RFC7231));
            return $this->client->sendRequest($request);
        } catch (ClientExceptionInterface $e) {
            $http = new RequestException($e->getMessage(), $e->getCode(), $e);
            $http->setRequest($request);
            throw $http;
        }
    }

    /**
     * Convert the response into a processable data structure. If no decoder can
     * process the response, its content is returned unprocessed.
     *
     * @param ResponseInterface $response
     * @return mixed
     * @throws RequestException
     */
    private function parseResponse(ResponseInterface $response)
    {
        foreach ($this->decoder as $decoder) {
            if ($decoder->supports($response)) {
                return $decoder->unserialize($response);
            }
        }

        return (string) $response->getBody();
    }
}
