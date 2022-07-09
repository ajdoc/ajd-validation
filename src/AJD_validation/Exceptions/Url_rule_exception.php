<?php namespace AJD_validation\Exceptions;
use AJD_validation\Contracts\Abstract_exceptions;

class Url_rule_exception extends Abstract_exceptions
{
	const HAS_SCHEME 				= 1;

    public static $defaultMessages 	= array(
        self::ERR_DEFAULT 			=> array(
            self::STANDARD 			=> ':field must be an URL.',
            self::HAS_SCHEME 		=> ':field must be an URL and must start with scheme :*'
        ),
        self::ERR_NEGATIVE 			=> array(
            self::STANDARD 			=> ':field must not be an URL.',
            self::HAS_SCHEME 		=> ':field must be not an URL and must not start with scheme :*'
        ),
    );

    public static $localizeFile     = 'url_rule_err';

    public function chooseMessage()
    {
    	if( $this->getParam('removeVeryBasic') )
    	{
    		return self::STANDARD;
   		}
    	else if( $this->getParam('schemes') ) 
        {
        	return self::HAS_SCHEME;
        }

        return self::STANDARD;
    }
}