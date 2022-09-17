<?php namespace AJD_validation\Exceptions;
use AJD_validation\Contracts\Abstract_exceptions;

class Date_rule_exception extends Abstract_exceptions
{
	const FORMAT = 1;

    public static $defaultMessages = [
        self::ERR_DEFAULT => [
           self::STANDARD => ':field must be a valid date',
           self::FORMAT => ':field must be a valid date. Sample format: {format}.',
        ],
        self::ERR_NEGATIVE => [
            self::STANDARD => ':field must not be a valid date',
            self::FORMAT => ':field must not be a valid date in the format: {format}.',
        ],
    ];

    public static $localizeFile = 'date_rule_err';

    public function chooseMessage()
    {
        return $this->getParam('format') ? static::FORMAT : static::STANDARD;
    }
}