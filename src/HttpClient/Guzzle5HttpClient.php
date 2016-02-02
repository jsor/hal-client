<?php

namespace Jsor\HalClient\HttpClient;

use GuzzleHttp\Client as GuzzleClient;
use GuzzleHttp\ClientInterface as GuzzleClientInterface;
use GuzzleHttp\Exception\RequestException as GuzzleRequestException;
use GuzzleHttp\Message\ResponseInterface as GuzzleResponse;
use GuzzleHttp\Psr7\Response;
use Psr\Http\Message\RequestInterface;

final class Guzzle5HttpClient implements HttpClientInterface
{
    private $client;

    public function __construct(GuzzleClientInterface $client = null)
    {
        $this->client = $client ?: new GuzzleClient();
    }

    public function send(RequestInterface $request)
    {
        $guzzleRequest = $this->createRequest($request);

        try {
            $response = $this->client->send($guzzleRequest);
        } catch (GuzzleRequestException $e) {
            $response = $e->getResponse();

            if (!$response) {
                return new Response(500, [], $e->getMessage());
            }

            return $this->createResponse($response);
        }

        return $this->createResponse($response);
    }

    private function createRequest(RequestInterface $request)
    {
        $options = [
            'exceptions'      => false,
            'allow_redirects' => false,
        ];

        $options['version'] = $request->getProtocolVersion();
        $options['headers'] = $request->getHeaders();
        $options['body']    = (string) $request->getBody();

        return $this->client->createRequest(
            $request->getMethod(),
            (string) $request->getUri(),
            $options
        );
    }

    private function createResponse(GuzzleResponse $response)
    {
        $body = $response->getBody();

        return new Response(
            $response->getStatusCode(),
            $response->getHeaders(),
            isset($body) ? $body->detach() : null,
            $response->getProtocolVersion(),
            $response->getReasonPhrase()
        );
    }
}
