<?php 
namespace AJD_validation\Exceptions;

use AJD_validation\Contracts\Abstract_exceptions;

class Dns_email_rule_exception extends Abstract_exceptions
{
	public static $defaultMessages = [
		 self::ERR_DEFAULT => [
		 	self::STANDARD => 'The :field field must have a valid dns email.',
		 ],
		  self::ERR_NEGATIVE => [
            self::STANDARD => 'The :field field must not have a valid dns email.',
        ]
	];

	public static $localizeFile = 'dns_email_rule_err';
}