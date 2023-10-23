<?php

namespace multidialogo\client\test;

use multidialogo\client\MultidialogoClient;
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
}