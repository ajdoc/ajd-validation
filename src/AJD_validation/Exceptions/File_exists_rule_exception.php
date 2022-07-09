<?php namespace AJD_validation\Exceptions;
use AJD_validation\Contracts\Abstract_exceptions;

class File_exists_rule_exception extends Abstract_exceptions
{
    public static $defaultMessages 	= array(
        self::ERR_DEFAULT 			=> array(
           self::STANDARD           => ':field must exists.'
        ),
        self::ERR_NEGATIVE 			=> array(
            self::STANDARD          => ':field must not exists.',
        ),
    );

    public static $localizeFile     = 'file_exists_rule_err';
}