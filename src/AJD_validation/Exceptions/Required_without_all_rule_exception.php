<?php namespace AJD_validation\Exceptions;
use AJD_validation\Exceptions\Nested_rule_exception;

class Required_without_all_rule_exception extends Nested_rule_exception
{
    public static $defaultMessages = [
        self::ERR_DEFAULT => [
            self::STANDARD => ':field is required when none {fieldsDisplay} are present.',
        ],
        self::ERR_NEGATIVE => [
         self::STANDARD => ':field is not required when none {fieldsDisplay} are present.',
        ],
    ];

    public static $localizeFile = 'required_without_all_rule_err';
}