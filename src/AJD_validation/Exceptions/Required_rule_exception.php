<?php namespace AJD_validation\Exceptions;
use AJD_validation\Contracts\Abstract_exceptions;

class Required_rule_exception extends Abstract_exceptions
{
	public static $defaultMessages 	= array(
		 self::ERR_DEFAULT 			=> array(
		 	self::STANDARD 			=> 'The :field field is required',
		 ),
		  self::ERR_NEGATIVE 		=> array(
            self::STANDARD 			=> 'The :field field is not required.',
        )
	);

	public static $localizeFile 	= 'required_rule_err';
}