<?php namespace AJD_validation\Exceptions;
use AJD_validation\Exceptions\Attribute_rule_exception;

class Key_nested_rule_exception extends Attribute_rule_exception
{
	public static $defaultMessages = [
        self::ERR_DEFAULT => [
            self::NOT_PRESENT => 'No items were found for key chain :field.',
            self::INVALID => 'Key chain :field is not valid.'
        ],
        self::ERR_NEGATIVE => [
            self::NOT_PRESENT => 'Items for key chain :field must not be present.',
            self::INVALID => 'Key chain :field must not be valid.'
        ],
    ];

    public static $localizeFile = 'key_nested_rule_err';
}