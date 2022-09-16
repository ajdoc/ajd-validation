<?php namespace AJD_validation\Exceptions;
use AJD_validation\Exceptions\Nested_rule_exception;

class Required_with_all_rule_exception extends Nested_rule_exception
{
    public static $defaultMessages = [
        self::ERR_DEFAULT => [
            self::STANDARD => ':field is required when all {fieldsDisplay} is present.',
        ],
        self::ERR_NEGATIVE => [
            self::STANDARD => ':field is not required when all {fieldsDisplay} is present.',
        ],
    ];

    public static $localizeFile = 'required_with_all_rule_err';
}