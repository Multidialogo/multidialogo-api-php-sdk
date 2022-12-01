<?php


namespace multidialogo\client;


use GuzzleHttp\Client as HttpClient;
use InvalidArgumentException;
use multidialogo\client\Auth\AuthProvider;
use multidialogo\client\Auth\FileTokenStorage;
use multidialogo\client\Auth\VolatileTokenStorage;
use multidialogo\client\Auth\TokenStorageInterface;

class MultidialogoClientBuilder
{
    private string $username;

    private string $password;

    private string $hostUrl;

    private ?TokenStorageInterface $tokenStorage = null;

    private string $apiVersion = '0.0.1';

    /**
     * @var callable $httpClientFactory
     */
    private $httpClientFactory;

    private array $headers = [];

    private ?string $language = null;

    public function withPasswordCredentials(string $username, string $password): self
    {
        $this->username = $username;
        $this->password = $password;

        return $this;
    }

    public function withHostUrl(string $hostUrl): self
    {
        $this->hostUrl = $hostUrl;

        return $this;
    }

    public function withApiVersion(string $apiVersion): self
    {
        $this->apiVersion = $apiVersion;

        return $this;
    }

    public function withLanguage(string $language): self
    {
        $this->language = $language;

        return $this;
    }

    public function withTokenStorage(TokenStorageInterface $tokenStorage): self
    {
        $this->tokenStorage = $tokenStorage;

        return $this;
    }

    public function withFileTokenStorage($basePath): self
    {
        $this->tokenStorage = new FileTokenStorage($basePath);

        return $this;
    }

    /**
     * @param callable $httpClientFactory
     * @return MultidialogoClientBuilder
     */
    public function withHttpClientFactory($httpClientFactory): self
    {
        $this->httpClientFactory = $httpClientFactory;

        return $this;
    }

    public function withAdditionalHeaders(array $headers): self
    {
        $this->headers = $headers;

        return $this;
    }

    public function build(): MultidialogoClient
    {
        if (!$this->hostUrl) {
            throw new InvalidArgumentException("Missing hostUrl property");
        }

        if (!$this->apiVersion) {
            throw new InvalidArgumentException("Missing apiVersion property");
        }

        $httpClientFactory = $this->httpClientFactory;
        if (!$httpClientFactory) {
            $httpClientFactory = function ($baseUri) {
                return new HttpClient(['base_uri' => $baseUri]);
            };
        }

        $httpClient = $httpClientFactory("{$this->hostUrl}/api/v{$this->apiVersion}/");

        $tokenStorage = $this->tokenStorage;
        if (!$tokenStorage) {
            $tokenStorage = new VolatileTokenStorage();
        }

        $authProvider = new AuthProvider(
            $httpClient,
            $tokenStorage,
            $this->username,
            $this->password
        );

        $client = new MultidialogoClient($httpClient, $authProvider, $this->headers);

        $client->setLanguage($this->language);

        return $client;
    }
}