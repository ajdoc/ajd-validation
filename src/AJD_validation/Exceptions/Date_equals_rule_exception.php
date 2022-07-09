<?php namespace AJD_validation\Exceptions;
use AJD_validation\Contracts\Abstract_exceptions;

class Date_equals_rule_exception extends Abstract_exceptions
{
    public static $defaultMessages 	= array(
        self::ERR_DEFAULT 			=> array(
           self::STANDARD           => ':field must be a date equal to {compareDate}.',
        ),
        self::ERR_NEGATIVE 			=> array(
            self::STANDARD          => ':field must be a date not equal to {compareDate}.',
        ),
    );

    public static $localizeFile     = 'date_equals_rule_err';
}