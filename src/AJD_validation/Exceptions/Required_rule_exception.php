<?php namespace AJD_validation\Exceptions;
use AJD_validation\Contracts\Abstract_exceptions;
use AJD_validation\Constants\Lang;

class Required_rule_exception extends Abstract_exceptions
{
	public static $defaultMessages = [
		 self::ERR_DEFAULT => [
		 	self::STANDARD => 'The :field field is required',
		 ],
		  self::ERR_NEGATIVE => [
            self::STANDARD => 'The :field field is not required.',
        ]
	];

	public static $localizeMessage = [
		Lang::FIL => [
			self::ERR_DEFAULT => [
			 	self::STANDARD => 'The :field field ay kelangan',
			 ],
			  self::ERR_NEGATIVE => [
        		self::STANDARD => 'The :field field ay hindi kelangan.',
	        ],
		]
	];

	public static $localizeFile = 'required_rule_err';
}