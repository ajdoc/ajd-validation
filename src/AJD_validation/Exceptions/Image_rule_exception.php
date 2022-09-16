<?php namespace AJD_validation\Exceptions;
use AJD_validation\Contracts\Abstract_exceptions;

class Image_rule_exception extends Abstract_exceptions
{
    public static $defaultMessages = [
        self::ERR_DEFAULT => [
           self::STANDARD => ':field must be a valid image.'
        ],
        self::ERR_NEGATIVE => [
            self::STANDARD => ':field must not be a valid image.',
        ],
    ];

    public static $localizeFile = 'image_rule_err';
}