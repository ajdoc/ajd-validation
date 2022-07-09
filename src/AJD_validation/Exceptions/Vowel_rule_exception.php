<?php namespace AJD_validation\Exceptions;
use AJD_validation\Exceptions\Alpha_rule_exception;

class Vowel_rule_exception extends Alpha_rule_exception
{
	public static $defaultMessages 	= array(
        self::ERR_DEFAULT 			=> array(
            self::STANDARD 			=> ':field must contain only vowels.',
            self::EXTRA 			=> ':field must contain only vowels and "{additionalChars}".',
        ),
        self::ERR_NEGATIVE 			=> array(
            self::STANDARD 			=> ':field must not contain vowels.',
            self::EXTRA 			=> ':field must not contain vowels and "{additionalChars}."',
        ),
    );

    public static $localizeFile     = 'vowel_rule_err';     
}