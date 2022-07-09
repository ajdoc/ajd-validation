<?php namespace AJD_validation\Exceptions;
use AJD_validation\Exceptions\Nested_rule_exception;

class Required_with_rule_exception extends Nested_rule_exception
{
    public static $defaultMessages 	= array(
        self::ERR_DEFAULT 			=> array(
            self::STANDARD 			=> ':field is required when either {fieldsDisplay} is present.',
        ),
        self::ERR_NEGATIVE 			=> array(
         self::STANDARD             => ':field is not required when either {fieldsDisplay} is present.',
        ),
    );

    public static $localizeFile     = 'required_with_rule_err';
}