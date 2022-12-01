<?php


namespace multidialogo\client\Auth;


interface TokenStorageInterface
{
    /**
     * @param string $userName
     * @param AuthToken $mainToken
     * @param AuthToken $refreshToken
     */
    function read($userName, &$mainToken, &$refreshToken);

    /**
     * @param string $userName
     * @param AuthToken $mainToken
     * @param AuthToken $refreshToken
     */
    function write($userName, $mainToken, $refreshToken);
}