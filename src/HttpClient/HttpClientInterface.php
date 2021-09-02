<?php

namespace Jsor\HalClient\HttpClient;

use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

interface HttpClientInterface
{
    /**
     * Note, that this method must not throw exceptions but always return a
     * response.
     *
     * @return ResponseInterface
     */
    public function send(RequestInterface $request);
}
