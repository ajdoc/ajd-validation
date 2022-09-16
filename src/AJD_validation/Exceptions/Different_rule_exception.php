<?php namespace AJD_validation\Exceptions;
use AJD_validation\Contracts\Abstract_exceptions;

class Different_rule_exception extends Abstract_exceptions
{
	const IDENTICAL_DIFF = 3;

 	public static $defaultMessages = [
        self::ERR_DEFAULT => [
            self::STANDARD => ':field must not be equals to {compareTo}.',
            self::IDENTICAL_DIFF => ':field must not be identical to {compareTo}.'
        ],
        self::ERR_NEGATIVE => [
            self::STANDARD => ':field must be equals to {compareTo}.',
            self::IDENTICAL_DIFF => ':field must be identical to {compareTo}.'
        ],
    ];

    public static $localizeFile = 'different_rule_err';

    public function chooseMessage()
    {
        return $this->getParam('identical') ? static::IDENTICAL_DIFF : static::STANDARD;
    }
}