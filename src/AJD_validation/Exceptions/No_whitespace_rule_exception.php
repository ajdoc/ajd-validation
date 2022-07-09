<?php namespace AJD_validation\Exceptions;
use AJD_validation\Contracts\Abstract_exceptions;

class No_whitespace_rule_exception extends Abstract_exceptions
{
	public static $defaultMessages 	= array(
		 self::ERR_DEFAULT 			=> array(
		 	self::STANDARD 			=> 'The :field must not contain whitespace.',
		 ),
		  self::ERR_NEGATIVE 		=> array(
            self::STANDARD 			=> 'The :field must contain whitespace.',
        )
	);

	public static $localizeFile 	= 'no_whitespace_rule_err';
}