<?php namespace AJD_validation\Exceptions;
use AJD_validation\Exceptions\Dependent_rule_exception;

class Dependent_all_rule_exception extends Dependent_rule_exception
{
    public static $defaultMessages = [
        self::ERR_DEFAULT => [
            self::STANDARD => ':field is validated when all {fieldsDisplay} passes all the required rules.',
            self::NEEDS_COMPARING => ':field is validated when all {fieldsDisplay} passes all the required rules and is in {valueDisplay}.',
        ],
        self::ERR_NEGATIVE => [
            self::STANDARD => ':field is not validated when all {fieldsDisplay} passes all the required rules.',
            self::NEEDS_COMPARING => ':field is not validated when all {fieldsDisplay} passes all the required rules and is in {valueDisplay}.',
        ],
    ];

    public static $localizeFile = 'dependent_all_rule_err';
}