<?php

namespace Jsor\HalClient;

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
            $uri = self::expandUriTemplate($uri, $variables);
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

    private static function expandUriTemplate($template, $variables)
    {
        static $guzzleUriTemplate;

        if (function_exists('\uri_template')) {
            // @codeCoverageIgnoreStart
            return \uri_template($template, $variables);
            // @codeCoverageIgnoreEnd
        }

        if (class_exists('\GuzzleHttp\UriTemplate')) {
            if (!$guzzleUriTemplate) {
                $guzzleUriTemplate = new \GuzzleHttp\UriTemplate();
            }

            return $guzzleUriTemplate->expand($template, $variables);
        }

        throw new \RuntimeException(
            'Could not detect supported method for expanding URI templates. ' .
            'You should either provide a global \uri_template function ' .
            '(e.g. by installing the uri_template extension from ' .
            'https://github.com/ioseb/uri-template) or by installing the ' .
            'guzzlehttp/guzzle package (version ^5.0 or ^6.0) via Composer.'
        );
    }
}
