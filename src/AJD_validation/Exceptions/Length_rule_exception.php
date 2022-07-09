<?php namespace AJD_validation\Exceptions;
use AJD_validation\Contracts\Abstract_exceptions;

class Length_rule_exception extends Abstract_exceptions
{
	const BOTH 		= 0;
    const LOWER 	= 1;
    const GREATER 	= 2;

    public static $defaultMessages 	= array(
        self::ERR_DEFAULT 			=> array(
            self::BOTH 				=> ':field must have a length between {minValue} and {maxValue}.',
            self::LOWER 			=> ':field must have a length greater than {minValue}.',
            self::GREATER 			=> ':field must have a length lower than {maxValue}.',
        ),
        self::ERR_NEGATIVE 			=> array(
         	self::BOTH 				=> ':field must not have a length between {minValue} and {maxValue}.',
            self::LOWER 			=> ':field must not have a length greater than {minValue}.',
            self::GREATER 			=> ':field must not have a length lower than {maxValue}.',
        ),
    );

    public static $localizeFile     = 'length_rule_err';

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