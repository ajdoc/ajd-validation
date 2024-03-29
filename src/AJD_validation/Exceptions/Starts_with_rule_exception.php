<?php namespace AJD_validation\Exceptions;
use AJD_validation\Contracts\Abstract_exceptions;

class Starts_with_rule_exception extends Abstract_exceptions
{
 	public static $defaultMessages = [
        self::ERR_DEFAULT => [
            self::STANDARD => ':field must start with {startValue}.',
        ],
        self::ERR_NEGATIVE => [
            self::STANDARD => ':field must not start with {startValue}.',
        ],
    ];

    public static $localizeFile = 'starts_with_rule_err';
}