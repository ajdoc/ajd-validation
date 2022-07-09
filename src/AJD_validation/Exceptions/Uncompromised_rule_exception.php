<?php namespace AJD_validation\Exceptions;
use AJD_validation\Contracts\Abstract_exceptions;

class Uncompromised_rule_exception extends Abstract_exceptions
{
    public static $defaultMessages     = array(
        self::ERR_DEFAULT           => array(
            self::STANDARD          => 'The :field field has appeared in a data leak.',
        ),
        self::ERR_NEGATIVE          => array(
         self::STANDARD             => 'he :field field has not appeared in a data leak.',
        ),
    );

    public static $localizeFile     = 'uncompromised_rule_er';
}