<?php namespace AJD_validation\Exceptions;
use AJD_validation\Contracts\Abstract_exceptions;

class Maxlength_rule_exception extends Abstract_exceptions
{
	const INCLUSIVE = 1;

    public static $defaultMessages = [
        self::ERR_DEFAULT => [
            self::STANDARD => ':field must be less than {interval}.',
            self::INCLUSIVE => ':field must be less than or equal to {interval}.',
        ],
        self::ERR_NEGATIVE => [
           self::STANDARD => ':field must not be less than {interval}.',
           self::INCLUSIVE => ':field must not be less than or equal to {interval}.',
        ],
    ];

    public static $localizeFile = 'maxlength_rule_err';

    public function chooseMessage()
    {
       return $this->getParam('inclusive') ? static::INCLUSIVE : static::STANDARD;
    }
}