<?php namespace AJD_validation\Exceptions;
use AJD_validation\Contracts\Abstract_exceptions;

class Null_type_rule_exception extends Abstract_exceptions
{
 	public static $defaultMessages = [
        self::ERR_DEFAULT => [
            self::STANDARD => ':field must be null.',
        ],
        self::ERR_NEGATIVE => [
            self::STANDARD => ':field must not be null.',
        ],
    ];

    public static $localizeFile = 'null_rule_err';
}