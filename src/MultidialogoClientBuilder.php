<?php


namespace multidialogo\client;


use GuzzleHttp\Client as HttpClient;
use multidialogo\client\Auth\AuthProvider;
use multidialogo\client\Auth\InMemoryTokenStorage;
use multidialogo\client\Auth\TokenStorageInterface;

class MultidialogoClientBuilder
{
    /**
     * @var string
     */
    private $username;

    /**
     * @var string
     */
    private $password;

    /**
     * @var string
     */
    private $hostUrl;

    /**
     * @var TokenStorageInterface
     */
    private $tokenStorage;

    /**
     * @var string
     */
    private $apiVersion = '0.0.1';

    /**
     * @var callable $httpClientFactory
     */
    private $httpClientFactory;

    /**
     * @var array $headers
     */
    private $headers;

    /**
     * @param string $username
     * @param string $password
     * @return MultidialogoClientBuilder
     */
    public function withPasswordCredentials($username, $password)
    {
        $this->username = $username;
        $this->password = $password;
        return $this;
    }

    /**
     * @param string $hostUrl
     * @return MultidialogoClientBuilder
     */
    public function withHostUrl($hostUrl)
    {
        $this->hostUrl = $hostUrl;
        return $this;
    }

    /**
     * @param string $apiVersion
     * @return MultidialogoClientBuilder
     */
    public function withApiVersion($apiVersion)
    {
        $this->apiVersion = $apiVersion;
        return $this;
    }

    /**
     * @param TokenStorageInterface $tokenStorage
     * @return MultidialogoClientBuilder
     */
    public function withTokenStorage($tokenStorage)
    {
        $this->tokenStorage = $tokenStorage;
        return $this;
    }

    /**
     * @param callable $httpClientFactory
     * @return MultidialogoClientBuilder
     */
    public function withHttpClientFactory($httpClientFactory)
    {
        $this->httpClientFactory = $httpClientFactory;
        return $this;
    }

    /**
     * @param array $headers
     * @return MultidialogoClientBuilder
     */
    public function withAdditionalHeaders($headers)
    {
        $this->headers = $headers;
        return $this;
    }

    /**
     * @return MultidialogoClient
     */
    public function build()
    {
        $httpClientFactory = $this->httpClientFactory;
        if (!$httpClientFactory) {
            $httpClientFactory = function ($baseUri) {
                return new HttpClient(['base_uri' => $baseUri]);
            };
        }

        $httpClient = $httpClientFactory("{$this->hostUrl}/api/v{$this->apiVersion}/");

        $tokenStorage = $this->tokenStorage;
        if (!$tokenStorage) {
            $tokenStorage = new InMemoryTokenStorage();
        }

        $authProvider = new AuthProvider(
            $httpClient,
            $tokenStorage,
            $this->username,
            $this->password);

        return new MultidialogoClient($httpClient, $authProvider, $this->headers ?: []);
    }
}