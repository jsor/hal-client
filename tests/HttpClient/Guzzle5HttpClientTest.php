<?php

namespace Jsor\HalClient\HttpClient;

use GuzzleHttp\Exception\BadResponseException as GuzzleBadResponseException;
use GuzzleHttp\Exception\RequestException as GuzzleRequestException;
use GuzzleHttp\Message\Request as GuzzleRequest;
use GuzzleHttp\Message\Response as GuzzleResponse;
use Jsor\HalClient\Exception\BadResponseException;
use Jsor\HalClient\Exception\HttpClientException;
use Jsor\HalClient\HalClient;
use Jsor\HalClient\TestCase;

class Guzzle5HttpClientTest extends TestCase
{
    protected function setUp(): void
    {
        $guzzleVersion = HalClient::getInstalledGuzzleVersion();
        if (7 === $guzzleVersion ||
            version_compare($guzzleVersion, '5.0.0', '<') ||
            version_compare($guzzleVersion, '6.0.0', '>=')) {
            $this->markTestIncomplete('GuzzleHttp version other than ~5.0 installed (Installed version ' . $guzzleVersion . ').');
        }
    }

    /**
     * @test
     */
    public function it_will_call_send()
    {
        $guzzleRequest  = new GuzzleRequest('GET', '/', []);
        $guzzleResponse = new GuzzleResponse(200, ['Content-Type' => 'application/hal+json']);

        $guzzleClient = $this->getMockBuilder('GuzzleHttp\ClientInterface')->getMock();

        $guzzleClient
            ->expects($this->once())
            ->method('createRequest')
            ->willReturn($guzzleRequest);

        $guzzleClient
            ->expects($this->once())
            ->method('send')
            ->willReturn($guzzleResponse);

        $client = new HalClient(
            'http://propilex.herokuapp.com',
            new Guzzle5HttpClient($guzzleClient)
        );

        $client->request('GET', '/');
    }

    /**
     * @test
     */
    public function it_will_transform_exception()
    {
        $guzzleRequest = new GuzzleRequest('GET', '/', []);

        $guzzleClient = $this->getMockBuilder('GuzzleHttp\ClientInterface')->getMock();

        $guzzleClient
            ->expects($this->once())
            ->method('createRequest')
            ->willReturn($guzzleRequest);

        $guzzleClient
            ->expects($this->once())
            ->method('send')
            ->willReturnCallback(function ($request) {
                throw GuzzleRequestException::create($request);
            });

        $client = new HalClient(
            'http://propilex.herokuapp.com',
            new Guzzle5HttpClient($guzzleClient)
        );

        $this->expectException(HttpClientException::class);

        $client->request('GET', '/');
    }

    /**
     * @test
     */
    public function it_will_transform_exception_with_500_response()
    {
        $guzzleRequest = new GuzzleRequest('GET', '/', []);

        $guzzleClient = $this->getMockBuilder('GuzzleHttp\ClientInterface')->getMock();

        $guzzleClient
            ->expects($this->once())
            ->method('createRequest')
            ->willReturn($guzzleRequest);

        $guzzleClient
            ->expects($this->once())
            ->method('send')
            ->willReturnCallback(function ($request) {
                throw GuzzleRequestException::create(
                    $request,
                    new GuzzleResponse(500)
                );
            });

        $client = new HalClient(
            'http://propilex.herokuapp.com',
            new Guzzle5HttpClient($guzzleClient)
        );

        $this->expectException(BadResponseException::class);

        $client->request('GET', '/');
    }

    /**
     * @test
     */
    public function it_will_transform_exception_with_404_response()
    {
        $guzzleRequest = new GuzzleRequest('GET', '/', []);

        $guzzleClient = $this->getMockBuilder('GuzzleHttp\ClientInterface')->getMock();

        $guzzleClient
            ->expects($this->once())
            ->method('createRequest')
            ->willReturn($guzzleRequest);

        $guzzleClient
            ->expects($this->once())
            ->method('send')
            ->willReturnCallback(function ($request) {
                throw GuzzleRequestException::create(
                    $request,
                    new GuzzleResponse(404)
                );
            });

        $client = new HalClient(
            'http://propilex.herokuapp.com',
            new Guzzle5HttpClient($guzzleClient)
        );

        $this->expectException(BadResponseException::class);

        $client->request('GET', '/');
    }

    /**
     * @test
     */
    public function it_will_transform_bad_response_exception_without_response()
    {
        $guzzleRequest = new GuzzleRequest('GET', '/', []);

        $guzzleClient = $this->getMockBuilder('GuzzleHttp\ClientInterface')->getMock();

        $guzzleClient
            ->expects($this->once())
            ->method('createRequest')
            ->willReturn($guzzleRequest);

        $guzzleClient
            ->expects($this->once())
            ->method('send')
            ->willReturnCallback(function ($request) {
                throw new GuzzleBadResponseException(
                    'Error',
                    $request
                );
            });

        $client = new HalClient(
            'http://propilex.herokuapp.com',
            new Guzzle5HttpClient($guzzleClient)
        );

        $this->expectException(BadResponseException::class);

        $client->request('GET', '/');
    }
}
