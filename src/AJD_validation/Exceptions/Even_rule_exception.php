<?php namespace AJD_validation\Exceptions;
use AJD_validation\Contracts\Abstract_exceptions;

class Even_rule_exception extends Abstract_exceptions
{
	public static $defaultMessages = [
		self::ERR_DEFAULT => [
			self::STANDARD => ':field must be an even number.'
		],
		self::ERR_NEGATIVE => [
			self::STANDARD => ':field must not be an even number.'
		],
	];

	public static $localizeFile = 'even_rule_err';
}