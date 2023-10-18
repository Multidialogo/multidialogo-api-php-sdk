<?php

namespace multidialogo\client\test\TestUtils;

use DateInterval;
use DateTimeImmutable;
use stdClass;

class AuthPayloadUtils
{
    public static function setExpireDatesAtDate(DateTimeImmutable $startDate, string $tokenPayload): stdClass
    {
        $tokenObj = json_decode($tokenPayload);
        $tokenObj->data->attributes->createdAt = $startDate->format('Y-m-d\TH:i:s\Z');
        $expireAt = $startDate->add(new DateInterval("PT3H"));
        $tokenObj->data->attributes->expireAt = $expireAt->format('Y-m-d\TH:i:s\Z');
        $refreshExpireAt = $startDate->add(new DateInterval("P15D"));
        $tokenObj->data->attributes->refreshTokenExpireAt = $refreshExpireAt->format('Y-m-d\TH:i:s\Z');

        return $tokenObj;
    }
}