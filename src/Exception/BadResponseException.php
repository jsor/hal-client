<?php

namespace Jsor\HalClient\Exception;

use Jsor\HalClient\HalResource;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use RuntimeException;

class BadResponseException extends RuntimeException implements ExceptionInterface
{
    private $request;
    private $response;
    private $resource;

    public function __construct(
        $message,
        RequestInterface $request,
        ResponseInterface $response,
        HalResource $resource,
        $previous = null
    ) {
        $code = $response ? $response->getStatusCode() : 0;

        parent::__construct($message, $code, $previous);

        $this->request  = $request;
        $this->response = $response;
        $this->resource = $resource;
    }

    public static function create(
        RequestInterface $request,
        ResponseInterface $response,
        HalResource $resource,
        $previous = null,
        $message = null
    ) {
        if (!$message) {
            $code = $response->getStatusCode();

            if ($code >= 400 && $code < 500) {
                $message = 'Client error';
            } elseif ($code >= 500 && $code < 600) {
                $message = 'Server error';
            } else {
                $message = 'Unsuccessful response';
            }
        }

        $message = sprintf(
            '%s [url] %s [http method] %s [status code] %s [reason phrase] %s.',
            $message,
            $request->getRequestTarget(),
            $request->getMethod(),
            $response->getStatusCode(),
            $response->getReasonPhrase()
        );

        return new self($message, $request, $response, $resource, $previous);
    }

    public function getRequest()
    {
        return $this->request;
    }

    public function getResponse()
    {
        return $this->response;
    }

    public function getResource()
    {
        return $this->resource;
    }

    public function isClientError()
    {
        return $this->getCode() >= 400 && $this->getCode() < 500;
    }

    public function isServerError()
    {
        return $this->getCode() >= 500 && $this->getCode() < 600;
    }
}
