<?php

namespace multidialogo\client;

use Exception;
use GuzzleHttp\Client as HttpClient;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\RequestOptions;
use multidialogo\client\Auth\AuthProvider;
use multidialogo\client\Auth\AuthProviderInterface;
use multidialogo\client\Exception\MultidialogoClientException;
use Psr\Http\Message\ResponseInterface;


class MultidialogoClient
{
    private static $instance = null;

    private $httpClient;

    private $authProvider;

    private $headers;

    private $language = null;

    private $options;

    /**
     * RestClient constructor.
     * @param HttpClient $httpClient a pre-configured HttpClient, the base_uri should point to the base api uri,
     *                               comprising the api version. ex: http://rest.multidialogo.it/api/v0.0.1/
     * @param AuthProviderInterface $authProvider
     * @param array $headers additional headers
     */
    public function __construct($httpClient, $authProvider, $headers)
    {
        $this->httpClient = $httpClient;
        $this->authProvider = $authProvider;
        $this->headers = $headers;
    }

    public static function builder(): MultidialogoClientBuilder
    {
        return new MultidialogoClientBuilder();
    }

    /**
     * @param string|null $language
     */
    public function setLanguage($language)
    {
        $this->language = $language;
    }

    public static function getInstance()
    {
        return static::$instance;
    }

    public static function setInstance(MultidialogoClient $multidialogoClientInstance)
    {
        static::$instance = $multidialogoClientInstance;
    }

    /**
     * @param string $urlPath
     * @param mixed $body the body to send into the post, it will be encoded to a json string
     * @param array|null $queryParams
     * @param ResponseInterface|null $response
     * @return ApiResponse
     * @throws Exception
     */
    public function postJson($urlPath, $body, $queryParams = null, &$response = null)
    {
        $response = $this->_doRequest('POST', $urlPath, $queryParams, json_encode($body));

        return ApiResponse::fromJsonResponse($response);
    }

    /**
     * @param string $method
     * @param string $url
     * @param array|null $queryParams
     * @param string|null $body
     * @return ResponseInterface
     * @throws Exception
     * @noinspection PhpDocMissingThrowsInspection
     */
    private function _doRequest($method, $url, $queryParams = null, $body = null)
    {
        if (!$this->language) {
            throw new MultidialogoClientException('Language property not setted!');
        }

        $this->options = [
            RequestOptions::HEADERS => array_merge(
                [
                    'Authorization' => "Bearer {$this->authProvider->getToken()}",
                    'Content-Type' => 'application/vnd.api+json',
                    'Accept' => 'application/vnd.api+json',
                    'Accept-Language' => $this->language,
                ],
                $this->headers
            ),
        ];

        if ($queryParams) {
            $this->options[RequestOptions::QUERY] = $queryParams;
        }

        if ($body) {
            $this->options[RequestOptions::BODY] = $body;
        }

        try {
            return $this->httpClient->request($method, $url, $this->options);
        } catch (ClientException $e) {
            $responseBody = $e->getResponse()->getBody()->getContents();

            throw new MultidialogoClientException("Request error. Code: {$e->getResponse()->getStatusCode()}, body: {$responseBody}", $e);
        } catch (Exception $e) {
            throw new MultidialogoClientException("Request error. {$e->getMessage()}", $e);
        }
    }

    /**
     * @param string $urlPath
     * @param mixed $body
     * @param mixed|null $queryParams
     * @param ResponseInterface|null $response
     * @return ApiResponse
     * @throws Exception
     */
    public function patchJson($urlPath, $body, $queryParams = null, &$response = null)
    {
        $response = $this->_doRequest('PATCH', $urlPath, $queryParams, json_encode($body));

        return ApiResponse::fromJsonResponse($response);
    }

    /**
     * @param string $urlPath
     * @param array|null $queryParams
     * @param ResponseInterface|null $response
     * @return ApiResponse
     * @throws Exception
     */
    public function getJson($urlPath, $queryParams = null, &$response = null)
    {
        $response = $this->_doRequest('GET', $urlPath, $queryParams);

        return ApiResponse::fromJsonResponse($response);
    }

    /**
     * @param string $urlPath
     * @param array|null $queryParams
     * @param ResponseInterface|null $response
     * @return ApiResponse
     * @throws Exception
     */
    public function deleteJson($urlPath, $queryParams = null, &$response = null)
    {
        $response = $this->_doRequest('DELETE', $urlPath, $queryParams);

        return ApiResponse::fromJsonResponse($response);
    }

    public function getBaseUri()
    {
        return $this->httpClient->getConfig('base_uri')->__toString();
    }

    public function getAuthProvider()
    {
        return $this->authProvider;
    }

    /**
     * @return mixed
     */
    public function getLastRequestHeaders()
    {
        return $this->options[RequestOptions::HEADERS];
    }
}