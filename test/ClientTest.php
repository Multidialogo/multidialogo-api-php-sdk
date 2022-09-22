<?php

namespace multidialogo\client\test;

use multidialogo\client\MultidialogoClient;
use PHPUnit\Framework\TestCase;

/**
 * Class RestClientTest
 */
class ClientTest extends TestCase
{
    public function testRestClientShouldBuild()
    {
        // with
        $client = $this->getDefaultBuilder()->build();

        // assert
        $this->assertEquals('http://rest.multidialogo.local/api/v0.0.1/', $client->getBaseUri());
    }

    private function getDefaultBuilder()
    {
        return MultidialogoClient::builder()
            ->withHostUrl('http://rest.multidialogo.local')
            ->withPasswordCredentials('admin', 'beta_12344');
    }
}