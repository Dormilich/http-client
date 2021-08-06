# HTTP client request/response handlers

The purpose of this library is to define handlers around a PSR-18 HTTP client that convert data
into a request and a response back into the desired data structure, while being independent of
the actual HTTP client implementation.

## Installation

You can install this library via composer:
```
composer require dormilich/http-client
```
To use this library, install you personal choice of a PSR-18 HTTP client and PSR-17 HTTP factories.

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
The client supports the most common HTTP methods as shorthand methods. This requires data encoders/
transformers to be defined previously.
```php
use Dormilich\HttpClient\Client;
use Dormilich\HttpClient\Transformer\JsonTransformer;

$client = new Client($httpClient, $requestFactory, $streamFactory);
$client->addTransformer(new JsonTransformer());

$client->get('https://example.com/item');
$client->post('https://example.com/item', $data);
$client->put('https://example.com/item', $data);
$client->patch('https://example.com/item', $data);
$client->delete('https://example.com/item');
```
One-time headers and other request methods can be submitted using the `fetch()` method.
```php
use Dormilich\HttpClient\Client;
use Dormilich\HttpClient\Transformer\UrlTransformer;

$client = new Client($httpClient, $requestFactory, $streamFactory);
$client->addTransformer(new UrlTransformer());

$search['foo'] = 'bar';
$header['authorization'] = 'Bearer 279ca9e0-ce59-48b2-8b6d-c0a6822195a1';
$result = $client->fetch('get', 'https://example.com/item', $search, $header);
```
Note: For requests that do not have a request body (GET, HEAD) the data will be put into the query
string. As there is no formal definition of the structure of the query string, the format of the
applied data encoder is used.

## Data transformers

Data transformers allow arbitrary data to be converted into a PSR-7 request and a PSR-7 response
back into a specific data structure.

For this purpose, several data transformers are predefined.
```php
use Dormilich\HttpClient\Client;
use Dormilich\HttpClient\Transformer\DomTransformer;
use Dormilich\HttpClient\Transformer\JsonTransformer;
use Dormilich\HttpClient\Transformer\TextTransformer;
use Dormilich\HttpClient\Transformer\UrlTransformer;
use Dormilich\HttpClient\Transformer\XmlTransformer;

$client = new Client($httpClient, $requestFactory, $streamFactory);
# converts DOMDocument
$client->addTransformer(new DomTransformer());
# converts JsonSerializable & plain objects
$client->addTransformer(new JsonTransformer());
# converts data that can be cast to string
$client->addTransformer(new TextTransformer());
# converts arrays
$client->addTransformer(new UrlTransformer());
# converts SimpleXML objects
$client->addTransformer(new XmlTransformer());
// should multiple transformers encode the same data type, the first one defined wins

$result = $client->post('https://example.com/item', $data);
```
If none of the predefined transformers suit your use case, you can define your own transformer.
For that, create a class that implements `TransformerInterface` and pass it to the client's
`addTransformer()` method.

If you want the response decoded only for selected response status codes, add a status matcher
instance.
```php
use Dormilich\HttpClient\Client;
use Dormilich\HttpClient\Transformer\JsonTransformer;
use Dormilich\HttpClient\Utility\StatusMatcher;

$client = new Client($httpClient, $requestFactory, $streamFactory);
// encodes any JSON object, but decodes only successful responses
$client->addTransformer(new JsonTransformer(), StatusMatcher::success());
$result = $client->get('https://example.com/toc');
```

### JSON transformer

The `JsonTransformer` accepts any `JSON_*` constants for encoding and decoding as constructor argument.
```php
use Dormilich\HttpClient\Transformer\JsonTransformer;

$object = new JsonTransformer();
$array = new JsonTransformer(JSON_OBJECT_AS_ARRAY);
$number = new JsonTransformer(JSON_BIGINT_AS_STRING);

$json = '{"foo":"bar"}';
$result = $object->decode($json);   // $result->foo = 'bar';
$result = $array->decode($json);    // $result['foo'] = 'bar';

$json = '55832922773198229333575409625894748160';
$result = $number->decode($json);   // "55832922773198229333575409625894748160"
$result = $object->decode($json);   // 5.583292277319823E+37
```
Note: Be aware that `JSON_NUMERIC_CHECK` will decode any integer string above `PHP_INT_MAX` into
a floating point number. It will also convert numeric strings that may not be intended for conversion
(e.g. phone numbers, postal codes, etc.).

### URL transformer

The `UrlTransformer` url-encodes/url-decodes data. By default, this uses the PHP-style of parsing.
There is another parser available that strictly parses key-value pairs (i.e. no nested arrays).

```php
use Dormilich\HttpClient\Transformer\UrlTransformer;
use Dormilich\HttpClient\Utility\NvpQuery;

$php = new UrlTransformer();
$nvp = new UrlTransformer(new NvpQuery());

$data['q'][] = 'foo';
$data['q'][] = 'bar';

$query_php = $php->encode($data);   // "q%5B0%5D=foo&q%5B1%5D=bar"
$query_nvp = $nvp->encode($data);   // "q=foo&q=bar"

$result = $php->decode($query_php)  // ['q' => ['foo', 'bar']]
$result = $php->decode($query_nvp)  // ['q' => 'bar']
$result = $nvp->decode($query_nvp)  // ['q' => ['foo', 'bar']]
$result = $nvp->decode($query_php)  // ['q[0]' => 'foo', 'q[1]' => 'bar']
```

## Request modification

Data transformers will always attempt to encode the request and decode the response. If you only
want to act on the request, define an encoder. For that, create a class that implements
`EncoderInterface` and pass it to the client's `addEncoder()` method.

An example where this is useful are encoders that modify the request, e.g. adding headers.
```php
use Dormilich\HttpClient\Client;
use Dormilich\HttpClient\Encoder\ContentLength;

$client = new Client($httpClient, $requestFactory, $streamFactory);
// this will add the `Content-Length` header, if appropriate
$client->addEncoder(new ContentLength());
```
Encoders that should modify the request must support `Psr\Http\Message\RequestInterface` in the
encoder's `supports()` method.

## Parsing the response

If you have defined transformers, these will also be used for converting back the response into
a data structure. However, there are other cases where a full transformer is inappropriate. This
includes processing non-successful responses. In contrast to encoders, the client does not need
to have decoders defined, it will return the response body if no decoder matches (or exists).
```php
use Dormilich\HttpClient\Client;
use Dormilich\HttpClient\Decoder\ErrorDecoder;
use Dormilich\HttpClient\Exception\RequestException;
use Dormilich\HttpClient\Transformer\JsonTransformer;

try {
    $client = new Client($httpClient, $requestFactory, $streamFactory);
    // converts a failed request into an exception
    // using the request body as exception message
    $client->addDecoder(new ErrorDecoder());
    // can only process successful responses
    // as failed responses are already handled by the error decoder
    $client->addTransformer(new JsonTransformer());
    $result = $client->get('https://example.com/toc')
} catch (RequestException $e) {
    $context['request'] = $e->getRequest();
    $context['response'] = $e->getResponse();
    $logger->error($e->getMessage(), $context);
}
```
If you only want to act on the response, define a decoder. For that, create a class that implements
`DecoderInterface` and pass it to the client's `addDecoder()` method.

Again, the first matching decoder wins and will skip any later defined decoders.

## Exceptions

Beside the aforementioned `UnsupportedDataTypeException` that indicates a setup issue, the client
can also throw a `RequestException`. The PSR-18 exceptions are wrapped into a `RequestException`
and failures to encode or decode will throw an `EncoderException` or `DecoderException`, respectively.
