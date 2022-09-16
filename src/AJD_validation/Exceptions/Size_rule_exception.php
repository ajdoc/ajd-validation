<?php namespace AJD_validation\Exceptions;
use AJD_validation\Contracts\Abstract_exceptions;

class Size_rule_exception extends Abstract_exceptions
{
	const ISSTRING = 2,
		  ISARRAY = 3,
		  ISFILE = 4;

	public static $defaultMessages = [
		self::ERR_DEFAULT => [
			self::STANDARD => ':field must be {size}.',
			self::ISSTRING => ':field must be {size} characters.',
			self::ISARRAY => ':field must contain {size} items.',
			self::ISFILE => ':field must be {size} kilobytes.',
		],
		self::ERR_NEGATIVE => [
			self::STANDARD => ':field must not be {size}.',
			self::ISSTRING => ':field must not be {size} characters.',
			self::ISARRAY => ':field must not contain {size} items.',
			self::ISFILE => ':field must not be {size} kilobytes.',
		]
	];

	public static $localizeFile = 'size_rule_rule_err';

 	public function chooseMessage()
    {
        if ( $this->getParam('isString') ) 
        {
            return static::ISSTRING;
        }
        else if( $this->getParam('isArray') )
        {
        	return static::ISARRAY;
        }
        else if( $this->getParam('isFile') )
        {
        	return static::ISFILE;
        }

        return self::STANDARD;
    }
}