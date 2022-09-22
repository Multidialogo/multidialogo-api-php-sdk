<?php


namespace multidialogo\client\Auth;


use DateTimeImmutable;
use multidialogo\client\Common\DateTimeUtils;

class AuthToken
{
    /**
     * @var string
     */
    private $token;

    /**
     * @var DateTimeImmutable
     */
    private $expiresAt;

    /**
     * AuthToken constructor.
     * @param string $token
     * @param DateTimeImmutable $expiresAt
     */
    public function __construct($token, $expiresAt)
    {
        $this->token = $token;
        $this->expiresAt = $expiresAt;
    }

    public function isValid()
    {
        return $this->token && $this->expiresAt && $this->expiresAt > DateTimeUtils::utcNow();
    }

    /**
     * @return DateTimeImmutable
     */
    public function getExpiresAt()
    {
        return $this->expiresAt;
    }

    /**
     * @return string
     */
    public function getToken()
    {
        return $this->token;
    }
}