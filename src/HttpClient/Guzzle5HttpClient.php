<?php

namespace Jsor\HalClient\HttpClient;

use GuzzleHttp\Client as GuzzleClient;
use GuzzleHttp\ClientInterface as GuzzleClientInterface;
use GuzzleHttp\Exception\BadResponseException as GuzzleBadResponseException;
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
        } catch (GuzzleBadResponseException $e) {
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
        return $this->client->createRequest(
            $request->getMethod(),
            (string) $request->getUri(),
            [
                'exceptions'      => false,
                'allow_redirects' => false,
                'version'         => $request->getProtocolVersion(),
                'headers'         => $request->getHeaders(),
                'body'            => (string) $request->getBody(),
            ]
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
