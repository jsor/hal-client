<?php

namespace Jsor\HalClient\Exception;

use Psr\Http\Message\RequestInterface;

class HttpClientException extends \RuntimeException implements ExceptionInterface
{
    private $request;

    public function __construct(
        $message,
        RequestInterface $request,
        \Exception $previous
    ) {
        parent::__construct($message, 0, $previous);

        $this->request  = $request;
    }

    public function getRequest()
    {
        return $this->request;
    }
}
