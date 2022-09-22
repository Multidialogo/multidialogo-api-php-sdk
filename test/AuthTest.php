<?php


namespace multidialogo\client\test;


use GuzzleHttp\Client as HttpClient;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\Psr7\Response;
use multidialogo\client\Auth\AuthProvider;
use multidialogo\client\Auth\AuthToken;
use multidialogo\client\Auth\InMemoryTokenStorage;
use multidialogo\client\test\TestUtils\JsonResources;
use PHPUnit\Framework\TestCase;

class AuthTest extends TestCase
{
    public function testItShouldStoreTheCredentials()
    {
        // with
        $storage = new InMemoryTokenStorage();
        $handler = new MockHandler([
            new Response(201, [], JsonResources::AuthOkResponse)
        ]);
        $httpClient =   new HttpClient([
                    'base_uri' => 'http://any.url/any/path',
                    'handler' => $handler
                ]);

        $provider = new AuthProvider($httpClient, $storage, 'admin', 'admin');

        // do
        $tokenString = $provider->getToken();

        /** @var AuthToken $token */
        $token = null;
        $refreshToken = null;
        $storage->read($token, $refreshToken);

        // assert
        $this->assertEquals('25ad1beadfeb089cb5bd0ea20da8a610.0004178967cf186762382528d7e994ba081z8111551061', $tokenString);
        $this->assertEquals('25ad1beadfeb089cb5bd0ea20da8a610.0004178967cf186762382528d7e994ba081z8111551061', $token->getToken());
    }
}