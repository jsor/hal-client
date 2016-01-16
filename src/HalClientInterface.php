<?php

namespace Jsor\HalClient;

use Psr\Http\Message\UriInterface;

interface HalClientInterface
{
    /**
     * @return UriInterface
     */
    public function getRootUrl();

    /**
     * @param string
     * @return string[]
     */
    public function getHeader($name);

    /**
     * @param string
     * @param string|string[]
     * @return HalClientInterface
     */
    public function withHeader($name, $value);

    /**
     * @param array
     * @return HalResource
     */
    public function root(array $options = []);

    /**
     * @param string|UriInterface
     * @param array
     * @return HalResource
     */
    public function get($uri, array $options = []);

    /**
     * @param string|UriInterface
     * @param array
     * @return HalResource
     */
    public function post($uri, array $options = []);

    /**
     * @param string|UriInterface
     * @param array
     * @return HalResource
     */
    public function put($uri, array $options = []);

    /**
     * @param string|UriInterface
     * @param array
     * @return HalResource
     */
    public function delete($uri, array $options = []);

    /**
     * @param string
     * @param string|UriInterface
     * @param array
     * @return HalResource
     */
    public function request($method, $uri, array $options = []);
}
