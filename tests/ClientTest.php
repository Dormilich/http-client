<?php

namespace Tests;

use Dormilich\HttpClient\Client;
use Dormilich\HttpClient\Decoder\DecoderInterface;
use Dormilich\HttpClient\Encoder\EncoderInterface;
use Dormilich\HttpClient\Exception\RequestException;
use Dormilich\HttpClient\Exception\UnsupportedDataTypeException;
use Dormilich\HttpClient\Transformer\TransformerInterface;
use PHPUnit\Framework\TestCase;
use Psr\Http\Client\ClientExceptionInterface;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamFactoryInterface;
use Psr\Http\Message\StreamInterface;

/**
 * @covers \Dormilich\HttpClient\Client
 * @covers \Dormilich\HttpClient\Exception\RequestException
 * @covers \Dormilich\HttpClient\Exception\UnsupportedDataTypeException
 * @uses \Dormilich\HttpClient\Decoder\Decoder
 * @uses \Dormilich\HttpClient\Encoder\Encoder
 * @uses \Dormilich\HttpClient\Utility\Header
 * @uses \Dormilich\HttpClient\Utility\StatusMatcher
 */
class ClientTest extends TestCase
{
    private function request()
    {
        $request = $this->createStub(RequestInterface::class);
        $request
            ->method('withAddedHeader')
            ->willReturnSelf();
        $request
            ->method('withHeader')
            ->willReturnSelf();
        $request
            ->method('withBody')
            ->willReturnSelf();

        return $request;
    }

    private function response(string $data, int $status)
    {
        $body = $this->createConfiguredMock(StreamInterface::class, [
            '__toString' => $data,
        ]);
        return $this->createConfiguredMock(ResponseInterface::class, [
            'getStatusCode' => $status,
            'getBody' => $body,
        ]);
    }

    private function factory(RequestInterface $request = null)
    {
        return $this->createConfiguredMock(RequestFactoryInterface::class, [
            'createRequest' => $request ?: $this->request(),
        ]);
    }

    private function http(string $data, int $status)
    {
        $response = $this->response($data, $status);

        $http = $this->createMock(ClientInterface::class);
        $http
            ->expects($this->once())
            ->method('sendRequest')
            ->willReturn($response);

        return $http;
    }

    public function testGetShorthandMethod()
    {
        $url = 'https://example.com/api/user/42';

        $stream = $this->createStub(StreamFactoryInterface::class);
        $factory = $this->createMock(RequestFactoryInterface::class);
        $factory
            ->expects($this->once())
            ->method('createRequest')
            ->with(
                $this->identicalTo('GET'),
                $this->identicalTo($url)
            )
            ->willReturn($this->request());

        $client = new Client($this->http('test', 200), $factory, $stream);
        $client->get($url);
    }

    public function testPostShorthandMethod()
    {
        $url = 'https://example.com/api/user/42';

        $stream = $this->createStub(StreamFactoryInterface::class);
        $factory = $this->createMock(RequestFactoryInterface::class);
        $factory
            ->expects($this->once())
            ->method('createRequest')
            ->with(
                $this->identicalTo('POST'),
                $this->identicalTo($url)
            )
            ->willReturn($this->request());

        $client = new Client($this->http('foo', 201), $factory, $stream);
        $client->post($url, null);
    }

    public function testPutShorthandMethod()
    {
        $url = 'https://example.com/api/user/42';

        $stream = $this->createStub(StreamFactoryInterface::class);
        $factory = $this->createMock(RequestFactoryInterface::class);
        $factory
            ->expects($this->once())
            ->method('createRequest')
            ->with(
                $this->identicalTo('PUT'),
                $this->identicalTo($url)
            )
            ->willReturn($this->request());

        $client = new Client($this->http('bar', 304), $factory, $stream);
        $client->put($url, null);
    }

    public function testPatchShorthandMethod()
    {
        $url = 'https://example.com/api/user/42';

        $stream = $this->createStub(StreamFactoryInterface::class);
        $factory = $this->createMock(RequestFactoryInterface::class);
        $factory
            ->expects($this->once())
            ->method('createRequest')
            ->with(
                $this->identicalTo('PATCH'),
                $this->identicalTo($url)
            )
            ->willReturn($this->request());

        $client = new Client($this->http('test', 200), $factory, $stream);
        $client->patch($url, null);
    }

    public function testDeleteShorthandMethod()
    {
        $url = 'https://example.com/api/user/42';

        $stream = $this->createStub(StreamFactoryInterface::class);
        $factory = $this->createMock(RequestFactoryInterface::class);
        $factory
            ->expects($this->once())
            ->method('createRequest')
            ->with(
                $this->identicalTo('DELETE'),
                $this->identicalTo($url)
            )
            ->willReturn($this->request());

        $client = new Client($this->http('test', 200), $factory, $stream);
        $client->delete($url);
    }

    public function testSetRequestHeaders()
    {
        $stream = $this->createStub(StreamFactoryInterface::class);
        $request = $this->createMock(RequestInterface::class);
        $request
            ->expects($this->exactly(2))
            ->method('withHeader')
            ->withConsecutive(
                [$this->identicalTo('Accept'), $this->identicalTo(['*/*'])],
                [$this->identicalTo('Date')]
            )
            ->willReturnSelf();

        $http = $this->http('data', 200);
        $factory = $this->factory();

        $client = new Client($http, $factory, $stream);
        $client->request($request);
    }

    public function testSetAdditionalHeaders()
    {
        $stream = $this->createStub(StreamFactoryInterface::class);
        $request = $this->createMock(RequestInterface::class);
        $request
            ->method('withHeader')
            ->willReturnSelf();
        $request
            ->expects($this->once())
            ->method('withAddedHeader')
            ->with(
                $this->identicalTo('User-Agent'), $this->identicalTo(['PHPUnit/9.5'])
            )
            ->willReturnSelf();

        $http = $this->http('data', 200);
        $factory = $this->factory($request);

        $client = new Client($http, $factory, $stream);
        $client->fetch('get', 'https://exmple.com', null, ['User-Agent' => 'PHPUnit/9.5']);
    }

    public function testSetDefaultHeaders()
    {
        $stream = $this->createStub(StreamFactoryInterface::class);
        $request = $this->createMock(RequestInterface::class);
        $request
            ->method('withHeader')
            ->willReturnSelf();
        $request
            ->expects($this->once())
            ->method('withAddedHeader')
            ->with(
                $this->identicalTo('User-Agent'), $this->identicalTo(['PHPUnit/9.5'])
            )
            ->willReturnSelf();

        $http = $this->http('data', 200);
        $factory = $this->factory($request);

        $client = new Client($http, $factory, $stream);
        $client->getHeaders()->add('User-Agent', 'PHPUnit/9.5');
        $client->get('https://exmple.com');
    }

    public function testThrowRequestException()
    {
        $this->expectException(RequestException::class);

        $request = $this->request();
        $factory = $this->factory();

        $stream = $this->createStub(StreamFactoryInterface::class);
        $http = $this->createMock(ClientInterface::class);
        $http
            ->expects($this->once())
            ->method('sendRequest')
            ->willThrowException($this->createStub(ClientExceptionInterface::class));

        try {
            $client = new Client($http, $factory, $stream);
            $client->request($request);
            $this->fail('Failed to throw a RequestException');
        } catch (RequestException $e) {
            $this->assertNotNull($e->getRequest());
            $this->assertNull($e->getResponse());
            throw $e;
        } catch (\Throwable $e) {
            $this->assertInstanceOf(RequestException::class, $e);
        }
    }

    public function testAddRequestData()
    {
        $data['foo'] = 'bar';

        $http = $this->http('data', 200);
        $request = $this->request();
        $factory = $this->factory($request);

        $stream = $this->createStub(StreamFactoryInterface::class);
        $encoder = $this->createMock(EncoderInterface::class);
        $encoder
            ->method('supports')
            ->willReturnCallback(function ($arg) {
                return is_array($arg);
            });
        $encoder
            ->expects($this->once())
            ->method('serialize')
            ->with(
                $this->identicalTo($request),
                $this->identicalTo($data)
            )
            ->willReturnArgument(0);

        $client = new Client($http, $factory, $stream);
        $client->addEncoder($encoder);
        $client->post('https://example.com', $data);
    }

    public function testMissingDataEncoderFails()
    {
        $this->expectException(UnsupportedDataTypeException::class);
        $this->expectExceptionMessage('There was no encoder configured to encode the request payload of type [array].');

        $stream = $this->createStub(StreamFactoryInterface::class);
        $factory = $this->factory();

        $http = $this->createMock(ClientInterface::class);
        $http
            ->expects($this->never())
            ->method('sendRequest');

        $encoder = $this->createMock(EncoderInterface::class);
        $encoder
            ->method('supports')
            ->willReturn(false);
        $encoder
            ->expects($this->never())
            ->method('serialize');

        $data['foo'] = 'bar';
        try {
            $client = new Client($http, $factory, $stream);
            $client->addEncoder($encoder);
            $client->post('https://example.com', $data);
        } catch (UnsupportedDataTypeException $e) {
            $this->assertSame($data, $e->getData());
            throw $e;
        }
    }

    public function testProcessRequest()
    {
        $http = $this->http('data', 200);
        $request = $this->request();
        $factory = $this->factory($request);

        $stream = $this->createStub(StreamFactoryInterface::class);
        $encoder = $this->createMock(EncoderInterface::class);
        $encoder
            ->method('supports')
            ->willReturnCallback(function ($arg) {
                return $arg instanceof RequestInterface;
            });
        $encoder
            ->expects($this->once())
            ->method('serialize')
            ->with(
                $this->identicalTo($request)
            )
            ->willReturnArgument(0);

        $client = new Client($http, $factory, $stream);
        $client->addEncoder($encoder);
        $client->get('https://example.com');
    }

    public function testProcessRequestFails()
    {
        $this->expectException(RequestException::class);

        $request = $this->request();
        $factory = $this->factory($request);

        $stream = $this->createStub(StreamFactoryInterface::class);
        $http = $this->createMock(ClientInterface::class);
        $http
            ->expects($this->never())
            ->method('sendRequest');

        $encoder = $this->createMock(EncoderInterface::class);
        $encoder
            ->method('supports')
            ->willReturn(true);
        $encoder
            ->expects($this->once())
            ->method('serialize')
            ->willThrowException(new RequestException());

        try {
            $client = new Client($http, $factory, $stream);
            $client->addEncoder($encoder);
            $client->get('https://example.com');
            $this->fail('Failed to throw a RequestException');
        } catch (RequestException $e) {
            $this->assertNotNull($e->getRequest());
            $this->assertNull($e->getResponse());
            throw $e;
        } catch (\Throwable $e) {
            $this->assertInstanceOf(RequestException::class, $e);
        }
    }

    public function testProcessResponse()
    {
        $http = $this->http('failure', 200);

        $stream = $this->createStub(StreamFactoryInterface::class);
        $request = $this->createMock(RequestInterface::class);
        $request
            ->method('withAddedHeader')
            ->willReturnSelf();
        $request
            ->expects($this->exactly(2))
            ->method('withHeader')
            ->withConsecutive(
                [$this->identicalTo('Accept'), $this->identicalTo(['text/plain'])],
                [$this->identicalTo('Date')]
            )
            ->willReturnSelf();

        $factory = $this->factory($request);

        $decoder = $this->createMock(DecoderInterface::class);
        $decoder
            ->method('getContentType')
            ->willReturn('text/plain');
        $decoder
            ->method('supports')
            ->willReturn(true);
        $decoder
            ->expects($this->once())
            ->method('unserialize')
            ->willReturn('success');

        $client = new Client($http, $factory, $stream);
        $client->addDecoder($decoder);
        $result = $client->get('https://example.com');

        $this->assertSame('success', $result);
    }

    public function testProcessResponseFails()
    {
        $this->expectException(RequestException::class);

        $http = $this->http('data', 200);
        $factory = $this->factory();

        $stream = $this->createStub(StreamFactoryInterface::class);
        $decoder = $this->createMock(DecoderInterface::class);
        $decoder
            ->method('getContentType')
            ->willReturn('text/plain');
        $decoder
            ->method('supports')
            ->willReturn(true);
        $decoder
            ->expects($this->once())
            ->method('unserialize')
            ->willThrowException(new RequestException());

        try {
            $client = new Client($http, $factory, $stream);
            $client->addDecoder($decoder);
            $client->get('https://example.com');
            $this->fail('Failed to throw a RequestException');
        } catch (RequestException $e) {
            $this->assertNotNull($e->getRequest());
            $this->assertNotNull($e->getResponse());
            throw $e;
        } catch (\Throwable $e) {
            $this->assertInstanceOf(RequestException::class, $e);
        }
    }

    public function testProcessWithTransformer()
    {
        $data['foo'] = 'bar';
        $expected['bar'] = 'foo';

        $http = $this->http('bar=foo', 200);
        $request = $this->request();
        $factory = $this->factory($request);

        $stream = $this->createStub(StreamFactoryInterface::class);
        $transformer = $this->createMock(TransformerInterface::class);
        $transformer
            ->method('contentType')
            ->willReturn('*/*');
        $transformer
            ->method('supports')
            ->willReturnCallback('is_array');
        $transformer
            ->expects($this->once())
            ->method('encode')
            ->with($this->equalTo($data))
            ->willReturn('foo=bar');
        $transformer
            ->expects($this->once())
            ->method('decode')
            ->with($this->identicalTo('bar=foo'))
            ->willReturn($expected);

        $client = new Client($http, $factory, $stream);
        $client->addTransformer($transformer);
        $result = $client->post('https://example.com', $data);

        $this->assertSame($expected, $result);
    }
}
