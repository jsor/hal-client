<?php

namespace Jsor\HalClient;

use GuzzleHttp\Psr7 as GuzzlePsr7;
use Jsor\HalClient\HttpClient\Guzzle6HttpClient;
use Jsor\HalClient\HttpClient\HttpClientInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

final class HalClient implements HalClientInterface
{
    private $httpClient;
    private $factory;
    private $defaultRequest;

    private static $validContentTypes = [
        'application/hal+json',
        'application/json',
        'application/vnd.error+json'
    ];

    public function __construct($rootUrl, HttpClientInterface $httpClient = null)
    {
        $this->httpClient = $httpClient ?: new Guzzle6HttpClient();

        $this->factory = new Internal\HalResourceFactory(self::$validContentTypes);

        $this->defaultRequest = new GuzzlePsr7\Request('GET', $rootUrl, [
            'User-Agent' => get_class($this),
            'Accept'     => implode(', ', self::$validContentTypes)
        ]);
    }

    public function __clone()
    {
        $this->httpClient     = clone $this->httpClient;
        $this->defaultRequest = clone $this->defaultRequest;
    }

    /**
     * @return \Psr\Http\Message\UriInterface
     */
    public function getRootUrl()
    {
        return $this->defaultRequest->getUri();
    }

    public function withRootUrl($rootUrl)
    {
        $instance = clone $this;

        $instance->defaultRequest = $instance->defaultRequest->withUri(
            GuzzlePsr7\uri_for($rootUrl)
        );

        return $instance;
    }

    public function getHeader($name)
    {
        return $this->defaultRequest->getHeader($name);
    }

    public function withHeader($name, $value)
    {
        $instance = clone $this;

        $instance->defaultRequest = $instance->defaultRequest->withHeader(
            $name,
            $value
        );

        return $instance;
    }

    public function root(array $options = [])
    {
        return $this->request('GET', '', $options);
    }

    public function get($uri, array $options = [])
    {
        return $this->request('GET', $uri, $options);
    }

    public function post($uri, array $options = [])
    {
        return $this->request('POST', $uri, $options);
    }

    public function put($uri, array $options = [])
    {
        return $this->request('PUT', $uri, $options);
    }

    public function delete($uri, array $options = [])
    {
        return $this->request('DELETE', $uri, $options);
    }

    public function request(
        $method,
        $uri,
        array $options = []
    ) {
        /** @var \Psr\Http\Message\RequestInterface $request */
        $request = clone $this->defaultRequest;

        $request = $request->withMethod($method);

        $request = $request->withUri(
            GuzzlePsr7\Uri::resolve($request->getUri(), $uri)
        );

        $request = $this->applyOptions($request, $options);

        try {
            $response = $this->httpClient->send($request);
        } catch (\Exception $e) {
            throw new Exception\HttpClientException(
                sprintf(
                    'Exception thrown by the http client while sending request: %s.',
                    $e->getMessage()
                ),
                $request,
                $e
            );
        }

        return $this->handleResponse($request, $response, $options);
    }

    private function applyOptions(RequestInterface $request, array $options)
    {
        if (isset($options['version'])) {
            $request = $request->withProtocolVersion($options['version']);
        }

        if (isset($options['query'])) {
            $request = $this->applyQuery($request, $options['query']);
        }

        if (isset($options['headers'])) {
            foreach ($options['headers'] as $name => $value) {
                $request = $request->withHeader($name, $value);
            }
        }

        if (isset($options['body'])) {
            $request = $this->applyBody($request, $options['body']);
        }

        return $request;
    }

    private function applyQuery(RequestInterface $request, $query)
    {
        $uri = $request->getUri();

        if (!is_array($query)) {
            $query = GuzzlePsr7\parse_query($query);
        }

        $newQuery = array_merge(
            GuzzlePsr7\parse_query($uri->getQuery()),
            $query
        );

        return $request->withUri(
            $uri->withQuery(http_build_query($newQuery, null, '&'))
        );
    }

    private function applyBody(RequestInterface $request, $body)
    {
        if (is_array($body)) {
            $body = json_encode($body);

            if (!$request->hasHeader('Content-Type')) {
                $request = $request->withHeader(
                    'Content-Type',
                    'application/json'
                );
            }
        }

        return $request->withBody(GuzzlePsr7\stream_for($body));
    }

    private function handleResponse(
        RequestInterface $request,
        ResponseInterface $response,
        array $options
    ) {
        $statusCode = $response->getStatusCode();

        if ($statusCode >= 200 && $statusCode < 300) {
            if (isset($options['return_raw_response']) &&
                true === $options['return_raw_response']) {
                return $response;
            }

            return $this->factory->createResource($this, $request, $response);
        }

        throw Exception\BadResponseException::create(
            $request,
            $response,
            $this->factory->createResource($this, $request, $response, true)
        );
    }
}
