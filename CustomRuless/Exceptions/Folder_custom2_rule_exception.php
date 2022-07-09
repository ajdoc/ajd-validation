<?php 

namespace AJD_validation\Exceptions;

use AJD_validation\Contracts\Abstract_exceptions;

class Folder_custom2_rule_exception extends Abstract_exceptions
{
    public static $defaultMessages  = array(
        self::ERR_DEFAULT           => array(
            self::STANDARD          => ':field with folder 2 is true.',
        ),
        self::ERR_NEGATIVE          => array(
         self::STANDARD             => ':field with folder 2 is not true.',
        ),
    );

    public static $localizeFile     = 'folder_custom2_rule_err';
}