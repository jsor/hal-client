<?php

namespace Jsor\HalClient;

use Jsor\HalClient\Exception\InvalidArgumentException;

class ResourceTest extends TestCase
{
    /**
     * @test
     */
    public function it_extracts_properties()
    {
        $httpClient = new RecordingHttpClient();

        $client = new Client(
            'http://propilex.herokuapp.com',
            $httpClient
        );

        $data = json_decode(file_get_contents(__DIR__ . '/fixtures/documents.json'), true);

        $resource = Resource::fromArray(
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
    public function it_extracts_embeds()
    {
        $httpClient = new RecordingHttpClient();

        $client = new Client(
            'http://propilex.herokuapp.com',
            $httpClient
        );

        $data                              = json_decode(file_get_contents(__DIR__ . '/fixtures/documents.json'), true);
        $data['_embedded']['empty_array']  = [];
        $data['_embedded']['string_array'] = ['StringArray'];
        $data['_embedded']['string']       = 'String';

        $resource = Resource::fromArray(
            $client,
            $data
        );

        $this->assertTrue($resource->hasEmbeds());
        $this->assertArrayHasKey('documents', $resource->getEmbeds());

        $this->assertTrue($resource->hasEmbed('documents'));
        $this->assertCount(3, $resource->getEmbed('documents'));
        $this->assertInstanceOf(Resource::class, $resource->getFirstEmbed('documents'));

        $this->assertNull($resource->getFirstEmbed('empty_array'));

        $this->assertCount(1, $resource->getEmbed('string_array'));
        $this->assertSame('StringArray', $resource->getFirstEmbed('string_array')->getProperty(0));

        $this->assertCount(1, $resource->getEmbed('string'));
        $this->assertSame('String', $resource->getFirstEmbed('string')->getProperty(0));

        $this->setExpectedException(InvalidArgumentException::class);
        $resource->getEmbed('non_existing');
    }

    /**
     * @test
     */
    public function it_extracts_links()
    {
        $httpClient = new RecordingHttpClient();

        $client = new Client(
            'http://propilex.herokuapp.com',
            $httpClient
        );

        $data                           = json_decode(file_get_contents(__DIR__ . '/fixtures/documents.json'), true);
        $data['_links']['empty_array']  = [];
        $data['_links']['string_array'] = ['StringArray'];
        $data['_links']['string']       = 'String';

        $resource = Resource::fromArray(
            $client,
            $data
        );

        $this->assertTrue($resource->hasLinks());
        $this->assertArrayHasKey('self', $resource->getLinks());

        $this->assertTrue($resource->hasLink('self'));
        $this->assertCount(1, $resource->getLink('self'));
        $this->assertInstanceOf(Link::class, $resource->getFirstLink('self'));

        $this->assertNull($resource->getFirstLink('empty_array'));

        $this->assertCount(1, $resource->getLink('string_array'));
        $this->assertSame('StringArray', $resource->getFirstLink('string_array')->getHref());

        $this->assertCount(1, $resource->getLink('string'));
        $this->assertSame('String', $resource->getFirstLink('string')->getHref());

        $this->assertFalse($resource->hasLink('non_existing'));
        $this->setExpectedException(InvalidArgumentException::class);
        $resource->getLink('non_existing');
    }

    /**
     * @test
     */
    public function it_extracts_curies()
    {
        $httpClient = new RecordingHttpClient();

        $client = new Client(
            'http://propilex.herokuapp.com',
            $httpClient
        );

        $data                     = json_decode(file_get_contents(__DIR__ . '/fixtures/documents.json'), true);
        $data['_links']['curies'] = array_merge(['Curie'], $data['_links']['curies']);

        $resource = Resource::fromArray(
            $client,
            $data
        );

        $this->assertTrue($resource->hasLink('documents'));
        $this->assertCount(1, $resource->getLink('documents'));
        $this->assertInstanceOf(Link::class, $resource->getFirstLink('documents'));

        $this->assertTrue($resource->hasLink('p:documents'));
        $this->assertCount(1, $resource->getLink('p:documents'));
        $this->assertInstanceOf(Link::class, $resource->getFirstLink('p:documents'));
    }

    /**
     * @test
     */
    public function it_can_get()
    {
        $httpClient = new RecordingHttpClient();

        $client = new Client(
            'http://propilex.herokuapp.com',
            $httpClient
        );

        $resource = new Resource(
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

        $client = new Client(
            'http://propilex.herokuapp.com',
            $httpClient
        );

        $resource = new Resource(
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

        $client = new Client(
            'http://propilex.herokuapp.com',
            $httpClient
        );

        $resource = new Resource(
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

        $client = new Client(
            'http://propilex.herokuapp.com',
            $httpClient
        );

        $resource = new Resource(
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

        $client = new Client(
            'http://propilex.herokuapp.com',
            $httpClient
        );

        $resource = new Resource(
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
