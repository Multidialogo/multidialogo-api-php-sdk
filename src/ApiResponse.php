<?php


namespace multidialogo\client;


use Psr\Http\Message\ResponseInterface;

class ApiResponse
{
    public int $statusCode;

    /**
     * @var mixed
     */
    public $body;

    public ResponseInterface $httpResponse;

    public bool $ok;

    /**
     * @param ResponseInterface $response
     * @return ApiResponse
     */
    public static function fromJsonResponse(ResponseInterface $response): ApiResponse
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