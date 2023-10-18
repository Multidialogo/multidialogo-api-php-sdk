<?php

namespace multidialogo\client\test;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Middleware;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use multidialogo\client\Auth\AuthProvider;
use multidialogo\client\Auth\FileTokenStorage;
use multidialogo\client\Common\DateTimeUtils;
use multidialogo\client\MultidialogoClient;
use multidialogo\client\test\TestUtils\AuthPayloadUtils;
use PHPUnit\Framework\TestCase;

class MultidialogoClientTest extends TestCase
{
    public function testShouldAuthenticateWithPassedToken()
    {
        $token = 'loremipsum';

        $client = MultidialogoClient::builder()
            ->withHostUrl('https://rest-stage.multidialogo.it')
            ->withBearerToken($token)
            ->withLanguage('it')
            ->build();

        $client->getJson('geo/countries');

        self::assertEquals('Bearer ' . $token, $client->getLastRequestHeaders()['Authorization']);
    }

    private const USER_TEST_OK_RESPONSE = <<<json
        {
            "status": "CREATED",
            "data": {
                "id": "25ad1beadfeb089cb5bd0ea20da8a610.0004178967cf186762382528d7e994ba081z8111551061",
                "type": "auth-tokens",
                "attributes": {
                    "token": "USER_TEST_OK_RESPONSE_TOKEN",
                    "category": "Bearer",
                    "createdAt": "2022-12-01T11:18:38Z",
                    "expireAt": "2022-12-01T14:18:38Z",
                    "refreshToken": "USER_TEST_OK_RESPONSE_REFRESH_TOKEN",
                    "refreshTokenExpireAt": "2023-12-16T11:18:38Z"
                }
            }
        }
json;

    public function testWillRecoverFromAccessDenied()
    {
        $container = [];
        $history = Middleware::history($container);

        $userTestOkResponseObj = AuthPayloadUtils::setExpireDatesAtDate(DateTimeUtils::utcNow(), self::USER_TEST_OK_RESPONSE);

        $mock = new MockHandler([
            new Response(201, [], json_encode($userTestOkResponseObj)),
            new Response(201, [], json_encode($userTestOkResponseObj)),
            new ClientException('AccessDenied', new Request('GET', 'geo/countries'), new Response(401, [], "[]")),
            new Response(201, [], json_encode($userTestOkResponseObj)),
            new Response(201, [], json_encode($userTestOkResponseObj)),
        ]);

        $handlerStack = new HandlerStack($mock);

        $handlerStack->push($history);

        $httpClient = new Client([
            'base_uri' => 'http://backend',
            'handler' => $handlerStack
        ]);

        $tokenStorage = new FileTokenStorage(__DIR__ . '/TestUtils/FileTokenStorage');

        $username = 'test_reset';
        $password = 'pass';

        $authProvider = new AuthProvider(
            $httpClient,
            $tokenStorage,
            $username,
            $password
        );

        $client = new MultidialogoClient($httpClient, $authProvider, []);

        $client->setLanguage('it');

        // 1. first call will go through login to get first tokens pair
        $client->getJson('geo/countries');

        // 2. second call will NOT call login (since it has a apparently valid tokens pair)
        // but get 401
        $client->getJson('geo/countries');

        // 3. third call will go through login since tokens pair was cleared in previous step
        $client->getJson('geo/countries');

        self::assertCount(5, $container);
        self::assertEquals('/users/login', $container[0]['request']->getUri()->getPath());
        self::assertEquals('/geo/countries', $container[1]['request']->getUri()->getPath());
        self::assertEquals('/geo/countries', $container[2]['request']->getUri()->getPath());
        self::assertEquals('/users/login', $container[3]['request']->getUri()->getPath());
        self::assertEquals('/geo/countries', $container[4]['request']->getUri()->getPath());
    }
}