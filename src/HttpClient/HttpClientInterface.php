<?php

namespace Jsor\HalClient\HttpClient;

use Jsor\HalClient\Exception\RequestException;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

interface HttpClientInterface
{
    /**
     * @param  RequestInterface  $request
     * @return ResponseInterface
     * @throws RequestException
     */
    public function send(RequestInterface $request);
}
