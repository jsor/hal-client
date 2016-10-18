<?php

namespace Jsor\HalClient;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\UriInterface;

interface HalClientInterface
{
    /**
     * @return UriInterface
     */
    public function getRootUrl();

    /**
     * @param string
     *
     * @return string[]
     */
    public function getHeader($name);

    /**
     * @param string
     * @param string|string[]
     *
     * @return HalClientInterface
     */
    public function withHeader($name, $value);

    /**
     * @param array
     *
     * @return HalResource|ResponseInterface
     */
    public function root(array $options = []);

    /**
     * @param string|UriInterface
     * @param array
     *
     * @return HalResource|ResponseInterface
     */
    public function get($uri, array $options = []);

    /**
     * @param string|UriInterface
     * @param array
     *
     * @return HalResource|ResponseInterface
     */
    public function post($uri, array $options = []);

    /**
     * @param string|UriInterface
     * @param array
     *
     * @return HalResource|ResponseInterface
     */
    public function put($uri, array $options = []);

    /**
     * @param string|UriInterface
     * @param array
     *
     * @return HalResource|ResponseInterface
     */
    public function delete($uri, array $options = []);

    /**
     * @param string
     * @param string|UriInterface
     * @param array
     *
     * @return HalResource|ResponseInterface
     */
    public function request($method, $uri, array $options = []);
}
