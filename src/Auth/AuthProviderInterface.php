<?php

namespace multidialogo\client\Auth;

interface AuthProviderInterface
{
    public function getToken();
}