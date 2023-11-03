<?php

namespace multidialogo\client\Exception;

use Exception;
use Psr\Http\Message\ResponseInterface;
use RuntimeException;

class MultidialogoClientException extends RuntimeException
{
    private ?ResponseInterface $response;

    public function __construct(?ResponseInterface $response, string $message, Exception $previous = null)
    {
        parent::__construct("[MultidialogoClient] {$message}", 0, $previous);

        $this->response = $response;
    }

    /**
     * @return ResponseInterface|null
     */
    public function getResponse(): ?ResponseInterface
    {
        return $this->response;
    }
}