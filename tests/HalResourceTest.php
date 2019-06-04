<?php

namespace Jsor\HalClient;

use Jsor\HalClient\Exception\InvalidArgumentException;

class HalResourceTest extends TestCase
{
    /**
     * @test
     */
    public function it_extracts_properties()
    {
        $httpClient = new RecordingHttpClient();

        $client = new HalClient(
            'http://propilex.herokuapp.com',
            $httpClient
        );

        $data = json_decode(file_get_contents(__DIR__ . '/fixtures/documents.json'), true);

        $resource = HalResource::fromArray(
            $client,
            $data
        );

        $expected = [
            'page'  => 1,
            'limit' => 10,
            'pages' => 1,
            'total' => 3
        ];

        $this->assertSame($expected, $resource->getProperties());
        $this->assertTrue($resource->hasProperty('page'));
        $this->assertTrue($resource->hasProperty('limit'));
        $this->assertTrue($resource->hasProperty('pages'));
        $this->assertTrue($resource->hasProperty('total'));
        $this->assertFalse($resource->hasProperty('foo'));
    }

    /**
     * @test
     */
    public function it_extracts_resources()
    {
        $httpClient = new RecordingHttpClient();

        $client = new HalClient(
            'http://propilex.herokuapp.com',
            $httpClient
        );

        $data                              = json_decode(file_get_contents(__DIR__ . '/fixtures/documents.json'), true);
        $data['_embedded']['empty_array']  = [];
        $data['_embedded']['string_array'] = ['StringArray'];
        $data['_embedded']['string']       = 'String';

        $resource = HalResource::fromArray(
            $client,
            $data
        );

        $this->assertTrue($resource->hasResources());
        $this->assertArrayHasKey('documents', $resource->getResources());

        $this->assertTrue($resource->hasResource('documents'));
        $this->assertCount(3, $resource->getResource('documents'));
        $this->assertInstanceOf('Jsor\HalClient\HalResource', $resource->getFirstResource('documents'));

        $this->assertNull($resource->getFirstResource('empty_array'));

        $this->assertCount(1, $resource->getResource('string_array'));
        $this->assertSame('StringArray', $resource->getFirstResource('string_array')->getProperty(0));

        $this->assertCount(1, $resource->getResource('string'));
        $this->assertSame('String', $resource->getFirstResource('string')->getProperty(0));

        $this->expectException(InvalidArgumentException::class);
        $resource->getResource('non_existing');
    }

    /**
     * @test
     */
    public function it_extracts_links()
    {
        $httpClient = new RecordingHttpClient();

        $client = new HalClient(
            'http://propilex.herokuapp.com',
            $httpClient
        );

        $data                           = json_decode(file_get_contents(__DIR__ . '/fixtures/documents.json'), true);
        $data['_links']['empty_array']  = [];
        $data['_links']['string_array'] = ['StringArray'];
        $data['_links']['string']       = 'String';

        $resource = HalResource::fromArray(
            $client,
            $data
        );

        $this->assertTrue($resource->hasLinks());
        $this->assertArrayHasKey('self', $resource->getLinks());

        $this->assertTrue($resource->hasLink('self'));
        $this->assertCount(1, $resource->getLink('self'));
        $this->assertInstanceOf('Jsor\HalClient\HalLink', $resource->getFirstLink('self'));

        $this->assertNull($resource->getFirstLink('empty_array'));

        $this->assertCount(1, $resource->getLink('string_array'));
        $this->assertSame('StringArray', $resource->getFirstLink('string_array')->getHref());

        $this->assertCount(1, $resource->getLink('string'));
        $this->assertSame('String', $resource->getFirstLink('string')->getHref());

        $this->assertFalse($resource->hasLink('non_existing'));
        $this->expectException(InvalidArgumentException::class);
        $resource->getLink('non_existing');
    }

    /**
     * @test
     */
    public function it_extracts_curies()
    {
        $httpClient = new RecordingHttpClient();

        $client = new HalClient(
            'http://propilex.herokuapp.com',
            $httpClient
        );

        $data                     = json_decode(file_get_contents(__DIR__ . '/fixtures/documents.json'), true);
        $data['_links']['curies'] = array_merge(['Curie'], $data['_links']['curies']);

        $resource = HalResource::fromArray(
            $client,
            $data
        );

        $this->assertTrue($resource->hasLink('documents'));
        $this->assertCount(1, $resource->getLink('documents'));
        $this->assertInstanceOf('Jsor\HalClient\HalLink', $resource->getFirstLink('documents'));

        $this->assertTrue($resource->hasLink('p:documents'));
        $this->assertCount(1, $resource->getLink('p:documents'));
        $this->assertInstanceOf('Jsor\HalClient\HalLink', $resource->getFirstLink('p:documents'));
    }

    /**
     * @test
     */
    public function it_can_get()
    {
        $httpClient = new RecordingHttpClient();

        $client = new HalClient(
            'http://propilex.herokuapp.com',
            $httpClient
        );

        $resource = new HalResource(
            $client,
            [],
            [
                'self' => [
                    'href' => '/documents'
                ]
            ]
        );

        $resource->get([
            'headers' => [
                'Foo' => 'bar'
            ],
            'body'  => 'Body',
            'query' => 'key1=key2'
        ]);

        $lastRequest = $httpClient->getLastRequest();

        $this->assertSame('GET', $lastRequest->getMethod());
        $this->assertSame('http://propilex.herokuapp.com/documents?key1=key2', (string) $lastRequest->getUri());
        $this->assertSame('Body', (string) $lastRequest->getBody());
        $this->assertSame(['bar'], $lastRequest->getHeader('Foo'));
    }

    /**
     * @test
     */
    public function it_can_post()
    {
        $httpClient = new RecordingHttpClient();

        $client = new HalClient(
            'http://propilex.herokuapp.com',
            $httpClient
        );

        $resource = new HalResource(
            $client,
            [],
            [
                'self' => [
                    'href' => '/documents'
                ]
            ]
        );

        $resource->post([
            'headers' => [
                'Foo' => 'bar'
            ],
            'body'  => 'Body',
            'query' => 'key1=key2'
        ]);

        $lastRequest = $httpClient->getLastRequest();

        $this->assertSame('POST', $lastRequest->getMethod());
        $this->assertSame('http://propilex.herokuapp.com/documents?key1=key2', (string) $lastRequest->getUri());
        $this->assertSame('Body', (string) $lastRequest->getBody());
        $this->assertSame(['bar'], $lastRequest->getHeader('Foo'));
    }

    /**
     * @test
     */
    public function it_can_put()
    {
        $httpClient = new RecordingHttpClient();

        $client = new HalClient(
            'http://propilex.herokuapp.com',
            $httpClient
        );

        $resource = new HalResource(
            $client,
            [],
            [
                'self' => [
                    'href' => '/documents'
                ]
            ]
        );

        $resource->put([
            'headers' => [
                'Foo' => 'bar'
            ],
            'body'  => 'Body',
            'query' => 'key1=key2'
        ]);

        $lastRequest = $httpClient->getLastRequest();

        $this->assertSame('PUT', $lastRequest->getMethod());
        $this->assertSame('http://propilex.herokuapp.com/documents?key1=key2', (string) $lastRequest->getUri());
        $this->assertSame('Body', (string) $lastRequest->getBody());
        $this->assertSame(['bar'], $lastRequest->getHeader('Foo'));
    }

    /**
     * @test
     */
    public function it_can_delete()
    {
        $httpClient = new RecordingHttpClient();

        $client = new HalClient(
            'http://propilex.herokuapp.com',
            $httpClient
        );

        $resource = new HalResource(
            $client,
            [],
            [
                'self' => [
                    'href' => '/documents'
                ]
            ]
        );

        $resource->delete([
            'headers' => [
                'Foo' => 'bar'
            ],
            'body'  => 'Body',
            'query' => 'key1=key2'
        ]);

        $lastRequest = $httpClient->getLastRequest();

        $this->assertSame('DELETE', $lastRequest->getMethod());
        $this->assertSame('http://propilex.herokuapp.com/documents?key1=key2', (string) $lastRequest->getUri());
        $this->assertSame('Body', (string) $lastRequest->getBody());
        $this->assertSame(['bar'], $lastRequest->getHeader('Foo'));
    }

    /**
     * @test
     */
    public function it_can_request()
    {
        $httpClient = new RecordingHttpClient();

        $client = new HalClient(
            'http://propilex.herokuapp.com',
            $httpClient
        );

        $resource = new HalResource(
            $client,
            [],
            [
                'self' => [
                    'href' => '/documents'
                ]
            ]
        );

        $resource->request('PATCH', [
            'headers' => [
                'Foo' => 'bar'
            ],
            'body'  => 'Body',
            'query' => 'key1=key2'
        ]);

        $lastRequest = $httpClient->getLastRequest();

        $this->assertSame('PATCH', $lastRequest->getMethod());
        $this->assertSame('http://propilex.herokuapp.com/documents?key1=key2', (string) $lastRequest->getUri());
        $this->assertSame('Body', (string) $lastRequest->getBody());
        $this->assertSame(['bar'], $lastRequest->getHeader('Foo'));
    }
}
