<?php namespace AJD_validation\Exceptions;
use AJD_validation\Exceptions\Nested_rule_exception;

class Required_unless_rule_exception extends Nested_rule_exception
{
    public static $defaultMessages 	= array(
        self::ERR_DEFAULT 			=> array(
            self::STANDARD 			=> ':field is required when all {fieldsDisplay} is in {valueDisplay}.',
        ),
        self::ERR_NEGATIVE 			=> array(
         self::STANDARD             => ':field is not required when all {fieldsDisplay} is in {valueDisplay}.',
        ),
    );

    public static $localizeFile     = 'required_unless_rule_err';
}