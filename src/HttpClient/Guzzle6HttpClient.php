<?php

namespace Jsor\HalClient\HttpClient;

use GuzzleHttp\Client as GuzzleClient;
use GuzzleHttp\ClientInterface as GuzzleClientInterface;
use GuzzleHttp\Exception\RequestException as GuzzleRequestException;
use Jsor\HalClient\Exception\RequestException;
use Psr\Http\Message\RequestInterface;

final class Guzzle6HttpClient implements HttpClientInterface
{
    private $client;

    public function __construct(GuzzleClientInterface $client = null)
    {
        $this->client = $client ?: new GuzzleClient();
    }

    public function send(RequestInterface $request)
    {
        try {
            return $this->client->send($request);
        } catch (GuzzleRequestException $e) {
            throw RequestException::create(
                $e->getRequest(),
                $e->getResponse(),
                $e,
                $e->getMessage()
            );
        }
    }
}
