<?php namespace AJD_validation\Exceptions;
use AJD_validation\Exceptions\Attribute_rule_exception;

class Key_rule_exception extends Attribute_rule_exception
{
	public static $defaultMessages 	= [
        self::ERR_DEFAULT => [
            self::NOT_PRESENT => 'Key :field must be present.',
            self::INVALID => 'Key :field must be valid.'
        ],
        self::ERR_NEGATIVE => [
            self::NOT_PRESENT => 'Key :field must not be present.',
            self::INVALID => 'Key :field must not be valid.'
        ],
    ];

    public static $localizeFile = 'key_rule_err';
}