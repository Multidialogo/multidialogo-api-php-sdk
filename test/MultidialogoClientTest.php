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
use multidialogo\client\Exception\MultidialogoClientException;
use multidialogo\client\MultidialogoClient;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;

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

    public function testClientPreserveServerResponseInCaseOfError()
    {
        $container = [];
        $history = Middleware::history($container);
        $mock = new MockHandler([
            new ClientException('AccessDenied', new Request('GET', 'geo/countries'), new Response(401, [], "{ some actual error message }")),
        ]);
        $handlerStack = new HandlerStack($mock);
        $handlerStack->push($history);

        $httpClient = new Client([
            'base_uri' => 'http://backend',
            'handler' => $handlerStack
        ]);

        $tokenStorage = new FileTokenStorage(__DIR__ . '/TestUtils/FileTokenStorage');

        $username = 'test_badrequest';
        $password = 'pass';

        $authProvider = new AuthProvider(
            $httpClient,
            $tokenStorage,
            $username,
            $password
        );

        $client = new MultidialogoClient($httpClient, $authProvider, []);

        $client->setLanguage('it');

        try {
            $client->getJson('geo/countries');
        } catch (MultidialogoClientException $ex) {
            self::assertTrue($ex->getResponse() instanceof ResponseInterface);
            self::assertEquals('{ some actual error message }', $ex->getResponse()->getBody()->__toString());
        }
    }
}