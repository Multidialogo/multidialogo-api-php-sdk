<?php


namespace multidialogo\client\test;


use GuzzleHttp\Client as HttpClient;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Middleware;
use GuzzleHttp\Psr7\Response;
use multidialogo\client\Auth\AuthProvider;
use multidialogo\client\Auth\AuthToken;
use multidialogo\client\Auth\FileTokenStorage;
use multidialogo\client\Auth\VolatileTokenStorage;
use multidialogo\client\test\TestUtils\JsonResources;
use PHPUnit\Framework\TestCase;

class AuthProviderTest extends TestCase
{
    public function testVolatileCredentialStore()
    {
        // with
        $storage = new VolatileTokenStorage();
        $handler = new MockHandler([
            new Response(201, [], JsonResources::AuthOkResponse)
        ]);
        $httpClient = new HttpClient([
            'base_uri' => 'http://any.url/any/path',
            'handler' => $handler
        ]);

        $userName = 'admin';

        $provider = new AuthProvider($httpClient, $storage, $userName, 'admin');

        // do
        $tokenString = $provider->getToken();

        /** @var AuthToken $token */
        $token = null;
        /** @var AuthToken $refreshToken */
        $refreshToken = null;
        $storage->read($userName, $token, $refreshToken);

        // assert
        $this->assertEquals('25ad1beadfeb089cb5bd0ea20da8a610.0004178967cf186762382528d7e994ba081z8111551061', $tokenString);
        $this->assertEquals('25ad1beadfeb089cb5bd0ea20da8a610.0004178967cf186762382528d7e994ba081z8111551061', $token->getToken());
    }

    public function testShouldStoreCredentials()
    {
        $storageFolder = __DIR__ . '/TestUtils/FileTokenStorage';

        // clean previous test run results
        foreach (glob($storageFolder . '/*') as $file) {
            if (is_file($file)) {
                unlink($file);
            }
        }

        $storage = new FileTokenStorage($storageFolder);

        $container = [];
        $history = Middleware::history($container);

        $mock = new MockHandler([
            new Response(201, [], JsonResources::User1Okresponse),
            new Response(201, [], JsonResources::User2Okresponse),
        ]);

        $handlerStack = new HandlerStack($mock);

        $handlerStack->push($history);

        $httpClient = new HttpClient([
            'base_uri' => 'http://any.url/any/path',
            'handler' => $handlerStack
        ]);

        $user1UserName = 'user1@some_domain.com';
        $user1Password = '!@**"';
        $user2UserName = 'user2@some_OtherStrangeDomain.com';
        $user2Password = 'bWaRx9';

        // login as 'user1'
        $provider = new AuthProvider($httpClient, $storage, $user1UserName, $user1Password);

        $tokenString = $provider->getToken();

        /** @var AuthToken $token */
        $token = null;
        /** @var AuthToken $refreshToken */
        $refreshToken = null;

        $storage->read($user1UserName, $token, $refreshToken);

        $this->assertEquals('0869acb92cc97d066745cfac59cb185e.0004178967cf186762382528d7e994ba081z8111551061', $tokenString);
        $this->assertEquals('0869acb92cc97d066745cfac59cb185e.0004178967cf186762382528d7e994ba081z8111551061', $token->getToken());

        // login as 'user2'
        $provider = new AuthProvider($httpClient, $storage, $user2UserName, $user2Password);

        $tokenString = $provider->getToken();

        $storage->read($user2UserName, $token, $refreshToken);

        $this->assertEquals('a418a9118e23c0b2f9146c4c98ce2ec4.0004178967cf186762382528d7e994ba081z8111551061', $tokenString);
        $this->assertEquals('a418a9118e23c0b2f9146c4c98ce2ec4.0004178967cf186762382528d7e994ba081z8111551061', $token->getToken());

        // login again as user1 and NO password
        $provider = new AuthProvider($httpClient, $storage, $user1UserName, null);

        $tokenString = $provider->getToken();

        $storage->read($user1UserName, $token, $refreshToken);

        $this->assertEquals('0869acb92cc97d066745cfac59cb185e.0004178967cf186762382528d7e994ba081z8111551061', $tokenString);
        $this->assertEquals('0869acb92cc97d066745cfac59cb185e.0004178967cf186762382528d7e994ba081z8111551061', $token->getToken());

        // The third call should NOT be done, "user1" should be retrieved from file cache
        self::assertCount(2, $container);
    }
}