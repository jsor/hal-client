<?php

namespace Jsor\HalClient;

use GuzzleHttp\Psr7 as GuzzlePsr7;
use Jsor\HalClient\HttpClient\Guzzle6HttpClient;
use Jsor\HalClient\HttpClient\HttpClientInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

final class Client implements ClientInterface
{
    private $httpClient;
    private $defaultRequest;

    private $validContentTypes = [
        'application/hal+json',
        'application/json',
        'application/vnd.error+json'
    ];

    public function __construct($rootUrl, HttpClientInterface $httpClient = null)
    {
        $this->httpClient = $httpClient ?: new Guzzle6HttpClient();

        $this->defaultRequest = new GuzzlePsr7\Request('GET', $rootUrl, [
            'User-Agent' => self::class,
            'Accept'     => implode(', ', $this->validContentTypes)
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
            $uri   = $request->getUri();
            $query = $options['query'];

            if (!is_array($query)) {
                $query = GuzzlePsr7\parse_query($query);
            }

            $newQuery = array_merge(
                GuzzlePsr7\parse_query($uri->getQuery()),
                $query
            );

            $request = $request->withUri(
                $uri->withQuery(http_build_query($newQuery, null, '&'))
            );
        }

        if (isset($options['headers'])) {
            foreach ($options['headers'] as $name => $value) {
                $request = $request->withHeader($name, $value);
            }
        }

        if (isset($options['body'])) {
            $body = $options['body'];

            if (is_array($body)) {
                $body = json_encode($body);

                if (!$request->hasHeader('Content-Type')) {
                    $request = $request->withHeader(
                        'Content-Type',
                        'application/json'
                    );
                }
            }

            $request = $request->withBody(GuzzlePsr7\stream_for($body));
        }

        return $request;
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

            return $this->createResource($request, $response);
        }

        throw Exception\BadResponseException::create(
            $request,
            $response,
            $this->createResource($request, $response, true)
        );
    }

    private function createResource(
        RequestInterface $request,
        ResponseInterface $response,
        $ignoreInvalidContentType = false
    ) {
        if (204 === $response->getStatusCode()) {
            // No-Content response
            return new Resource($this);
        }

        if (!$this->isValidContentType($response)) {
            if ($ignoreInvalidContentType) {
                return new Resource($this);
            }

            $types = $response->getHeader('Content-Type') ?: ['none'];

            throw new Exception\BadResponseException(
                sprintf(
                    'Request did not return a valid content type. Returned content type: %s.',
                    implode(', ', $types)
                ),
                $request,
                $response,
                new Resource($this)
            );
        }

        $body = $this->fetchBody($request, $response);

        if ('' === $body) {
            return new Resource($this);
        }

        $data = $this->decodeBody($request, $response, $body);

        return Resource::fromArray($this, (array) $data);
    }

    private function isValidContentType(ResponseInterface $response)
    {
        $contentTypeHeaders = $response->getHeader('Content-Type');

        foreach ($this->validContentTypes as $validContentType) {
            if (in_array($validContentType, $contentTypeHeaders)) {
                return true;
            }
        }

        return false;
    }

    private function fetchBody(
        RequestInterface $request,
        ResponseInterface $response
    ) {
        try {
            return $response->getBody()->getContents();
        } catch (\Exception $e) {
            throw new Exception\BadResponseException(
                sprintf(
                    'Error getting response body: %s.',
                    $e->getMessage()
                ),
                $request,
                $response,
                new Resource($this),
                $e
            );
        }
    }

    private function decodeBody(
        RequestInterface $request,
        ResponseInterface $response,
        $body
    ) {
        $data = json_decode($body, true);

        if (JSON_ERROR_NONE !== json_last_error()) {
            throw new Exception\BadResponseException(
                sprintf(
                    'JSON parse error: %s.',
                    json_last_error_msg()
                ),
                $request,
                $response,
                new Resource($this)
            );
        }

        return $data;
    }
}
