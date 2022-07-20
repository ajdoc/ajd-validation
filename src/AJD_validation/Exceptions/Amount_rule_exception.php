<?php namespace AJD_validation\Exceptions;
use AJD_validation\Exceptions\Alpha_rule_exception;

class Amount_rule_exception extends Alpha_rule_exception
{
    const DECIMAL_PLACE             = 2;

	public static $defaultMessages 	= array(
        self::ERR_DEFAULT 			=> array(
            self::STANDARD 			=> ':field must be a valid amount.',
            self::DECIMAL_PLACE     => ':field must be a valid amount and must have {decimalPlace} decimal place.'
        ),
        self::ERR_NEGATIVE 			=> array(
            self::STANDARD 			=> ':field must not be a valid amount.',
            self::DECIMAL_PLACE     => ':field must not be a valid amount and must not have {decimalPlace} decimal place.'
        ),
    );

    public static $localizeFile     = 'amount_rule_err';

    public function chooseMessage()
    {
       return $this->getParam('decimalPlace') ? static::DECIMAL_PLACE : static::STANDARD;
    }
}