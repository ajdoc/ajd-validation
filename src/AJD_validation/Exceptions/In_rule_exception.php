<?php namespace AJD_validation\Exceptions;
use AJD_validation\Contracts\Abstract_exceptions;

class In_rule_exception extends Abstract_exceptions
{
    public static $defaultMessages = [
        self::ERR_DEFAULT => [
            self::STANDARD => ':field must be in {haystack}.',
        ],
        self::ERR_NEGATIVE => [
         self::STANDARD => ':field must not be in {haystack}.',
        ],
    ];

    public static $localizeFile = 'in_rule_err';
}