<?php namespace AJD_validation\Exceptions;
use AJD_validation\Exceptions\Nested_rule_exception;

class Required_without_rule_exception extends Nested_rule_exception
{
    public static $defaultMessages 	= array(
        self::ERR_DEFAULT 			=> array(
            self::STANDARD 			=> ':field is required when either {fieldsDisplay} is not present.',
        ),
        self::ERR_NEGATIVE 			=> array(
         self::STANDARD             => ':field is not required when either {fieldsDisplay} is not present.',
        ),
    );

    public static $localizeFile     = 'required_without_rule_err';
}