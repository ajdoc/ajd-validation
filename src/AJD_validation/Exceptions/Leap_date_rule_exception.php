<?php namespace AJD_validation\Exceptions;
use AJD_validation\Contracts\Abstract_exceptions;

class Leap_date_rule_exception extends Abstract_exceptions
{
    public static $defaultMessages = [
        self::ERR_DEFAULT => [
           self::STANDARD => ':field must be a leap date.',
        ],
        self::ERR_NEGATIVE => [
            self::STANDARD => ':field must not be a leap date.',
        ],
    ];

    public static $localizeFile = 'leap_date_rule_err';
}