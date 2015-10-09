<?php

namespace Jsor\HalClient;

use GuzzleHttp\Psr7\Response;
use Jsor\HalClient\HttpClient\HttpClientInterface;
use Psr\Http\Message\RequestInterface;

class RecordingHttpClient implements HttpClientInterface
{
    public $requests = [];

    public function send(RequestInterface $request)
    {
        $this->requests[] = $request;

        return new Response(200, ['Content-Type' => 'application/hal+json']);
    }

    /**
     * @return RequestInterface
     */
    public function getLastRequest()
    {
        return $this->requests[count($this->requests) - 1];
    }
}
