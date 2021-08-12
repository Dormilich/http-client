# HTTP client request/response handlers

The purpose of this library is to define handlers around a PSR-18 HTTP client that convert data
into a request and a response back into the desired data structure, while being independent of
the actual HTTP client implementation.

## Installation

You can install this library via composer:
```
composer require dormilich/http-client
```
To use this library, install you personal choice of a [PSR-18](https://www.php-fig.org/psr/psr-18/)
HTTP client and [PSR-17](https://www.php-fig.org/psr/psr-17/) HTTP factories.

## HTTP Client

The HTTP client needs to be set up with a PSR-18 HTTP client and a PSR-17 request and stream factory.
```php
use Dormilich\HttpClient\Client;

// replace this with the actual implementations
$httpClient = new HttpClient();         // PSR-18
$requestFactory = new RequestFactory(); // PSR-17
$streamFactory = new StreamFactory();   // PSR-17

$client = new Client($httpClient, $requestFactory, $streamFactory);
```
While this may look tedious for manual setup, this becomes easy in most frameworks with dependency
injection.

Before sending off the request, the client will apply any default headers that were defined as well
as any request modifications (see [Request modification](#request-modification)).
```php
use Dormilich\HttpClient\Client;

$client = new Client($httpClient, $requestFactory, $streamFactory);
$client->getHeaders()->add('User-Agent', 'curl/7.64.1');
// all requests will now contain the `User-Agent` header
```
If no data transformer is defined (see [Data transformers](#data-transformers)), it is only possible
to send PSR-7 requests. Otherwise, the client will complain that no encoder is defined for the data
to be processed and throw a `UnsupportedDataTypeException`.
```php
use Dormilich\HttpClient\Client;

$client = new Client($httpClient, $requestFactory, $streamFactory);

$request = new Request('GET', 'https://example.com'); // PSR-7
// $result will contain the response body content
$result = $client->request($request);
```
The client supports the most common HTTP methods as shorthand methods. This requires data encoders
to be defined previously.
```php
use Dormilich\HttpClient\Client;
use Dormilich\HttpClient\Transformer\JsonEncoder;

$client = new Client($httpClient, $requestFactory, $streamFactory);
$client->addTransformer(new JsonEncoder());

$client->get('https://example.com/item');
$client->post('https://example.com/item', $data);
$client->put('https://example.com/item', $data);
$client->patch('https://example.com/item', $data);
$client->delete('https://example.com/item');
```
One-time headers and other request methods can be submitted using the `fetch()` method.
```php
use Dormilich\HttpClient\Client;
use Dormilich\HttpClient\Transformer\UrlEncoder;

$client = new Client($httpClient, $requestFactory, $streamFactory);
$client->addTransformer(new UrlEncoder());

$search['foo'] = 'bar';
$header['authorization'] = 'Bearer 279ca9e0-ce59-48b2-8b6d-c0a6822195a1';
$result = $client->fetch('get', 'https://example.com/item', $search, $header);
```
Note: For requests that do not have a request body (GET, HEAD) the data will be put into the query
string. As there is no formal definition of the structure of the query string, the format of the
applied data encoder is used.

## Data transformers

Data transformers allow arbitrary data to be converted into a PSR-7 request and a PSR-7 response
back into a specific data structure. For this purpose, several data transformers are predefined.

Data transformers implementing `TransformerInterface` can encode the request and decode the response.
```php
use Dormilich\HttpClient\Client;
use Dormilich\HttpClient\Transformer\DomTransformer;
use Dormilich\HttpClient\Transformer\TextTransformer;
use Dormilich\HttpClient\Transformer\XmlTransformer;

$client = new Client($httpClient, $requestFactory, $streamFactory);
# converts DOMDocument
$client->addTransformer(new DomTransformer());
# converts data that can be cast to string
$client->addTransformer(new TextTransformer());
# converts SimpleXML objects
$client->addTransformer(new XmlTransformer());

$result = $client->post('https://example.com/item', $data);
```
Data transformers implementing `DataEncoderInterface` can encode the request but ignore the response.
```php
use Dormilich\HttpClient\Client;
use Dormilich\HttpClient\Transformer\JsonEncoder;
use Dormilich\HttpClient\Transformer\UrlEncoder;

$client = new Client($httpClient, $requestFactory, $streamFactory);
# converts JsonSerializable & plain objects
$client->addTransformer(new JsonEncoder());
# converts arrays
$client->addTransformer(new UrlEncoder());

$result = $client->post('https://example.com/item', $data);
```
Data transformers implementing `DataDecoderInterface` can decode the response but ignore the request.
These transformers are set up to only decode successful responses.
```php
use Dormilich\HttpClient\Client;
use Dormilich\HttpClient\Transformer\JsonDecoder;
use Dormilich\HttpClient\Transformer\UrlDecoder;

$client = new Client($httpClient, $requestFactory, $streamFactory);
$client->addTransformer(new JsonDecoder(JSON_OBJECT_AS_ARRAY));
$client->addTransformer(new UrlDecoder());

$result = $client->post('https://example.com/item', $data);
```
If you need to decode an error response, you can wrap the transformer into a `Decoder` object and
add the status restriction using a status matcher instance.
```php
use Dormilich\HttpClient\Client;
use Dormilich\HttpClient\Decoder\Decoder;
use Dormilich\HttpClient\Transformer\JsonDecoder;
use Dormilich\HttpClient\Utility\StatusMatcher;

$decoder = new Decoder(new JsonDecoder());
$decoder->setStatusMatcher(StatusMatcher::clientError());

$client = new Client($httpClient, $requestFactory, $streamFactory);
// only decodes HTTP 4xx errors in JSON format
$client->addDecoder($decoder);
```
Should multiple data transformers encode the same data type or process the same response type,
the first one defined wins.

The transformers for handling JSON and URL-encoded data have been split into encoders and decoders
to allow the response to be decoded independently of the request encoder (e.g. when you want to
decode a JSON response into a specific object).

### JSON transformers

The `JsonDecoder` and `JsonEncoder` accept any `JSON_*` constants for encoding and decoding as
constructor argument.
```php
use Dormilich\HttpClient\Transformer\JsonEncoder;

$default = new JsonEncoder();
$slashes = new JsonEncoder(JSON_UNESCAPED_SLASHES);

$data = 'text/plain';
$result = $default->encode($data);  // "text\/plain"
$result = $slashes->encode($data);  // "text/plain"
```
```php
use Dormilich\HttpClient\Transformer\JsonDecoder;

$object = new JsonDecoder();
$array = new JsonDecoder(JSON_OBJECT_AS_ARRAY);

$json = '{"foo":"bar"}';
$result = $object->decode($json);   // $result->foo = 'bar';
$result = $array->decode($json);    // $result['foo'] = 'bar';
```
Note: Be aware that `JSON_NUMERIC_CHECK` will decode any integer string above `PHP_INT_MAX` into
a floating point number. It will also convert numeric strings that may not be intended for conversion
(e.g. phone numbers, postal codes, etc.).

### URL transformers

These transformers url-encode/url-decode data. By default, this uses the PHP-style of parsing.
There is another parser available that strictly parses key-value pairs (i.e. no nested arrays).
```php
use Dormilich\HttpClient\Transformer\UrlDecoder;
use Dormilich\HttpClient\Transformer\UrlEncoder;
use Dormilich\HttpClient\Utility\NvpQuery;

$php_encoder = new UrlEncoder();
$nvp_encoder = new UrlEncoder(new NvpQuery());

$data['q'][] = 'foo';
$data['q'][] = 'bar';

$query_php = $php_encoder->encode($data);   // "q%5B0%5D=foo&q%5B1%5D=bar"
$query_nvp = $nvp_encoder->encode($data);   // "q=foo&q=bar"

$php_decoder = new UrlEncoder();
$nvp_decoder = new UrlEncoder(new NvpQuery());

$result = $php_decoder->decode($query_php)  // ['q' => ['foo', 'bar']]
$result = $php_decoder->decode($query_nvp)  // ['q' => 'bar']
$result = $nvp_decoder->decode($query_nvp)  // ['q' => ['foo', 'bar']]
$result = $nvp_decoder->decode($query_php)  // ['q[0]' => 'foo', 'q[1]' => 'bar']
```

## Request modification

Despite encoding data for the request, encoders can also be used for modifying the request.
Therefore, the encoder must support `Psr\Http\Message\RequestInterface` as payload data type in the
encoder's `supports()` method.

An example where this is useful are encoders that modify the request, e.g. adding headers.
```php
use Dormilich\HttpClient\Client;
use Dormilich\HttpClient\Encoder\ContentLength;

$client = new Client($httpClient, $requestFactory, $streamFactory);
// this will add the `Content-Length` header, if appropriate
$client->addEncoder(new ContentLength());
```

## Parsing the response

The response can be processed by defining response decoders or data decoders. Data decoders will
only be used on successful responses while response decoders can be configured to process any
(specific) response.

In contrast to encoders, the client does not need to have decoders defined, it will return the
response body content if no decoder matches (or exists).
```php
use Psr\Log\LoggerInterface;
use Dormilich\HttpClient\Client;
use Dormilich\HttpClient\Decoder\ErrorDecoder;
use Dormilich\HttpClient\Exception\RequestException;
use Dormilich\HttpClient\Transformer\JsonDecoder;

try {
    $client = new Client($httpClient, $requestFactory, $streamFactory);
    // converts a failed request into an exception
    // using the request body as exception message
    $client->addDecoder(new ErrorDecoder());
    $client->addTransformer(new JsonDecoder());
    $result = $client->get('https://example.com/toc')
} catch (RequestException $e) {
    $context['request'] = $e->getRequest();
    $context['response'] = $e->getResponse();
    $logger->error($e->getMessage(), $context);
}
```

## Exceptions

Beside the aforementioned `UnsupportedDataTypeException` that indicates a setup issue, the client
can also throw a `RequestException`. The PSR-18 exceptions are wrapped into a `RequestException`
and failures to encode or decode will throw an `EncoderException` or `DecoderException`, respectively.
