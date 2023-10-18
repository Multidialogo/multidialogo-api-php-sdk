<?php

namespace multidialogo\client\Auth;

class TokenWrapper implements AuthProviderInterface
{
    private $token;

    public function __construct($token)
    {
        $this->token = $token;
    }

    /**
     * @return mixed
     */
    public function getToken()
    {
        return $this->token;
    }

    public function reset()
    {
        $this->token = null;
    }
}