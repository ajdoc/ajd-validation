<?php namespace AJD_validation\Exceptions;
use AJD_validation\Contracts\Abstract_exceptions;

class Date_before_rule_exception extends Abstract_exceptions
{
	const INCLUSIVE = 2;	
	
    public static $defaultMessages = [
        self::ERR_DEFAULT => [
           self::STANDARD => ':field must be a date before or equal to {compareDate}.',
           self::INCLUSIVE => ':field must be a date before {compareDate}.',
        ],
        self::ERR_NEGATIVE => [
            self::STANDARD => ':field must not be a date before or equal to {compareDate}.',
            self::INCLUSIVE => ':field must not be a date before {compareDate}.',
        ],
    ];

    public static $localizeFile = 'date_before_rule_err';

 	public function chooseMessage()
    {
        return $this->getParam('inclusive') ? static::INCLUSIVE : static::STANDARD;
    }
}