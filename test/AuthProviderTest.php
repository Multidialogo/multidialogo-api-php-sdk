<?php


namespace multidialogo\client\test;


use DateInterval;
use DateTimeImmutable;
use DateTimeZone;
use GuzzleHttp\Client as HttpClient;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Middleware;
use GuzzleHttp\Psr7\Response;
use multidialogo\client\Auth\AuthProvider;
use multidialogo\client\Auth\AuthToken;
use multidialogo\client\Auth\FileTokenStorage;
use multidialogo\client\Auth\VolatileTokenStorage;
use multidialogo\client\test\TestUtils\AuthPayloadUtils;
use multidialogo\client\test\TestUtils\JsonResources;
use PHPUnit\Framework\TestCase;
use function PHPUnit\Framework\assertEquals;

class AuthProviderTest extends TestCase
{
    private string $storageFolder = __DIR__ . '/TestUtils/FileTokenStorage';

    private const USER1_OK_RESPONSE = <<<json
        {
            "status": "CREATED",
            "data": {
                "id": "25ad1beadfeb089cb5bd0ea20da8a610.0004178967cf186762382528d7e994ba081z8111551061",
                "type": "auth-tokens",
                "attributes": {
                    "token": "USER_1_OK_RESPONSE_TOKEN",
                    "category": "Bearer",
                    "createdAt": "2022-12-01T11:18:38Z",
                    "expireAt": "2022-12-01T14:18:38Z",
                    "refreshToken": "USER_1_OK_RESPONSE_REFRESH_TOKEN",
                    "refreshTokenExpireAt": "2023-12-16T11:18:38Z"
                }
            }
        }
json;

    private const USER2_OK_RESPONSE = <<<json
        {
            "status": "CREATED",
            "data": {
                "id": "25ad1beadfeb089cb5bd0ea20da8a610.0004178967cf186762382528d7e994ba081z8111551061",
                "type": "auth-tokens",
                "attributes": {
                    "token": "USER_2_OK_RESPONSE_TOKEN",
                    "category": "Bearer",
                    "createdAt": "2022-12-01T11:18:38Z",
                    "expireAt": "2022-12-01T14:18:38Z",
                    "refreshToken": "USER_2_OK_RESPONSE_REFRESH_TOKEN",
                    "refreshTokenExpireAt": "2022-12-16T11:18:38Z"
                }
            }
        }
json;

    protected function setUp(): void
    {
        parent::setUp();

        // clean previous test run results
        foreach (glob($this->storageFolder . '/*') as $file) {
            if (is_file($file)) {
                unlink($file);
            }
        }
    }

    public function testVolatileTokenStorageCanStoreCredentials()
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
        $this->assertEquals($tokenString, $token->getToken());
    }

    public function testFileTokenStorageCanStoreCredentials()
    {
        $container = [];
        $history = Middleware::history($container);

        $now = new DateTimeImmutable("now", new DateTimeZone("UTC"));
        $user1OkResponseObj = AuthPayloadUtils::setExpireDatesAtDate($now, self::USER1_OK_RESPONSE);
        $user2OkResponseObj = AuthPayloadUtils::setExpireDatesAtDate($now, self::USER2_OK_RESPONSE);

        $mock = new MockHandler([
            new Response(201, [], json_encode($user1OkResponseObj)),
            new Response(201, [], json_encode($user2OkResponseObj)),
            new Response(201, [], json_encode($user2OkResponseObj)),
        ]);

        $handlerStack = new HandlerStack($mock);

        $handlerStack->push($history);

        $httpClient = new HttpClient([
            'base_uri' => 'http://any.url/any/path',
            'handler' => $handlerStack
        ]);

        $user1UserName = 'user1@some_domain.com';
        $user1Password = '!@**"';

        $storage = new FileTokenStorage($this->storageFolder);

        // Given that we login as 'user1'
        $provider = new AuthProvider($httpClient, $storage, $user1UserName, $user1Password);

        $tokenString = $provider->getToken();

        /** @var AuthToken $token */
        $token = null;
        /** @var AuthToken $refreshToken */
        $refreshToken = null;

        $storage->read($user1UserName, $token, $refreshToken);

        $this->assertEquals($tokenString, $token->getToken());
        $this->assertEquals('USER_1_OK_RESPONSE_TOKEN', $tokenString);

        // Then we login as 'user2'
        $user2UserName = 'user2@some_OtherStrangeDomain.com';
        $user2Password = 'bWaRx9';

        $provider = new AuthProvider($httpClient, $storage, $user2UserName, $user2Password);

        $tokenString = $provider->getToken();

        $storage->read($user2UserName, $token, $refreshToken);

        $this->assertEquals($tokenString, $tokenString);
        $this->assertEquals('USER_2_OK_RESPONSE_TOKEN', $token->getToken());

        // Then we login again as user1 and NO password
        $provider = new AuthProvider($httpClient, $storage, $user1UserName, null);

        $tokenString = $provider->getToken();

        $storage->read($user1UserName, $token, $refreshToken);

        $this->assertEquals($tokenString, $token->getToken());
        $this->assertEquals('USER_1_OK_RESPONSE_TOKEN', $tokenString);

        // The third call should NOT be done, "user1" token (which is still valid) should be retrieved from file cache
        self::assertCount(2, $container);
    }

    public function testShouldCallAuthenticateWhenNoTokenAreStored()
    {
        $user1UserName = 'user1@some_domain.com';
        $user1Password = '!@**"';

        $storage = new FileTokenStorage($this->storageFolder);

        // Scenario: NO tokens are stored, authenticate call must be issued

        $container = [];
        $history = Middleware::history($container);

        $now = new DateTimeImmutable("now", new DateTimeZone("UTC"));
        $user1OkResponseObj = AuthPayloadUtils::setExpireDatesAtDate($now, self::USER1_OK_RESPONSE);

        $mock = new MockHandler([
            new Response(201, [], json_encode($user1OkResponseObj)),
        ]);

        $handlerStack = new HandlerStack($mock);

        $handlerStack->push($history);

        $httpClient = new HttpClient([
            'base_uri' => 'http://any.url/any/path',
            'handler' => $handlerStack
        ]);

        $provider = new AuthProvider($httpClient, $storage, $user1UserName, $user1Password);

        $actual = $provider->getToken();

        self::assertEquals("USER_1_OK_RESPONSE_TOKEN", $actual);

        // Call to refresh token is needed here
        self::assertCount(1, $container);
        assertEquals('/any/users/login', $container[0]['request']->getUri()->getPath());
    }

    public function testShouldCallRefreshTokenWhenStoredAuthIsExpiredAndRefreshIsValid()
    {
        $user1UserName = 'user1@some_domain.com';
        $user1Password = '!@**"';

        $storage = new FileTokenStorage($this->storageFolder);

        // Scenario: an auth token is stored already, but it's expired. The refresh token must be used
        $pastMoment = (new DateTimeImmutable("now", new DateTimeZone("UTC")))->sub(new DateInterval("PT240M"));
        $futureMoment = (new DateTimeImmutable("now", new DateTimeZone("UTC")))->add(new DateInterval("PT240M"));
        $storage->write($user1UserName,
            new AuthToken("USER1_START_EXPIRED_AUTH_TOKEN", $pastMoment),
            new AuthToken("USER1_START_VALID_REFRESH_TOKEN", $futureMoment)
        );

        $container = [];
        $history = Middleware::history($container);

        $now = new DateTimeImmutable("now", new DateTimeZone("UTC"));

        $user1OkResponseObj = AuthPayloadUtils::setExpireDatesAtDate($now, self::USER1_OK_RESPONSE);

        $mock = new MockHandler([
            new Response(201, [], json_encode($user1OkResponseObj)),
        ]);

        $handlerStack = new HandlerStack($mock);

        $handlerStack->push($history);

        $httpClient = new HttpClient([
            'base_uri' => 'http://any.url/any/path',
            'handler' => $handlerStack
        ]);

        $provider = new AuthProvider($httpClient, $storage, $user1UserName, $user1Password);

        $actual = $provider->getToken();

        self::assertEquals("USER_1_OK_RESPONSE_TOKEN", $actual);

        // Should have called login/refresh
        self::assertCount(1, $container);
        assertEquals('/any/users/login/refresh', $container[0]['request']->getUri()->getPath());
    }

    public function testShouldCallAuthenticateWhenBothStoredAuthAndRefreshAreExpired()
    {
        $user1UserName = 'user1@some_domain.com';
        $user1Password = '!@**"';

        $storage = new FileTokenStorage($this->storageFolder);

        // Scenario: the stored auth and refresh are both expired
        $pastMoment = (new DateTimeImmutable("now", new DateTimeZone("UTC")))->sub(new DateInterval("PT240M"));
        $storage->write($user1UserName,
            new AuthToken("USER1_START_EXPIRED_AUTH_TOKEN", $pastMoment),
            new AuthToken("USER1_START_EXPIRED_REFRESH_TOKEN", $pastMoment)
        );

        $container = [];
        $history = Middleware::history($container);

        $now = new DateTimeImmutable("now", new DateTimeZone("UTC"));
        $user1OkResponseObj = AuthPayloadUtils::setExpireDatesAtDate($now, self::USER1_OK_RESPONSE);

        $mock = new MockHandler([
            new Response(201, [], json_encode($user1OkResponseObj)),
        ]);

        $handlerStack = new HandlerStack($mock);

        $handlerStack->push($history);

        $httpClient = new HttpClient([
            'base_uri' => 'http://any.url/any/path',
            'handler' => $handlerStack
        ]);

        $provider = new AuthProvider($httpClient, $storage, $user1UserName, $user1Password);

        $actual = $provider->getToken();

        self::assertEquals("USER_1_OK_RESPONSE_TOKEN", $actual);

        // Call to refresh token is needed here
        self::assertCount(1, $container);
        assertEquals('/any/users/login', $container[0]['request']->getUri()->getPath());
    }

}