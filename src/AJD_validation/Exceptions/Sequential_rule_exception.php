<?php 
namespace AJD_validation\Exceptions;

use AJD_validation\Exceptions\Nested_rule_exception;

class Sequential_rule_exception extends Nested_rule_exception
{
	public static $defaultMessages 	= array(
        self::ERR_DEFAULT 			=> array(
            self::STANDARD 			=> 'The rule must pass for :field. ',
            // self::EXTRA 			=> ':field not All".',
        ),
        self::ERR_NEGATIVE 			=> array(
            self::STANDARD 			=> 'The rule must not pass for :field.',
            // self::EXTRA 			=> ':field not All".',
        ),
    );

    public static $localizeFile     = 'sequential_rule_err';
}