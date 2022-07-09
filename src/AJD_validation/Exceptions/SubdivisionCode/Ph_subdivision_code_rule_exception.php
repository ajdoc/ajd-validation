<?php namespace AJD_validation\Exceptions\SubdivisionCode;
use AJD_validation\Exceptions\Subdivision_code_rule_exception;

class Ph_subdivision_code_rule_exception extends Subdivision_code_rule_exception
{
 	public static $defaultMessages 	= array(
        self::ERR_DEFAULT 			=> array(
            self::STANDARD 			=> ':field must be a subdivision code of Philippines.',
        ),
        self::ERR_NEGATIVE 			=> array(
            self::STANDARD 			=> ':field must not be a subdivision code of Philippines.',
        ),
    );

    public static $localizeFile     = 'ph_subdivision_code_rule_err';
}