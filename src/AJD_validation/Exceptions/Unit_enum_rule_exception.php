<?php namespace AJD_validation\Exceptions;
use AJD_validation\Contracts\Abstract_exceptions;

class Unit_enum_rule_exception extends Abstract_exceptions
{
    public static $defaultMessages = [
        self::ERR_DEFAULT => [
            self::STANDARD => ':field is an invalid unit enum.',
        ],
        self::ERR_NEGATIVE => [
            self::STANDARD => ':field is a valid unit enum.',
        ],
    ];

    public static $localizeFile = 'enum_rule_err';
}