<?php

namespace Jsor\HalClient\HttpClient;

use GuzzleHttp\ClientInterface as GuzzleClientInterface;
use GuzzleHttp\Exception\RequestException as GuzzleRequestException;
use GuzzleHttp\Psr7\Response;
use Jsor\HalClient\Client;
use Jsor\HalClient\TestCase;

class Guzzle6HttpClientTest extends TestCase
{
    /**
     * @test
     */
    public function it_will_call_send()
    {
        $guzzleClient = $this->getMock(GuzzleClientInterface::class);

        $guzzleClient
            ->expects($this->once())
            ->method('send')
            ->will($this->returnValue(new Response(200)));

        $client = new Client(
            'http://propilex.herokuapp.com',
            new Guzzle6HttpClient($guzzleClient)
        );

        $client->request('GET', '/');
    }

    /**
     * @test
     * @expectedException \Jsor\HalClient\Exception\RequestException
     */
    public function it_will_transform_exception()
    {
        $guzzleClient = $this->getMock(GuzzleClientInterface::class);

        $guzzleClient
            ->expects($this->once())
            ->method('send')
            ->will($this->returnCallback(function($request) {
                throw GuzzleRequestException::create($request);
            }));

        $client = new Client(
            'http://propilex.herokuapp.com',
            new Guzzle6HttpClient($guzzleClient)
        );

        $client->request('GET', '/');
    }
}
