<?php

namespace Jsor\HalClient;

final class Resource
{
    private $client;
    private $properties;
    private $links;
    private $embedded;

    public function __construct(
        Client $client,
        array $properties = [],
        array $links = [],
        array $embedded = []
    ) {
        $this->client     = $client;
        $this->properties = $properties;
        $this->links      = $links;
        $this->embedded   = $embedded;
    }

    public static function fromArray(Client $client, array $array)
    {
        $links    = [];
        $embedded = [];

        if (isset($array['_links'])) {
            $links = $array['_links'];
        }

        if (isset($array['_embedded'])) {
            $embedded = $array['_embedded'];
        }

        unset($array['_links'], $array['_embedded']);

        $properties = $array;

        return new self(
            $client,
            $properties,
            $links,
            $embedded
        );
    }

    public function getProperties()
    {
        return $this->properties;
    }

    public function hasProperty($name)
    {
        return isset($this->properties[$name]);
    }

    public function getProperty($name)
    {
        if (isset($this->properties[$name])) {
            return $this->properties[$name];
        }

        return null;
    }

    public function hasEmbeds()
    {
        return count($this->embedded) > 0;
    }

    public function getEmbeds()
    {
        $all = [];

        foreach ($this->embedded as $rel => $_) {
            $all[$rel] = $this->getEmbed($rel);
        }

        return $all;
    }

    public function hasEmbed($name)
    {
        return isset($this->embedded[$name]);
    }

    /**
     * @return Resource[]
     */
    public function getEmbed($rel)
    {
        return array_map(function ($data) {
            return static::fromArray($this->client, $data);
        }, $this->getEmbedData($rel));
    }

    public function getFirstEmbed($rel)
    {
        $embedded = $this->getEmbedData($rel);

        if (!isset($embedded[0])) {
            return null;
        }

        return static::fromArray($this->client, $embedded[0]);
    }

    private function getEmbedData($rel)
    {
        if (isset($this->embedded[$rel])) {
            $embedded = $this->embedded[$rel];

            if (!$embedded) {
                return [];
            }

            if (!is_array($embedded)) {
                $embedded = [$embedded];
            }

            $embedded = array_map(function($embed) {
                if (null !== $embed && !is_array($embed)) {
                    $embed = [$embed];
                }

                return $embed;
            }, $embedded);

            return array_filter($embedded, function($embed) {
                return null !== $embed;
            });
        }

        throw new Exception\InvalidArgumentException(
            sprintf(
                'Unknown embedded %s.',
                json_encode($rel)
            )
        );
    }

    public function hasLinks()
    {
        return count($this->links) > 0;
    }

    public function getLinks()
    {
        $all = [];

        foreach ($this->links as $rel => $_) {
            $all[$rel] = $this->getLink($rel);
        }

        return $all;
    }

    public function hasLink($rel)
    {
        if (isset($this->links[$rel])) {
            return true;
        }

        foreach ($this->getLink('curies') as $curie) {
            if (!$curie->getName()) {
                continue;
            }

            $linkRel = $curie->getName() . ':' . $rel;

            if (isset($this->links[$linkRel])) {
               return true;
            }
        }

        return false;
    }

    /**
     * @return Link[]
     */
    public function getLink($rel)
    {
        return array_map(function ($link) {
            return Link::fromArray($this->client, $link);
        }, $this->getLinkData($rel));
    }

    public function getFirstLink($rel)
    {
        $link = $this->getLinkData($rel);

        if (!isset($link[0])) {
            return null;
        }

        return Link::fromArray($this->client, $link[0]);
    }

    private function getLinkData($rel)
    {
        if (isset($this->links[$rel])) {
            $links = $this->links[$rel];

            if (!$links) {
                return [];
            }

            if (!isset($links[0]) || !is_array($links)) {
                $links = [$links];
            }

            $links = array_map(function($link) {
                if (null !== $link && !is_array($link)) {
                    $link = ['href' => $link];
                }

                return $link;
            }, $links);

            return array_filter($links, function($link) {
                return null !== $link;
            });
        }

        foreach ($this->getLink('curies') as $curie) {
            if (!$curie->getName()) {
                continue;
            }

            $linkRel = $curie->getName() . ':' . $rel;

            if (!isset($this->links[$linkRel])) {
                continue;
            }

            return $this->getLinkData($linkRel);
        }

        throw new Exception\InvalidArgumentException(
            sprintf(
                'Unknown link %s.',
                json_encode($rel)
            )
        );
    }

    public function get(array $options = [])
    {
        return $this->request('GET', $options);
    }

    public function post(array $options = [])
    {
        return $this->request('POST', $options);
    }

    public function put(array $options = [])
    {
        return $this->request('PUT', $options);
    }

    public function delete(array $options = [])
    {
        return $this->request('DELETE', $options);
    }

    public function request($method, array $options = [])
    {
        return $this->getFirstLink('self')->request($method, [], $options);
    }
}
