<?php

namespace Jsor\HalClient;

interface HalClientInterface
{
    public function getRootUrl();

    public function getHeader($name);

    public function withHeader($name, $value);

    public function root(array $options = []);

    public function get($uri, array $options = []);

    public function post($uri, array $options = []);

    public function put($uri, array $options = []);

    public function delete($uri, array $options = []);

    public function request($method, $uri, array $options = []);
}
