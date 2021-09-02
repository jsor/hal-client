<?php

namespace Jsor\HalClient;

use GuzzleHttp\Psr7\Response;
use Jsor\HalClient\HttpClient\HttpClientInterface;
use Psr\Http\Message\RequestInterface;

class FixtureHttpClient implements HttpClientInterface
{
    public function send(RequestInterface $request)
    {
        switch ($request->getUri()->getPath()) {
            case '/documents/1':
                if ('GET' === $request->getMethod()) {
                    return new Response(
                        200,
                        [
                            'Content-Type' => 'application/hal+json',
                        ],
                        file_get_contents(__DIR__ . '/fixtures/documents_1.json')
                    );
                }

                break;
            case '/documents/2':
                if ('GET' === $request->getMethod()) {
                    return new Response(
                        200,
                        [
                            'Content-Type' => 'application/hal+json',
                        ],
                        file_get_contents(__DIR__ . '/fixtures/documents_2.json')
                    );
                }

                break;
            case '/documents/3':
                if ('GET' === $request->getMethod()) {
                    return new Response(
                        200,
                        [
                            'Content-Type' => 'application/hal+json',
                        ],
                        file_get_contents(__DIR__ . '/fixtures/documents_3.json')
                    );
                }

                break;
            case '/documents/4':
                if ('DELETE' === $request->getMethod()) {
                    return new Response(
                        204,
                        [
                            'Content-Type' => 'text/html',
                        ]
                    );
                }

                if ('PUT' === $request->getMethod()) {
                    if ('{"title":"Test 4 changed","body":"Lorem ipsum"}' === $request->getBody()->getContents()) {
                        return new Response(
                            200,
                            [
                                'Content-Type' => 'application/hal+json',
                            ],
                            file_get_contents(__DIR__ . '/fixtures/documents_4_changed.json')
                        );
                    }
                }

                if ('GET' === $request->getMethod()) {
                    return new Response(
                        200,
                        [
                            'Content-Type' => 'application/hal+json',
                        ],
                        file_get_contents(__DIR__ . '/fixtures/documents_4.json')
                    );
                }

                break;
            case '/documents':
                if ('POST' === $request->getMethod()) {
                    if ('{"title":"Test 4","body":"Lorem ipsum"}' === $request->getBody()->getContents()) {
                        return new Response(
                            201,
                            [
                                'Location' => '/documents/4',
                            ]
                        );
                    }
                }

                if ('GET' === $request->getMethod()) {
                    return new Response(
                        200,
                        [
                            'Content-Type' => 'application/hal+json',
                        ],
                        file_get_contents(__DIR__ . '/fixtures/documents.json')
                    );
                }

                break;
            case '':
                if ('GET' === $request->getMethod()) {
                    return new Response(
                        200,
                        [
                            'Content-Type' => 'application/hal+json',
                        ],
                        file_get_contents(__DIR__ . '/fixtures/root.json')
                    );
                }

                break;
            default:
                return new Response(404);
        }

        return new Response(
            405,
            [
                'Content-Type' => 'text/plain',
            ],
            sprintf(
                'No route found for "%s %s": Method Not Allowed',
                $request->getUri()->getPath(),
                $request->getMethod()
            )
        );
    }
}
