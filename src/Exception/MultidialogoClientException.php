<?php

namespace multidialogo\client\Exception;

use Exception;
use RuntimeException;

class MultidialogoClientException extends RuntimeException
{
    /**
     * MultidialogoClientException constructor.
     * @param string $message
     * @param Exception|null $previous
     */
    public function __construct(string $message, Exception $previous = null)
    {
        parent::__construct("[MultidialogoClient] {$message}", 0, $previous);
    }
}