<?php namespace AJD_validation\Exceptions;
use AJD_validation\Contracts\Abstract_exceptions;

class Json_rule_exception extends Abstract_exceptions
{
	public static $defaultMessages 	= array(
        self::ERR_DEFAULT 			=> array(
            self::STANDARD 			=> ':field must be a valid JSON string.',
        ),
        self::ERR_NEGATIVE 			=> array(
            self::STANDARD 			=> ':field must not be a valid JSON string.',
        ),
    );

    public static $localizeFile     = 'json_rule_err';
}