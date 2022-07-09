<?php namespace AJD_validation\Exceptions;
use AJD_validation\Exceptions\Alpha_rule_exception;

class Mobileno_rule_exception extends Alpha_rule_exception
{
	public static $defaultMessages 	= array(
        self::ERR_DEFAULT 			=> array(
            self::STANDARD 			=> ':field must be a valid mobile number.',
            self::EXTRA 			=> ':field must be a valid mobile number and "{additionalChars}".',
        ),
        self::ERR_NEGATIVE 			=> array(
            self::STANDARD 			=> ':field must not be a valid mobile number.',
            self::EXTRA 			=> ':field must be a valid mobile number and "{additionalChars}."',
        ),
    );

    public static $localizeFile     = 'mobile_no_rule_err';     
}