<?php 

namespace AJD_validation\Exceptions;
use AJD_validation\Contracts\Abstract_exceptions;

class File_rule_exception extends Abstract_exceptions
{
    public static $defaultMessages = [
        self::ERR_DEFAULT => [
           self::STANDARD => ':field must be a file.'
        ],
        self::ERR_NEGATIVE => [
            self::STANDARD => ':field must not be a file.',
        ],
    ];

    public static $localizeFile = 'file_rule_err';
}