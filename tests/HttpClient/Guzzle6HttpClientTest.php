<?php

namespace Jsor\HalClient\HttpClient;

use GuzzleHttp\ClientInterface as GuzzleClientInterface;
use GuzzleHttp\Exception\RequestException as GuzzleRequestException;
use GuzzleHttp\Psr7\Response;
use Jsor\HalClient\HalClient;
use Jsor\HalClient\TestCase;

class Guzzle6HttpClientTest extends TestCase
{
    /**
     * @test
     */
    public function it_will_call_send()
    {
        $response = new Response(200, ['Content-Type' => 'application/hal+json']);

        $guzzleClient = $this->getMock(GuzzleClientInterface::class);

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
        $guzzleClient = $this->getMock(GuzzleClientInterface::class);

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
}
