<?php


namespace multidialogo\client;


use Psr\Http\Message\ResponseInterface;

class ApiResponse
{
    /**
     * @var int
     */
    public $statusCode;

    /**
     * @var mixed
     */
    public $body;

    /**
     * @var ResponseInterface
     */
    public $httpResponse;

    /**
     * @var bool
     */
    public $ok;

    /**
     * @param ResponseInterface $response
     * @return ApiResponse
     */
    public static function fromJsonResponse(ResponseInterface $response)
    {
        $result = new self();
        $bodyString = $response->getBody()->__toString();
        $result->body = $bodyString ? json_decode($bodyString) : null;
        $result->statusCode = $response->getStatusCode();
        $result->ok = $result->statusCode >= 200 && $result->statusCode <= 299;
        $result->httpResponse = $response;

        return $result;
    }
}