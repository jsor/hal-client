<?php

namespace Jsor\HalClient;

use function count;
use function is_array;

final class HalResource
{
    private $client;
    private $properties;
    private $links;
    private $resources;

    public function __construct(
        HalClientInterface $client,
        array $properties = [],
        array $links = [],
        array $resources = []
    ) {
        $this->client     = $client;
        $this->properties = $properties;
        $this->links      = $links;
        $this->resources  = $resources;
    }

    public static function fromArray(HalClientInterface $client, array $array)
    {
        $links     = [];
        $resources = [];

        if (isset($array['_links'])) {
            $links = $array['_links'];
        }

        if (isset($array['_embedded'])) {
            $resources = $array['_embedded'];
        }

        unset($array['_links'], $array['_embedded']);

        $properties = $array;

        return new self(
            $client,
            $properties,
            $links,
            $resources
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
        return false !== $this->resolveLinkRel($rel);
    }

    /**
     * @return HalLink[]
     */
    public function getLink($rel)
    {
        return array_map(function ($link) {
            return HalLink::fromArray($this->client, $link);
        }, $this->getLinkData($rel));
    }

    public function getFirstLink($rel)
    {
        $link = $this->getLinkData($rel);

        if (!isset($link[0])) {
            return null;
        }

        return HalLink::fromArray($this->client, $link[0]);
    }

    private function getLinkData($rel)
    {
        $resolvedRel = $this->resolveLinkRel($rel);

        if (false === $resolvedRel) {
            throw new Exception\InvalidArgumentException(
                sprintf(
                    'Unknown link %s.',
                    json_encode($rel)
                )
            );
        }

        return $this->normalizeData($this->links[$resolvedRel], function ($link) {
            return ['href' => $link];
        });
    }

    private function resolveLinkRel($rel)
    {
        if (isset($this->links[$rel])) {
            return $rel;
        }

        if (!isset($this->links['curies'])) {
            return false;
        }

        foreach ($this->getLink('curies') as $curie) {
            if (!$curie->getName()) {
                continue;
            }

            $linkRel = $curie->getName() . ':' . $rel;

            if (isset($this->links[$linkRel])) {
                return $linkRel;
            }
        }

        return false;
    }

    public function hasResources()
    {
        return count($this->resources) > 0;
    }

    public function getResources()
    {
        $all = [];

        foreach ($this->resources as $rel => $_) {
            $all[$rel] = $this->getResource($rel);
        }

        return $all;
    }

    public function hasResource($name)
    {
        return isset($this->resources[$name]);
    }

    /**
     * @return HalResource[]
     */
    public function getResource($rel)
    {
        return array_map(function ($data) {
            return static::fromArray($this->client, $data);
        }, $this->getResourceData($rel));
    }

    public function getFirstResource($rel)
    {
        $resources = $this->getResourceData($rel);

        if (!isset($resources[0])) {
            return null;
        }

        return static::fromArray($this->client, $resources[0]);
    }

    private function getResourceData($rel)
    {
        if (isset($this->resources[$rel])) {
            return $this->normalizeData($this->resources[$rel], function ($resource) {
                return [$resource];
            });
        }

        throw new Exception\InvalidArgumentException(
            sprintf(
                'Unknown resource %s.',
                json_encode($rel)
            )
        );
    }

    private function normalizeData($data, callable $arrayNormalizer)
    {
        if (!$data) {
            return [];
        }

        if (!isset($data[0]) || !is_array($data)) {
            $data = [$data];
        }

        $data = array_map(function ($entry) use ($arrayNormalizer) {
            if (null !== $entry && !is_array($entry)) {
                $entry = $arrayNormalizer($entry);
            }

            return $entry;
        }, $data);

        return array_filter($data, function ($entry) {
            return null !== $entry;
        });
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
