<?php namespace AJD_validation\Exceptions;
use AJD_validation\Contracts\Abstract_exceptions;

class Multiple_rule_exception extends Abstract_exceptions
{
	 public static $defaultMessages 	= array(
        self::ERR_DEFAULT 			=> array(
            self::STANDARD 			=> ':field must be multiple of {multipleof}.',
        ),
        self::ERR_NEGATIVE 			=> array(
            self::STANDARD 			=> ':field must not be multiple of {multipleof}.',
        ),
    );

    public static $localizeFile     = 'multiple_rule_err';
}