<?php namespace AJD_validation\Exceptions;
use AJD_validation\Contracts\Abstract_exceptions;

class Distinct_rule_exception extends Abstract_exceptions
{
    public static $defaultMessages 	= array(
        self::ERR_DEFAULT 			=> array(
            self::STANDARD 			=> 'The :field has a duplicate value.',
        ),
        self::ERR_NEGATIVE 			=> array(
            self::STANDARD 			=> 'The :field does\'nt has a duplicate value.',
        ),
    );

    public static $localizeFile     = 'distinct_rule_err';
}