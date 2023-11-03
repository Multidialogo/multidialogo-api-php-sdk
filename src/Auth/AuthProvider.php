<?php


namespace multidialogo\client\Auth;


use Exception;
use GuzzleHttp\Client as HttpClient;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\RequestOptions;
use multidialogo\client\Common\DateTimeUtils;
use multidialogo\client\Exception\MultidialogoClientException;

class AuthProvider implements AuthProviderInterface
{
    /**
     * @var HttpClient
     */
    private $httpClient;

    /**
     * @var TokenStorageInterface
     */
    private $tokenStorage;

    /**
     * @var string
     */
    private $username;

    /**
     * @var string
     */
    private $password;

    /**
     * @var AuthToken
     */
    private $authToken;

    /**
     * @var AuthToken
     */
    private $refreshToken;

    /**
     * AuthManager constructor.
     * @param HttpClient $httpClient
     * @param TokenStorageInterface $tokenStorage
     * @param string $username
     * @param string $password
     */
    public function __construct($httpClient, $tokenStorage, $username, $password)
    {
        $this->httpClient = $httpClient;
        $this->tokenStorage = $tokenStorage;
        $this->username = $username;
        $this->password = $password;
    }

    /**
     * @return string
     * @throws Exception
     */
    public function getToken(): string
    {
        if (!$this->authToken) {
            // try get a token from the storage
            $this->tokenStorage->read($this->username, $this->authToken, $this->refreshToken);
        }

        if (!$this->authToken || !$this->authToken->isValid()) {
            // try refresh the token or - if the refresh fails - issue a full authentication request...
            if ($this->refreshToken && $this->refreshToken->isValid()) {
                if (!$this->_doRefreshToken()) {
                    $this->_doAuthenticate();
                }
                return $this->authToken->getToken();
            } else {
                $this->_doAuthenticate();
            }
        }

        return $this->authToken->getToken();
    }

    private function _doRefreshToken(): bool
    {
        try {
            $response = $this->httpClient->post("users/login/refresh",
                [
                    RequestOptions::HEADERS => [
                        'Accept' => 'application/vnd.api+json',
                        'Content-Type' => 'application/vnd.api+json'
                    ],
                    RequestOptions::BODY => json_encode([
                        'data' => [
                            'attributes' => [
                                'grantType' => GrantType::REFRESH_TOKEN,
                                'username' => $this->username,
                                'refreshToken' => $this->refreshToken->getToken()
                            ]
                        ]
                    ])
                ]
            );
        } catch (ClientException $e) {
            $responseBody = $e->getResponse()->getBody()->getContents();
            throw new MultidialogoClientException($e->getResponse(), "Request error. Code: {$e->getResponse()->getStatusCode()}, body: {$responseBody}", $e);
        } catch (Exception $e) {
            throw new MultidialogoClientException(null, "Request error. {$e->getMessage()}", $e);
        }

        if ($response->getStatusCode() === 201) {
            $responseBody = json_decode($response->getBody()->__toString());
            if (!$responseBody) {
                throw new MultidialogoClientException($response, "Failed to deserialize token refresh response.");
            }
            $this->_setAuthTokensFromJsonData($responseBody->data->attributes);
            return true;
        } else {
            return false;
        }
    }

    private function _setAuthTokensFromJsonData($data)
    {
        $this->authToken = new AuthToken(
            $data->token,
            DateTimeUtils::fromUtcString($data->expireAt)
        );

        $this->refreshToken = new AuthToken(
            $data->refreshToken,
            DateTimeUtils::fromUtcString($data->refreshTokenExpireAt)
        );

        $this->tokenStorage->write($this->username, $this->authToken, $this->refreshToken);
    }

    /**
     * @throws Exception
     */
    private function _doAuthenticate()
    {
        try {
            $response = $this->httpClient->post("users/login",
                [
                    RequestOptions::HEADERS => [
                        'Accept' => 'application/vnd.api+json',
                        'Content-Type' => 'application/vnd.api+json'
                    ],
                    RequestOptions::BODY => json_encode([
                        'data' => [
                            'attributes' => [
                                'grantType' => GrantType::PASSWORD,
                                'username' => $this->username,
                                'password' => $this->password
                            ]
                        ]
                    ])
                ]
            );
        } catch (ClientException $e) {
            $responseBody = $e->getResponse()->getBody()->getContents();
            throw new MultidialogoClientException($e->getResponse(), "Request error. Code: {$e->getResponse()->getStatusCode()}, body: {$responseBody}", $e);
        } catch (Exception $e) {
            throw new MultidialogoClientException(null, "Request error. {$e->getMessage()}", $e);
        }

        if ($response->getStatusCode() === 201) {
            $responseBody = json_decode($response->getBody()->__toString());
            if (!$responseBody) {
                throw new MultidialogoClientException($response, "Failed to deserialize authentication response.");
            }
            $this->_setAuthTokensFromJsonData($responseBody->data->attributes);
        } else {
            throw new MultidialogoClientException($response, "Authentication failed. (status code: {$response->getStatusCode()})");
        }
    }

    public function reset()
    {
        $this->authToken = null;
        $this->refreshToken = null;
        $this->tokenStorage->reset($this->username);
    }
}