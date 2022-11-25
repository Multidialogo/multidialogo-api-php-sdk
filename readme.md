# Multidialogo api php sdk

A set of classes to manage the interaction with Multidialogo API.

## Quick Start

### Installation
```bash
composer require multidialogo/api-php-sdk
```

### Use example
```php
use multidialogo\client\MultidialogoClient;

$client = MultidialogoClient::builder()
    ->withHostUrl('http://rest.multidialogo.local')
    ->withPasswordCredentials('username', 'password')
    ->withLanguage('it')
    ->build();

$response = $client->getJson('users/me', ['include' => 'profile']);

print_r($response->body);
```


## How to run unit tests in a docker environment

Install dependencies including dev ones:

```bash
docker compose run --rm multidialogo-api-php-sdk-composer composer install
```

Run phpunit test suite:

```bash
docker compose run --rm multidialogo-api-php-sdk-composer vendor/bin/phpunit .
```

