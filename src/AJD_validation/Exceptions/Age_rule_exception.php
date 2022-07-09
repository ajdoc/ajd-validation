<?php namespace AJD_validation\Exceptions;
use AJD_validation\Contracts\Abstract_exceptions;

class Age_rule_exception extends Abstract_exceptions
{
	const BOTH 		= 0;
    const LOWER 	= 1;
    const GREATER 	= 2;

    public static $defaultMessages 	= array(
        self::ERR_DEFAULT 			=> array(
            self::BOTH 				=> ':field must be between {minAge} and {maxAge} years ago',
            self::LOWER 			=> ':field must be lower than {maxAge} years ago',
            self::GREATER 			=> ':field must be greater than {minAge} years ago',
        ),
        self::ERR_NEGATIVE 			=> array(
            self::BOTH 				=> ':field must not be between {minAge} and {maxAge} years ago',
            self::LOWER 			=> ':field must not be lower than {maxAge} years ago',
            self::GREATER 			=> ':field must not be greater than {minAge} years ago',
        ),
    );

    public static $localizeFile     = 'age_rule_err';

    public function chooseMessage()
    {
        if ( !$this->getParam('minAge') ) 
        {
            return static::LOWER;
        }

        if ( !$this->getParam('maxAge') ) 
        {
            return static::GREATER;
        }

        return static::BOTH;
    }
}