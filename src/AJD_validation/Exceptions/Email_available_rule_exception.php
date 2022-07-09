<?php namespace AJD_validation\Exceptions;
use AJD_validation\Contracts\Abstract_exceptions;

class Email_available_rule_exception extends Abstract_exceptions
{
	public static $defaultMessages 	= array(
        self::ERR_DEFAULT 			=> array(
            self::STANDARD 			=> 'Sorry, but :field ":value" already exists.',
        ),
        self::ERR_NEGATIVE 			=> array(
            self::STANDARD 			=> 'Sorry but :field ":value" does\'t exists.',
        ),
    );

    public static $localizeFile     = 'email_available_rule_err';
}