<?php


namespace multidialogo\client\Auth;


class InMemoryTokenStorage implements TokenStorageInterface
{
    /**
     * @var AuthToken
     */
    private $mainToken;

    /**
     * @var AuthToken
     */
    private $refreshToken;

    function read(&$mainToken, &$refreshToken)
    {
        $mainToken = $this->mainToken;
        $refreshToken = $this->refreshToken;
    }

    function write($mainToken, $refreshToken)
    {
        $this->mainToken = $mainToken;
        $this->refreshToken = $refreshToken;
    }
}