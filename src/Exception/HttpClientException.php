<?php

namespace Jsor\HalClient\Exception;

use Psr\Http\Message\RequestInterface;

class HttpClientException extends \RuntimeException implements ExceptionInterface
{
    private $request;

    public function __construct(
        $message,
        RequestInterface $request,
        $previous
    ) {
        parent::__construct($message, 0, $previous);

        $this->request  = $request;
    }

    public function getRequest()
    {
        return $this->request;
    }

    public static function create(
        RequestInterface $request,
        $previous = null,
        $message = null
    ) {
        if (!$message) {
            $message = 'Exception thrown by the http client while sending request.';

            if ($previous) {
                $message = sprintf(
                    'Exception thrown by the http client while sending request: %s.',
                    $previous->getMessage()
                );
            }
        }

        return new self($message, $request, $previous);
    }
}
