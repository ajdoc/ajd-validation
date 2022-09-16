<?php namespace AJD_validation\Exceptions;
use AJD_validation\Contracts\Abstract_exceptions;

class Leap_year_rule_exception extends Abstract_exceptions
{
    public static $defaultMessages = [
        self::ERR_DEFAULT => [
           self::STANDARD => ':field must be a leap year.',
        ],
        self::ERR_NEGATIVE => [
            self::STANDARD => ':field must not be a leap year.',
        ],
    ];

    public static $localizeFile = 'leap_year_rule_err';
}