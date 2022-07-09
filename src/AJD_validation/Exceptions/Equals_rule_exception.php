<?php namespace AJD_validation\Exceptions;
use AJD_validation\Contracts\Abstract_exceptions;

class Equals_rule_exception extends Abstract_exceptions
{
 	public static $defaultMessages 	= array(
        self::ERR_DEFAULT 			=> array(
            self::STANDARD 			=> ':field must be equals to {compareto}.',
        ),
        self::ERR_NEGATIVE 			=> array(
            self::STANDARD 			=> ':field must not be equals to {compareto}.',
        ),
    );

    public static $localizeFile     = 'equals_rule_err';
}