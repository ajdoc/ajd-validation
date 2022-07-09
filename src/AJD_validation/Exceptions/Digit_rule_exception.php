<?php namespace AJD_validation\Exceptions;
use AJD_validation\Exceptions\Alpha_rule_exception;

class Digit_rule_exception extends Alpha_rule_exception
{
	public static $defaultMessages 	= array(
        self::ERR_DEFAULT 			=> array(
            self::STANDARD 			=> ':field must contain only digits (0-9).',
            self::EXTRA 			=> ':field must contain only digits (0-9) and "{additionalChars}".',
        ),
        self::ERR_NEGATIVE 			=> array(
            self::STANDARD 			=> ':field must not contain digits (0-9).',
            self::EXTRA 			=> ':field must not contain digits (0-9) and "{additionalChars}."',
        ),
    );

    public static $localizeFile     = 'digit_rule_err';
}