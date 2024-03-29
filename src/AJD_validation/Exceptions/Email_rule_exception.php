<?php namespace AJD_validation\Exceptions;
use AJD_validation\Contracts\Abstract_exceptions;

class Email_rule_exception extends Abstract_exceptions
{
	public static $defaultMessages = [
		 self::ERR_DEFAULT => [
		 	self::STANDARD => 'The :field field must be a valid email.',
		 ],
		  self::ERR_NEGATIVE => [
            self::STANDARD => 'The :field field must not be a valid email.',
        ]
	];

	public static $localizeFile = 'email_rule_err';
}