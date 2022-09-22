<?php

use PHPUnit\Framework\AssertionFailedError;
use PHPUnit\Framework\Error\Notice;
use PHPUnit\Framework\TestSuite;

if (! interface_exists(PHPUnit_Framework_Test::class)) {
    class_alias(\PHPUnit\Framework\Test::class, PHPUnit_Framework_Test::class);
}
if (! class_exists(PHPUnit_Framework_AssertionFailedError::class)) {
    class_alias(AssertionFailedError::class, PHPUnit_Framework_AssertionFailedError::class);
}
if (! class_exists(PHPUnit_Framework_TestSuite::class)) {
    class_alias(TestSuite::class, PHPUnit_Framework_TestSuite::class);
}
if (! class_exists(\PHPUnit\Framework\Error\Error::class)) {
    class_alias(PHPUnit_Framework_Error::class, \PHPUnit\Framework\Error\Error::class);
}
if (! class_exists(Notice::class)) {
    class_alias(PHPUnit_Framework_Error_Notice::class, Notice::class);
}