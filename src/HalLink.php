<?php

namespace Jsor\HalClient;

use GuzzleHttp\UriTemplate\UriTemplate;

final class HalLink
{
    private $client;
    private $href;
    private $templated;
    private $type;
    private $deprecation;
    private $name;
    private $profile;
    private $title;
    private $hreflang;

    public function __construct(
        HalClientInterface $client,
        $href,
        $templated = null,
        $type = null,
        $deprecation = null,
        $name = null,
        $profile = null,
        $title = null,
        $hreflang = null
    ) {
        $this->client      = $client;
        $this->href        = $href;
        $this->templated   = $templated;
        $this->type        = $type;
        $this->deprecation = $deprecation;
        $this->name        = $name;
        $this->profile     = $profile;
        $this->title       = $title;
        $this->hreflang    = $hreflang;
    }

    public static function fromArray(HalClientInterface $client, array $array)
    {
        $array = array_replace([
            'href'        => null,
            'templated'   => null,
            'type'        => null,
            'deprecation' => null,
            'name'        => null,
            'profile'     => null,
            'title'       => null,
            'hreflang'    => null,
        ], $array);

        return new self(
            $client,
            $array['href'],
            $array['templated'],
            $array['type'],
            $array['deprecation'],
            $array['name'],
            $array['profile'],
            $array['title'],
            $array['hreflang']
        );
    }

    public function getUri(array $variables = [])
    {
        $uri = (string) $this->href;

        if (true === $this->templated) {
            $uri = UriTemplate::expand($uri, $variables);
        }

        return $uri;
    }

    public function getHref()
    {
        return $this->href;
    }

    public function getTemplated()
    {
        return $this->templated;
    }

    public function getType()
    {
        return $this->type;
    }

    public function getDeprecation()
    {
        return $this->deprecation;
    }

    public function getName()
    {
        return $this->name;
    }

    public function getProfile()
    {
        return $this->profile;
    }

    public function getTitle()
    {
        return $this->title;
    }

    public function getHreflang()
    {
        return $this->hreflang;
    }

    public function get(array $variables = [], array $options = [])
    {
        return $this->request('GET', $variables, $options);
    }

    public function post(array $variables = [], array $options = [])
    {
        return $this->request('POST', $variables, $options);
    }

    public function put(array $variables = [], array $options = [])
    {
        return $this->request('PUT', $variables, $options);
    }

    public function delete(array $variables = [], array $options = [])
    {
        return $this->request('DELETE', $variables, $options);
    }

    public function request($method, array $variables = [], array $options = [])
    {
        return $this->client->request(
            $method,
            $this->getUri($variables),
            $options
        );
    }
}
