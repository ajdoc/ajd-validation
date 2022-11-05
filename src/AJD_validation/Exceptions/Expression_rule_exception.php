<?php namespace AJD_validation\Exceptions;
use AJD_validation\Contracts\Abstract_exceptions;

class Expression_rule_exception extends Abstract_exceptions
{
	const DEBUG = 1;

	public static $defaultMessages = [
		 self::ERR_DEFAULT => [
		 	self::STANDARD => 'The :field field is invalid.',
		 	self::DEBUG => 'The :field field must pass the given expression {expression}.'
		 ],
		  self::ERR_NEGATIVE => [
		  	self::STANDARD => 'The :field field must not be valid.',
		 	self::DEBUG => 'The :field field must not pass the given expression {expression}.'
        ]
	];

	public static $localizeFile = 'expression_rule_err';

	public function chooseMessage()
    {
       return $this->getParam('debug') ? static::DEBUG : static::STANDARD;
    }
}