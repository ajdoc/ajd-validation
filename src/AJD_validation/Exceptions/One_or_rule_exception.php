<?php namespace AJD_validation\Exceptions;
use AJD_validation\Exceptions\Nested_rule_exception;

class One_or_rule_exception extends Nested_rule_exception
{
    public static $defaultMessages = [
        self::ERR_DEFAULT => [
            self::STANDARD => 'At least one of these rules must pass for :field.',
        ],
        self::ERR_NEGATIVE => [
         self::STANDARD => 'At least one of these rules must not pass for :field.',
        ],
    ];

    public static $localizeFile = 'one_or_rule_err';
}