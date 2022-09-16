<?php namespace AJD_validation\Exceptions;
use AJD_validation\Exceptions\Nested_rule_exception;

class Required_if_rule_exception extends Nested_rule_exception
{
    public static $defaultMessages = [
        self::ERR_DEFAULT => [
            self::STANDARD => ':field is required when either {fieldsDisplay} is {valueDisplay}.',
        ],
        self::ERR_NEGATIVE => [
            self::STANDARD => ':field is not required when either {fieldsDisplay} is {valueDisplay}.',
        ],
    ];

    public static $localizeFile = 'required_if_rule_err';
}