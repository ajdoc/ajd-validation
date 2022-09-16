<?php namespace AJD_validation\Exceptions;
use AJD_validation\Contracts\Abstract_exceptions;

class Numeric_rule_exception extends Abstract_exceptions
{
 	public static $defaultMessages = [
        self::ERR_DEFAULT => [
            self::STANDARD => ':field must be numeric.',
        ],
        self::ERR_NEGATIVE => [
            self::STANDARD => ':field must not be numeric.',
        ],
    ];

    public static $localizeFile = 'numeric_rule_err';
}