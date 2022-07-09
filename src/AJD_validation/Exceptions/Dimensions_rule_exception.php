<?php namespace AJD_validation\Exceptions;
use AJD_validation\Contracts\Abstract_exceptions;

class Dimensions_rule_exception extends Abstract_exceptions
{
    public static $defaultMessages 	= array(
        self::ERR_DEFAULT 			=> array(
           self::STANDARD           => ':field has invalid dimensions.'
        ),
        self::ERR_NEGATIVE 			=> array(
            self::STANDARD          => 'field has valid dimensions.',
        ),
    );

    public static $localizeFile     = 'dimensions_rule_err';
}