<?php

namespace multidialogo\client\Common;

use DateTimeImmutable;
use DateTimeZone;

class DateTimeUtils
{
    const DATETIME_ISO8601_UTC_FORMAT = 'Y-m-d\TH:i:s\Z';

    /** @noinspection PhpUnhandledExceptionInspection */
    public static function utcNow(): DateTimeImmutable
    {
        return new DateTimeImmutable('now', new DateTimeZone('UTC'));
    }

    /**
     * @param string $dateString
     */
    public static function fromUtcString($dateString)
    {
        return DateTimeImmutable::createFromFormat($dateString, static::DATETIME_ISO8601_UTC_FORMAT);
    }
}