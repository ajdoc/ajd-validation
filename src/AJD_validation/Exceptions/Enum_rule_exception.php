<?php namespace AJD_validation\Exceptions;
use AJD_validation\Contracts\Abstract_exceptions;

class Enum_rule_exception extends Abstract_exceptions
{
    public static $defaultMessages = [
        self::ERR_DEFAULT => [
            self::STANDARD => ':field is invalid.',
        ],
        self::ERR_NEGATIVE => [
         self::STANDARD => ':field is valid.',
        ],
    ];

    public static $localizeFile = 'enum_rule_err';
}