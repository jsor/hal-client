<?php

namespace Jsor\HalClient;

class HalLinkTest extends TestCase
{
    private $variables  = [
        'page'  => 1,
        'limit' => 10
    ];

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

        $link = new HalLink(
            $client,
            '/documents{?page,limit}',
            true,
            'application/hal+json',
            'http://example.com/deprecation',
            'name',
            'http://example.com/profile',
            'title',
            'en'
        );

        $this->assertSame('/documents?page=1&limit=10', $link->getUri($this->variables));
        $this->assertSame('/documents{?page,limit}', $link->getHref());
        $this->assertSame(true, $link->getTemplated());
        $this->assertSame('application/hal+json', $link->getType());
        $this->assertSame('http://example.com/deprecation', $link->getDeprecation());
        $this->assertSame('name', $link->getName());
        $this->assertSame('http://example.com/profile', $link->getProfile());
        $this->assertSame('title', $link->getTitle());
        $this->assertSame('en', $link->getHreflang());
    }

    /**
     * @test
     */
    public function it_can_get()
    {
        $httpClient = new RecordingHttpClient();

        $client = new HalClient(
            'http://propilex.herokuapp.com/documents',
            $httpClient
        );

        $link = new HalLink(
            $client,
            '/documents{?page,limit}',
            true,
            'application/hal+json',
            'http://example.com/deprecation',
            'name',
            'http://example.com/profile',
            'title',
            'en'
        );

        $link->get($this->variables, [
            'headers' => [
                'Foo' => 'bar'
            ],
            'body'  => 'Body',
            'query' => 'key1=key2'
        ]);

        $lastRequest = $httpClient->getLastRequest();

        $this->assertSame('GET', $lastRequest->getMethod());
        $this->assertSame('http://propilex.herokuapp.com/documents?page=1&limit=10&key1=key2', (string) $lastRequest->getUri());
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
            'http://propilex.herokuapp.com/documents',
            $httpClient
        );

        $link = new HalLink(
            $client,
            '/documents{?page,limit}',
            true,
            'application/hal+json',
            'http://example.com/deprecation',
            'name',
            'http://example.com/profile',
            'title',
            'en'
        );

        $link->post($this->variables, [
            'headers' => [
                'Foo' => 'bar'
            ],
            'body'  => 'Body',
            'query' => 'key1=key2'
        ]);

        $lastRequest = $httpClient->getLastRequest();

        $this->assertSame('POST', $lastRequest->getMethod());
        $this->assertSame('http://propilex.herokuapp.com/documents?page=1&limit=10&key1=key2', (string) $lastRequest->getUri());
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
            'http://propilex.herokuapp.com/documents',
            $httpClient
        );

        $link = new HalLink(
            $client,
            '/documents{?page,limit}',
            true,
            'application/hal+json',
            'http://example.com/deprecation',
            'name',
            'http://example.com/profile',
            'title',
            'en'
        );

        $link->put($this->variables, [
            'headers' => [
                'Foo' => 'bar'
            ],
            'body'  => 'Body',
            'query' => 'key1=key2'
        ]);

        $lastRequest = $httpClient->getLastRequest();

        $this->assertSame('PUT', $lastRequest->getMethod());
        $this->assertSame('http://propilex.herokuapp.com/documents?page=1&limit=10&key1=key2', (string) $lastRequest->getUri());
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
            'http://propilex.herokuapp.com/documents',
            $httpClient
        );

        $link = new HalLink(
            $client,
            '/documents{?page,limit}',
            true,
            'application/hal+json',
            'http://example.com/deprecation',
            'name',
            'http://example.com/profile',
            'title',
            'en'
        );

        $link->delete($this->variables, [
            'headers' => [
                'Foo' => 'bar'
            ],
            'body'  => 'Body',
            'query' => 'key1=key2'
        ]);

        $lastRequest = $httpClient->getLastRequest();

        $this->assertSame('DELETE', $lastRequest->getMethod());
        $this->assertSame('http://propilex.herokuapp.com/documents?page=1&limit=10&key1=key2', (string) $lastRequest->getUri());
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
            'http://propilex.herokuapp.com/documents',
            $httpClient
        );

        $link = new HalLink(
            $client,
            '/documents{?page,limit}',
            true,
            'application/hal+json',
            'http://example.com/deprecation',
            'name',
            'http://example.com/profile',
            'title',
            'en'
        );

        $link->request('PATCH', $this->variables, [
            'headers' => [
                'Foo' => 'bar'
            ],
            'body'  => 'Body',
            'query' => 'key1=key2'
        ]);

        $lastRequest = $httpClient->getLastRequest();

        $this->assertSame('PATCH', $lastRequest->getMethod());
        $this->assertSame('http://propilex.herokuapp.com/documents?page=1&limit=10&key1=key2', (string) $lastRequest->getUri());
        $this->assertSame('Body', (string) $lastRequest->getBody());
        $this->assertSame(['bar'], $lastRequest->getHeader('Foo'));
    }
}
