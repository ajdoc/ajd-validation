<?php namespace AJD_validation\Exceptions;
use AJD_validation\Contracts\Abstract_exceptions;

class Callback_rule_exception extends Abstract_exceptions
{
    public static $defaultMessages 	= array(
        self::ERR_DEFAULT 			=> array(
            self::STANDARD 			=> ':field must be valid.',
        ),
        self::ERR_NEGATIVE 			=> array(
            self::STANDARD 			=> ':field must not be valid.',
        ),
    );

    public static $localizeFile     = 'callback_rule_err';
}