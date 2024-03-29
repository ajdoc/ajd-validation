<?php namespace AJD_validation\Exceptions;
use AJD_validation\Contracts\Abstract_exceptions;

class Identical_rule_exception extends Abstract_exceptions
{
 	public static $defaultMessages = [
        self::ERR_DEFAULT => [
            self::STANDARD => ':field must be identical as {compareto}.',
        ],
        self::ERR_NEGATIVE => [
            self::STANDARD => ':field must not be identical as {compareto}.',
        ],
    ];

    public static $localizeFile = 'equals_rule_err';
}