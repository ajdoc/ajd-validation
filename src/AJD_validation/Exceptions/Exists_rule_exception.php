<?php namespace AJD_validation\Exceptions;
use AJD_validation\Contracts\Abstract_exceptions;

class Exists_rule_exception extends Abstract_exceptions
{
	public static $defaultMessages 	= array(
        self::ERR_DEFAULT 			=> array(
            self::STANDARD 			=> ':field doesn\'t exists in {table}.',
        ),
        self::ERR_NEGATIVE 			=> array(
            self::STANDARD 			=> ':field already exists in {table}.',
        ),
    );
	
    public static $localizeFile     = 'exists_rule_err';
}