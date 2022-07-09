<?php namespace AJD_validation\Exceptions;
use AJD_validation\Contracts\Abstract_exceptions;

class Contains_rule_exception extends Abstract_exceptions
{
    public static $defaultMessages 	= array(
        self::ERR_DEFAULT 			=> array(
            self::STANDARD 			=> ':field must contain the value {haystack}.',
        ),
        self::ERR_NEGATIVE 			=> array(
         self::STANDARD             => ':field must not contain the value {haystack}.',
        ),
    );

    public static $localizeFile     = 'contains_rule_err';
}