<?php namespace AJD_validation\Exceptions;
use AJD_validation\Exceptions\Alpha_rule_exception;

class Consonant_rule_exception extends Alpha_rule_exception
{
	public static $defaultMessages 	= array(
        self::ERR_DEFAULT 			=> array(
            self::STANDARD 			=> ':field must contain only consonants.',
            self::EXTRA 			=> ':field must contain only consonants and "{additionalChars}".',
        ),
        self::ERR_NEGATIVE 			=> array(
            self::STANDARD 			=> ':field must not contain consonants.',
            self::EXTRA 			=> ':field must not contain consonants and "{additionalChars}."',
        ),
    );

    public static $localizeFile     = 'consonant_rule_err';     
}