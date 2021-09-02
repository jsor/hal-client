<?php

namespace Jsor\HalClient\HttpClient;

use GuzzleHttp\ClientInterface as GuzzleClientInterface;
use GuzzleHttp\Exception\BadResponseException as GuzzleBadResponseException;
use GuzzleHttp\Exception\RequestException as GuzzleRequestException;
use GuzzleHttp\Psr7\Response;
use Jsor\HalClient\Exception\BadResponseException;
use Jsor\HalClient\Exception\HttpClientException;
use Jsor\HalClient\HalClient;
use Jsor\HalClient\TestCase;

class Guzzle6HttpClientTest extends TestCase
{
    public function setUp(): void
    {
        $guzzleVersion = HalClient::getInstalledGuzzleVersion();
        if ($guzzleVersion === 7 ||
            version_compare($guzzleVersion, '6.0.0', '<')) {
            $this->markTestIncomplete('GuzzleHttp version other than ~6.0 installed (Installed version ' . $guzzleVersion . ').');
        }
    }

    /**
     * @test
     */
    public function it_will_call_send()
    {
        $response = new Response(200, ['Content-Type' => 'application/hal+json']);

        $guzzleClient = $this->getMockBuilder('GuzzleHttp\ClientInterface')->getMock();

        $guzzleClient
            ->expects($this->once())
            ->method('send')
            ->will($this->returnValue($response));

        $client = new HalClient(
            'http://propilex.herokuapp.com',
            new Guzzle6HttpClient($guzzleClient)
        );

        $client->request('GET', '/');
    }

    /**
     * @test
     */
    public function it_will_transform_exception()
    {
        $guzzleClient = $this->getMockBuilder('GuzzleHttp\ClientInterface')->getMock();

        $guzzleClient
            ->expects($this->once())
            ->method('send')
            ->will($this->returnCallback(function ($request) {
                throw GuzzleRequestException::create($request);
            }));

        $client = new HalClient(
            'http://propilex.herokuapp.com',
            new Guzzle6HttpClient($guzzleClient)
        );

        $this->expectException(HttpClientException::class);

        $client->request('GET', '/');
    }

    /**
     * @test
     */
    public function it_will_transform_exception_with_500_response()
    {
        $guzzleClient = $this->getMockBuilder('GuzzleHttp\ClientInterface')->getMock();

        $guzzleClient
            ->expects($this->once())
            ->method('send')
            ->will($this->returnCallback(function ($request) {
                throw GuzzleRequestException::create(
                    $request,
                    new Response(500)
                );
            }));

        $client = new HalClient(
            'http://propilex.herokuapp.com',
            new Guzzle6HttpClient($guzzleClient)
        );

        $this->expectException(BadResponseException::class);

        $client->request('GET', '/');
    }

    /**
     * @test
     */
    public function it_will_transform_exception_with_404_response()
    {
        $guzzleClient = $this->getMockBuilder('GuzzleHttp\ClientInterface')->getMock();

        $guzzleClient
            ->expects($this->once())
            ->method('send')
            ->will($this->returnCallback(function ($request) {
                throw GuzzleRequestException::create(
                    $request,
                    new Response(404)
                );
            }));

        $client = new HalClient(
            'http://propilex.herokuapp.com',
            new Guzzle6HttpClient($guzzleClient)
        );

        $this->expectException(BadResponseException::class);

        $client->request('GET', '/');
    }

    /**
     * @test
     */
    public function it_will_transform_bad_response_exception_without_response()
    {
        $guzzleClient = $this->getMockBuilder('GuzzleHttp\ClientInterface')->getMock();

        $guzzleClient
            ->expects($this->once())
            ->method('send')
            ->will($this->returnCallback(function ($request) {
                throw new GuzzleBadResponseException(
                    'Error',
                    $request
                );
            }));

        $client = new HalClient(
            'http://propilex.herokuapp.com',
            new Guzzle6HttpClient($guzzleClient)
        );

        $this->expectException(BadResponseException::class);

        $client->request('GET', '/');
    }
}
