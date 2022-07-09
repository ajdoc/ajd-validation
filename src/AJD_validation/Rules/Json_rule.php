<?php namespace AJD_validation\Rules;

use AJD_validation\Contracts\Abstract_rule;
use AJD_validation\AJD_validation as v;

class Json_rule extends Abstract_rule
{
	public function __construct() 
	{
	}

	public function run( $value, $satisfier = NULL, $field = NULL )
	{
		$check 		= FALSE;

	 	if( !is_string($value) OR '' === $value ) 
	 	{
            $check 	= FALSE;
        }

        json_decode($value);

        $check 		= (json_last_error() === JSON_ERROR_NONE);

		return $check;
	}

	public function validate( $value )
	{
		$check 			= $this->run( $value );

		if( is_array( $check ) )
		{
			return $check['check'];
		}

		return $check;
	}
}