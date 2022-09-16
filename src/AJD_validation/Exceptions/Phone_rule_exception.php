<?php namespace AJD_validation\Exceptions;
use AJD_validation\Exceptions\Alpha_rule_exception;

class Phone_rule_exception extends Alpha_rule_exception
{
	public static $defaultMessages = [
        self::ERR_DEFAULT => [
            self::STANDARD => ':field must be a valid telephone number.',
            self::EXTRA => ':field must be a valid telephone number "{additionalChars}".',
        ],
        self::ERR_NEGATIVE => [
            self::STANDARD => ':field must not be a valid telephone number.',
            self::EXTRA => ':field must not be a valid telephone number "{additionalChars}".',
        ],
    ];

    public static $localizeFile = 'phone_rule_err';     
}