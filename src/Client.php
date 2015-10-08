<?php

namespace Jsor\HalClient;

use GuzzleHttp\Psr7 as GuzzlePsr7;
use Jsor\HalClient\HttpClient\Guzzle6HttpClient;
use Jsor\HalClient\HttpClient\HttpClientInterface;
use Psr\Http\Message\ResponseInterface;

final class Client implements ClientInterface
{
    private $httpClient;
    private $defaultRequest;

    public function __construct($rootUrl, HttpClientInterface $httpClient = null)
    {
        $this->httpClient = $httpClient ?: new Guzzle6HttpClient();

        $this->defaultRequest = new GuzzlePsr7\Request('GET', $rootUrl, [
            'User-Agent' => self::class,
            'Accept'     => 'application/hal+json, application/json'
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

        $uri = GuzzlePsr7\Uri::resolve($request->getUri(), $uri);

        if (isset($options['query'])) {
            $query = $options['query'];

            if (!is_array($query)) {
                $query = GuzzlePsr7\parse_query($query);
            }

            $newQuery = array_merge(
                GuzzlePsr7\parse_query($uri->getQuery()),
                $query
            );

            $uri = $uri->withQuery(http_build_query($newQuery, null, '&'));
        }

        $request = $request->withUri($uri);

        if (isset($options['headers'])) {
            foreach ($options['headers'] as $name => $value) {
                $request = $request->withHeader($name, $value);
            }
        }

        if (isset($options['body'])) {
            $body = $options['body'];

            if (is_array($body)) {
                $body = http_build_query($body, null, '&');

                if (!$request->hasHeader('Content-Type')) {
                    $request = $request->withHeader(
                        'Content-Type',
                        'application/x-www-form-urlencoded'
                    );
                }
            }

            $request = $request->withBody(GuzzlePsr7\stream_for($body));
        }

        $response = $this->httpClient->send($request);

        // Check for "misbehaving" http clients returning not successful
        // responses instead of throwing a RequestException
        $statusCode = $response->getStatusCode();

        if ($statusCode >= 200 && $statusCode < 300) {
            return $this->createResource($response);
        }

        throw Exception\RequestException::create(
            $request,
            $response
        );
    }

    private function createResource(ResponseInterface $response)
    {
        $data = json_decode($response->getBody()->getContents(), true);

        if (JSON_ERROR_NONE !== json_last_error()) {
            throw new Exception\InvalidJsonException(
                $response,
                json_last_error_msg()
            );
        }

        return Resource::fromArray($this, (array) $data);
    }
}
