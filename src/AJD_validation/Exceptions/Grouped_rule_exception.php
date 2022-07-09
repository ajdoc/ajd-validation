<?php namespace AJD_validation\Exceptions;

use AJD_validation\Exceptions\Nested_rule_exception;

class Grouped_rule_exception extends Nested_rule_exception
{
	const NONE = 0;
    const SOME = 1;

    public static $defaultMessages 	= array(
        self::ERR_DEFAULT 			=> array(
        	self::NONE 				=> 'All of the required rules must pass for :field.',
        	self::SOME 				=> 'These rules must pass for :field.'
        ),
        self::ERR_NEGATIVE 			=> array(
		 	self::NONE 				=> 'None of there rules must pass for :field.',
            self::SOME 				=> 'These rules must not pass for :field.',
        ),
    );

    public static $localizeFile     = 'grouped_rule_err';
}