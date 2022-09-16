<?php 
namespace AJD_validation\Exceptions;

use AJD_validation\Contracts\Abstract_exceptions;

class Spoof_email_rule_exception extends Abstract_exceptions
{
	public static $defaultMessages = [
		 self::ERR_DEFAULT => [
		 	self::STANDARD => 'The :field field must not be a spoof email.',
		 ],
		  self::ERR_NEGATIVE => [
        	self::STANDARD => 'The :field field must be a spoof email.',
        ]
	];

	public static $localizeFile = 'spoof_email_rule_err';
}