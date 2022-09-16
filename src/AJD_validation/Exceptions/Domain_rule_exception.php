<?php namespace AJD_validation\Exceptions;
use AJD_validation\Exceptions\Nested_rule_exception;

class Domain_rule_exception extends Nested_rule_exception
{
	public static $defaultMessages = [
		self::ERR_DEFAULT => [
            self::STANDARD => ':field must be a valid domain.',
        ],
        self::ERR_NEGATIVE => [
            self::STANDARD => ':field must not be a valid domain.',
        ],
	];
}