<?php

namespace multidialogo\client\test;

use multidialogo\client\MultidialogoClient;
use PHPUnit\Framework\TestCase;

class MultidialogoClientBuilderTest extends TestCase
{
    public function testItShouldBuildAClientWithNoCredentials()
    {
        $actual = MultidialogoClient::builder()
            ->withHostUrl('http://rest.multidialogo.local')
            ->withBearerToken('token')
            ->withLanguage('it')
            ->build();

        self::assertNotNull($actual);
    }
}