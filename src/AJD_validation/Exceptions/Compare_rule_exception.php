<?php namespace AJD_validation\Exceptions;
use AJD_validation\Contracts\Abstract_exceptions;

class Compare_rule_exception extends Abstract_exceptions
{
    const EQUALS    = '==',
        IDENTICAL   = '===',
        NOT_EQUALS  = '!=',
        NOT_IDENTICAL = '!==',
        NOT_EQ      = '<>',
        GREATER     = '>',
        GREATER_EQ  = '>=',
        LESS        = '<',
        LESS_EQ     = '<=';

	public static $defaultMessages 	= array(
        self::ERR_DEFAULT 			=> array(
            self::STANDARD 			=> ':field doesn\'t match {compareValue}.',
            self::EQUALS            => ':field must be equal to {compareValue}.',
            self::IDENTICAL         => ':field must be identical to {compareValue}.',
            self::NOT_EQUALS        => ':field must not be equal to {compareValue}.',
            self::NOT_IDENTICAL     => ':field must not be identical to {compareValue}.',
            self::NOT_EQ            => ':field must not be equal to {compareValue}.',
            self::GREATER           => ':field must be greater than to {compareValue}.',
            self::GREATER_EQ        => ':field must be greater than or equal to {compareValue}.',
            self::LESS              => ':field must be less than to {compareValue}.',
            self::LESS_EQ           => ':field must be less than or equal to {compareValue}.',
        ),
        self::ERR_NEGATIVE 			=> array(
            self::STANDARD 			=> ':field match {compareValue}.',
            self::EQUALS            => ':field must not be equal to {compareValue}.',
            self::IDENTICAL         => ':field must not be identical to {compareValue}.',
            self::NOT_EQUALS        => ':field must be equal to {compareValue}.',
            self::NOT_IDENTICAL     => ':field must be identical to {compareValue}.',
            self::NOT_EQ            => ':field must be equal to {compareValue}.',
            self::GREATER           => ':field must not be greater than to {compareValue}.',
            self::GREATER_EQ        => ':field must not be greater than or equal to {compareValue}.',
            self::LESS              => ':field must not be less than to {compareValue}.',
            self::LESS_EQ           => ':field must not be less than or equal to {compareValue}.',
        ),
    );
	
    public static $localizeFile     = 'compare_rule_err';

    public function chooseMessage()
    {
        $comparator     = $this->getParam('comparator');

        if( !$comparator )
        {
            return static::STANDARD;
        }
        else
        {
            return $comparator;
        }
    }
}