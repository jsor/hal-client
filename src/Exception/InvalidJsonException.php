<?php

namespace Jsor\HalClient\Exception;

use Psr\Http\Message\ResponseInterface;

class InvalidJsonException extends \Exception implements Exception
{
    private $response;

    public function __construct(
        ResponseInterface $response,
        $message = null,
        \Exception $previous = null
    ) {
        parent::__construct($message, 0, $previous);

        $this->response = $response;
    }

    public function getResponse()
    {
        return $this->response;
    }
}
