<?php namespace AJD_validation\Exceptions;
use AJD_validation\Contracts\Abstract_exceptions;

class Digit_count_rule_exception extends Abstract_exceptions
{
	public static $defaultMessages 	= array(
        self::ERR_DEFAULT 			=> array(
            self::STANDARD 			=> ':field must contain a number of {digitLength} digit(s).',
        ),
        self::ERR_NEGATIVE 			=> array(
            self::STANDARD 			=> ':field must not contain a number of {digitLength} digit(s).',
        ),
    );

    public static $localizeFile     = 'digit_count_rule_err';
}