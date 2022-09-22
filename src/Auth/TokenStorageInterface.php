<?php


namespace multidialogo\client\Auth;


interface TokenStorageInterface
{
    /**
     * @param AuthToken $mainToken
     * @param AuthToken $refreshToken
     */
    function read(&$mainToken, &$refreshToken);

    /**
     * @param AuthToken $mainToken
     * @param AuthToken $refreshToken
     */
    function write($mainToken, $refreshToken);
}