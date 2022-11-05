<?php namespace AJD_validation\Exceptions;
use AJD_validation\Contracts\Abstract_exceptions;

class Extend_rule_exception extends Abstract_exceptions
{
	public static $defaultMessages = [
		 self::ERR_DEFAULT => [
		 	self::STANDARD => 'The :field failed.',
		 ],
		  self::ERR_NEGATIVE => [
            self::STANDARD => 'The :field passed.',
        ]
	];

	public static $localizeFile = 'extend_rule_err';
}