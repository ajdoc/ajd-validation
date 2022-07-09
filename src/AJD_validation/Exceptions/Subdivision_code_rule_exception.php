<?php namespace AJD_validation\Exceptions;
use AJD_validation\Contracts\Abstract_exceptions;

class Subdivision_code_rule_exception extends Abstract_exceptions
{
 	public static $defaultMessages 	= array(
        self::ERR_DEFAULT 			=> array(
            self::STANDARD 			=> ':field must be a valid subdivision code for {countryCode}.',
        ),
        self::ERR_NEGATIVE 			=> array(
            self::STANDARD 			=> ':field must not be a valid subdivision code for {countryCode}.',
        ),
    );

    public static $localizeFile     = 'subdivision_code_rule_err';
}