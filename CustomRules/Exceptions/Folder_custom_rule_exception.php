<?php 

namespace AJD_validationa\Exceptions;

use AJD_validation\Contracts\Abstract_exceptions;

class Folder_custom_rule_exception extends Abstract_exceptions
{
    public static $defaultMessages  = array(
        self::ERR_DEFAULT           => array(
            self::STANDARD          => ':field with folder is true.',
        ),
        self::ERR_NEGATIVE          => array(
         self::STANDARD             => ':field with folder is not true.',
        ),
    );

    public static $localizeFile     = 'folder_custom_rule_err';
}