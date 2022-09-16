<?php namespace AJD_validation\Exceptions;
use AJD_validation\Exceptions\Alpha_rule_exception;

class Regex_rule_exception extends Alpha_rule_exception
{
	public static $defaultMessages = [
        self::ERR_DEFAULT => [
            self::STANDARD => ':field must be valid.',
            self::EXTRA => ':field must validate against "{regex}".',
        ],
        self::ERR_NEGATIVE => [
            self::STANDARD => ':field must not be valid.',
            self::EXTRA => ':field must not validate against "{regex}".',
        ],
    ];

    public static $localizeFile = 'regex_rule_err';     
}