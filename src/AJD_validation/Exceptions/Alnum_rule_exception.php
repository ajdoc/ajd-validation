<?php namespace AJD_validation\Exceptions;
use AJD_validation\Exceptions\Alpha_rule_exception;

class Alnum_rule_exception extends Alpha_rule_exception
{
	public static $defaultMessages 	= array(
        self::ERR_DEFAULT 			=> array(
            self::STANDARD 			=> ':field must contain only letters (a-z) and digits (0-9).',
            self::EXTRA 			=> ':field must contain only letters (a-z), digits (0-9) and "{additionalChars}".',
        ),
        self::ERR_NEGATIVE 			=> array(
            self::STANDARD 			=> ':field must not contain letters (a-z) and digits (0-9).',
            self::EXTRA 			=> ':field must not contain letters (a-z), digits (0-9) and "{additionalChars}".',
        ),
    );

    public static $localizeFile     = 'alnum_rule_err';
}