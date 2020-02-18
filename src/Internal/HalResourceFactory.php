<?php

namespace Jsor\HalClient\Internal;

use Jsor\HalClient\Exception;
use Jsor\HalClient\HalClientInterface;
use Jsor\HalClient\HalResource;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

final class HalResourceFactory
{
    private $validContentTypes;

    public function __construct(array $validContentTypes)
    {
        $this->validContentTypes = $validContentTypes;
    }

    public function createResource(
        HalClientInterface $client,
        RequestInterface $request,
        ResponseInterface $response,
        $ignoreInvalidContentType = false
    ) {
        if (204 === $response->getStatusCode()) {
            // No-Content response
            return new HalResource($client);
        }

        $body = trim($this->fetchBody($client, $request, $response));

        if (
            '' === $body &&
            201 === $response->getStatusCode() &&
            $response->hasHeader('Location')
        ) {
            // Created response with Location header
            return $client->request('GET', $response->getHeader('Location')[0]);
        }

        if (!$this->isValidContentType($response)) {
            return $this->handleInvalidContentType(
                $client,
                $request,
                $response,
                $ignoreInvalidContentType
            );
        }

        return $this->handleValidContentType(
            $client,
            $request,
            $response,
            $body
        );
    }

    private function isValidContentType(ResponseInterface $response)
    {
        if (!$response->hasHeader('Content-Type')) {
            return false;
        }

        $contentTypeHeader = $response->getHeaderLine('Content-Type');

        if (preg_match("/^([^;]+)(;[\s]?(charset|boundary)=(.+))?$/", $contentTypeHeader, $match)) {
            $contentTypeHeader = $match[1];
        }

        return in_array($contentTypeHeader, $this->validContentTypes, true);
    }

    private function handleInvalidContentType(
        HalClientInterface $client,
        RequestInterface $request,
        ResponseInterface $response,
        $ignoreInvalidContentType
    ) {
        if ($ignoreInvalidContentType) {
            return new HalResource($client);
        }

        $types = $response->getHeader('Content-Type') ?: ['none'];

        throw new Exception\BadResponseException(
            sprintf(
                'Request did not return a valid content type. Returned content type: %s.',
                implode(', ', $types)
            ),
            $request,
            $response,
            new HalResource($client)
        );
    }

    private function handleValidContentType(
        HalClientInterface $client,
        RequestInterface $request,
        ResponseInterface $response,
        $body
    ) {
        if ('' === $body) {
            return new HalResource($client);
        }

        $data = $this->decodeBody($client, $request, $response, $body);

        return HalResource::fromArray($client, (array) $data);
    }

    private function fetchBody(
        HalClientInterface $client,
        RequestInterface $request,
        ResponseInterface $response
    ) {
        try {
            return $response->getBody()->getContents();
        } catch (\Throwable $e) {
            throw new Exception\BadResponseException(
                sprintf(
                    'Error getting response body: %s.',
                    $e->getMessage()
                ),
                $request,
                $response,
                new HalResource($client),
                $e
            );
        }
    }

    private function decodeBody(
        HalClientInterface $client,
        RequestInterface $request,
        ResponseInterface $response,
        $body
    ) {
        $data = json_decode($body, true);

        if (JSON_ERROR_NONE !== json_last_error()) {
            throw new Exception\BadResponseException(
                sprintf(
                    'JSON parse error: %s.',
                    self::getLastJsonError()
                ),
                $request,
                $response,
                new HalResource($client)
            );
        }

        return $data;
    }

    private static function getLastJsonError()
    {
        if (function_exists('json_last_error_msg')) {
            return json_last_error_msg();
        }

        static $errors = [
            JSON_ERROR_NONE           => null,
            JSON_ERROR_DEPTH          => 'Maximum stack depth exceeded',
            JSON_ERROR_STATE_MISMATCH => 'Underflow or the modes mismatch',
            JSON_ERROR_CTRL_CHAR      => 'Unexpected control character found',
            JSON_ERROR_SYNTAX         => 'Syntax error, malformed JSON',
            JSON_ERROR_UTF8           => 'Malformed UTF-8 characters, possibly incorrectly encoded',
        ];

        $error = json_last_error();

        return array_key_exists($error, $errors)
            ? $errors[$error]
            : sprintf('Unknown error (%s)', $error);
    }
}
