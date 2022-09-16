<?php namespace AJD_validation\Exceptions;
use AJD_validation\Exceptions\Nested_rule_exception;

class All_rule_exception extends Nested_rule_exception
{
	public static $defaultMessages = [
        self::ERR_DEFAULT => [
            self::STANDARD => 'All of the required rules must pass for {field}.',
            // self::EXTRA 			=> ':field not All".',
        ],
        self::ERR_NEGATIVE => [
            self::STANDARD => 'None of these rules must pass for {field}.',
            // self::EXTRA 			=> ':field not All".',
        ],
    ];

    public static $localizeFile = 'all_rule_err';
}