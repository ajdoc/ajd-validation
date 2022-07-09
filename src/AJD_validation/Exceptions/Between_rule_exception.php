<?php namespace AJD_validation\Exceptions;
use AJD_validation\Exceptions\Age_rule_exception;

class Between_rule_exception extends Age_rule_exception
{

    public static $defaultMessages 	= array(
        self::ERR_DEFAULT 			=> array(
            self::BOTH 				=> ':field must be between {minValue} and {maxValue}.',
            self::LOWER 			=> ':field must be greater than {minValue}.',
            self::GREATER 			=> ':field must be lower than {maxValue}.',
        ),
        self::ERR_NEGATIVE 			=> array(
            self::BOTH 				=> ':field must not be between {minValue} and {maxValue}.',
            self::LOWER 			=> ':field must not be greater than {minValue}.',
            self::GREATER 			=> ':field must not be lower than {maxValue}',
        ),
    );

    public static $localizeFile     = 'between_rule_err';

    public function chooseMessage()
    {
        if ( !$this->getParam('minValue') ) 
        {
            return static::GREATER;
        }

        if ( !$this->getParam('maxValue') ) 
        {
            return static::LOWER;
        }

        return static::BOTH;
    }
}