<?php


namespace multidialogo\client\Auth;


class VolatileTokenStorage implements TokenStorageInterface
{
    /**
     * @var AuthToken
     */
    private $mainToken;

    /**
     * @var AuthToken
     */
    private $refreshToken;

    function read($userName, &$mainToken, &$refreshToken)
    {
        $mainToken = $this->mainToken;
        $refreshToken = $this->refreshToken;
    }

    function write($userName, $mainToken, $refreshToken)
    {
        $this->mainToken = $mainToken;
        $this->refreshToken = $refreshToken;
    }
}