<?php namespace AJD_validation\Exceptions;
use AJD_validation\Contracts\Abstract_exceptions;

class Mac_address_rule_exception extends Abstract_exceptions
{
    public static $defaultMessages 	= array(
        self::ERR_DEFAULT 			=> array(
            self::STANDARD 			=> ':field must be a valid mac address.',
        ),
        self::ERR_NEGATIVE 			=> array(
         self::STANDARD             => ':field must not be a valid mac address.',
        ),
    );

    public static $localizeFile     = 'mac_address_rule_err';
}