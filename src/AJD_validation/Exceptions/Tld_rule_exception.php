<?php namespace AJD_validation\Exceptions;
use AJD_validation\Contracts\Abstract_exceptions;

class Tld_rule_exception extends Abstract_exceptions
{
 	public static $defaultMessages 	= array(
        self::ERR_DEFAULT 			=> array(
            self::STANDARD 			=> ':field must be a valid top-level domain name.',
        ),
        self::ERR_NEGATIVE 			=> array(
            self::STANDARD 			=> ':field must not be a valid top-level domain name.',
        ),
    );

    public static $localizeFile     = 'tld_rule_err';
}