<?php

namespace multidialogo\client\Auth;

use multidialogo\client\Common\DateTimeUtils;
use stdClass;

class FileTokenStorage implements TokenStorageInterface
{
    private const FILE_SUFFIX = '_ftkstrg';

    /**
     * @var string $basePath
     */
    private $basePath;

    /**
     * @inheritDoc
     */
    function read($userName, &$mainToken, &$refreshToken)
    {
        $fileName = $this->getFileName($userName);

        if (file_exists($fileName)) {
            $payload = json_decode(file_get_contents($fileName));

            $mainToken = new AuthToken(
                $payload->token,
                DateTimeUtils::fromUtcString($payload->expireAt)
            );

            $refreshToken = new AuthToken(
                $payload->refreshToken,
                DateTimeUtils::fromUtcString($payload->refreshTokenExpireAt)
            );

        } else {
            $mainToken = null;
            $refreshToken = null;
        }
    }

    /**
     * @inheritDoc
     */
    function write($userName, $mainToken, $refreshToken)
    {
        $fileName = $this->getFileName($userName);

        $payload = new stdClass();
        $payload->token = $mainToken->getToken();
        $payload->expireAt = DateTimeUtils::toUtcString($mainToken->getExpiresAt());

        $payload->refreshToken = $refreshToken->getToken();
        $payload->refreshTokenExpireAt = DateTimeUtils::toUtcString($refreshToken->getExpiresAt());

        file_put_contents($fileName, json_encode($payload));
    }

    /**
     * @param string $userName
     * @return string
     */
    protected function getFileName(string $userName): string
    {
        return $this->basePath . "/$userName" . static::FILE_SUFFIX;
    }

    /**
     * @param string $basePath
     */
    public function __construct($basePath)
    {
        $this->basePath = $basePath;
    }


    function reset($userName)
    {
        $fileName = $this->getFileName($userName);

        unlink($fileName);
    }
}