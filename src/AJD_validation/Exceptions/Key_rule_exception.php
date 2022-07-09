<?php namespace AJD_validation\Exceptions;
use AJD_validation\Exceptions\Attribute_rule_exception;

class Key_rule_exception extends Attribute_rule_exception
{
	public static $defaultMessages 	= array(
        self::ERR_DEFAULT 			=> array(
            self::NOT_PRESENT 		=> 'Key :field must be present.',
            self::INVALID           => 'Key :field must be valid.'
        ),
        self::ERR_NEGATIVE 			=> array(
            self::NOT_PRESENT       => 'Key :field must not be present.',
            self::INVALID           => 'Key :field must not be valid.'
        ),
    );

    public static $localizeFile     = 'key_rule_err';
}