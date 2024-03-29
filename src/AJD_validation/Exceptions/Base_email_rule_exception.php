<?php 
namespace AJD_validation\Exceptions;

use AJD_validation\Contracts\Abstract_exceptions;

class Base_email_rule_exception extends Abstract_exceptions
{
	public static $defaultMessages = [
		 self::ERR_DEFAULT => [
		 	self::STANDARD => 'The :field field must be valid email.',
		 ],
		  self::ERR_NEGATIVE => [
            self::STANDARD => 'The :field field must not be valid email.',
        ]
	];

	public static $localizeFile = 'base_email_rule_err';
}