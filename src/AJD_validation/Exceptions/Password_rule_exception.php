<?php 
namespace AJD_validation\Exceptions;

use AJD_validation\Exceptions\Nested_rule_exception;

class Password_rule_exception extends Nested_rule_exception
{
	public static $defaultMessages 	= array(
        self::ERR_DEFAULT 			=> array(
            self::STANDARD 			=> 'All of the rules must pass for :field.',
            // self::EXTRA 			=> ':field not All".',
        ),
        self::ERR_NEGATIVE 			=> array(
            self::STANDARD 			=> 'None of these rules must pass for :field.',
            // self::EXTRA 			=> ':field not All".',
        ),
    );

    public static $localizeFile     = 'password_rule_err';
}