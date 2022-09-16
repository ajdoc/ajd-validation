<?php namespace AJD_validation\Exceptions;
use AJD_validation\Contracts\Abstract_exceptions;

class Ends_with_rule_exception extends Abstract_exceptions
{
 	public static $defaultMessages = [
        self::ERR_DEFAULT => [
            self::STANDARD => ':field must end with {endValue}.',
        ],
        self::ERR_NEGATIVE => [
            self::STANDARD => ':field must not end with {endValue}.',
        ],
    ];

    public static $localizeFile = 'starts_with_rule_err';
}