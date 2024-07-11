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

It is possible to configure the client with a filesystem-based credentials store. <p>
This is achieved with this helper:
```
        ->withFileTokenStorage(CLIENT_PROVIDED_FILESYSTEM_PATH) 
```
So the full example would be:

```php
        $client = MultidialogoClient::builder()
            ->withHostUrl('https://rest-stage.multidialogo.it')
            ->withFileTokenStorage(CLIENT_PROVIDED_FILESYSTEM_PATH)
            ->withLanguage('it')
            ->build();

        $client->getJson('geo/countries');
```

CLIENT_PROVIDED_FILESYSTEM_PATH is a string specifying a folder, that will be used by the client to store the credentials.
The folder must be writable.
It is totally optional, and it allows to omit user password until it is mandatory (ie until the main or refresh token expires).

It is possibile to pass a token directly to the client.
This use case is suitable for situation where the login is managed by a frontend, that is handling the token refresh procedure and passing the token to a backend proxy.
This is done via the ``withBearerToken`` helper method.
Example:

```php
        $client = MultidialogoClient::builder()
            ->withHostUrl('https://rest-stage.multidialogo.it')
            ->withBearerToken($token)
            ->withLanguage('it')
            ->build();

        $client->getJson('geo/countries');
```


## Local development

### How to build required docker images

To build the main image (php 7.4)
```bash
docker build -f provisioning/Dockerfile-74-composer -t multidialogo-api-php-sdk-composer:latest .
```

To build the php 8.2 version image (note: it does not contain composer).
```bash
docker build -f provisioning/Dockerfile-82-cli -t multidialogo-api-php-sdk-php82:latest .
```

Keep in mind that the php82 version is intended to be used only to run unit tests, that's why composer is not included.

### Install/update/manage vendors

```bash
docker run --rm --interactive --tty -v ${PWD}/:/app multidialogo-api-php-sdk-composer:latest composer <rest of the composer command>
```

### Run unit tests

Run tests under php 7.4:
```bash
docker run --rm --interactive --tty -v ${PWD}/:/app multidialogo-api-php-sdk-composer:latest ./vendor/bin/phpunit -c .
```

Run tests under php 8.2:
```bash
docker run --rm --interactive --tty -v ${PWD}/:/app multidialogo-api-php-sdk-php82:latest ./vendor/bin/phpunit -c .
```

### Semantic versioning

Project is following semantic versioning.
Please use properly git tags before any release in master.

Example:
```bash
git tag -a v0.0.2 -m "Bugfix"
git push --follow-tags
```
