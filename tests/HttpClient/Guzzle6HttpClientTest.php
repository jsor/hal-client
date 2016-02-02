<?php

namespace Jsor\HalClient\HttpClient;

use GuzzleHttp\ClientInterface as GuzzleClientInterface;
use GuzzleHttp\Exception\RequestException as GuzzleRequestException;
use GuzzleHttp\Psr7\Response;
use Jsor\HalClient\HalClient;
use Jsor\HalClient\TestCase;

class Guzzle6HttpClientTest extends TestCase
{
    public function setUp()
    {
        if (version_compare(GuzzleClientInterface::VERSION, '6.0.0', '<') ||
            version_compare(GuzzleClientInterface::VERSION, '7.0.0', '>=')) {
            $this->markTestIncomplete('GuzzleHttp version other than ~6.0 installed (Installed version ' . GuzzleClientInterface::VERSION . ').');
        }
    }

    /**
     * @test
     */
    public function it_will_call_send()
    {
        $response = new Response(200, ['Content-Type' => 'application/hal+json']);

        $guzzleClient = $this->getMock('GuzzleHttp\ClientInterface');

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
     * @expectedException \Jsor\HalClient\Exception\BadResponseException
     */
    public function it_will_transform_exception()
    {
        $guzzleClient = $this->getMock('GuzzleHttp\ClientInterface');

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

        $client->request('GET', '/');
    }

    /**
     * @test
     * @expectedException \Jsor\HalClient\Exception\BadResponseException
     */
    public function it_will_transform_exception_with_response()
    {
        $guzzleClient = $this->getMock('GuzzleHttp\ClientInterface');

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

        $client->request('GET', '/');
    }
}
